<?php
namespace Skrape\Parser;

use Skrape\Parser\Parser;
use VDB\Uri\Uri;
use Symfony\Component\DomCrawler\Crawler;

class HtmlParser extends Parser
{

    protected $crawler;

    /**
     * @return Crawler
     */
    public function getCrawler()
    {
        if ( ! $this->crawler) {
            $this->crawler = new Crawler($this->response->getBody());
        }
        return $this->crawler;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $description = null;
        $meta = $this->getMeta('description');
        if ($meta && isset($meta['content'])) {
            $description = $meta['content'];
        }
        return $description;
    }

    /**
     * @return string
     */
    public function getFavicon()
    {
        $favicon_url = null;
        $links = $this->getCrawler()->filter('link')->extract(['rel', 'href']);
        foreach ($links as $link) {
            if (isset($link[0])) {
                $rel = strtolower($link[0]);
                if ($rel == 'shortcut icon' || $rel == 'icon') {
                    $favicon_url = $link[1];
                }
            }
        }
        $metas = $this->getMetas();
        foreach ($metas as $meta) {
            if (isset($meta['itemprop'])) {
                if ($meta['itemprop'] == 'image') {
                    $favicon_url = $meta['content'];
                }
            }
        }
        if ($favicon_url) {
            $favicon_url = (new Uri($favicon_url, $this->uri))->toString();
        }
        return $favicon_url;
    }

    /**
     * Find any RSS feeds in the html
     * @return array
     */
    public function getFeeds()
    {
        $feeds = [];
        $links = $this->getLinks();
        foreach ($links as $link) {
            if (stristr($link['href'], 'feedburner.com')) {
                $feeds[] = $link['href'];
            }
        }
        return $feeds;
    }

    /**
     * Attempt to get the featured image
     * @return string
     */
    public function getImage()
    {
        $images = $this->getImages();
        if ($images) {
            return $images[0];
        }
    }

    /**
     * Get all images in html
     * @return array
     */
    public function getImages()
    {
        $images = [];
        $metas = $this->getMetas();
        foreach ($metas as $meta) {
            if (isset($meta['property']) && isset($meta['content']) && $meta['property'] == 'og:image') {
                $images[(new Uri($meta['content'], $this->uri))->toString()] = true;
            }
        }
        $dom_images = $this->crawler->filter('img')->extract(['src', 'class']);
        foreach ($dom_images as $dom_image) {
            $images[(new Uri($dom_image[0], $this->uri))->toString()] = true;
        }
        return array_keys($images);
    }

    /**
     * Get keywords in html
     * @return array
     */
    public function getKeywords()
    {
        $keywords = [];
        $metas = $this->getMetas();
        foreach ($metas as $meta) {
            if ($meta['property'] == 'keywords') {
                $parts = explode(',', $meta['content']);
                foreach ($parts as $part) {
                    $keywords[] = trim($part);
                }
            }
        }
        return $keywords;
    }

    /**
     * Get links
     * @return array
     */
    public function getLinks()
    {
        $extract = [
            'text' => '_text',
            'href' => 'href',
            'class' => 'class',
            'id' => 'id'
        ];
        $link_crawler = $this->getCrawler()->filter('body a')->extract($extract);
        $links = [];
        foreach ($link_crawler as $link) {
            $l = [];
            $i = 0;
            foreach ($extract as $key => $extract_key) {
                $l[$key] = trim($link[$i]);
                $i++;
            }
            $l['uri'] = null;
            if ($l['href']) {
                $l['uri'] = (new Uri($l['href'], $this->uri))->toString();
            }
            $links[] = $l;
        }
        return $links;
    }

    /**
     * Get a meta element by name or property
     * @param string $name
     * @return array
     */
    public function getMeta($name)
    {
        $metas = $this->getMetas();
        foreach ($metas as $meta) {
            if (isset($meta['name']) && $meta['name'] == $name) {
                return $meta;
            }
            if (isset($meta['property']) && $meta['property'] == $name) {
                return $meta;
            }
        }
    }

    /**
     * @return array
     */
    public function getMetas()
    {
        $metas = [];
        $dom_metas = $this->getCrawler()->filter('meta')->extract([
            'name', 'property', 'content', 'itemprop'
        ]);
        foreach ($dom_metas as $dom_meta) {
            $meta = [];
            $dom_meta[0] ? $meta['name'] = $dom_meta[0] : null;
            $dom_meta[1] ? $meta['property'] = $dom_meta[1] : null;
            $dom_meta[2] ? $meta['content'] = $dom_meta[2] : null;
            $dom_meta[3] ? $meta['itemprop'] = $dom_meta[3] : null;
            $metas[] = $meta;
        }
        return $metas;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        $title = $this->getCrawler()->filter('title')->first();
        if ($title) {
            return $title->text();
        }
    }

}
