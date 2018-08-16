<?php
/**
 * Created by Domingo Oropeza for cryptocompare
 * Date: 23/04/2018
 * Time: 10:24 PM
 */

namespace App\Command;


use App\HttpClient\HttpClient;
use App\LocalBtc\LocalBtcClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class LocalBtcSell extends Command
{
    /**
     * @var LocalBtcClient
     */
    protected $client;

    /**
     * @var array
     */
    private $tableColums = [
        'price' => 1,
        'min' => 2,
        'max' => 3,
    ];

    public function __construct(LocalBtcClient $localBtcClient)
    {
        $this->client = $localBtcClient;
        parent::__construct();
    }

    /**
     * Configuration of the command
     */
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('localbtc:sell:online')
            // the short description shown while running "php bin/console list"
            ->setDescription('List online sells.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Returns the online sells ads from localbitcoin.')
            ->addArgument('currency', InputArgument::REQUIRED, 'Currency ISO code')
            ->addOption('amount', 'a', InputOption::VALUE_REQUIRED, 'Desired amount to trade', 0)
            ->addOption('bank', 'b', InputOption::VALUE_REQUIRED, 'Bank name', '')
            ->addOption(
                'exclude',
                'x',
                InputOption::VALUE_NONE,
                'Exclude other ads not related to the searched ammount'
            )
            ->addOption('username', 'u', InputOption::VALUE_NONE, 'Show username and reputation')
            ->addOption('top', 't', InputOption::VALUE_OPTIONAL, 'Show top number of ads', 0)
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
        $fields = 'profile,temp_price,min_amount,max_amount,bank_name,temp_price_usd';
        $queryUrl = LocalBtcClient::API_URL.'/sell-bitcoins-online/'.$currency.'/.json?fields='.$fields;
        $dataRows = $this->client->listAds($queryUrl, $options);
        if (!$dataRows) {
            $output->writeln('No results found.');

            return;
        }

        // Process result
        $price = array_column($dataRows, $this->tableColums['price']);
        $minimun = array_column($dataRows, $this->tableColums['min']);
        $maximun = array_column($dataRows, $this->tableColums['max']);
        array_multisort($price, SORT_ASC, $minimun, SORT_DESC, $maximun, SORT_DESC, $dataRows);
        if ($top > 0 && count($dataRows) > $top) {
            $dataRows = array_slice($dataRows, (-1 * $top));
        }
        $fmt = new \NumberFormatter($currency, \NumberFormatter::CURRENCY);
        foreach ($dataRows as $key => $row){
            foreach ($this->tableColums as $colName => $colNumber){
                $row[$colNumber] = $fmt->formatCurrency($row[$colNumber], $currency);
            }
            $dataRows[$key] = $row;
        }

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