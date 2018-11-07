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

namespace App\HttpClient\Handler;

use App\Exception\ConnectException;
use App\Exception\LogicException;
use App\Exception\RuntimeException;
use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use GuzzleHttp\Exception\RequestException;

class ErrorHandler
{
    /**
     * Handler options.
     *
     * @var array
     */
    private $options;

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Handles different types of exceptions.
     *
     * @param \Exception $e The exception.
     *
     * @return string
     * @throws ConnectException
     * @throws LogicException
     * @throws RuntimeException
     */
    public function onException(\Exception $e)
    {
        if ($e instanceOf GuzzleConnectException) {
            throw new ConnectException($e->getMessage());
        }
        if ($e instanceOf RequestException) {
            return $this->onRequestException($e);
        }
        if ($e instanceOf \LogicException) {
            throw new LogicException($e->getMessage(), $e->getCode());
        }
        throw new RuntimeException($e->getMessage(), $e->getCode());
    }

    /**
     * Handles a Request Exception.
     *
     * @param RequestException $e The request exception.
     *
     * @return string
     * @throws RuntimeException
     */
    protected function onRequestException(RequestException $e)
    {
        $response = $e->getResponse();
        if (!$response) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        return 'HTTP Code: '.$response->getStatusCode()."\n".'Message: '.$response->getBody()->getContents()."\n";
    }

}