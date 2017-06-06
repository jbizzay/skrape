<?php
namespace Skrape\Meta;

use Skrape\Meta\Meta;

class Config extends Meta
{

    /**
     * Default caching options
     * @var array
     */
    CONST CACHE_OPTIONS = [
        'fetch' => true,
        'location' => '/tmp/skrape',
        'store' => true
    ];

    /**
     * Default Guzzle request options
     * @var array
     */
    CONST REQUEST_OPTIONS = [
        'request' => [
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
        ]
    ];


    /**
     * Global options to be used for each new Config
     * @var array
     */
    protected static $globalOptions = [];


    /**
     * Get default config data
     * @param string $key
     * @return any
     */
    public static function getDefault($key = null)
    {
        $defaults = [
            'cache' => self::CACHE_OPTIONS,
            'request' => self::REQUEST_OPTIONS
        ];
        return self::lookup($defaults, $key);
    }

    /**
     * Get global config data
     * @param string $key
     * @return any
     */
    public static function getGlobal($key = null)
    {
        return self::lookup(self::$globalOptions, $key);
    }

    /**
     * Clear global config
     */
    public static function clearGlobal()
    {
        self::$globalOptions = [];
    }

    /**
     * Set global config data that will used by all
     * Config classes
     * @param string|array $keyOrData
     * @param any $data
     */
    public static function setGlobal($keyOrData, $data = null)
    {
        self::$globalOptions = self::setData(self::$globalOptions, $keyOrData, $data);
    }

    /**
     * Get meta data, override to include default and global
     * @param string $key
     * @return any
     */
    public function get($key = null)
    {
        $data = array_merge(self::getDefault(), self::getGlobal(), $this->data);
        return self::lookup($data, $key);
    }


}
