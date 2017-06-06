<?php
namespace Skrape\Fetcher;

use Skrape\Meta\Config;
use VDB\Uri\UriInterface;

abstract class Fetcher
{
    protected $config;
    protected $uri;

    public function __construct(Config $config, UriInterface $uri)
    {
        $this->config = $config;
        $this->uri = $uri;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getUri()
    {
        return $this->uri;
    }
}
