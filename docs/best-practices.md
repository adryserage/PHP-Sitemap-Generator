# Best Practices

This guide provides best practices for using PHP Sitemap Generator effectively.

## Table of Contents

- [General Guidelines](#general-guidelines)
- [Performance Optimization](#performance-optimization)
- [SEO Best Practices](#seo-best-practices)
- [Large Websites](#large-websites)
- [Security Considerations](#security-considerations)
- [Maintenance and Updates](#maintenance-and-updates)

## General Guidelines

### 1. Use GZip Compression in Production

Always enable GZip compression for production environments:

```php
$generator->createGZipFile = true;
```

**Benefits:**
- Reduces file size by 70-80%
- Faster downloads for search engines
- Lower bandwidth usage

### 2. Organize URLs by Priority

Add high-priority pages first:

```php
// High priority pages (homepage, main sections)
$generator->addUrl('/', new DateTime(), 'daily', '1.0');
$generator->addUrl('/products', new DateTime(), 'daily', '0.9');

// Medium priority pages
$generator->addUrl('/blog', new DateTime(), 'weekly', '0.7');

// Lower priority pages
$generator->addUrl('/archive', new DateTime(), 'yearly', '0.3');
```

### 3. Set Realistic Change Frequencies

Don't lie to search engines about update frequency:

```php
// Static pages that rarely change
$generator->addUrl('/about', null, 'yearly', '0.6');

// Dynamic content
$generator->addUrl('/blog', new DateTime(), 'daily', '0.8');

// Real-time content
$generator->addUrl('/news', new DateTime(), 'hourly', '0.9');
```

### 4. Use Accurate Last Modified Dates

Provide actual modification dates when available:

```php
// From database
$lastModified = new DateTime($post['updated_at']);
$generator->addUrl("/blog/{$post['slug']}", $lastModified, 'monthly', '0.7');

// From filesystem
$fileTime = filemtime($filePath);
$lastModified = new DateTime('@' . $fileTime);
$generator->addUrl($url, $lastModified, 'weekly', '0.6');
```

## Performance Optimization

### 1. Batch URL Addition

Add URLs in batches for better performance:

```php
$urls = [];
foreach ($products as $product) {
    $urls[] = [
        "/products/{$product['slug']}",
        new DateTime($product['updated_at']),
        'weekly',
        '0.8'
    ];
}
$generator->addUrls($urls);
```

### 2. Set Appropriate Limits

For sites with very long URLs, reduce the limit:

```php
// Default: 50,000 URLs per sitemap
$generator->maxURLsPerSitemap = 50000;

// For long URLs (>200 chars average)
$generator->maxURLsPerSitemap = 10000;

// For very long URLs (>500 chars average)
$generator->maxURLsPerSitemap = 5000;
```

### 3. Memory Management

For large websites, process in chunks:

```php
// Process in batches of 10,000
$batchSize = 10000;
$offset = 0;

while (true) {
    $urls = $database->getUrls($batchSize, $offset);
    if (empty($urls)) break;

    foreach ($urls as $url) {
        $generator->addUrl($url['path'], new DateTime($url['updated_at']));
    }

    $offset += $batchSize;
}
```

### 4. Caching Strategy

Generate sitemaps periodically, not on every request:

```php
$cacheFile = './cache/sitemap-cache.json';
$cacheTime = 86400; // 24 hours

if (!file_exists($cacheFile) || (time() - filemtime($cacheFile)) > $cacheTime) {
    // Generate new sitemap
    $generator->createSitemap();
    $generator->writeSitemap();

    // Cache metadata
    file_put_contents($cacheFile, json_encode([
        'generated_at' => time(),
        'url_count' => $generator->countUrls()
    ]));
}
```

## SEO Best Practices

### 1. Include Only Indexable URLs

Don't include URLs that shouldn't be indexed:

```php
// ❌ DON'T include
// - Admin pages
// - Login/logout pages
// - Duplicate content
// - Pages with noindex meta tag
// - Parameterized URLs (e.g., ?page=1)

// ✅ DO include
$generator->addUrl('/products/item-1', new DateTime(), 'weekly', '0.8');
$generator->addUrl('/blog/article-1', new DateTime(), 'monthly', '0.7');
```

### 2. Use Canonical URLs

Only include canonical versions of URLs:

```php
// ✅ Include canonical URL
$generator->addUrl('/products/awesome-product', ...);

// ❌ Don't include variations
// /products/awesome-product?ref=email
// /products/awesome-product?utm_source=twitter
```

### 3. Prioritize Important Content

Set priorities based on business importance:

```php
// Critical pages
$generator->addUrl('/', new DateTime(), 'daily', '1.0');
$generator->addUrl('/products', new DateTime(), 'daily', '0.95');

// Important pages
$generator->addUrl('/about', new DateTime(), 'monthly', '0.8');

// Standard pages
$generator->addUrl('/blog/old-post', new DateTime(), 'yearly', '0.5');
```

### 4. Update robots.txt

Always update robots.txt after generating sitemaps:

```php
$generator->updateRobots();
```

Verify robots.txt contains:
```
Sitemap: https://example.com/sitemap.xml
```

### 5. Submit to Search Engines

Submit your sitemap after generation:

```php
try {
    $results = $generator->submitSitemap();
    foreach ($results as $result) {
        if ($result['http_code'] === 200) {
            echo "✅ Successfully submitted to {$result['site']}\n";
        } else {
            echo "⚠️ Failed to submit to {$result['site']}: {$result['http_code']}\n";
        }
    }
} catch (Exception $e) {
    error_log("Sitemap submission failed: " . $e->getMessage());
}
```

## Large Websites

### 1. Create Multiple Sitemaps by Section

For large sites, create separate sitemaps:

```php
// Products sitemap
$productsGen = new SitemapGenerator('https://example.com', './public/');
$productsGen->sitemapFileName = 'sitemap-products.xml';
// Add product URLs...
$productsGen->createSitemap();
$productsGen->writeSitemap();

// Blog sitemap
$blogGen = new SitemapGenerator('https://example.com', './public/');
$blogGen->sitemapFileName = 'sitemap-blog.xml';
// Add blog URLs...
$blogGen->createSitemap();
$blogGen->writeSitemap();

// Create manual sitemap index
```

### 2. Implement Incremental Updates

Update only changed URLs:

```php
// Get URLs modified in last 24 hours
$recentUrls = $database->getRecentlyModifiedUrls(86400);

if (!empty($recentUrls)) {
    // Regenerate sitemap
    $generator = new SitemapGenerator('https://example.com');
    // Add all URLs (or implement partial updates)
    foreach ($allUrls as $url) {
        $generator->addUrl(...);
    }
    $generator->createSitemap();
    $generator->writeSitemap();
}
```

### 3. Use Database Indexes

Optimize database queries:

```sql
-- Index for last modified dates
CREATE INDEX idx_updated_at ON posts(updated_at);

-- Index for published status
CREATE INDEX idx_published ON posts(published);
```

### 4. Scheduled Generation

Use cron jobs for regular updates:

```bash
# Generate sitemap daily at 2 AM
0 2 * * * /usr/bin/php /path/to/generate-sitemap.php >> /var/log/sitemap.log 2>&1
```

## Security Considerations

### 1. Validate Input URLs

Always validate URLs before adding:

```php
function isValidUrl($url) {
    // Check URL length
    if (strlen($url) > 2048) return false;

    // Check for dangerous characters
    if (preg_match('/[<>"\']/', $url)) return false;

    // Check for valid path
    if (!preg_match('#^/[a-zA-Z0-9/_-]*$#', $url)) return false;

    return true;
}

if (isValidUrl($url)) {
    $generator->addUrl($url, ...);
}
```

### 2. Protect Sensitive Information

Don't expose sensitive URLs:

```php
// ❌ DON'T include
// - Admin panels
// - User profiles with sensitive data
// - Internal tools
// - Development/staging URLs
```

### 3. Set Proper File Permissions

Ensure sitemap files have correct permissions:

```bash
chmod 644 sitemap.xml
chmod 644 robots.txt
chmod 755 /path/to/sitemap/directory
```

### 4. Sanitize Output Paths

Validate output paths:

```php
$basePath = realpath('./output/');
if ($basePath === false || !is_writable($basePath)) {
    throw new Exception("Invalid or non-writable output path");
}

$generator = new SitemapGenerator('https://example.com', $basePath . '/');
```

## Maintenance and Updates

### 1. Monitor Sitemap Generation

Log sitemap generation:

```php
try {
    $startTime = microtime(true);

    $generator->createSitemap();
    $generator->writeSitemap();

    $duration = microtime(true) - $startTime;
    $urlCount = $generator->countUrls();

    error_log(sprintf(
        "Sitemap generated: %d URLs in %.2f seconds",
        $urlCount,
        $duration
    ));
} catch (Exception $e) {
    error_log("Sitemap generation failed: " . $e->getMessage());
    // Send alert
}
```

### 2. Validate Generated Sitemaps

Test your sitemaps:

```bash
# Validate XML structure
xmllint --noout sitemap.xml

# Check file size
ls -lh sitemap.xml

# Count URLs
grep -c "<loc>" sitemap.xml
```

### 3. Regular Testing

Test sitemap generation in development:

```php
// test-sitemap.php
$generator = new SitemapGenerator('http://localhost:8000');
$generator->maxURLsPerSitemap = 10; // Small limit for testing

$generator->addUrl('/', new DateTime(), 'daily', '1.0');
$generator->createSitemap();

$sitemaps = $generator->toArray();
print_r($sitemaps);
```

### 4. Monitor Search Console

Check Google Search Console regularly:
- Sitemap processing status
- Coverage issues
- Index status
- Crawl errors

## Common Pitfalls to Avoid

1. ❌ Including URLs with query parameters
2. ❌ Setting unrealistic change frequencies
3. ❌ Using incorrect priority values
4. ❌ Not compressing large sitemaps
5. ❌ Including non-canonical URLs
6. ❌ Forgetting to update robots.txt
7. ❌ Not testing before deploying
8. ❌ Hardcoding URLs instead of using a database
9. ❌ Not handling errors gracefully
10. ❌ Generating sitemaps on every page load

## Checklist

Before deploying your sitemap:

- [ ] GZip compression enabled for production
- [ ] All URLs are canonical and indexable
- [ ] Last modified dates are accurate
- [ ] Change frequencies are realistic
- [ ] Priorities reflect business importance
- [ ] robots.txt is updated
- [ ] Sitemap is submitted to search engines
- [ ] File permissions are correct
- [ ] Error handling is implemented
- [ ] Logging and monitoring in place
- [ ] Tested with sample data

## Next Steps

- Review [Configuration Options](configuration.md)
- Check [Troubleshooting Guide](troubleshooting.md)
- Explore [API Documentation](api.md)
