<?php

namespace Skrape;

use Skrape\Parser\Html;
use Skrape\Parser\Feed;
use Skrape\Parser\Image;
use Skrape\Parser\Json;
use Skrape\Cache;
use Skrape\Meta\Config;
use Skrape\Meta\Meta;
use Skrape\Response;
use Skrape\Fetcher\FetcherInterface;
use Skrape\Fetcher\HttpFetcher;
use Skrape\Exception\UriInvalidException;

use VDB\Uri\Uri;
use VDB\Uri\UriInterface;

class Skrape {

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var FetcherInterface
     */
    protected $fetcher;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var UriInterface
     */
    protected $uri;

    /**
     * Create a new Skrape
     * @param UriInterface|string $uri
     * @param string|boolean $autoScheme
     */
    public function __construct($uri, $autoScheme = 'http')
    {
        try {
            if ($uri instanceof UriInterface) {
                $this->uri = $uri;
            }
            else if (is_string($uri)) {
                $uriString = $uri;
                $parts = parse_url($uri);
                if (empty($parts['scheme'])) {
                    if ( ! $autoScheme) {
                        throw new UriInvalidException('URI missing scheme');
                    }
                    $uriString = $autoScheme . '://' . $uri;
                }
                $this->uri = new Uri($uriString);
            }
        } catch (\Exception $e) {
            throw new Exception\UriInvalidException('Invalid URI: ' . (string) $uri . ' Message: ' . $e->getMessage());
        }
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        if ( ! $this->config) {
            $this->config = new Config;
        }
        return $this->config;
    }

    /**
     * Set this Skrape's config
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return UriInterace
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        if ( ! $this->cache) {
            $this->cache = new Cache($this->getConfig(), $this->getUri());
        }
        return $this->cache;
    }

    /**
     * @param Cache
     * @return $this
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return FetcherInterface
     */
    public function getFetcher()
    {
        if ( ! $this->fetcher) {
            $this->fetcher = new HttpFetcher($this->getConfig(), $this->getUri());
        }
        return $this->fetcher;
    }

    /**
     * @var FetcherInterface
     */
    public function setFetcher(FetcherInterface $fetcher)
    {
        $this->fetcher = $fetcher;
        return $this;
    }

    /**
     * Get response of resource
     * @return Response
     */
    public function fetch()
    {
        $this->response = null;
        $source = null;
        if ($this->getConfig()->get('cache.fetch')) {
            $this->response = $this->getCache()->fetch();
            $source = 'cache';
        }
        if ( ! $this->response) {
            $fetcher = $this->getFetcher();
            $this->response = $fetcher->fetch();
            $source = 'http';
            $this->response->getMeta()->set([
                'http' => [
                    'date_fetched' => time()
                ]
            ]);
        }
        // Store response in cache if from http and config allows
        if ($this->getConfig()->get('cache.store') && $source != 'cache') {
            $this->response->getMeta()->set('cache.date_stored', time());
            $this->getCache()->store($this->response);
        }
        $this->response->getMeta()->set('source', $source);
        return $this->response;
    }

    /**
     * Get a Parser based on response media type
     * @return Parser
     */
    public function getParser()
    {
        if ($this->response && ! $this->parser) {
            $type = $this->response->getMediaType();
            $class = 'Skrape\\Parser\\' . ucwords($type) . 'Parser';
            if (class_exists($class)) {
                $this->parser = new $class($this->response, $this->uri, $this->getConfig());
            }
        }
        return $this->parser;
    }

    /**
     * Parse data from this resource, e.g.
     * $skrape->parse(['title', 'description', 'image']);
     * $skrape->parse(); // To get everything possible
     * @param array|null $methods
     * @return array
     */
    public function parse($methods = null)
    {
        $parser = $this->getParser();
        if ( ! $parser) {
            throw new \Exception('Couldnt get parser');
        }
        $parsed = [];
        if ( ! $methods) {
            $methods = $parser->getAllMethods();
        }
        foreach ($methods as $method) {
            $methodName = 'get' . ucwords($method);
            if (method_exists($parser, $methodName)) {
                $parsed[$method] = $parser->$methodName();
            } else {
                $parsed[$method] = null;
            }
        }
        return $parsed;
    }

}
