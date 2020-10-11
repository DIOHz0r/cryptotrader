<?php


namespace App\Traits;


use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;

trait LocalCryptosProcessAdsTrait
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
        $dataRows = $this->sortDataRows($dataRows, $this->tableColums['price'], $this->tableColums['min'],
            $this->tableColums['max'], $sort['min_max_sort'], $top);
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