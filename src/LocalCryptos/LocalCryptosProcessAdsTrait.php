<?php


namespace App\LocalCryptos;


use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;

trait LocalCryptosProcessAdsTrait
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
     * Set crypto market
     * @var int
     */
    protected $marketId;

    /**
     * @param string $coin
     */
    protected function switchMarket(string $coin): void
    {
        switch (strtoupper($coin)) {
            case 'ETH':
                $this->marketId = 1;
                break;
            case 'BTC':
                $this->marketId = 2;
                break;
            case 'LTC':
                $this->marketId = 3;
                break;
            case 'DASH':
                $this->marketId = 4;
                break;
            default:
                throw new \RuntimeException('Invalid ticker symbol');
        }
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
        $finalData = [];
        foreach ($dataRows as $key => $row) {
            $fmt = new \NumberFormatter('und_'.$row['country_code'], \NumberFormatter::CURRENCY);
            foreach ($this->tableColums as $colName => $colNumber) {
                $row[$colNumber] = $fmt->formatCurrency($row[$colNumber], $row['local_currency_code']);
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