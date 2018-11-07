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


use App\LocalEth\LocalEthClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class LocalEthCommand extends Command
{

    protected static $defaultName = 'localeth';

    /**
     * @var array
     */
    protected $tableColums = [
        'price' => 1,
        'min' => 2,
        'max' => 3,
    ];

    /**
     * @var LocalEthClient
     */
    protected $client;


    public function __construct(LocalEthClient $LocalEthClient)
    {
        $this->client = $LocalEthClient;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('amount', 'a', InputOption::VALUE_REQUIRED, 'Desired amount to trade', 0)
            ->addOption('bank', 'b', InputOption::VALUE_REQUIRED, 'Bank name', '')
            ->addOption('currency', 'c', InputOption::VALUE_REQUIRED, 'Show only the selected currency', '')
            ->addOption('json', 'j', InputOption::VALUE_NONE, 'Prin the result as json string')
            ->addOption(
                'exclude',
                'x',
                InputOption::VALUE_NONE,
                'Exclude other ads not related to the searched amount'
            )
            ->addOption('username', 'u', InputOption::VALUE_NONE, 'Show username')
            ->addOption('top', 't', InputOption::VALUE_OPTIONAL, 'Show top number of ads', 0);
    }

    /**
     * Process the result array from listing ads
     *
     * @param $dataRows
     * @param $top
     * @param array $sort
     * @return array
     */
    protected function processDataRows(
        array $dataRows,
        $top,
        $sort = ['price_sort' => SORT_ASC, 'min_max_sort' => SORT_DESC]
    ): array {
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
        foreach ($dataRows as $key => $row) {
            $fmt = new \NumberFormatter('und_'.$row['country_code'], \NumberFormatter::CURRENCY);
            foreach ($this->tableColums as $colName => $colNumber) {
                $row[$colNumber] = $fmt->formatCurrency($row[$colNumber], $row['local_currency_code']);
            }
            unset($row['local_currency_code'], $row['country_code']);
            $dataRows[$key] = $row;
        }

        return $dataRows;
    }
}