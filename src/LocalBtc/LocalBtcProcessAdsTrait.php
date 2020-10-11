<?php

namespace App\LocalBtc;


use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;

trait LocalBtcProcessAdsTrait
{
    /**
     * @var array
     */
    protected $tableColums = [
        'price' => 1,
        'min' => 2,
        'max' => 3,
    ];

    /**
     * Process the result array from listing ads
     *
     * @param $dataRows
     * @param $top
     * @param $currency
     * @param array $sort
     * @return array
     */
    protected function processDataRows(
        array $dataRows,
        $top,
        $currency,
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
        $finalData = [];
        $fmt = new \NumberFormatter($currency, \NumberFormatter::CURRENCY);
        foreach ($dataRows as $key => $row) {
            foreach ($this->tableColums as $colName => $colNumber) {
                $row[$colNumber] = $fmt->formatCurrency($row[$colNumber], $currency);
            }
            $finalData[] = [$row[0], $row[1], $row[2], $row[3]];
            $finalData[] = new TableSeparator();
            $finalData[] = [new TableCell($row[4], ['colspan' => 5])];
            $finalData[] = new TableSeparator();
        }
        array_pop($finalData); // remove last separator

        return $finalData;
    }
}