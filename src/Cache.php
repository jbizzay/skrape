<?php
namespace Skrape;

use Symfony\Component\Filesystem\Filesystem;
use VDB\Uri\UriInterface;

class Cache
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var UriInterface
     */
    protected $uri;

    /**
     * @param Config $config
     * @param UriInterface $uri
     */
    public function __construct(Config $config, UriInterface $uri)
    {
        $this->config = $config;
        $this->uri = $uri;
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

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        if ( ! $this->filesystem) {
            $this->filesystem = new Filesystem;
        }
        return $this->filesystem;
    }

    /**
     * Get filepath to the cache directory for the uri
     * @return string $directory
     */
    public function getCacheDirectory()
    {
        $directory = $this->config->get('cache.location');
        $directory = $directory ? rtrim($directory, '/') : '/tmp/skrape';
        $directory .= '/' . $this->uri->getHost() . '/';
        return $directory;
    }

    /**
     * Get full filepath to a cached resource
     * return string $filepath
     */
    public function getCacheFilepath()
    {
        $directory = $this->getCacheDirectory();
        $scheme = $this->uri->getScheme();
        $filename = $scheme . '-' . str_replace([$scheme . '://', '/'], ['', '-'], $this->uri->toString());
        $filepath = $directory . $filename;
        return $filepath;
    }

    /**
     * Fetch a resource response from cache
     * @return object $response
     */
    public function fetch()
    {
        $response = null;
        $filesystem = new Filesystem;
        $filepath = $this->getCacheFilepath();
        if ($filesystem->exists($filepath)) {
            $response = unserialize(file_get_contents($filepath));
        }
        return $response;
    }

    /**
     * Store a resource response in cache
     * @return object $response
     */
    public function store($response)
    {
        $filepath = $this->getCacheFilepath();
        $directory = dirname($filepath);
        $filesystem = $this->getFilesystem();
        if ( ! $filesystem->exists($directory)) {
            $filesystem->mkdir($directory, 0775);
        }
        $filesystem->dumpFile($filepath, serialize($response));
        return $this;
    }

}
