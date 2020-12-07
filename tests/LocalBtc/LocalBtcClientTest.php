<?php


namespace App\Tests\LocalBtc;


use App\HttpClient\HttpClient;
use App\LocalBtc\LocalBtcClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class LocalBtcClientTest extends TestCase
{
    public function testGetClient()
    {
        $client = new LocalBtcClient();
        $this->assertInstanceOf(HttpClient::class, $client->getHttpClient());
    }

    public function testOption()
    {
        $client = new LocalBtcClient();
        $this->assertNull($client->getOption('foo'));
        $client->setOption('foo', 'bar');
        $this->assertEquals('bar', $client->getOption('foo'));
        $this->assertIsArray($client->getOptions());
    }

    public function testListAds()
    {
        $mockHandler = new MockHandler(
            [
                new Response(200, [], file_get_contents(__DIR__.'/../Fixtures/localbitcoins-pg1.json')),
                new Response(200, [], file_get_contents(__DIR__.'/../Fixtures/localbitcoins-pg2.json')),
            ]
        );
        $handlerStack = HandlerStack::create($mockHandler);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client = new LocalBtcClient();
        $client->setHttpClient($httpClient);
        $data = $client->listAds(LocalBtcClient::API_URL, ['currency' => 'VES']);
        $this->assertNotEmpty($data);
        $this->assertIsArray($data);
    }
}