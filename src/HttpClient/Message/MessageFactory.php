<?php
/**
 * Created by Domingo Oropeza for cryptocompare
 * Date: 17/04/2018
 * Time: 12:38 AM
 */

namespace App\HttpClient\Message;

use GuzzleHttp\Psr7\Stream;

class MessageFactory
{
    /**
     * Create new response.
     *
     * @param int $statusCode Response status code.
     * @param array $headers Response headers.
     * @param mixed $body Response body.
     * @param array $options Options.
     *
     * @return Response
     */
    public function createResponse($statusCode, array $headers = array(), $body = null, array $options = array())
    {
        if (null !== $body) {
            $body = Stream::create($body);
        }

        return new Response($statusCode, $headers, $body, $options);
    }
}