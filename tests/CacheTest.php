<?php
namespace Skrape\Tests;

use Skrape\Skrape;
use Skrape\Cache;
use Skrape\Config;
use VDB\Uri\Uri;

class CacheTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        Config::setGlobal('cache.location', '/tmp/skrape-test');
    }

    public function testSetup()
    {
        $cache = new Cache(new Config, new Uri('http://example.org'));
        $this->assertInstanceOf('Skrape\\Config', $cache->getConfig());
        $this->assertInstanceOf('VDB\\Uri\\UriInterface', $cache->getUri());
    }

    public function testGetFilesystem()
    {
        $cache = new Cache(new Config, new Uri('http://example.org'));
        $this->assertInstanceOf('Symfony\\Component\\Filesystem\\Filesystem', $cache->getFilesystem());
    }

    public function testGetCacheDirectory()
    {
        $cache = new Cache(new Config, new Uri('http://example.org'));
        $this->assertEquals('/tmp/skrape-test/example.org/', $cache->getCacheDirectory());
    }

    public function testGetCacheFilepath()
    {
        $cache = new Cache(new Config, new Uri('http://example.org'));
        $this->assertEquals('/tmp/skrape-test/example.org/http-example.org', $cache->getCacheFilepath());
    }

    public function testGetCacheFilepathFullUri()
    {
        $cache = new Cache(new Config, new Uri('http://example.org/foo/bar?hello=test&bye=1'));
        $this->assertEquals('/tmp/skrape-test/example.org/http-example.org-foo-bar?hello=test&bye=1', $cache->getCacheFilepath());
    }

    public function testStoreInCacheAndFetch()
    {
        $cache = new Cache(new Config, new Uri('http://example.org'));
        $filepath = $cache->getCacheFilepath();
        $data = ['foo' => 'bar'];
        $cache->store($data);
        $this->assertFileExists($filepath);
        $this->assertEquals('bar', $cache->fetch()['foo']);
    }
}
