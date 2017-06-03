<?php

namespace Jbizzay\Skrape;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use webignition\InternetMediaType\Parser\SubtypeParser;
use webignition\InternetMediaType\Parser\Parser as TypeParser;

class Response {

    protected $body;
    protected $headers;
    protected $statusCode;
    protected $reasonPhrase;

    public function __construct()
    {

    }

    public function getContentType()
    {
        $content_type = $this->getHeader('CONTENT-TYPE');
        return $content_type;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function setResponse(GuzzleResponse $response)
    {
        $this->statusCode = $response->getStatusCode();
        $this->reasonPhrase = $response->getReasonPhrase();
        $this->body = (string) $response->getBody();
        $this->headers = $response->getHeaders();
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function getHeader($name)
    {
        $headers = $this->getHeaders();
        if (isset($headers[$name])) {
            return $headers[$name];
        }
        return null;
    }

    public function getHeaders()
    {
        $ret = [];
        $headers = $this->headers;
        if ($headers) {
            foreach ($headers as $name => $header) {
                $ret[strtoupper($name)] = isset($header[0]) ? $header[0] : '';
            }
        }
        return $ret;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getMediaType()
    {
        $type_parser = new TypeParser;
        $content_type = $this->getHeader('CONTENT-TYPE');
        $type = $type_parser->parse($content_type);
        $media_type = $type->getType();
        switch ($media_type) {
            case 'text':
            case 'xml':
            case 'application':
                $sub_type_parser = new SubtypeParser;
                return $sub_type_parser->parse($content_type);
            break;
        }
        return $media_type;
    }

    public function hasHeader($name)
    {
        $val = $this->headers[$name];
        return $val ? true : false;
    }

}
