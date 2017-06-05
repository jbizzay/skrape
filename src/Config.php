<?php
namespace Skrape;

class Config
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
     * Custom options for this Config
     * @var array
     */
    protected $options = [];

    /**
     * @param string|array $keyOrOptions
     *  Options to set on this Config
     * @param any $value
     *  Value to set on this Config
     */
    public function __construct($keyOrOptions = [], $value = null)
    {
        $this->options = self::setOptions($this->options, $keyOrOptions, $value);
    }

    /**
     * Get default config value
     * @param string $key
     * @return any config value
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
     * Get global config value
     * @param string $key
     * @return any config value
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
     * Set global config value that will used by all
     * Config classes
     * @param string|array $keyOrOptions
     * @param any $value
     */
    public static function setGlobal($keyOrOptions, $value = null)
    {
        self::$globalOptions = self::setOptions(self::$globalOptions, $keyOrOptions, $value);
    }

    /**
     * Get Config value
     * @param string $key
     * @return any config value
     */
    public function get($key = null)
    {
        $options = array_merge(self::getDefault(), self::getGlobal(), $this->options);
        return self::lookup($options, $key);
    }

    /**
     * Set config value for this config
     * @param string|array $keyOrOptions
     * @param mixed $value
     * @return Object $this
     */
    public function set($keyOrOptions, $value = null)
    {
        $this->options = self::setOptions($this->options, $keyOrOptions, $value);
        return $this;
    }

    /**
     * Get value out of an array of options
     * by key or dot notation
     * @param array $options
     * @param string $key
     * @return any config value
     */
    protected static function lookup(array $options, $key = null)
    {
        if ($key) {
            $parts = explode('.', $key);
            $count = count($parts);
            $current = $options;
            foreach ($parts as $i => $part) {
                if ( ! isset($current[$part])) {
                    return null;
                }
                if ($i + 1 == $count) {
                    return $current[$part];
                }
                $current = $current[$part];
            }
        }
        return $options;
    }

    /**
     * Set value on an array
     * @param array $options
     * @param string|array $keyOrOptions
     * @param any $value
     * @return array $options
     */
    protected static function setOptions(array $options, $keyOrOptions, $value)
    {
        if (is_array($keyOrOptions)) {
            $options = array_replace_recursive($options, $keyOrOptions);
        } elseif (is_string($keyOrOptions)) {
            $parts = explode('.', $keyOrOptions);
            $current = &$options;
            $count = count($parts);
            foreach ($parts as $i => $part) {
                if ( ! isset($current[$part])) {
                    $current[$part] = [];
                }
                if ($i + 1 == $count) {
                    if (is_array($value)) {
                        $current[$part] = array_replace_recursive($current[$part], $value);
                    } else {
                        $current[$part] = $value;
                    }
                    continue;
                }
                $current = &$current[$part];
            }
        }
        return $options;
    }

}
