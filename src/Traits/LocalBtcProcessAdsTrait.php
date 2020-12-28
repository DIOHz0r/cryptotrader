<?php

namespace App\Traits;


use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;

trait LocalBtcProcessAdsTrait
{
    use DataRowsTrait;

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
    public function processDataRows(
        array $dataRows,
        $top,
        $currency,
        $sort = ['price_sort' => SORT_ASC, 'min_max_sort' => SORT_DESC]
    ): array {
        $dataRows = $this->sortDataRows($dataRows, $this->tableColums['price'], $this->tableColums['min'],
            $this->tableColums['max'], $sort['min_max_sort'], $top);
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