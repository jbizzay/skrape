<?php
namespace Skrape\Tests\Parser;

use Skrape\Tests\TestCase;
use Skrape\Parser\Parser;
use Skrape\Response;
use Skrape\Meta\Config;
use VDB\Uri\Uri;

class ParserTest extends TestCase
{
    public function testSetup()
    {
        $parser = new Parser(new Response, new Uri('http://example.org'), new Config);
        $this->assertInstanceOf('Skrape\\Response', $parser->getResponse());
        $this->assertInstanceOf('VDB\\Uri\\Uri', $parser->getUri());
        $this->assertInstanceOf('Skrape\\Meta\\Config', $parser->getConfig());
    }
}
