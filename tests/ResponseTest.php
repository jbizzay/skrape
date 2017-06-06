<?php
namespace Skrape\Tests;

use Skrape\Response;

class ResponseTest extends TestCase
{

    protected function setUp()
    {
        parent::setUp();
        $this->response = new Response(
            200,
            $this->testHeaders,
            'foobar',
            '1.1',
            'OK'
        );
    }

    public function testSetup()
    {
        $response = new Response;
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetStatusCode()
    {
        $this->assertSame(200, $this->response->getStatusCode());
    }

    public function testGetHeaders()
    {
        $this->assertEquals('-1', $this->response->getHeaders()['EXPIRES']);
    }

    public function testGetHeadersRaw()
    {
        $this->assertEquals('-1', $this->response->getHeaders(false)['expires'][0]);
    }

    public function testGetHeader()
    {
        $this->assertEquals('-1', $this->response->getHeader('expires'));
        $this->assertEquals('-1', $this->response->getHeader('eXpiRes'));
        $this->assertNull($this->response->getHeader('notexists'));
    }

    public function testGetBody()
    {
        $this->assertSame('foobar', $this->response->getBody());
    }

    public function testGetVersion()
    {
        $this->assertSame('1.1', $this->response->getVersion());
    }

    public function testGetReason()
    {
        $this->assertSame('OK', $this->response->getReason());
    }

    public function testMeta()
    {
        $this->response->getMeta()->set('test.ing', 123);
        $this->assertSame(123, $this->response->getMeta()->get('test.ing'));
    }

}
