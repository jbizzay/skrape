<?php
namespace Skrape\Tests;

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
}
