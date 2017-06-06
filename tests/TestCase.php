<?php
namespace Skrape\Tests;

use Skrape\Skrape;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Stream;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $testHeaders = [
        'date' => ['Mon, 05 Jun 2017 09:36:41 GMT'],
        'expires' => ['-1'],
        'cache-control' => ['private, max-age=0'],
        'content-type' => ['text/html; charset=ISO-8859-1'],
        'p3p' => ['CP="This is not a P3P policy! See https://www.google.com/support/accounts/answer/151657?hl=en for more info."'],
        'server' => ['gws'],
        'x-xss-protection' => ['1; mode=block'],
        'x-frame-options' => ['SAMEORIGIN'],
        'set-cookie' => ['NID=104=Gs_vm4U3s5Ikynp49YGuPMVA70ZfUPde65hJt_-i8_qpvJS60fMd8npEOaugmJmK9Fi6Gkbu-Yy5KrNkLFSTdY71FbIyvIontOmWp805ZDJCh5qANIz1oXtfl2euonTtWJpMp8-q75_RuGgZeQ; expires=Tue, 05-Dec-2017 09:36:41 GMT; path=/; domain=.google.com; HttpOnly'],
        'accept-ranges' => ['none'],
        'vary' => ['Accept-Encoding'],
        'transfer-encoding' => ['chunked']
    ];

    protected function getMockSkrape($uri, $filename)
    {
        $body = file_get_contents(__DIR__ . '/Fixtures/' . $filename);
        $mock = new MockHandler([
            new Response(200, ['content-type' => ['text/html; charset=ISO-8859-1']], $body)
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $skrape = new Skrape($uri);
        $skrape->getConfig()->set('cache.fetch', false);
        $skrape->getFetcher()->setClient($client);
        return $skrape;
    }
}
