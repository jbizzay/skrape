# Skrape

PHP web scraper and link previewer

## Usage

### Simple Example

This is a simple example of how to create a link preview for a url

```php
use Skrape\Skrape;

// Create the scraper
$skrape = new Skrape('http://soundersfc.com');

// Fetch the page
$skrape->fetch();

// Parse out some data
$parsed = $skrape->parse(['title', 'image']);

// Generate link preview html
$html = sprintf(
    '<h1>%s</h1><img src="%s" /><a href="%s">Visit Site</a>',
    $parsed['title'],
    $parsed['image'],
    $resource->getUri()->toString()
);
```

### Debugging

Fetch a resource and see all response debug info. The response body is trimmed to 100 characters by default. Change this by passing a different number to getDebug(), or false to show entire body. Also, in the meta array, you can tell when it was last fetched, cached and from which source this response came from.

```php
$skrape = new Skrape('http://example.org');
$skrape->getConfig()->set('cache', [
    'fetch' => false,
    'store' => true
]);
$response = $skrape->fetch();
print_r($response->getDebug());

```

Output:


```php
Array
(
    [StatusCode] => 200
    [Headers] => Array
        (
            [ACCEPT-RANGES] => bytes
            [CACHE-CONTROL] => max-age=604800
            [CONTENT-TYPE] => text/html
            [DATE] => Sat, 24 Jun 2017 04:26:00 GMT
            [ETAG] => "359670651+gzip"
            [EXPIRES] => Sat, 01 Jul 2017 04:26:00 GMT
            [LAST-MODIFIED] => Fri, 09 Aug 2013 23:54:35 GMT
            [SERVER] => ECS (pae/3796)
            [VARY] => Accept-Encoding
            [X-CACHE] => HIT
            [CONTENT-LENGTH] => 1270
        )

    [Version] => 1.1
    [Reason] => OK
    [ContentType] => text/html
    [MediaType] => html
    [Meta] => Array
        (
            [http] => Array
                (
                    [date_fetched] => 1498278360
                )

            [cache] => Array
                (
                    [date_stored] => 1498278360
                )

            [source] => http
        )

    [Body_100] => <!doctype html>
<html>
<head>
    <title>Example Domain</title>

    <meta charset="utf-8" />
    <m
)

```

### Config

Skrape uses a config with some defaults for caching and http requests. These can be overriden on a global or per scrape level. Here are some examples on how to modify the config


On a global level, it will be used for every request
```php
use Skrape\Config;

// Set cache location for all requests (default: /tmp/skrape)
Config::setGlobal('cache.location', '/tmp/myapp');

// Store all responses in cache (default: true)
Config::setGlobal('cache.store', true);

// Fetch responses from cache before trying http (default: true)
Config::setGlobal('cache.fetch', true);

// Can also set same config values like this:
Config::setGlobal('cache', [
  'store' => true,
  'fetch' => true
]);

// Or this:
Config::setGlobal([
  'cache' => [
    'store' => true,
    'fetch' => true
  ]
]);
```

Or per scrape

```php
$skrape = new Skrape('example.org');
$skrape->getConfig()->set('request.timeout', 15);
```
