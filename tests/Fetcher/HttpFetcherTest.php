<?php
namespace Skrape\Tests\Fetcher;

use Skrape\Tests\TestCase;
use Skrape\Meta\Config;
use Skrape\Fetcher\HttpFetcher;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use VDB\Uri\Uri;

class HttpFetcherTest extends TestCase
{

    protected $fetcher;

    protected function setUp()
    {
        parent::setUp();
        $this->fetcher = new HttpFetcher(new Config, new Uri('http://example.org'));
    }

    public function testSetup()
    {
        $this->assertInstanceOf('Skrape\\Meta\\Config', $this->fetcher->getConfig());
        $this->assertInstanceOf('VDB\\Uri\\UriInterface', $this->fetcher->getUri());
    }

    public function testGetClient()
    {
        $this->assertInstanceOf('GuzzleHttp\\Client', $this->fetcher->getClient());
    }

    public function testSetClient()
    {
        $client = new Client;
        $this->fetcher->setClient($client);
        $this->assertSame($client, $this->fetcher->getClient());
    }

    public function testFetch()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0])
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->fetcher->setClient($client);
        $response = $this->fetcher->fetch();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReason());
        $this->assertInstanceOf('Skrape\\Response', $response);
    }

    /** @expectedException GuzzleHttp\Exception\RequestException */
    public function testFetchError()
    {
        $mock = new MockHandler([
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->fetcher->setClient($client);
        $this->fetcher->fetch();
    }
}
