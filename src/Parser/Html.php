<?php

namespace Skrape\Parser;

use Skrape\Parser;
use Skrape\Uri;

class Html extends Parser
{

    public function getDescription()
    {
        $description = null;
        $meta = $this->getMeta('description');
        if ($meta && isset($meta['content'])) {
            $description = $meta['content'];
        }
        return $description;
    }

    public function getFavicon()
    {
        $favicon_url = null;
        $links = $this->crawler->filter('link')->extract(['rel', 'href']);
        foreach ($links as $link) {
            if (isset($link['rel'])) {
                $rel = strtolower($link['rel']);
                if ($rel == 'shortcut icon' || $rel == 'icon') {
                    $favicon_url = $link['href'];
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
            $favicon_url = Uri::resolve($this->skrape->uri, $favicon_url);
        }
        return $favicon_url;
    }

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

    public function getImage()
    {
        $images = $this->getImages();
        if ($images) {
            return $images[0];
        }
    }

    public function getImages()
    {
        $images = [];
        $metas = $this->getMetas();
        foreach ($metas as $meta) {
            if (isset($meta['property']) && isset($meta['content']) && $meta['property'] == 'og:image') {
                $images[] = (string) Uri::resolve($this->skrape->uri, $meta['content']);
            }
        }
        $dom_images = $this->crawler->filter('img')->extract(['src', 'class']);
        foreach ($dom_images as $dom_image) {
            $images[] = (string) Uri::resolve($this->skrape->uri, $dom_image[0]);
        }
        return array_unique($images);
    }

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

    public function getLinks()
    {
        $extract = [
            'title' => '_text',
            'href' => 'href',
            'class' => 'class',
            'id' => 'id'
        ];
        $link_crawler = $this->crawler->filter('body a')->extract($extract);
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
                $uri = Uri::resolve($this->skrape->uri, $l['href']);
                $l['uri'] = (string) $uri;
            }
            $links[] = $l;
        }
        return $links;
    }

    public function getMeta($name)
    {
        $metas = $this->getMetas();
        foreach ($metas as $meta) {
            if ($meta['name'] == $name) {
                return $meta;
            }
        }
    }

    public function getMetas()
    {
        $metas = [];
        $dom_metas = $this->crawler->filter('meta')->extract([
            'name', 'property', 'content', 'itemprop'
        ]);
        foreach ($dom_metas as $dom_meta) {
            $metas[] = [
                'name' => $dom_meta[0],
                'property' => $dom_meta[1],
                'content' => $dom_meta[2],
                'itemprop' => $dom_meta[3]
            ];
        }
        return $metas;
    }

    public function getTitle()
    {
        $title = $this->crawler->filter('title')->first();
        if ($title) {
            return $title->text();
        }
    }

}
