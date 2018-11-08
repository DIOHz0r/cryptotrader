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

namespace App\Command;


use App\LocalBtc\LocalBtcClient;
use App\LocalEth\LocalEthClient;
use App\Uphold\UpholdClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpholdSellCommand extends Command
{
    protected $btcClient;
    protected $ethClient;
    protected $upholdClient;

    # Withdrawal fees
    const USD_WDL = 2.99; //usd
    const ETH_WDL = 0.005; // satoshis
    const BTC_WDL = 0.0003; // satoshis

    # conversion rates
    const ETH_RATE = 1.4; // percent
    const BTC_RATE = 1.05; // percent

    # trading rates
    const ETH_TRADE_RATE = 0; // percent
    const BTC_TRADE_RATE = 0.0002; // satoshis

    /**
     * @var array
     */
    protected $tableColums = [
        'price' => 1,
        'min' => 2,
        'max' => 3,
        'rate' => 'rate',
    ];

    public function __construct(UpholdClient $upholdClient, LocalBtcClient $btcClient, LocalEthClient $ethClient)
    {
        $this->btcClient = $btcClient;
        $this->ethClient = $ethClient;
        $this->upholdClient = $upholdClient;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('amount', 'a', InputOption::VALUE_REQUIRED, 'Desired amount to get', 0)
            ->addOption('bank', 'b', InputOption::VALUE_REQUIRED, 'Bank name', '')
            ->addOption(
                'exclude',
                'x',
                InputOption::VALUE_NONE,
                'Exclude other ads not related to the searched amount'
            )
            ->addOption('username', 'u', InputOption::VALUE_NONE, 'Show username and reputation')
            ->addOption('top', 't', InputOption::VALUE_OPTIONAL, 'Show top number of ads', 0);

        $this
            // the name of the command (the part after "bin/console")
            ->setName('uphold:sell')
            // the short description shown while running "php bin/console list"
            ->setDescription('Find best options to sell.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Returns the top options to sell by currency.')
            ->addArgument('country', InputArgument::REQUIRED, 'Country code (ISO 3166-2)')
            ->addArgument('currency',InputArgument::REQUIRED, 'Currency to obtain (ISO 4217 code)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $country = $input->getArgument('country');
        $currency = $input->getArgument('currency');
        $options['amount'] = $input->getOption('amount');
        $options['bank'] = $input->getOption('bank');
        $options['exclude'] = $input->getOption('exclude');
        $options['currency'] = $currency;
        $top = $input->getOption('top');

        // Find Uphold rates
        $urlBase = UpholdClient::UPHOLD_API_URL;
        $response = $this->upholdClient->get($urlBase.'/v0/ticker/USD');
        $upholdTickets = json_decode($response->getBody()->getContents(), true);
        $btcTicket = $ethTicket = [];
        foreach ($upholdTickets as $key => $ticket) {
            switch ($ticket['pair']) {
                case 'BTCUSD':
                    $btcTicket = $ticket;
                    break;
                case 'ETHUSD':
                    $ethTicket = $ticket;
                    break;
            }
        }

        $table = new Table($output);
        $headers = ['payment', 'price', 'min', 'max', 'rate', 'send'];

        // Find ETH
        $output->writeln('<info>Getting localethereum ads, Market rate ('.$ethTicket['bid'].')</info>');
        $queryUrl = LocalEthClient::API_URL.'/v1/offers/find?offer_type=buy&sort_by=price&city_id='.$country;
        $ethRows = $this->ethClient->listAds($queryUrl, $options);
        $ethDataRows = $this->processDataRows($ethRows, $top, $options['amount'], $ethTicket);

        if (!$ethDataRows) {
            $output->writeln('No results found.');

            return;
        } else {
            $table->setHeaders($headers)->setRows($ethDataRows);
            $table->render();
        }


        // Find BTC
        $output->writeln('<info>Getting localbitcoins ads, Market rate ('.$btcTicket['bid'].')</info>');
        $queryUrl = LocalBtcClient::API_URL.'/sell-bitcoins-online/'.$currency.'/.json?fields=profile,temp_price,min_amount,max_amount,bank_name,temp_price_usd';
        $btcRows = $this->btcClient->listAds($queryUrl, $options);
        $btcDataRows = $this->processDataRows($btcRows, $top, $options['amount'], $btcTicket);

        if (!$btcDataRows) {
            $output->writeln('No results found.');

            return;
        } else {
            $table->setHeaders($headers)->setRows($btcDataRows);
            $table->render();
        }

    }

    /**
     * Process the result array from listing ads
     *
     * @param array $dataRows
     * @param $top
     * @param $amount
     * @param array $ticket
     * @param array $sort
     * @return array
     */
    protected function processDataRows(array $dataRows, $top, $amount, array $ticket, $sort = ['price_sort'=> SORT_ASC, 'min_max_sort'=> SORT_DESC]): array
    {
        $price = array_column($dataRows, $this->tableColums['price']);
        $minimun = array_column($dataRows, $this->tableColums['min']);
        $maximun = array_column($dataRows, $this->tableColums['max']);
        array_multisort(
            $price,
            $sort['price_sort'],
            $minimun,
            $sort['min_max_sort'],
            $maximun,
            $sort['min_max_sort'],
            $dataRows
        );
        if ($top > 0 && count($dataRows) > $top) {
            $dataRows = array_slice($dataRows, (-1 * $top));
        }

        // set fee rates
        switch ($ticket['pair']) {
            case 'BTCUSD':
                $conversionRate = self::BTC_RATE / 100;
                $networkFee = self::BTC_WDL;
                $tradingFee = self::BTC_TRADE_RATE;
                break;
            case 'ETHUSD':
                $conversionRate = self::ETH_RATE / 100;
                $networkFee = self::ETH_WDL;
                $tradingFee = self::ETH_TRADE_RATE / 100;
                break;
        }

        $finalData = [];
        foreach ($dataRows as $key => $row) {
            // calc the price rate
            $marketRate = $ticket['bid'] * 1.005; // approx percent
            $sellerRate = $row[$this->tableColums['price']] / $marketRate;
            $row['rate'] = $sellerRate;

            // calc the amount of USD to send for each page
            $row['send'] = '';
            if ($amount > 0) {
                $sellingAmount = ($amount / $row[$this->tableColums['price']]);
                $UsdAmountToSend = ($marketRate * ($sellingAmount + $networkFee + $tradingFee) + self::USD_WDL) / (1 - $conversionRate);
                $row['send'] = number_format($UsdAmountToSend, 2);
            }

            foreach ($this->tableColums as $colName => $colKey) {
                $row[$colKey] = number_format($row[$colKey], 2);
            }
            $finalData[] = [$row[0], $row[1], $row[2], $row[3], $row['rate'], $row['send']];
            $finalData[] = new TableSeparator();
            $finalData[] = [new TableCell($row[4], ['colspan' => 6])];
            $finalData[] = new TableSeparator();
        }
        array_pop($finalData); // remove last separator

        return $finalData;
    }
}