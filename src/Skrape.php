<?php

namespace Jbizzay\Skrape;

use Jbizzay\Skrape\Parser\Html;
use Jbizzay\Skrape\Response;
use GuzzleHttp\Client;
use Symfony\Component\Filesystem\Filesystem;

class Skrape {

    /**
     * Map media type to parser
     * @var array
     */
    protected $class_map = [
        'html' => 'Html',
        'xml' => 'Feed',
        'rss+xml' => 'Feed',
        'atom+xml' => 'Feed',
        'image' => 'Image',
        'json' => 'Json'
    ];

    /**
     * Skrape object config
     * @var array
     */
    protected $config = [];

    /**
     * Default Skrape config
     * @var array
     */
    public static $default_config = [
        'get_from_cache' => true,
        'get_only_from_cache' => false,
        'store_in_cache' => true,
        'cache_directory' => '/tmp/skrape'
    ];

    /**
     * Default Guzzle options
     * @var array
     */
    public static $default_options = [
        'allow_redirects' => [
            'max'             => 5,
            'strict'          => false,
            'referer'         => true,
            'protocols'       => ['http', 'https'],
            'track_redirects' => false
        ],
        'connect_timeout' => 20,
        'decode_content' => true,
        'expect' => 1048576,
        'http_errors' => true,
        'stream' => false,
        'verify' => true,
        'timeout' => 20,
        'version' => '1.1',
        'headers' => [
            'User-Agent' => 'Popwords 0.1'
        ],
        'curl' => [
            //CURLOPT_SSLVERSION => CURL_SSLVERSION_SSLv3
        ]
    ];

    /**
     * Filesystem used for file based caching
     * @var Filesystem
     */
    protected static $filesystem;

    /**
     * High level info about the Skrape object
     * @var array
     */
    protected $info = [
        'from_cache' => false,
        'stored_in_cache' => false
    ];

    /**
     * @var
     */
    protected $mapped_type;

    /**
     * @var
     */
    protected $media_type;

    /**
     * Skrape object Guzzle options
     * @var array
     */
    protected $options = [];

    protected $parser;

    protected $response;

    public $uri;

    public function __construct($uri)
    {
        if (is_string($uri)) {
            $parts = parse_url($uri);
            if (empty($parts['scheme'])) {
                $uri = 'http://' . $uri;
            }
            $this->uri = new Uri($uri);
        } else {
            $this->uri = $uri;
        }
        if ( ! $this->uri) {
            throw new \Exception("Unable to set uri");
        }
        $this->config = self::$default_config;
        $this->options = self::$default_options;
        self::$filesystem = new Filesystem;
    }

    /**
     * Load resource based on type
     * @return mixed
     */
    public function fetch()
    {
        $this->response = null;

        // Check cached
        if ($this->config['get_from_cache']) {
            $this->response = $this->getCached();
            if ($this->response) {
                $this->info['from_cache'] = true;
            }
        }

        if ( ! $this->response && ! $this->config['get_only_from_cache']) {
            $client = new Client(self::$default_options);
            try {
                $response = $client->get((string) $this->uri, $this->options);
            } catch (\Exception $e) {
                throw new \Exception(' URL: ' . (string) $this->uri);
            }
            $this->response = new Response;
            $this->response->setResponse($response);
            if ($this->config['store_in_cache']) {
                $directory = $this->getCacheDirectory();
                $file_path = $directory . '/' . $this->getCacheFilename();
                self::$filesystem->dumpFile($file_path, serialize($this->response));
                $this->info['stored_in_cache'] = true;
            }
        }

        if ($this->response) {
            $this->media_type = $this->response->getMediaType();
            $this->mapped_type = $this->class_map[$this->media_type];
            $class = 'Jbizzay\\Skrape\\Parser\\' . $this->mapped_type;
            $this->parser = new $class($this);
            return true;
        }

        return false;
    }

    public function getBody()
    {
        return $this->response->getBody();
    }

    public function getCached()
    {
        $file_path = $this->getCacheDirectory() . '/' . $this->getCacheFilename();
        if (self::$filesystem->exists($file_path)) {
            return unserialize(file_get_contents($file_path));
        }
    }

    public function getCacheDirectory()
    {
        $directory = rtrim($this->config['cache_directory'], '/') . '/' . $this->uri->getHost();
        if ( ! self::$filesystem->exists($directory)) {
            self::$filesystem->mkdir($directory, 0775);
        }
        return $directory;
    }

    public function getCacheFilename()
    {
        return str_replace('/', '-', (string) $this->uri);
    }

    public function getConfig()
    {
        return $this->config;
    }

    public static function getDefaultConfig()
    {
        return self::$default_config;
    }

    public static function getDefaultOptions()
    {
        return self::$default_options;
    }

    public function getInfo()
    {
        $info = [];
        $info['url'] = (string) $this->uri;
        if ($this->response) {
            $info['status_code'] = $this->response->getStatusCode();
            $info['reason_phrase'] = $this->response->getReasonPhrase();
            $info['content_type'] = $this->response->getContentType();
            $info['media_type'] = $this->media_type;
        }
        $info['cache_filepath'] = $this->getCacheDirectory() . '/' . $this->getCacheFilename();
        $info = array_merge($info, $this->info);
        return $info;
    }

    public function getLinks()
    {
        if ($this->media_type == 'html') {
            return $this->parser->getLinks();
        }
        return [];
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getParse($parse_methods)
    {
        $info = [];
        foreach ($parse_methods as $method) {
            $method_name = 'get' . ucwords($method);
            if (method_exists($this->parser, $method_name)) {
                $info[$method] = $this->parser->$method_name();
            } else {
                $info[$method] = null;
            }
        }
        return $info;
    }

    public static function setDefaultConfig($config)
    {
        self::$default_config = array_merge(self::$default_config, $config);
    }

    public static function setDefaultOptions($options)
    {
        self::$default_options = array_merge(self::$default_options, $options);
    }

    public function setOptions()
    {
        return $this->options;
    }

    public function setUri(Uri $uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @param array $class_map
     */
    public function setClassMap($class_map)
    {
        $this->class_map = $class_map;
    }

}
