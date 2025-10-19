# PHP Sitemap Generator

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D5.5-blue.svg)](https://php.net)

A simple yet powerful PHP library for generating XML sitemaps compliant with the [Sitemaps.org protocol](https://www.sitemaps.org/protocol.html). This library helps you create sitemaps for search engine optimization (SEO) and can handle large websites with multiple sitemap files and sitemap index files.

## Features

- Generate XML sitemaps according to sitemaps.org protocol
- Support for multiple sitemaps with automatic sitemap index generation
- GZip compression support for smaller file sizes
- Automatic robots.txt generation and updating
- Submit sitemaps to search engines (Google, Bing)
- Memory-efficient using SplFixedArray
- Validates URLs, priorities, and change frequencies
- PSR-4 autoloading compatible
- Well-documented and tested

## Requirements

- PHP >= 5.5.0
- ext-SPL (Standard PHP Library)
- ext-curl (for sitemap submission to search engines)
- ext-zlib (optional, for GZip compression)
- ext-mbstring (optional, for accurate URL length validation)

## Installation

### Using Composer (Recommended)

```bash
composer require icamys/php-sitemap-generator
```

### Manual Installation

1. Download or clone this repository
2. Include the autoloader in your project:

```php
require_once 'path/to/php-sitemap-generator/vendor/autoload.php';
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use Icamys\SitemapGenerator\SitemapGenerator;

// Create a new sitemap generator
$generator = new SitemapGenerator('https://example.com');

// Add URLs
$generator->addUrl('/', new DateTime(), 'daily', '1.0');
$generator->addUrl('/about', new DateTime(), 'monthly', '0.8');
$generator->addUrl('/contact', new DateTime(), 'monthly', '0.8');

// Generate the sitemap
$generator->createSitemap();

// Write to file
$generator->writeSitemap();

// Update robots.txt
$generator->updateRobots();

echo "Sitemap generated successfully!";
```

## Usage

### Basic Usage

#### Creating a Generator Instance

```php
use Icamys\SitemapGenerator\SitemapGenerator;

// Basic initialization
$generator = new SitemapGenerator('https://example.com');

// With custom output directory
$generator = new SitemapGenerator('https://example.com', './sitemaps/');
```

#### Adding URLs

##### Single URL

```php
// Add URL with all parameters
$generator->addUrl(
    '/products/item-1',           // URL path
    new DateTime('2024-01-15'),   // Last modified date
    'weekly',                      // Change frequency
    '0.9'                         // Priority (0.0 to 1.0)
);

// Add URL with minimal parameters
$generator->addUrl('/simple-page');
```

##### Multiple URLs

```php
$urls = [
    ['/', new DateTime(), 'daily', '1.0'],
    ['/about', new DateTime(), 'monthly', '0.8'],
    ['/contact', new DateTime(), 'yearly', '0.5'],
];

$generator->addUrls($urls);
```

#### Change Frequency Values

Valid values for the `changefreq` parameter:

- `always` - Document changes each time it is accessed
- `hourly` - Updated every hour
- `daily` - Updated every day
- `weekly` - Updated every week
- `monthly` - Updated every month
- `yearly` - Updated every year
- `never` - Archived URL, never changes

#### Priority Values

The `priority` parameter accepts values from `0.0` to `1.0`:

- `1.0` - Highest priority
- `0.5` - Medium priority (default)
- `0.0` - Lowest priority

### Advanced Usage

#### Custom Configuration

```php
$generator = new SitemapGenerator('https://example.com', './output/');

// Enable GZip compression
$generator->createGZipFile = true;

// Set custom filenames
$generator->sitemapFileName = 'my-sitemap.xml';
$generator->sitemapIndexFileName = 'my-sitemap-index.xml';
$generator->robotsFileName = 'robots.txt';

// Set custom limits
$generator->maxURLsPerSitemap = 10000;  // Default: 50000
$generator->maxSitemaps = 10000;        // Default: 50000
```

#### Working with Large Websites

```php
$generator = new SitemapGenerator('https://example.com');
$generator->maxURLsPerSitemap = 10000; // Split into multiple files

// Add thousands of URLs
for ($i = 1; $i <= 50000; $i++) {
    $generator->addUrl("/page-{$i}", new DateTime(), 'weekly', '0.7');
}

// This will create multiple sitemap files and a sitemap index
$generator->createSitemap();
$generator->writeSitemap();
```

#### Getting Sitemap Data Without Writing Files

```php
$generator->createSitemap();

// Get sitemaps as array
$sitemaps = $generator->toArray();

foreach ($sitemaps as $sitemap) {
    $filename = $sitemap[0];
    $xmlContent = $sitemap[1];

    // Process or store as needed
    echo "Sitemap: {$filename}\n";
}
```

#### Submitting to Search Engines

```php
$generator->createSitemap();
$generator->writeSitemap();

try {
    $results = $generator->submitSitemap();

    foreach ($results as $result) {
        echo "Submitted to {$result['site']}: ";
        echo "HTTP {$result['http_code']} - {$result['message']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

#### Getting URL Statistics

```php
// Get total number of URLs
$count = $generator->countUrls();
echo "Total URLs: {$count}\n";

// Get all URLs as array
$urls = $generator->getUrls();
foreach ($urls as $url) {
    print_r($url);
}
```

## API Reference

### Constructor

```php
__construct(string $baseURL, string $basePath = "")
```

- `$baseURL` - Your website URL (with trailing slash)
- `$basePath` - Directory path where sitemaps will be stored

### Methods

#### addUrl()

```php
addUrl(string $url, DateTime $lastModified = null,
       string $changeFrequency = null, string $priority = null)
```

Add a single URL to the sitemap.

#### addUrls()

```php
addUrls(array $urlsArray)
```

Add multiple URLs at once.

#### createSitemap()

```php
createSitemap()
```

Generate the sitemap(s) in memory.

#### writeSitemap()

```php
writeSitemap()
```

Write sitemap files to disk.

#### updateRobots()

```php
updateRobots()
```

Update or create robots.txt file with sitemap location.

#### submitSitemap()

```php
submitSitemap(): array
```

Submit sitemap to search engines (Google, Bing). Returns array of results.

#### toArray()

```php
toArray(): array
```

Get sitemaps as array without writing to files.

#### getUrls()

```php
getUrls(): array
```

Get all URLs with their parameters as array.

#### countUrls()

```php
countUrls(): int
```

Get the total number of URLs.

#### getVersion()

```php
getVersion(): string
```

Get the library version.

## Examples

Complete examples are available in the [examples](examples/) directory:

- [basic-usage.php](examples/basic-usage.php) - Basic sitemap generation
- [advanced-usage.php](examples/advanced-usage.php) - Advanced features and options

## Documentation

For detailed documentation, see the [docs](docs/) directory:

- [Installation Guide](docs/installation.md)
- [Configuration Options](docs/configuration.md)
- [API Documentation](docs/api.md)
- [Best Practices](docs/best-practices.md)
- [Troubleshooting](docs/troubleshooting.md)

## Specifications

This library follows the [Sitemaps.org Protocol](https://www.sitemaps.org/protocol.html):

- Maximum 50,000 URLs per sitemap file
- Maximum 10MB per sitemap file (uncompressed)
- Maximum 50,000 sitemaps per sitemap index
- URL length maximum 2,048 characters

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Testing

```bash
# Run tests (when available)
composer test

# Run linting
composer lint
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

- Original Author: [Prisacari Dmitrii](https://github.com/icamys)
- Contributors: See [CONTRIBUTORS.md](CONTRIBUTORS.md)

## Support

- Report bugs: [GitHub Issues](https://github.com/icamys/php-sitemap-generator/issues)
- Ask questions: [GitHub Discussions](https://github.com/icamys/php-sitemap-generator/discussions)

## Resources

- [Sitemaps.org Protocol](https://www.sitemaps.org/protocol.html)
- [Google Search Central - Sitemaps](https://developers.google.com/search/docs/crawling-indexing/sitemaps/overview)
- [Bing Webmaster Tools](https://www.bing.com/webmasters/)

---

Made with ❤️ by the PHP community
