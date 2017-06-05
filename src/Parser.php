<?php

namespace Skrape;

use Symfony\Component\DomCrawler\Crawler;

class Parser
{

    protected $crawler;
    protected $skrape;

    public function __construct($skrape)
    {
        $this->skrape = $skrape;
        $response = $this->skrape->getResponse();
        $this->crawler = new Crawler($response->getBody());
    }

    public function getLinks()
    {
        return [];
    }

}
