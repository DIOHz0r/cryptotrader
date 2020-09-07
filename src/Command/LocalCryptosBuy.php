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


use App\LocalCryptos\LocalCryptosClient;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LocalCryptosBuy extends LocalCryptosCommand
{

    protected function configure()
    {
        parent::configure();
        $this
            // the name of the command (the part after "bin/console")
            ->setName('lc:buy:online')
            // the short description shown while running "php bin/console list"
            ->setDescription('List online buys.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Returns the online buys ads from localcryptos.')
            ->addArgument('country', InputArgument::REQUIRED, 'Country ISO 3166-2 code')
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

            return;
        }

        // Process result
        $dataRows = $this->processDataRows(
            $dataRows,
            $top,
            ['price_sort' => SORT_DESC, 'min_max_sort' => SORT_DESC]
        );

        // Print the result
        $format = $input->getOption('json');
        if ($format) {
            $output->write(json_encode($dataRows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return;
        }

        $table = new Table($output);
        $headers = ['payment', 'price', 'min', 'max'];
        if ($options['username']) {
            $headers[] = 'user';
        }
        $table->setHeaders($headers)->setRows($dataRows);
        $table->render();
    }
}