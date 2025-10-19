# Configuration Guide

This guide covers all configuration options available in PHP Sitemap Generator.

## Table of Contents

- [Basic Configuration](#basic-configuration)
- [Public Properties](#public-properties)
- [File Configuration](#file-configuration)
- [Size and Limit Configuration](#size-and-limit-configuration)
- [Compression Configuration](#compression-configuration)
- [Advanced Configuration](#advanced-configuration)

## Basic Configuration

### Constructor Parameters

```php
$generator = new SitemapGenerator($baseURL, $basePath);
```

#### `$baseURL` (required)

The base URL of your website. Must include the protocol (http:// or https://).

```php
// With trailing slash (recommended)
$generator = new SitemapGenerator('https://example.com/');

// Without trailing slash (will be added automatically)
$generator = new SitemapGenerator('https://example.com');

// Subdirectory
$generator = new SitemapGenerator('https://example.com/blog/');
```

#### `$basePath` (optional)

The directory path where sitemap files will be saved, relative to the script location.

```php
// Default (same directory as script)
$generator = new SitemapGenerator('https://example.com', '');

// Custom directory
$generator = new SitemapGenerator('https://example.com', './sitemaps/');

// Absolute path
$generator = new SitemapGenerator('https://example.com', '/var/www/sitemaps/');
```

**Important:** Ensure the directory exists and is writable.

## Public Properties

All configuration properties are public and can be modified after instantiation.

### File Configuration

#### `sitemapFileName`

Name of the main sitemap file.

```php
$generator->sitemapFileName = "sitemap.xml"; // Default
$generator->sitemapFileName = "my-sitemap.xml";
```

#### `sitemapIndexFileName`

Name of the sitemap index file (used when multiple sitemaps are generated).

```php
$generator->sitemapIndexFileName = "sitemap-index.xml"; // Default
$generator->sitemapIndexFileName = "my-sitemap-index.xml";
```

#### `robotsFileName`

Name of the robots.txt file.

```php
$generator->robotsFileName = "robots.txt"; // Default
$generator->robotsFileName = "my-robots.txt";
```

### Size and Limit Configuration

#### `maxURLsPerSitemap`

Maximum number of URLs per sitemap file. According to the sitemaps.org protocol, the maximum is 50,000.

```php
$generator->maxURLsPerSitemap = 50000; // Default (maximum allowed)
$generator->maxURLsPerSitemap = 10000; // More conservative
$generator->maxURLsPerSitemap = 1000;  // For testing
```

**When to reduce:**

- URLs are very long (approaching 2048 characters)
- Including many optional parameters (lastmod, changefreq, priority)
- Want smaller, more manageable files
- Testing sitemap generation

#### `maxSitemaps`

Maximum number of sitemaps in a sitemap index. Maximum allowed is 50,000.

```php
$generator->maxSitemaps = 50000; // Default
$generator->maxSitemaps = 1000;  // Custom limit
```

### Compression Configuration

#### `createGZipFile`

Enable GZip compression for sitemap files.

```php
$generator->createGZipFile = false; // Default
$generator->createGZipFile = true;  // Enable compression
```

**Benefits of compression:**

- Reduced file size (typically 70-80% smaller)
- Faster upload to search engines
- Lower bandwidth usage

**Requirements:**

- PHP `zlib` extension must be installed

**Behavior:**

- When enabled, both `.xml` and `.xml.gz` files are created
- The `.gz` file is submitted to search engines
- If multiple sitemaps are generated, all except the index are compressed

## Configuration Examples

### Example 1: Basic Blog

```php
$generator = new SitemapGenerator('https://myblog.com');
$generator->sitemapFileName = "blog-sitemap.xml";
$generator->createGZipFile = true;
```

### Example 2: Large E-commerce Site

```php
$generator = new SitemapGenerator('https://shop.com', './public/sitemaps/');
$generator->maxURLsPerSitemap = 10000; // Smaller chunks
$generator->createGZipFile = true;
$generator->sitemapFileName = "products-sitemap.xml";
$generator->sitemapIndexFileName = "products-index.xml";
```

### Example 3: Multiple Sitemaps for Different Sections

```php
// Products sitemap
$productsGenerator = new SitemapGenerator('https://example.com', './public/');
$productsGenerator->sitemapFileName = "sitemap-products.xml";
$productsGenerator->createGZipFile = true;

// Blog sitemap
$blogGenerator = new SitemapGenerator('https://example.com', './public/');
$blogGenerator->sitemapFileName = "sitemap-blog.xml";
$blogGenerator->createGZipFile = true;

// Add URLs to each generator...
$productsGenerator->addUrl('/products/item-1', ...);
$blogGenerator->addUrl('/blog/post-1', ...);
```

### Example 4: Development/Testing Configuration

```php
$generator = new SitemapGenerator('http://localhost:8000', './test-output/');
$generator->maxURLsPerSitemap = 100; // Small limit for testing
$generator->createGZipFile = false;  // Disable compression for easier inspection
$generator->sitemapFileName = "test-sitemap.xml";
```

## Advanced Configuration

### Custom Search Engines

By default, the library submits to Google and Bing. To customize (requires modifying the source):

```php
// In SitemapGenerator.php, modify the $searchEngines property
private $searchEngines = array(
    "http://www.google.com/webmasters/tools/ping?sitemap=",
    "http://www.bing.com/webmaster/ping.aspx?siteMap=",
    "http://your-custom-search-engine.com/ping?sitemap="
);
```

### Custom Output Paths

```php
// Save to different locations based on environment
$basePath = getenv('APP_ENV') === 'production'
    ? '/var/www/public/'
    : './dev-output/';

$generator = new SitemapGenerator('https://example.com', $basePath);
```

### Dynamic Configuration

```php
// Load from configuration file
$config = parse_ini_file('sitemap-config.ini');

$generator = new SitemapGenerator($config['base_url'], $config['output_path']);
$generator->maxURLsPerSitemap = $config['max_urls'];
$generator->createGZipFile = $config['enable_compression'];
```

Example `sitemap-config.ini`:

```ini
base_url = "https://example.com"
output_path = "./sitemaps/"
max_urls = 10000
enable_compression = true
```

## Configuration Validation

### Validate Before Generation

```php
function validateConfiguration($generator) {
    $errors = [];

    if ($generator->maxURLsPerSitemap > 50000) {
        $errors[] = "maxURLsPerSitemap cannot exceed 50000";
    }

    if ($generator->maxURLsPerSitemap < 1) {
        $errors[] = "maxURLsPerSitemap must be at least 1";
    }

    if ($generator->createGZipFile && !extension_loaded('zlib')) {
        $errors[] = "GZip compression requires zlib extension";
    }

    return $errors;
}

$errors = validateConfiguration($generator);
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "Configuration Error: $error\n";
    }
    exit(1);
}
```

## Best Practices

1. **Use GZip compression** in production to reduce file size
2. **Set appropriate maxURLsPerSitemap** based on your URL lengths
3. **Use descriptive filenames** when generating multiple sitemaps
4. **Ensure output directory is writable** before generation
5. **Validate configuration** before processing large numbers of URLs
6. **Keep separate sitemaps** for different sections of large sites
7. **Test with small limits** before generating production sitemaps

## Next Steps

- Learn about [Best Practices](best-practices.md)
- Explore the [API Documentation](api.md)
- Review [Troubleshooting](troubleshooting.md)
