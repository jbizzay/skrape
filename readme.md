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
