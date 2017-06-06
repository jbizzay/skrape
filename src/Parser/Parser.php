<?php
namespace Skrape\Parser;

use Skrape\Response;
use Skrape\Config;
use VDB\Uri\UriInterface;

class Parser
{

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Uri
     */
    protected $uri;

    /**
     * When parsing "*", what methods to call
     * @var array
     */
    protected $allMethods = [];

    /**
     * @param Response
     * @param Config
     */
    public function __construct(Response $response, UriInterface $uri, $config = null)
    {
        $this->response = $response;
        $this->uri = $uri;
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getAllMethods()
    {
        return $this->allMethods;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return UriInterface
     */
    public function getUri()
    {
        return $this->uri;
    }

}
