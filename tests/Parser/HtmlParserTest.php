<?php
namespace Skrape\Tests\Parser;

use Skrape\Tests\TestCase;
use Skrape\Parser\HtmlParser;
use Skrape\Response;
use VDB\Uri\Uri;

class HtmlParserTest extends TestCase
{

    protected function getParser($htmlPart)
    {
        $html = '<html>' . $htmlPart . '</html>';
        $response = new Response(200, ['Content-type' => ['text/html']], $html);
        return new HtmlParser($response, new Uri('http://example.org'));
    }

    public function testGetCrawler()
    {
        $parser = $this->getParser('');
        $this->assertInstanceOf('Symfony\\Component\\DomCrawler\\Crawler', $parser->getCrawler());
    }

    public function testGetDescription()
    {
        $parser = $this->getParser('<meta name="description" content="Example description" />');
        $this->assertEquals('Example description', $parser->getDescription());
    }

    public function testGetFavicon()
    {
        foreach ([
            '<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">',
            '<link rel="icon" href="favicon.ico" type="image/x-icon">',
            '<link rel="icon" href="/favicon.ico" type="image/x-icon">',
            '<link rel="icon" href="/test/../favicon.ico" type="image/x-icon">',
            '<link rel="icon" href="http://example.org/favicon.ico" type="image/x-icon">'
        ] as $htmlPart) {
            $parser = $this->getParser($htmlPart);
            $this->assertEquals('http://example.org/favicon.ico', $parser->getFavicon());
        }
    }

    public function testGetFeeds()
    {
        $html = '<a href="http://feeds.feedburner.com/Example"></a>';
        $parser = $this->getParser($html);
        $this->assertEquals([
            'http://feeds.feedburner.com/Example'
        ], $parser->getFeeds());
    }

    public function testGetImage()
    {
        $html = [
            '<meta property="og:image" content="http://example.org/featured.png">',
            '<meta property="og:image" content="/featured.png">'
        ];
        foreach ($html as $htmlPart) {
            $parser = $this->getParser($htmlPart);
            $this->assertEquals('http://example.org/featured.png', $parser->getImage());
        }
    }

    public function testGetImages()
    {
        $html = '
            <meta property="og:image" content="http://example.org/featured.png">
            <img src="/logo.svg" />
            <img src="logo.svg" />
            <img src="http://external.com/logo.svg" />
        ';
        $parser = $this->getParser($html);
        $this->assertEquals([
            'http://example.org/featured.png',
            'http://example.org/logo.svg',
            'http://external.com/logo.svg'
        ], $parser->getImages());
    }

    public function testGetKeywords()
    {
        $html = '<meta property="keywords" content="Example keywords">
        <meta property="keywords" content="Example, keywords">';
        $parser = $this->getParser($html);
        $this->assertEquals([
            'Example keywords',
            'Example',
            'keywords'
        ], $parser->getKeywords());
    }

    public function testGetLinks()
    {
        $html = '
            <a href="test" class="test-class" id="test-id">Link Text</a>
        ';
        $parser = $this->getParser($html);
        $this->assertEquals([
            [
                'text' => 'Link Text',
                'href' => 'test',
                'class' => 'test-class',
                'id' => 'test-id',
                'uri' => 'http://example.org/test'
            ]
        ], $parser->getLinks());
    }

    public function testGetMeta()
    {
        $html = '
            <meta property="og:description" content="Description">
            <meta name="theme" content="#222222">
        ';
        $parser = $this->getParser($html);
        $this->assertEquals('Description', $parser->getMeta('og:description')['content']);
        $this->assertEquals('#222222', $parser->getMeta('theme')['content']);
    }

    public function testGetMetas()
    {
        $html = '
            <meta property="og:description" content="Description">
            <meta name="theme" content="#222222">
        ';
        $parser = $this->getParser($html);
        $this->assertEquals([
            [
                'property' => 'og:description',
                'content' => 'Description'
            ],
            [
                'name' => 'theme',
                'content' => '#222222'
            ]
        ], $parser->getMetas());
    }

    public function testGetTitle()
    {
        $parser = $this->getParser('<title>Example Page A</title>');
        $this->assertEquals('Example Page A', $parser->getTitle());
    }
}
