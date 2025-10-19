# Troubleshooting Guide

Common issues and their solutions when using PHP Sitemap Generator.

## Table of Contents

- [Installation Issues](#installation-issues)
- [Generation Issues](#generation-issues)
- [File Writing Issues](#file-writing-issues)
- [Memory Issues](#memory-issues)
- [Validation Errors](#validation-errors)
- [Submission Issues](#submission-issues)

## Installation Issues

### Class Not Found

**Error:**
```
Fatal error: Class 'Icamys\SitemapGenerator\SitemapGenerator' not found
```

**Solutions:**

1. **Check autoloader inclusion:**
```php
require_once 'vendor/autoload.php';
```

2. **Regenerate autoload files:**
```bash
composer dump-autoload
```

3. **Verify installation:**
```bash
composer show icamys/php-sitemap-generator
```

### SPL Extension Not Found

**Error:**
```
Fatal error: Class 'SplFixedArray' not found
```

**Solutions:**

1. **Check if SPL is enabled:**
```bash
php -m | grep SPL
```

2. **SPL is usually enabled by default. If missing, reinstall PHP or contact your host**

### cURL Extension Missing

**Error:**
```
BadMethodCallException: cURL library is needed to do submission
```

**Solutions:**

1. **Ubuntu/Debian:**
```bash
sudo apt-get install php-curl
sudo service apache2 restart
```

2. **CentOS/RHEL:**
```bash
sudo yum install php-curl
sudo systemctl restart httpd
```

3. **Windows:**
Edit `php.ini` and uncomment:
```ini
extension=curl
```

## Generation Issues

### Empty Sitemap Created

**Issue:** Sitemap file is created but contains no URLs

**Causes & Solutions:**

1. **No URLs added:**
```php
// ❌ Wrong
$generator = new SitemapGenerator('https://example.com');
$generator->createSitemap(); // No URLs!

// ✅ Correct
$generator = new SitemapGenerator('https://example.com');
$generator->addUrl('/page1');
$generator->addUrl('/page2');
$generator->createSitemap();
```

2. **Exception thrown during URL addition:**
```php
try {
    $generator->addUrl($url, $date, $freq, $priority);
} catch (Exception $e) {
    echo "Error adding URL: " . $e->getMessage();
}
```

### Sitemap Size Too Large

**Error:**
```
LengthException: Sitemap size equals to X bytes is more than 10MB
```

**Solutions:**

1. **Reduce URLs per sitemap:**
```php
$generator->maxURLsPerSitemap = 10000; // Default: 50000
```

2. **Enable GZip compression:**
```php
$generator->createGZipFile = true;
```

3. **Check URL lengths:**
```php
// URLs should be < 2048 characters
if (strlen($url) < 2048) {
    $generator->addUrl($url);
}
```

### Too Many Sitemaps

**Error:**
```
LengthException: Sitemap index can contain 50000 sitemaps
```

**Solutions:**

1. **Increase URLs per sitemap:**
```php
$generator->maxURLsPerSitemap = 50000;
```

2. **Split into multiple generators:**
```php
// Create separate sitemaps for different sections
$productsGen = new SitemapGenerator('https://example.com');
$blogGen = new SitemapGenerator('https://example.com');
```

### Invalid URL Error

**Error:**
```
InvalidArgumentException: URL length can't be bigger than 2048 characters
```

**Solutions:**

1. **Check URL length:**
```php
if (strlen($url) <= 2048) {
    $generator->addUrl($url);
} else {
    error_log("URL too long: $url");
}
```

2. **Shorten URLs:**
```php
// Use shorter, clean URLs
// ❌ /products/category/subcategory/item?param1=value1&param2=value2
// ✅ /p/item-slug
```

### Invalid Change Frequency

**Error:**
```
InvalidArgumentException: Invalid change frequency
```

**Solution:**

Use valid values only:
```php
$validFreqs = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];
$generator->addUrl($url, null, 'daily'); // ✅
$generator->addUrl($url, null, 'sometimes'); // ❌ Invalid
```

### Invalid Priority

**Error:**
```
InvalidArgumentException: Priority must be between 0.0 and 1.0
```

**Solution:**

```php
// ✅ Valid priorities
$generator->addUrl($url, null, null, 0.5);
$generator->addUrl($url, null, null, 1.0);

// ❌ Invalid priorities
$generator->addUrl($url, null, null, 1.5); // Too high
$generator->addUrl($url, null, null, -0.5); // Too low
```

## File Writing Issues

### Permission Denied

**Error:**
```
Warning: fopen(): failed to open stream: Permission denied
```

**Solutions:**

1. **Check directory permissions:**
```bash
# Linux/Mac
chmod 755 /path/to/output/directory

# Or make writable by web server
chmod 777 /path/to/output/directory
```

2. **Check directory ownership:**
```bash
# Change owner to web server user
chown www-data:www-data /path/to/output/directory
```

3. **Verify directory exists:**
```php
$outputDir = './output/';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

if (!is_writable($outputDir)) {
    throw new Exception("Output directory is not writable");
}
```

### Directory Not Found

**Error:**
```
Warning: fopen(./output/sitemap.xml): failed to open stream: No such file or directory
```

**Solution:**

```php
// Create directory if it doesn't exist
$basePath = './output/';
if (!is_dir($basePath)) {
    mkdir($basePath, 0755, true);
}

$generator = new SitemapGenerator('https://example.com', $basePath);
```

### Cannot Write GZip File

**Error:**
```
RuntimeException: Cannot open gzip file for writing
```

**Solutions:**

1. **Check if zlib is installed:**
```bash
php -m | grep zlib
```

2. **Install zlib:**
```bash
# Ubuntu/Debian
sudo apt-get install php-zlib

# CentOS/RHEL
sudo yum install php-zlib
```

3. **Disable GZip if not needed:**
```php
$generator->createGZipFile = false;
```

## Memory Issues

### Memory Exhausted

**Error:**
```
Fatal error: Allowed memory size of X bytes exhausted
```

**Solutions:**

1. **Increase PHP memory limit:**
```php
ini_set('memory_limit', '256M');
```

2. **In php.ini:**
```ini
memory_limit = 256M
```

3. **Process in batches:**
```php
$batchSize = 5000;
$offset = 0;

while (true) {
    $urls = getUrls($batchSize, $offset);
    if (empty($urls)) break;

    foreach ($urls as $url) {
        $generator->addUrl($url);
    }

    $offset += $batchSize;

    // Optional: Clear memory
    gc_collect_cycles();
}
```

4. **Reduce URLs per sitemap:**
```php
$generator->maxURLsPerSitemap = 10000;
```

### Script Timeout

**Error:**
```
Maximum execution time of 30 seconds exceeded
```

**Solutions:**

1. **Increase execution time:**
```php
set_time_limit(300); // 5 minutes
```

2. **In php.ini:**
```ini
max_execution_time = 300
```

3. **Use CLI instead of web:**
```bash
php generate-sitemap.php
```

4. **Process asynchronously:**
```php
// Use queue system or background job
```

## Validation Errors

### XML Validation Failed

**Issue:** Generated XML is invalid

**Solutions:**

1. **Validate manually:**
```bash
xmllint --noout sitemap.xml
```

2. **Check for special characters:**
```php
// Properly escape URLs
$url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
```

3. **Use online validators:**
- [XML Sitemap Validator](https://www.xml-sitemaps.com/validate-xml-sitemap.html)
- Google Search Console

### URLs Not Being Crawled

**Issue:** Sitemap generated but URLs not being indexed

**Possible Causes:**

1. **robots.txt blocking:**
```
# Check robots.txt
User-agent: *
Disallow: /
Sitemap: https://example.com/sitemap.xml
```

2. **Sitemap not submitted:**
```php
$generator->submitSitemap();
```

3. **Incorrect URL format:**
```php
// ✅ Correct: absolute URLs
https://example.com/page

// ❌ Wrong: relative URLs
/page
```

4. **Sitemap not accessible:**
```bash
# Test accessibility
curl -I https://example.com/sitemap.xml
```

## Submission Issues

### Submission Failed

**Error:**
```
Search engine submission returned HTTP 400/500
```

**Solutions:**

1. **Check sitemap accessibility:**
```bash
curl https://example.com/sitemap.xml
```

2. **Verify robots.txt:**
```
Sitemap: https://example.com/sitemap.xml
```

3. **Submit manually:**
- [Google Search Console](https://search.google.com/search-console)
- [Bing Webmaster Tools](https://www.bing.com/webmasters/)

4. **Check for errors in response:**
```php
$results = $generator->submitSitemap();
foreach ($results as $result) {
    if ($result['http_code'] !== 200) {
        echo "Error from {$result['site']}: {$result['message']}\n";
    }
}
```

### Timeout During Submission

**Issue:** Submission takes too long and times out

**Solutions:**

1. **Increase timeout:**
```php
// Modify in source if needed
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
```

2. **Submit manually through webmaster tools**

3. **Check network connectivity:**
```bash
ping www.google.com
```

## Debugging Tips

### Enable Error Reporting

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Add Logging

```php
try {
    $generator->createSitemap();
    $generator->writeSitemap();
    error_log("Sitemap generated successfully");
} catch (Exception $e) {
    error_log("Sitemap generation failed: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
}
```

### Validate Configuration

```php
echo "PHP Version: " . phpversion() . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "cURL: " . (extension_loaded('curl') ? 'Yes' : 'No') . "\n";
echo "Zlib: " . (extension_loaded('zlib') ? 'Yes' : 'No') . "\n";
echo "mbstring: " . (extension_loaded('mbstring') ? 'Yes' : 'No') . "\n";
```

### Test with Small Dataset

```php
// Test with just a few URLs first
$generator = new SitemapGenerator('https://example.com');
$generator->addUrl('/test1');
$generator->addUrl('/test2');
$generator->createSitemap();
$generator->writeSitemap();
```

## Getting Help

If you still have issues:

1. **Check existing issues:**
   - [GitHub Issues](https://github.com/icamys/php-sitemap-generator/issues)

2. **Create a new issue with:**
   - PHP version (`php -v`)
   - Error messages (full stack trace)
   - Steps to reproduce
   - Expected vs actual behavior
   - Code sample

3. **Join discussions:**
   - [GitHub Discussions](https://github.com/icamys/php-sitemap-generator/discussions)

## Related Documentation

- [Installation Guide](installation.md)
- [Configuration Guide](configuration.md)
- [Best Practices](best-practices.md)
- [API Documentation](api.md)
