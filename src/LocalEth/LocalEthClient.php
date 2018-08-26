<?php
/**
 * Created by Domingo Oropeza for cryptotrader
 * Date: 19/08/2018
 * Time: 11:27 PM
 */

namespace App\LocalEth;

use App\Exception\AuthenticationRequiredException;
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
    public function __construct(array $options = array())
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
            $minAmount = (float) $ad['limits_minimum'];
            $maxAmount = (float) $ad['limits_maximum'];
            if ($amount && ($minAmount <= $amount && $maxAmount == 0 || $minAmount <= $amount && $amount <= $maxAmount)) {
                $mark .= '$';
                $skip = false;
            }
            $matchBankname = str_replace(' ', '', $bankName);
            if (stripos($matchBankname, $options['bank']) !== false) {
                $mark .= '+';
            }
            $row = [
                $bankName,
                $ad['price']['amount'],
                $minAmount,
                $maxAmount,
                'https://localethereum.com/offer/'.$ad['id'].$mark,
            ];
            if ($options['username']) {
                $row[] = $ad['account_username'];
            }
            $row['local_currency_code'] = $ad['local_currency_code'];
            $row['country_code'] = $ad['city']['country_code'];
            if ($skip && $options['exclude']) {
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