<?php
/**
 * Created by Domingo Oropeza for cryptocompare
 * Date: 17/04/2018
 * Time: 12:20 AM
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