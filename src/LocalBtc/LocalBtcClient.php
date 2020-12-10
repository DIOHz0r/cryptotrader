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

namespace App\LocalBtc;

use App\HttpClient\CrawlerInterface;
use App\HttpClient\HttpClient;
use App\HttpClient\HttpClientInterface;

class LocalBtcClient implements CrawlerInterface
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
     * @var array
     */
    private $options = [];

    /**
     * Constructor.
     *
     * @param array $options LocalbitcoinClient options.
     */
    public function __construct(array $options = [])
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

    /**
     * @param $queryUrl
     * @param array $options
     * @return array
     */
    public function listAds($queryUrl, array $options): array
    {
        $dataRows = [];
        $contents = $this->getApiResponse($queryUrl);
        if (!$contents) {
            return $dataRows;
        }
        $amount = $this->getAmount($options);
        $dataRows = $this->parseAds($contents, $amount, $options, $dataRows);

        return $dataRows;
    }

    /**
     * @param $queryUrl
     * @return array|null
     */
    protected function getApiResponse($queryUrl): ?array
    {
        $response = $this->httpClient->get($queryUrl);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param array $options
     * @return mixed|null
     */
    protected function getAmount(array $options)
    {
        return key_exists('amount', $options) ? $options['amount'] : null;
    }

    /**
     * @param $contents
     * @param $amount
     * @param array $options
     * @param array $dataRows
     * @return array
     */
    protected function parseAds(iterable $contents, $amount, array $options, array $dataRows): array
    {
        foreach ($contents['data']['ad_list'] as $key => $ad) {
            $mark = ' ';
            $skip = true;
            $data = $ad['data'];
            $bankName = preg_replace('/[^\x{20}-\x{7F}]/u', '', $data['bank_name']);
            $minAmount = (float)$data['min_amount'];
            $maxAmount = (float)$data['max_amount'];
            if ($this->amountInRange($amount, $minAmount, $maxAmount)) {
                $mark .= '<info>$</info>';
                $skip = false;
            }
            if ($this->excludeAd($skip, $options)) {
                continue;
            }
            if ($this->bankMatched($options, $bankName)) {
                $mark .= '<fg=cyan>+</>';
            }
            $row = [
                $bankName,
                $data['temp_price'],
                $minAmount,
                $maxAmount,
                $ad['actions']['public_view'].$mark,
            ];
            if ($this->showUser($options)) {
                $row[] = $data['profile']['name'];
            }
            $dataRows[] = $row;
        }
        $dataRows = $this->getNextPage($contents, $dataRows, $options);

        return $dataRows;
    }

    /**
     * @param $amount
     * @param float $minAmount
     * @param float $maxAmount
     * @return bool
     */
    protected function amountInRange($amount, float $minAmount, float $maxAmount): bool
    {
        return $amount && ($minAmount <= $amount && $maxAmount == 0 || $minAmount <= $amount && $amount <= $maxAmount);
    }

    /**
     * @param array $options
     * @param string $matchBankname
     * @return bool
     */
    protected function bankMatched(array $options, $matchBankname): bool
    {
        $matchBankname = str_replace(' ', '', $matchBankname);

        return key_exists('bank', $options) && stripos($matchBankname, $options['bank']) !== false;
    }

    /**
     * @param array $options
     * @return bool
     */
    protected function showUser(array $options): bool
    {
        return isset($options['username']) && $options['username'];
    }

    /**
     * @param bool $skip
     * @param array $options
     * @return bool
     */
    protected function excludeAd(bool $skip, array $options): bool
    {
        return $skip && isset($options['exclude']) && $options['exclude'];
    }

    /**
     * @param iterable $contents
     * @param array $dataRows
     * @param array $options
     * @return array
     */
    protected function getNextPage(iterable $contents, array $dataRows, array $options): array
    {
        if (isset($contents['pagination']['next'])) {
            $dataRows = array_merge($dataRows, $this->listAds($contents['pagination']['next'], $options));
        }

        return $dataRows;
    }
}