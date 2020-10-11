<?php

namespace App\HttpClient;

use App\LocalBtc\LocalBtcClient;

interface CrawlerInterface
{
    /**
     * Get Http client.
     *
     * @return HttpClientInterface $httpClient
     */
    public function getHttpClient();

    /**
     * Get client option.
     *
     * @param string $name Option name.
     *
     * @return mixed
     */
    public function getOption($name);

    /**
     * Sets client option.
     *
     * @param string $name Option name.
     * @param mixed $value Option value.
     * @return $this
     */
    public function setOption($name, $value);

    /**
     * Get all client options.
     *
     * @return array
     */
    public function getOptions();

    /**
     * @param HttpClientInterface $httpClient
     */
    public function setHttpClient(HttpClientInterface $httpClient);

    /**
     * @param $queryUrl
     * @param array $options
     * @return array
     */
    public function listAds($queryUrl, array $options): array;
}