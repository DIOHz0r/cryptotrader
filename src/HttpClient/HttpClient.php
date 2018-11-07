<?php
/**
 * cryptotrader
 * Copyright (C) 2018 Domingo Oropeza
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\HttpClient;

use App\HttpClient\Handler\ErrorHandler;
use GuzzleHttp\Client as GuzzleClient;


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
    protected $options = [];

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge(
            $this->options,
            //array('message_factory' => new MessageFactory()),
            $options
        );
        $this->client = new GuzzleClient($this->options);
        $this->errorHandler = new ErrorHandler($this->options);
    }

    public function get($path, array $parameters = [], array $headers = [])
    {
        return $this->request($path, null, 'GET', $headers);
    }

    public function post($path, $body = null, array $headers = [])
    {
        return $this->request($path, $body, 'POST', $headers);
    }

    public function patch($path, $body = null, array $headers = [])
    {
        return $this->request($path, $body, 'PATCH', $headers);
    }

    public function put($path, $body, array $headers = [])
    {
        return $this->request($path, $body, 'PUT', $headers);
    }

    public function delete($path, $body = null, array $headers = [])
    {
        return $this->request($path, $body, 'DELETE', $headers);
    }

    public function request($path, $body, $httpMethod = 'GET', array $headers = [])
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