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


use App\HttpClient\CrawlerInterface;
use App\LocalCryptos\LocalCryptosClient;
use App\Traits\LocalCryptosProcessAdsTrait;
use App\Traits\RenderAdsTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LocalCryptosBuyCommand extends Command
{
    use LocalCryptosProcessAdsTrait;
    use RenderAdsTable;

    protected static $defaultName = 'lc:buy:online';

    /**
     * @var CrawlerInterface
     */
    protected $client;

    public function __construct(CrawlerInterface $localCryptosClient)
    {
        $this->client = $localCryptosClient;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('amount', 'a', InputOption::VALUE_REQUIRED, 'Desired amount to trade', 0)
            ->addOption('bank', 'b', InputOption::VALUE_REQUIRED, 'Bank name', '')
            ->addOption('currency', 'c', InputOption::VALUE_REQUIRED, 'Show only the selected currency', '')
            ->addOption('json', 'j', InputOption::VALUE_NONE, 'Print the result as json string')
            ->addOption(
                'exclude',
                'x',
                InputOption::VALUE_NONE,
                'Exclude other ads not related to the searched amount'
            )
            ->addOption('username', 'u', InputOption::VALUE_NONE, 'Show username')
            ->addOption('top', 't', InputOption::VALUE_OPTIONAL, 'Show top number of ads', 0)
            ->addOption('coin', 'o', InputOption::VALUE_OPTIONAL, 'Set coin type (use ticker symbol)', 'ETH')
            // the short description shown while running "php bin/console list"
            ->setDescription('List online buys from localcryptos.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Returns the online buys ads from localcryptos.')
            ->addArgument('country', InputArgument::REQUIRED, 'Country ISO 3166-2 code')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->switchMarket($input->getOption('coin'));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get arguments and options
        $country = $input->getArgument('country');
        $top = $input->getOption('top');
        $options['username'] = $input->getOption('username');
        $options['currency'] = $input->getOption('currency');
        $options['exclude'] = $input->getOption('exclude');
        $options['amount'] = $input->getOption('amount');
        $options['bank'] = $input->getOption('bank');
        $options['market_id'] = $this->marketId;

        // Request end-point
        $queryUrl = LocalCryptosClient::API_URL . '/v1/offers/find?offer_type=sell&sort_by=price&city_id=' . $country . '&market_id=' . $options['market_id'];
        $dataRows = $this->client->listAds($queryUrl, $options);
        if (!$dataRows) {
            $output->writeln('No results found.');

            return 0;
        }

        // Process result
        $dataRows = $this->processDataRows(
            $dataRows,
            $top,
            ['price_sort' => SORT_DESC, 'min_max_sort' => SORT_DESC]
        );

        // Print the result
        return $this->renderAdsTable($input, $output, $dataRows, $options);
    }
}