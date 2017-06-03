<?php

namespace Jbizzay\Skrape\Parser;

use Jbizzay\Skrape\Parser;

class Feed extends Parser {

    public function getDescription()
    {
        $subtitle = $this->crawler->filterXPath('//subtitle');
        if (count($subtitle)) {
            return $subtitle->text();
        }
    }

    public function getTitle()
    {
        $title = $this->crawler->filter('title')->first();
        if ($title) {
            return $title->text();
        }
    }

    public function getPosts()
    {
        $ret = [];
/*
        $posts = $this->crawler->filterXPath('//.regularitem');
        foreach ($posts as $post) {
            $ret[] = $post->text();
        };
*/
        $this->crawler->registerNamespace('m', 'http://www.w3.org/2005/Atom');
        $posts = $this->crawler->filterXPath('//item');
        if ( ! count($posts)) {
            $posts = $this->crawler->filterXPath('//entry');
        }
        if ( ! count($posts)) {
            $posts = $this->crawler->filterXPath('//.regularitem');
        }
        foreach ($posts as $post) {

            $ret[] = [
                'title' => $this->getPostTitle($post),
                'url' => $this->getPostUrl($post),
                'published_at' => $this->getPostPublishedAt($post),
                'description' => $this->getPostDescription($post)
            ];
        }

        return $ret;
    }

    public function getPostDescription($post)
    {
        $desc = $this->getPostElem($post, 'description');
        if ( ! $desc) {
            $desc = $this->getPostElem($post, 'content');
        }
        return $desc;
    }

    public function getPostPublishedAt($post)
    {
        $date = $this->getPostElem($post, 'pubDate');
        if ( ! $date) {
            $date = $this->getPostElem($post, 'updated');
        }
        return $date;
    }

    public function getPostTitle($post)
    {
        return $this->getPostElem($post, 'title');
    }

    public function getPostUrl($post)
    {
        $url = $this->getPostElem($post, 'guid');
        if ( ! $url) {
            $url = $this->getPostElem($post, 'link', 'href');
        }
        if ( ! $url) {
            $url = $this->getPostElem($post, 'link');
        }
        if ( ! $url) {
            $url = $this->getPostElem($post, 'uri');
        }
        return $url;
    }

    public function getPostElem($post, $name, $attribute = null)
    {
        $elem = $post->getElementsByTagName($name);
        if ($attribute) {
            return strip_tags($elem[0]->getAttribute($attribute));
        }
        if ( ! empty($elem[0]) && $elem[0]->firstChild) {
            $ret = $elem[0]->firstChild;
            return strip_tags($ret->nodeValue);
        }
        return '';
    }

}
