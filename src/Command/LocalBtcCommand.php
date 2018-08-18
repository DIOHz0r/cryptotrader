<?php
/**
 * Created by Domingo Oropeza for cryptotrader
 * Date: 18/08/2018
 * Time: 03:13 PM
 */

namespace App\Command;


use App\LocalBtc\LocalBtcClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class LocalBtcCommand extends Command
{

    protected static $defaultName = 'localbtc';

    /**
     * @var array
     */
    protected $tableColums = [
        'price' => 1,
        'min' => 2,
        'max' => 3,
    ];
    /**
     * @var LocalBtcClient
     */
    protected $client;

    /**
     * @var string field to search by default
     */
    protected $defaultFields = 'profile,temp_price,min_amount,max_amount,bank_name,temp_price_usd';


    public function __construct(LocalBtcClient $localBtcClient)
    {
        $this->client = $localBtcClient;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('amount', 'a', InputOption::VALUE_REQUIRED, 'Desired amount to trade', 0)
            ->addOption('bank', 'b', InputOption::VALUE_REQUIRED, 'Bank name', '')
            ->addOption(
                'exclude',
                'x',
                InputOption::VALUE_NONE,
                'Exclude other ads not related to the searched ammount'
            )
            ->addOption('username', 'u', InputOption::VALUE_NONE, 'Show username and reputation')
            ->addOption('top', 't', InputOption::VALUE_OPTIONAL, 'Show top number of ads', 0);
    }

    /**
     * Process the result array from listing ads
     *
     * @param $dataRows
     * @param $top
     * @param $currency
     * @return array
     */
    protected function processDataRows($dataRows, $top, $currency): array
    {
        $price = array_column($dataRows, $this->tableColums['price']);
        $minimun = array_column($dataRows, $this->tableColums['min']);
        $maximun = array_column($dataRows, $this->tableColums['max']);
        array_multisort($price, SORT_ASC, $minimun, SORT_DESC, $maximun, SORT_DESC, $dataRows);
        if ($top > 0 && count($dataRows) > $top) {
            $dataRows = array_slice($dataRows, (-1 * $top));
        }
        $fmt = new \NumberFormatter($currency, \NumberFormatter::CURRENCY);
        foreach ($dataRows as $key => $row) {
            foreach ($this->tableColums as $colName => $colNumber) {
                $row[$colNumber] = $fmt->formatCurrency($row[$colNumber], $currency);
            }
            $dataRows[$key] = $row;
        }

        return $dataRows;
    }
}