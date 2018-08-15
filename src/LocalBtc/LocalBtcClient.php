<?php
/**
 * Created by Domingo Oropeza for cryptocompare
 * Date: 23/04/2018
 * Time: 10:28 PM
 */

namespace App\LocalBtc;

use App\Exception\AuthenticationRequiredException;
use App\HttpClient\HttpClient;
use App\HttpClient\HttpClientInterface;

class LocalBtcClient
{
    /**
     * API urls.
     */
    const API_URL = 'https://localbitcoins.com';
    const SANDBOX_API_URL = 'https://localbitcoins.com';

    /**
     * Guzzle instance used to communicate with Localbitcoin.
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Constructor.
     *
     * @param array $options LocalbitcoinClient options.
     */
    public function __construct(array $options = array())
    {
        if (!isset($options['base_url'])) {
            $options['base_url'] = isset($options['sandbox']) && $options['sandbox'] ? self::SANDBOX_API_URL : self::API_URL;
        }
        $this->options = array_merge($this->options, $options);
        $this->setHttpClient(new HttpClient($this->options));
    }

    /**
     * Get Http client.
     *
     * @return HttpClientInterface $httpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Get client option.
     *
     * @param string $name Option name.
     *
     * @return mixed
     */
    public function getOption($name)
    {
        if (!isset($this->options[$name])) {
            return null;
        }
        return $this->options[$name];
    }

    /**
     * Sets client option.
     *
     * @param string $name Option name.
     * @param mixed $value Option value.
     * @return $this
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }
    /**
     * Get all client options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}