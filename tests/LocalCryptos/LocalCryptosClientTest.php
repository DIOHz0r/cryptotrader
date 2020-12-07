<?php

namespace App\Tests\LocalCryptos;

use App\HttpClient\HttpClient;
use App\LocalCryptos\LocalCryptosClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class LocalCryptosClientTest extends TestCase
{

    public function testGetClient()
    {
        $client = new LocalCryptosClient();
        $this->assertInstanceOf(HttpClient::class, $client->getHttpClient());
    }

    public function testOption()
    {
        $client = new LocalCryptosClient();
        $this->assertNull($client->getOption('foo'));
        $client->setOption('foo', 'bar');
        $this->assertEquals('bar',$client->getOption('foo'));
        $this->assertIsArray($client->getOptions());
    }

    public function testListAds()
    {
        $mockHandler = new MockHandler(
            [
                new Response(200, [], file_get_contents(__DIR__.'/../Fixtures/localcryptos-pg1.json')),
                new Response(200, [], file_get_contents(__DIR__.'/../Fixtures/localcryptos-pg2.json')),
            ]
        );
        $handlerStack = HandlerStack::create($mockHandler);
        $httpClient = new HttpClient(['handler' => $handlerStack]);
        $client = new LocalCryptosClient();
        $client->setHttpClient($httpClient);
        $data = $client->listAds(LocalCryptosClient::API_URL, ['currency' => 'VES']);
        $this->assertNotEmpty($data);
        $this->assertIsArray($data);
    }
}
