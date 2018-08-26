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


/**
 * Performs requests on an API.
 */
interface HttpClientInterface
{
    /**
     * Send a GET request.
     * @param $path
     * @param array $parameters
     * @param array $headers
     */
    public function get($path, array $parameters = array(), array $headers = array());

    /**
     * Send a POST request.
     * @param $path
     * @param null $body
     * @param array $headers
     */
    public function post($path, $body = null, array $headers = array());

    /**
     * Send a PATCH request.
     * @param $path
     * @param null $body
     * @param array $headers
     */
    public function patch($path, $body = null, array $headers = array());

    /**
     * Send a PUT request.
     * @param $path
     * @param $body
     * @param array $headers
     */
    public function put($path, $body, array $headers = array());

    /**
     * Send a DELETE request.
     * @param $path
     * @param null $body
     * @param array $headers
     */
    public function delete($path, $body = null, array $headers = array());

    /**
     * Send a request to the server, receive a response,
     * decode the response and returns an associative array.
     * @param $path
     * @param $body
     * @param string $httpMethod
     * @param array $headers
     */
    public function request($path, $body, $httpMethod = 'GET', array $headers = array());

    /**
     * Change an option value.
     * @param $name
     * @param $value
     */
    public function setOption($name, $value);
}