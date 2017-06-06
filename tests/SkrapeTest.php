<?php
namespace Skrape\Tests;

use Skrape\Skrape;
use Skrape\Fetcher\HttpFetcher;
use Skrape\Response;
use Skrape\Cache;
use Skrape\Meta\Config;
use VDB\Uri\Uri;
use VDB\Uri\UriInterface;

class SkrapeTest extends TestCase
{

    protected $skrape;

    protected function setUp()
    {
        parent::setUp();
        $this->skrape = new Skrape('example.org');
        $this->skrape->getConfig()->set('cache.location', '/tmp/skrape-test');
    }

    public function testChaining()
    {
        $ref = $this->skrape
            ->setConfig(new Config)
            ->setFetcher(new HttpFetcher(new Config, new Uri('http://example.org')))
            ->setCache(new Cache($this->skrape->getConfig(), $this->skrape->getUri()));
        $this->assertInstanceOf('Skrape\\Skrape', $ref);
    }

    public function testSetupUriByString()
    {
        $skrape = new Skrape('http://example.org');
        $this->assertInstanceOf('VDB\\Uri\\UriInterface', $skrape->getUri());
    }

    public function testSetupUriByStringAutoScheme()
    {
        $skrape = new Skrape('example.org');
        $this->assertEquals('http://example.org', $skrape->getUri()->toString());
    }

    public function testSetupUriByStringAutoSchemeHttps()
    {
        $skrape = new Skrape('example.org', 'https');
        $this->assertEquals('https://example.org', $skrape->getUri()->toString());
    }

    public function testSetupUriByClass()
    {
        $skrape = new Skrape(new Uri('example.org'));
        $this->assertInstanceOf('VDB\\Uri\\UriInterface', $skrape->getUri());
    }

    /**
     * @expectedException Skrape\Exception\UriInvalidException
     */
    public function testSetupUriInvalid()
    {
        $skrape = new Skrape('example', false);
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf('Skrape\\Meta\\Config', $this->skrape->getConfig());
    }

    public function testSetConfig()
    {
        $config = new Config('foo', 'bar');
        $this->skrape->setConfig($config);
        $this->assertEquals('bar', $this->skrape->getConfig()->get('foo'));
    }

    public function testGetCache()
    {
        $this->assertInstanceOf('Skrape\\Cache', $this->skrape->getCache());
    }

    public function testSetCache()
    {
        $cache = new Cache($this->skrape->getConfig(), $this->skrape->getUri());
        $this->skrape->setCache($cache);
        $this->assertSame($cache, $this->skrape->getCache());
    }

    public function testResponse()
    {
        $this->assertNull($this->skrape->getResponse());
    }

    public function testFetchFromHttpAndCache()
    {
        $this->skrape->getConfig()->set('cache', [
            'fetch' => false,
            'store' => true
        ]);
        $response = $this->skrape->fetch();

        $this->assertEquals('http', $response->getMeta()->get('source'));
        $this->assertNotEmpty($response->getMeta()->get('http.date_fetched'));

        // Next fetch should come from cache
        $this->skrape->getConfig()->set('cache.fetch', true);

        $response = $this->skrape->fetch();
        $this->assertInstanceOf('Skrape\\Response', $response);
        $this->assertEquals('cache', $response->getMeta()->get('source'));

        $response = $this->skrape->getResponse();
        $this->assertInstanceOf('Skrape\\Response', $response);

        $this->assertNotEmpty($response->getMeta()->get('cache.date_stored'));
    }

    public function testGetParser()
    {
        $skrape = $this->getMockSkrape('example.org', 'example-page-a.html');
        $response = $skrape->fetch();
        $this->assertInstanceOf('Skrape\\Parser\\HtmlParser', $skrape->getParser());
        $this->assertSame($skrape->getResponse(), $skrape->getParser()->getResponse());
    }

    public function testParse()
    {
        $skrape = $this->getMockSkrape('example.org', 'example-page-a.html');
        $response = $skrape->fetch();
        $parsed = $skrape->parse(['title', 'image', 'description']);
        $this->assertEquals('Example Page A', $parsed['title']);
        $this->assertEquals('http://example.org/image.png', $parsed['image']);
        $this->assertEquals('Example description', $parsed['description']);

        $parsed = $skrape->parse();
        $this->assertEquals('Example Page A', $parsed['title']);
    }

}
