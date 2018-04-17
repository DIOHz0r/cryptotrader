<?php
/**
 * Created by Domingo Oropeza for cryptocompare
 * Date: 17/04/2018
 * Time: 12:24 AM
 */

namespace App\HttpClient\Handler;

use App\Exception\ApiLimitExceedException;
use App\Exception\AuthenticationRequiredException;
use App\Exception\BadRequestException;
use App\Exception\ConnectException;
use App\Exception\LogicException;
use App\Exception\NotFoundException;
use App\Exception\RuntimeException;
use App\Exception\TwoFactorAuthenticationRequiredException;
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
    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * Handles different types of exceptions.
     *
     * @param \Exception $e The exception.
     *
     * @throws ApiLimitExceedException
     * @throws AuthenticationRequiredException
     * @throws BadRequestException
     * @throws ConnectException
     * @throws LogicException
     * @throws NotFoundException
     * @throws RuntimeException
     * @throws TwoFactorAuthenticationRequiredException
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
     * @throws ApiLimitExceedException
     * @throws AuthenticationRequiredException
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws RuntimeException
     * @throws TwoFactorAuthenticationRequiredException
     */
    protected function onRequestException(RequestException $e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();
        if (!$response) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }
        $statusCode = $response->getStatusCode();
        $isClientError = $response->isClientError();
        $isServerError = $response->isServerError();
        if ($isClientError || $isServerError) {
            $content = $response->getContent();
            $error = $response->getError();
            $description = $response->getErrorDescription();
            if (400 === $statusCode) {
                throw new BadRequestException($description, $error, $statusCode, $response, $request);
            }
            if (401 === $statusCode) {
                $otp = (string)$response->getHeader('OTP-Token');
                if ('required' === $otp) {
                    $description = 'Two factor authentication is enabled on this account';
                    throw new TwoFactorAuthenticationRequiredException(
                        $description,
                        $error,
                        $statusCode,
                        $response,
                        $request
                    );
                }
                throw new AuthenticationRequiredException($description, $error, $statusCode, $response, $request);
            }
            if (404 === $statusCode) {
                $description = sprintf('Object or route not found: %s', $request->getPath());
                throw new NotFoundException($description, 'not_found', $statusCode, $response, $request);
            }
            if (429 === $statusCode) {
                $rateLimit = $response->getApiRateLimit();
                $description = sprintf(
                    'You have reached the API limit. API limit is: %s. Your remaining requests will be reset at %s.',
                    $rateLimit['limit'],
                    date('Y-m-d H:i:s', $rateLimit['reset'])
                );
                throw new ApiLimitExceedException($description, $error, $statusCode, $response, $request);
            }
            throw new RuntimeException($description, $error, $statusCode, $response, $request);
        }
    }

}