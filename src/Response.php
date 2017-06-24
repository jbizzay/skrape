<?php

namespace Skrape;

use Skrape\Meta\Meta;
use webignition\InternetMediaType\Parser\TypeParser;
use webignition\InternetMediaType\Parser\SubtypeParser;

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
    public function __construct($status = 200, $headers = [], $body = '', $version = '1.1', $reason = 'OK')
    {
        $this->statusCode = (int) $status;
        $this->headers = $headers;
        $this->normalizeHeaders();
        $this->body = (string) $body;
        $this->protocol = $version;
        $this->reasonPhrase = $reason;
    }

    public function getDebug($bodyLength = 100)
    {
        $debug = [];
        foreach ([
            'StatusCode',
            'Headers',
            'Version',
            'Reason',
            'ContentType',
            'MediaType'
        ] as $method) {
            $call = 'get' . $method;
            $debug[$method] = $this->$call();
        }
        $debug['Meta'] = $this->getMeta()->get();
        if ($bodyLength) {
            $debug['Body_' . $bodyLength] = substr($this->getBody(), 0, 100);
        } else {
            $debug['Body'] = $this->getBody();
        }
        return $debug;
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
        return $this->headersNormalized;
    }

    /**
     * Get Meta object, will be cached with this response
     * @return Meta
     */
    public function getMeta()
    {
        if ( ! $this->meta) {
            $this->meta = new Meta;
        }
        return $this->meta;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->getHeader('CONTENT-TYPE');
    }

    /**
     * Get the media type of the response
     * @return string
     */
    public function getMediaType()
    {
        $contentType = $this->getContentType();
        if ( ! $contentType) {
            throw new \Exception('Couldnt get content type');
        }
        $typeParser = new TypeParser;
        $type = $typeParser->parse($contentType);
        switch ($type) {
            case 'text':
            case 'xml':
            case 'application':
                $subParser = new SubtypeParser;
                return $subParser->parse($contentType);
            break;
        }
        return $type;
    }

}
