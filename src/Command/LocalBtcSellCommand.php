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
use App\LocalBtc\LocalBtcClient;
use App\Traits\LocalBtcProcessAdsTrait;
use App\Traits\RenderAdsTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class LocalBtcSellCommand extends Command
{
    use LocalBtcProcessAdsTrait;
    use RenderAdsTable;

    protected static $defaultName = 'localbtc:sell:online';

    /**
     * @var CrawlerInterface
     */
    protected $client;

    /**
     * @var string field to search by default
     */
    protected $defaultFields = 'profile,temp_price,min_amount,max_amount,bank_name,temp_price_usd';


    public function __construct(CrawlerInterface $localBtcClient)
    {
        $this->client = $localBtcClient;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('amount', 'a', InputOption::VALUE_REQUIRED, 'Desired amount to trade', 0)
            ->addOption('bank', 'b', InputOption::VALUE_REQUIRED, 'Bank name', '')
            ->addOption('json', 'j', InputOption::VALUE_NONE, 'Prin the result as json string')
            ->addOption(
                'exclude',
                'x',
                InputOption::VALUE_NONE,
                'Exclude other ads not related to the searched ammount'
            )
            ->addOption('username', 'u', InputOption::VALUE_NONE, 'Show username and reputation')
            ->addOption('top', 't', InputOption::VALUE_OPTIONAL, 'Show top number of ads', 0)
            // the short description shown while running "php bin/console list"
            ->setDescription('List online sells from localbitcoins.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Returns the online sells ads from localbitcoin.')
            ->addArgument('currency', InputArgument::REQUIRED, 'Currency ISO code')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get arguments and options
        $currency = $input->getArgument('currency');
        $top = $input->getOption('top');
        $options['username'] = $input->getOption('username');
        $options['exclude'] = $input->getOption('exclude');
        $options['amount'] = $input->getOption('amount');
        $options['bank'] = $input->getOption('bank');

        // Request end-point
        $queryUrl = LocalBtcClient::API_URL.'/sell-bitcoins-online/'.$currency.'/.json?fields='.$this->defaultFields;
        $dataRows = $this->client->listAds($queryUrl, $options);
        if (!$dataRows) {
            $output->writeln('No results found.');

            return 0;
        }

        // Process result
        $dataRows = $this->processDataRows($dataRows, $top, $currency);

        // Print the result
        return $this->renderAdsTable($input, $output, $dataRows, $options);
    }

}