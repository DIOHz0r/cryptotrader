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

namespace App\LocalEth;

use App\HttpClient\HttpClient;
use App\HttpClient\HttpClientInterface;

class LocalEthClient
{
    /**
     * API urls.
     */
    const API_URL = 'https://api.localethereumapi.com';
    const SANDBOX_API_URL = 'https://api.localethereumapi.com';

    /**
     * Guzzle instance used to communicate with Localethereum.
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
     * @param array $options LocalethereumClient options.
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
        $response = $this->httpClient->get($queryUrl);
        $contents = json_decode($response->getBody()->getContents(), true);
        $dataRows = [];
        if (!$contents) {
            return $dataRows;
        }
        $amount = $options['amount'];
        foreach ($contents['offers'] as $key => $ad) {
            $mark = ' ';
            $skip = true;
            if ($options['currency'] != '' && $options['currency'] != $ad['local_currency_code']) {
                continue;
            }
            $bankName = preg_replace('/[^\x{20}-\x{7F}]/u', '', $ad['headline']);
            $minAmount = (float)$ad['limits_minimum'];
            $maxAmount = (float)$ad['limits_maximum'];
            if ($amount && ($minAmount <= $amount && $maxAmount == 0 || $minAmount <= $amount && $amount <= $maxAmount)) {
                $mark .= '<info>$</info>';
                $skip = false;
            }
            $matchBankname = str_replace(' ', '', $bankName);
            if (stripos($matchBankname, $options['bank']) !== false) {
                $mark .= '<fg=cyan>+</>';
            }
            $row = [
                $bankName,
                $ad['price']['amount_including_taker_fee'],
                $minAmount,
                $maxAmount,
                'https://localethereum.com/offer/'.$ad['id'].$mark,
            ];
            if (isset($options['username']) && $options['username']) {
                $row[] = $ad['account_username'].' ('.$ad['account_intro'].')';
            }
            $row['local_currency_code'] = $ad['local_currency_code'];
            $row['country_code'] = $ad['city']['country_code'];
            if ($skip && isset($options['exclude']) && $options['exclude']) {
                continue;
            }
            $dataRows[] = $row;
        }
        if (!is_null($contents['next'])) {
            if (preg_match('/&after=(\d+)/i', $queryUrl, $regs)) {
                $queryUrl = str_replace($regs[1], $contents['next'], $queryUrl);
            } else {
                $queryUrl .= "&after=".$contents['next'];
            }
            $dataRows = array_merge($dataRows, $this->listAds($queryUrl, $options));
        }

        return $dataRows;
    }
}