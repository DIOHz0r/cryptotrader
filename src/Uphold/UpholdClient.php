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

namespace App\Uphold;


use App\Exception\AuthenticationRequiredException;
use App\HttpClient\HttpClient;
use App\HttpClient\HttpClientInterface;

class UpholdClient
{
    /**
     * Uphold API urls.
     */
    const UPHOLD_API_URL = 'https://api.uphold.com';
    const UPHOLD_SANDBOX_API_URL = 'https://api-sandbox.uphold.com';
    protected $options = [];

    /**
     * Guzzle instance used to communicate with Uphold.
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Constructor.
     *
     * @param array $options UpholdClient options.
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['base_url'])) {
            $options['base_url'] = isset($options['sandbox']) && $options['sandbox'] ? self::UPHOLD_SANDBOX_API_URL : self::UPHOLD_API_URL;
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
     * @return UpholdClient
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

    /**
     * Send a GET request with query parameters.
     *
     * @param string $path Request path.
     * @param array $parameters GET parameters.
     * @param array $requestHeaders Request Headers.
     *
     * @return \GuzzleHttp\EntityBodyInterface|mixed|string
     */
    public function get($path, array $parameters = [], $requestHeaders = [])
    {
        return $this->getHttpClient()->get(
            $this->buildPath($path),
            $parameters,
            array_merge($this->getDefaultHeaders(), $requestHeaders)
        );
    }

    /**
     * Send a POST request with JSON-encoded parameters.
     *
     * @param string $path Request path.
     * @param array $parameters POST parameters to be JSON encoded.
     * @param array $requestHeaders Request headers.
     *
     * @return \GuzzleHttp\EntityBodyInterface|mixed|string
     */
    public function post($path, array $parameters = [], $requestHeaders = [])
    {
        return $this->getHttpClient()->post(
            $this->buildPath($path),
            $this->createJsonBody($parameters),
            array_merge($this->getDefaultHeaders(), $requestHeaders)
        );
    }

    /**
     * Send a PATCH request with JSON-encoded parameters.
     *
     * @param string $path Request path.
     * @param array $parameters POST parameters to be JSON encoded.
     * @param array $requestHeaders Request headers.
     *
     * @return \GuzzleHttp\EntityBodyInterface|mixed|string
     */
    public function patch($path, array $parameters = [], $requestHeaders = [])
    {
        return $this->getHttpClient()->patch(
            $this->buildPath($path),
            $this->createJsonBody($parameters),
            array_merge($this->getDefaultHeaders(), $requestHeaders)
        );
    }

    /**
     * Send a PUT request with JSON-encoded parameters.
     *
     * @param string $path Request path.
     * @param array $parameters POST parameters to be JSON encoded.
     * @param array $requestHeaders Request headers.
     *
     * @return \GuzzleHttp\EntityBodyInterface|mixed|string
     */
    public function put($path, array $parameters = [], $requestHeaders = [])
    {
        return $this->getHttpClient()->put(
            $this->buildPath($path),
            $this->createJsonBody($parameters),
            array_merge($this->getDefaultHeaders(), $requestHeaders)
        );
    }

    /**
     * Send a DELETE request with JSON-encoded parameters.
     *
     * @param string $path Request path.
     * @param array $parameters POST parameters to be JSON encoded.
     * @param array $requestHeaders Request headers.
     *
     * @return \GuzzleHttp\EntityBodyInterface|mixed|string
     */
    public function delete($path, array $parameters = [], $requestHeaders = [])
    {
        return $this->getHttpClient()->delete(
            $this->buildPath($path),
            $this->createJsonBody($parameters),
            array_merge($this->getDefaultHeaders(), $requestHeaders)
        );
    }

    /**
     * Build the API path that includes the API version.
     *
     * @param string $path The path to append to the base URL.
     *
     * @return string
     */
    protected function buildPath($path)
    {
        if (empty($this->options['api_version'])) {
            return $path;
        }

        return sprintf('%s%s', $this->options['api_version'], $path);
    }

    /**
     * Create a JSON encoded version of an array of parameters.
     *
     * @param array $parameters Request parameters
     *
     * @return null|string
     */
    protected function createJsonBody(array $parameters)
    {
        $options = 0;
        if (empty($parameters)) {
            $options = JSON_FORCE_OBJECT;
        }

        return json_encode($parameters, $options);
    }

    /**
     * Create the API default headers that are mandatory.
     *
     * @return array
     */
    protected function getDefaultHeaders()
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => str_replace(
                '{version}',
                sprintf('v%s', $this->getOption('version')),
                $this->getOption('user_agent')
            ),
        ];
        if (null !== $this->getOption('bearer') && '' !== $this->getOption('bearer')) {
            $headers['Authorization'] = sprintf('Bearer %s', $this->getOption('bearer'));
        }

        return $headers;
    }
}