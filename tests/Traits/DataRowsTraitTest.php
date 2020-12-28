<?php

namespace App\Tests\Traits;

use App\LocalBtc\LocalBtcClient;
use App\Traits\DataRowsTrait;
use PHPUnit\Framework\TestCase;

class DataRowsTraitTest extends TestCase
{

    /**
     * @dataProvider adsProvider
     * @param array $expected
     * @param array $ads
     * @param $sort
     */
    public function testSortPrice(array $expected, array $ads, $sort)
    {
        $trait = $this->getMockBuilder(DataRowsTrait::class)->getMockForTrait();
        $dataRows = $trait->sortDataRows($ads, 1, 2, 3, $sort, 0);
        $this->assertEquals($expected, $dataRows);
    }

    public function adsProvider(): array
    {
        // get ads lists
        $contents = file_get_contents(__DIR__.'/../Fixtures/localbitcoins-pg1.json');
        $decoded = json_decode($contents, true);
        unset($decoded['pagination']); // pagination no needed.

        // let's transform the list to the expected array for the trait
        $method = new \ReflectionMethod(LocalBtcClient::class, 'parseAds');
        $method->setAccessible(true);
        $ads = $method->invoke(new LocalBtcClient(), $decoded, 0, [], []);

        return [
            [[$ads[2], $ads[1], $ads[0]], $ads, SORT_DESC],
            [$ads, $ads, SORT_ASC],
        ];
    }
}
