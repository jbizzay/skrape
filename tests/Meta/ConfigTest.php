<?php
namespace Skrape\Tests\Meta;

use Skrape\Tests\TestCase;
use Skrape\Meta\Config;

class ConfigTest extends TestCase
{

    protected function setUp()
    {
        parent::setUp();
        Config::clearGlobal();
        Config::setGlobal('foo', ['bar' => true, 'qux' => true]);
    }

    public function testGetDefault()
    {
        $this->assertArrayHasKey('request', Config::getDefault());
        $this->assertArrayHasKey('cache', Config::getDefault());
    }

    public function testGetDefaultByKey()
    {
        $this->assertTrue(Config::getDefault('cache')['fetch']);
    }

    public function testGetDefaultByDot()
    {
        $this->assertTrue(Config::getDefault('request.verify'));
    }

    public function testGetGlobal()
    {
        $this->assertTrue(Config::getGlobal()['foo']['bar']);
    }

    public function testGetGlobalByKey()
    {
        $this->assertTrue(Config::getGlobal('foo')['bar']);
    }

    public function testGetGlobalByDot()
    {
        $this->assertTrue(Config::getGlobal('foo.bar'));
    }

    public function testClearGlobal()
    {
        Config::clearGlobal();
        $this->assertEmpty(Config::getGlobal());
    }

    public function testSetGlobal()
    {
        Config::setGlobal([
            'foo' => [
                'bar' => 'newval'
            ]
        ]);
        $config = new Config;
        $this->assertEquals('newval', Config::getGlobal('foo')['bar']);
        $this->assertEquals('newval', $config->get('foo')['bar']);
        // Setting a global value should apply to existing objects
        Config::setGlobal([
            'foo' => [
                'bar' => 'anothernewval'
            ]
        ]);
        $this->assertEquals('anothernewval', $config->get('foo')['bar']);
    }

    public function testSetGlobalByKey()
    {
        Config::setGlobal('foo', [
            'bar' => 'baz'
        ]);
        $this->assertEquals('baz', Config::getGlobal('foo')['bar']);
    }

    public function testSetGlobalByKeyDot()
    {
        Config::setGlobal('foo.bar', 'baz');
        $this->assertEquals('baz', Config::getGlobal('foo')['bar']);
    }

    public function testGetFromClassUsesDefaults()
    {
        $config = new Config;
        $this->assertArrayHasKey('request', $config->get());
        $this->assertArrayHasKey('cache', $config->get());
    }

    public function testGetOverrides()
    {
        $config = new Config;
        $this->assertTrue($config->get()['cache']['fetch']);
        // Global should override default
        Config::setGlobal('cache', ['fetch' => 'global']);
        $this->assertEquals('global', $config->get()['cache']['fetch']);
        // Instance should override global
        $config->set('cache', ['fetch' => 'instance']);
        $this->assertEquals('instance', $config->get()['cache']['fetch']);
    }

}
