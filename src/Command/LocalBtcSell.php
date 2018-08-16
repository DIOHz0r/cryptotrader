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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class LocalBtcSell extends Command
{
    protected $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
        parent::__construct();
    }

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
            ->addOption('amount', 'a', InputOption::VALUE_REQUIRED, 'Desired amount to trade', 0)
            ->addOption('bank', 'b', InputOption::VALUE_REQUIRED, 'Bank name', '')
            ->addOption('currency', 'c', InputOption::VALUE_REQUIRED, 'Currency ISO code', 'USD')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $currency = $input->getOption('currency');
        $queryUrl = LocalBtcClient::API_URL.'/sell-bitcoins-online/'.$currency.'/.json';
        $options['amount'] = $input->getOption('amount');
        $options['bank'] = $input->getOption('bank');
        $dataRows = $this->listAds($queryUrl, $options);
        if (!$dataRows) {
            $output->writeln('No results found.');

            return;
        }
        $price = array_column($dataRows, 2);
        $minimun = array_column($dataRows, 3);
        $maximun = array_column($dataRows, 4);
        array_multisort($price, SORT_ASC, $minimun, SORT_DESC, $maximun, SORT_DESC, $dataRows);
        $fmt = new \NumberFormatter($currency, \NumberFormatter::CURRENCY);
        foreach ($dataRows as $key => $row){
            foreach ([2, 3, 4] as $colNumber){
                $row[$colNumber] = $fmt->formatCurrency($row[$colNumber], $currency);
            }
            $dataRows[$key] = $row;
        }
        $table = new Table($output);
        $table->setHeaders(['user', 'payment', 'price', 'min', 'max', 'url',])->setRows($dataRows);
        $table->render();
    }

    /**
     * @param $queryUrl
     * @param array $options
     * @return array
     */
    protected function listAds($queryUrl, array $options): array
    {
        $response = $this->client->get($queryUrl);
        $contents = json_decode($response->getBody()->getContents(), true);
        $dataRows = [];
        if (!$contents) {
            return $dataRows;
        }
        $amount = $options['amount'];
        foreach ($contents['data']['ad_list'] as $key => $ad) {
            $mark = ' ';
            $data = $ad['data'];
            $bankName = preg_replace('/[^\x{20}-\x{7F}]/u', '', $data['bank_name']);
            if ($amount && ((int)$data['min_amount'] <= $amount)) {
                $mark .= '$';
            }
            if (stripos($bankName, $options['bank']) !== false) {
                $mark .= '+';
            }
            $dataRows[] = [
                $data['profile']['name'],
                $bankName,
                $data['temp_price'],
                $data['min_amount'],
                $data['max_amount'],
                $ad['actions']['public_view'].$mark,
            ];
        }
        if (isset($contents['pagination']['next'])) {
            $dataRows = array_merge($dataRows, $this->listAds($contents['pagination']['next'], $options));
        }

        return $dataRows;
    }
}