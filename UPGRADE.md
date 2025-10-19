# Upgrade Guide

## Upgrading from 1.x to 2.0

This guide helps you upgrade from version 1.x to version 2.0 of PHP Sitemap Generator.

### Breaking Changes

#### File Structure

The main source file has been moved:

**Before (v1.x):**
```
/index.php (or SitemapGenerator.php)
```

**After (v2.0):**
```
/src/SitemapGenerator.php
```

**Migration:**
If you were including the file directly (not using Composer), update your path:

```php
// Old
require_once 'SitemapGenerator.php';

// New
require_once 'src/SitemapGenerator.php';

// Or use Composer autoloader (recommended)
require_once 'vendor/autoload.php';
```

#### Composer Autoload

Update your `composer.json` autoload path:

**Before:**
```json
"autoload": {
    "psr-4": {"Icamys\\SitemapGenerator\\": "."}
}
```

**After:**
```json
"autoload": {
    "psr-4": {"Icamys\\SitemapGenerator\\": "src/"}
}
```

Then run:
```bash
composer dump-autoload
```

### New Features in 2.0

#### 1. Version Method

Get the library version:
```php
$version = $generator->getVersion();
echo "Using version: " . $version;
```

#### 2. Enhanced Validation

Priority and change frequency are now validated:
```php
// Throws InvalidArgumentException if invalid
$generator->addUrl('/page', null, 'invalid-freq', 2.0);
```

Valid values:
- **Change frequency:** always, hourly, daily, weekly, monthly, yearly, never
- **Priority:** 0.0 to 1.0

#### 3. Improved Error Handling

Better error messages for common issues:
```php
try {
    $generator->createSitemap();
} catch (BadMethodCallException $e) {
    echo "No URLs added: " . $e->getMessage();
} catch (LengthException $e) {
    echo "Sitemap too large: " . $e->getMessage();
}
```

#### 4. Better URL Handling

Trailing slashes are now handled automatically:
```php
// Both work the same
$generator = new SitemapGenerator('https://example.com');
$generator = new SitemapGenerator('https://example.com/');
```

#### 5. Updated Search Engines

Removed deprecated Yahoo API, updated to current search engine APIs.

### Deprecated Features

#### Yahoo Submission

Yahoo sitemap submission has been removed. The library now only submits to:
- Google
- Bing

**Before:**
```php
$generator->submitSitemap($yahooAppId);
```

**After:**
```php
$generator->submitSitemap(); // No Yahoo app ID needed
```

### Recommended Updates

#### 1. Use Composer

If not already using Composer:
```bash
composer require icamys/php-sitemap-generator
```

#### 2. Enable GZip

Take advantage of compression:
```php
$generator->createGZipFile = true;
```

#### 3. Validate URLs

Add validation before adding URLs:
```php
function isValidUrl($url) {
    return strlen($url) <= 2048 && preg_match('#^/[a-zA-Z0-9/_-]*$#', $url);
}

if (isValidUrl($url)) {
    $generator->addUrl($url);
}
```

#### 4. Use DateTime::ATOM

The library now uses ISO 8601 format:
```php
$lastModified = new DateTime();
$generator->addUrl('/page', $lastModified, 'daily', '0.8');
```

### Configuration Changes

#### composer.json

Update your `composer.json`:

```json
{
  "require": {
    "icamys/php-sitemap-generator": "^2.0"
  }
}
```

Then run:
```bash
composer update icamys/php-sitemap-generator
```

#### PHP Version

Minimum PHP version remains 5.5.0, but PHP 7.0+ is recommended for better performance.

### Testing Your Upgrade

After upgrading, test your implementation:

```php
<?php
require_once 'vendor/autoload.php';

use Icamys\SitemapGenerator\SitemapGenerator;

// Test basic functionality
$generator = new SitemapGenerator('https://example.com');
echo "Version: " . $generator->getVersion() . "\n";

// Test URL addition
$generator->addUrl('/', new DateTime(), 'daily', '1.0');
$generator->addUrl('/about', new DateTime(), 'monthly', '0.8');

// Test sitemap creation
$generator->createSitemap();
echo "URLs added: " . $generator->countUrls() . "\n";

// Test file writing
$generator->writeSitemap();
echo "Sitemap written successfully\n";
```

### Troubleshooting

#### Class Not Found After Upgrade

```bash
composer dump-autoload
```

#### Old Files Causing Issues

Remove old files:
```bash
rm -f index.php SitemapGenerator.php
composer install
```

#### Autoload Issues

Ensure correct namespace:
```php
use Icamys\SitemapGenerator\SitemapGenerator;
```

### Getting Help

If you encounter issues during the upgrade:

1. Check the [Troubleshooting Guide](docs/troubleshooting.md)
2. Review [GitHub Issues](https://github.com/icamys/php-sitemap-generator/issues)
3. Create a new issue with:
   - Current version (1.x)
   - Target version (2.0)
   - Error messages
   - Steps taken

### Benefits of Upgrading

- 📚 Comprehensive documentation
- 🐛 Better error handling
- ✅ Enhanced validation
- 🔧 Improved code organization
- 🚀 Better performance
- 🔐 Security improvements
- 📦 PSR-4 compliance
- 🐳 Docker support
- 🤖 GitHub Actions workflows
- 📝 Complete examples

### Rollback

If you need to rollback:

```bash
composer require icamys/php-sitemap-generator:^1.0
```

However, we recommend staying on 2.0 for the latest features and security updates.
