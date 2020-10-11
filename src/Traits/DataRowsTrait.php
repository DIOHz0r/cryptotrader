<?php


namespace App\Traits;


trait DataRowsTrait
{

    /**
     * The ads data is a multidimensional array so lest order its values according to the user filters
     *
     * @param array $dataRows
     * @param int $priceCol
     * @param int $minCol
     * @param int $maxCol
     * @param $min_max_sort
     * @param $top
     * @return array
     */
    protected function sortDataRows(
        array $dataRows,
        int $priceCol,
        int $minCol,
        int $maxCol,
        $min_max_sort,
        $top
    ): array {
        $price = array_column($dataRows, $priceCol);
        $minimun = array_column($dataRows, $minCol);
        $maximun = array_column($dataRows, $maxCol);
        array_multisort(
            $price,
            $min_max_sort['price_sort'],
            $minimun,
            $min_max_sort,
            $maximun,
            $min_max_sort,
            $dataRows
        );
        if ($top > 0 && count($dataRows) > $top) {
            $dataRows = array_slice($dataRows, (-1 * $top));
        }
        return $dataRows;
    }
}