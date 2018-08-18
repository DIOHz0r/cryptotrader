<?php
/**
 * Created by Domingo Oropeza for cryptocompare
 * Date: 23/04/2018
 * Time: 10:24 PM
 */

namespace App\Command;


use App\LocalBtc\LocalBtcClient;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class LocalBtcSell extends LocalBtcCommand
{

    protected function configure()
    {
        parent::configure();
        $this
            // the name of the command (the part after "bin/console")
            ->setName('localbtc:sell:online')
            // the short description shown while running "php bin/console list"
            ->setDescription('List online sells.')
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

            return;
        }

        // Process result
        $dataRows = $this->processDataRows($dataRows, $top, $currency);

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