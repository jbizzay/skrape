<?php
namespace Skrape\Tests\Meta;

use Skrape\Tests\TestCase;
use Skrape\Meta\Meta;

class MetaTest extends TestCase
{
    /**
     * @var Meta
     */
    protected $meta;

    protected function setUp()
    {
        parent::setUp();
        $this->meta = new Meta('foo', [
            'bar' => true,
            'qux' => true
        ]);
    }

    public function testSetThroughConstructorSetsOptions()
    {
        $meta = new Meta('test', 'example');
        $this->assertEquals('example', $meta->get('test'));
    }

    public function testGet()
    {
        $this->assertTrue($this->meta->get()['foo']['bar']);
    }

    public function testGetByKey()
    {
        $this->assertTrue($this->meta->get('foo')['bar']);
    }

    public function testGetByKeyDot()
    {
        $this->assertTrue($this->meta->get('foo.bar'));
    }

    public function testGetNotSetReturnsNull()
    {
        $this->assertNull($this->meta->get('notexists'));
        $this->assertNull($this->meta->get('not.exist.s'));
    }

    public function testSet()
    {
        $this->meta->set(['test' => ['param' => true]]);
        $this->assertTrue($this->meta->get()['test']['param']);
    }

    public function testSetByKey()
    {
        $this->meta->set('test', ['param' => true]);
        $this->assertTrue($this->meta->get()['test']['param']);
    }

    public function testSetByKeyDot()
    {
        $this->meta->set('test.param', true);
        $this->assertTrue($this->meta->get()['test']['param']);
    }

    public function testSetSingleValueRetainsOtherValuesInNamespace()
    {
        $this->meta->set(['test' => ['param1' => true, 'param2' => true]]);
        $this->meta->set('test', ['param2' => false]);
        // Should retain other values in same namespace
        $this->assertTrue($this->meta->get()['test']['param1']);
    }
}
