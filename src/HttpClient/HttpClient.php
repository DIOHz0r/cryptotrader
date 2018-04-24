<?php
/**
 * Created by Domingo Oropeza for cryptocompare
 * Date: 17/04/2018
 * Time: 12:22 AM
 */

namespace App\HttpClient;

use App\HttpClient\Handler\ErrorHandler;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class HttpClient implements HttpClientInterface
{
    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * Error handler.
     *
     * @var ErrorHandler
     */
    protected $errorHandler;

    /**
     * @var $options
     */
    protected $options = array();

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = array_merge(
            $this->options,
            //array('message_factory' => new MessageFactory()),
            $options
        );
        $this->client = new GuzzleClient($this->options);
        $this->errorHandler = new ErrorHandler($this->options);
    }

    public function get($path, array $parameters = array(), array $headers = array())
    {
        return $this->request($path, null, 'GET', $headers);
    }

    public function post($path, $body = null, array $headers = array())
    {
        return $this->request($path, $body, 'POST', $headers);
    }

    public function patch($path, $body = null, array $headers = array())
    {
        return $this->request($path, $body, 'PATCH', $headers);
    }

    public function put($path, $body, array $headers = array())
    {
        return $this->request($path, $body, 'PUT', $headers);
    }

    public function delete($path, $body = null, array $headers = array())
    {
        return $this->request($path, $body, 'DELETE', $headers);
    }

    public function request($path, $body, $httpMethod = 'GET', array $headers = array())
    {
        if (!empty($this->options['debug'])) {
            $options['debug'] = $this->options['debug'];
        }
        if (count($headers) > 0) {
            $options['headers'] = $headers;
        }
        $options['body'] = $body;
        try {
            $response = $this->client->request($httpMethod, $path, $options);
        } catch (\Exception $e) {
            $this->errorHandler->onException($e);
        }

        return $response;
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function getOption($name)
    {
        return $this->options[$name];
    }
}