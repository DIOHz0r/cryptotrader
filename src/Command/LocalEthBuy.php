<?php
/**
 * Created by Domingo Oropeza for cryptotrader
 * Date: 19/08/2018
 * Time: 11:27 PM
 */

namespace App\Command;


use App\LocalEth\LocalEthClient;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LocalEthBuy extends LocalEthCommand
{

    protected function configure()
    {
        parent::configure();
        $this
            // the name of the command (the part after "bin/console")
            ->setName('localeth:buy:online')
            // the short description shown while running "php bin/console list"
            ->setDescription('List online buys.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Returns the online buys ads from localethereum.')
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

        // Request end-point
        $queryUrl = LocalEthClient::API_URL.'/v1/offers/find?offer_type=sell&sort_by=price&city_id='.$country;
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
        $table = new Table($output);
        $headers = ['payment', 'price', 'min', 'max', 'url'];
        if ($options['username']) {
            $headers[] = 'user';
        }
        $table->setHeaders($headers)->setRows($dataRows);
        $table->render();
    }
}