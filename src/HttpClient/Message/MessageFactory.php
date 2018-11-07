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
    public function createResponse($statusCode, array $headers = [], $body = null, array $options = [])
    {
        if (null !== $body) {
            $body = Stream::create($body);
        }

        return new Response($statusCode, $headers, $body, $options);
    }
}