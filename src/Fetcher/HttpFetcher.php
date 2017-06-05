<?php
namespace Skrape\Fetcher;

use Skrape\Response;
use GuzzleHttp\Client;

class HttpFetcher extends Fetcher implements FetcherInterface
{
    protected $client;

    public function getClient()
    {
        if ( ! $this->client) {
            $this->client = new Client($this->config->get('request'));
        }
        return $this->client;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function fetch()
    {
        $client = $this->getClient();
        $response = $client->get($this->uri->toString());

        $skrapeResponse = new Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase()
        );
        return $skrapeResponse;
    }
}
