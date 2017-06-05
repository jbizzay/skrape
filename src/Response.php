<?php

namespace Skrape;

use Skrape\Config;

class Response {

    /** @var string **/
    protected $protocol;

    /** @var array Raw headers set on the response **/
    protected $headers = [];

    /** @var array Normalized headers **/
    protected $headersNormalized = [];

    /** @var string Body of the response **/
    protected $body = '';

    /** @var string **/
    protected $reasonPhrase;

    /** @var int Status code of the response **/
    protected $statusCode;

    /** @var Config Any custom data to be stored in the cache **/
    protected $meta;

    /**
     * @param int $status Status code fro the response
     * @param array $headers Headers for the response
     * @param mixed $body Body of the response
     * @param string $version Protocal version
     * @param string $reason Reason phrase
     */
    public function __construct($status, $headers, $body, $version, $reason)
    {
        $this->statusCode = (int) $status;
        $this->headers = $headers;
        $this->normalizeHeaders();
        $this->body = $body;
        $this->protocol = $version;
        $this->reasonPhrase = $reason;
    }

    /**
     * @return int $statusCode
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get headers from the response
     * @param boolean $normalized
     * @return array $headers
     */
    public function getHeaders($normalized = true)
    {
        if ($normalized) {
            return $this->headersNormalized;
        }
        return $this->headers;
    }

    /**
     * Get a header value
     * @param string $key
     * @return string $value
     */
    public function getHeader($key)
    {
        $key = strtoupper($key);
        return isset($this->headersNormalized[$key]) ? $this->headersNormalized[$key] : null;
    }

    /**
     * @return string $body
     */
    public function getBody()
    {
        return (string) $this->body;
    }

    /**
     * @return string $protocol
     */
    public function getVersion()
    {
        return (string) $this->protocol;
    }

    /**
     * @return string $reasonPhrase
     */
    public function getReason()
    {
        return $this->reasonPhrase;
    }

    /**
     * Convert headers into easier to access array
     * @return void
     */
    protected function normalizeHeaders()
    {
        foreach ($this->headers as $key => $header) {
            $key = strtoupper($key);
            $this->headersNormalized[$key] = $header[0];
        }
    }

    /**
     * Get Meta config, will be cached with this response
     * @return Config
     */
    public function getMeta()
    {
        if ( ! $this->meta) {
            $this->meta = new Config;
        }
        return $this->meta;
    }

}
