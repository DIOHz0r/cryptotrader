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

namespace App\LocalCryptos;

use App\HttpClient\CrawlerInterface;
use App\HttpClient\HttpClient;
use App\HttpClient\HttpClientInterface;

class LocalCryptosClient implements CrawlerInterface
{
    /**
     * API urls.
     */
    const API_URL = 'https://localcryptosapi.com';
    const SANDBOX_API_URL = 'https://localcryptosapi.com';

    /**
     * Guzzle instance used to communicate with Localcryptos.
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
     * @param array $options LocalcryptosClient options.
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
        $dataRows = $this->parseAds($contents, $options, $amount, $dataRows, $queryUrl);

        return $dataRows;
    }

    /**
     * @param $queryUrl
     * @return mixed
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
     * @param $amount
     * @param float $minAmount
     * @param float $maxAmount
     * @return bool
     */
    protected function amountInRage($amount, float $minAmount, float $maxAmount): bool
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
     * @param bool $skip
     * @param array $options
     * @return bool
     */
    protected function excludeAd(bool $skip, array $options): bool
    {
        return $skip && isset($options['exclude']) && $options['exclude'];
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
     * @param $contents
     * @param array $options
     * @param $amount
     * @param array $dataRows
     * @param $queryUrl
     * @return array
     */
    protected function parseAds($contents, array $options, $amount, array $dataRows, $queryUrl): array
    {
        foreach ($contents['offers'] as $key => $ad) {
            $mark = ' ';
            $skip = true;
            if ($this->filterCurrency($options['currency'], $ad['local_currency_code'])) {
                continue;
            }
            $bankName = preg_replace('/[^\x{20}-\x{7F}]/u', '', $ad['headline']);
            $minAmount = (float)$ad['limits_minimum'];
            $maxAmount = (float)$ad['limits_maximum'];
            if ($this->amountInRage($amount, $minAmount, $maxAmount)) {
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
                $ad['price']['amount_including_taker_fee'],
                $minAmount,
                $maxAmount,
                'https://localcryptos.com/offer/'.$ad['id'].$mark,
            ];
            if ($this->showUser($options)) {
                $row[] = $ad['account_username'].' ('.$ad['account_intro'].')';
            }
            $row['local_currency_code'] = $ad['local_currency_code'];
            $row['country_code'] = $ad['city']['country_code'];
            $dataRows[] = $row;
        }
        $dataRows = $this->getNextPage($contents['next'], $queryUrl, $dataRows, $options);

        return $dataRows;
    }

    /**
     * @param $currency
     * @param $ad
     * @return bool
     */
    protected function filterCurrency($currency, $ad): bool
    {
        return $currency != '' && $currency != $ad;
    }

    /**
     * @param $contents
     * @param $queryUrl
     * @param array $dataRows
     * @param array $options
     * @return array
     */
    protected function getNextPage($contents, $queryUrl, array $dataRows, array $options): array
    {
        if (!is_null($contents)) {
            $queryUrl = $this->getQueryUrl($queryUrl, $contents);
            $dataRows = array_merge($dataRows, $this->listAds($queryUrl, $options));
        }

        return $dataRows;
    }

    /**
     * @param $queryUrl
     * @param $contents
     * @return string
     */
    protected function getQueryUrl($queryUrl, $contents): string
    {
        if (preg_match('/&after=(\d+)/i', $queryUrl, $regs)) {
            $queryUrl = str_replace($regs[1], $contents, $queryUrl);
        } else {
            $queryUrl .= "&after=".$contents;
        }

        return $queryUrl;
    }
}