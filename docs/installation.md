# Installation Guide

This guide covers different installation methods for PHP Sitemap Generator.

## Table of Contents

- [Requirements](#requirements)
- [Installation Methods](#installation-methods)
  - [Composer (Recommended)](#composer-recommended)
  - [Manual Installation](#manual-installation)
- [Verification](#verification)
- [Troubleshooting](#troubleshooting)

## Requirements

Before installing PHP Sitemap Generator, ensure your environment meets these requirements:

### Minimum Requirements

- **PHP**: >= 5.5.0
- **PHP Extensions**:
  - `ext-SPL` (Standard PHP Library) - Required

### Optional Extensions

- `ext-curl` - Required for sitemap submission to search engines
- `ext-zlib` - Required for GZip compression
- `ext-mbstring` - Recommended for accurate URL length validation

### Check Your PHP Version

```bash
php -v
```

### Check Installed Extensions

```bash
php -m
```

## Installation Methods

### Composer (Recommended)

Composer is the recommended way to install PHP Sitemap Generator as it handles autoloading and dependencies automatically.

#### 1. Install Composer

If you don't have Composer installed, download it from [getcomposer.org](https://getcomposer.org/).

#### 2. Install the Package

In your project directory, run:

```bash
composer require icamys/php-sitemap-generator
```

#### 3. Include the Autoloader

In your PHP file:

```php
<?php

require_once 'vendor/autoload.php';

use Icamys\SitemapGenerator\SitemapGenerator;
```

#### Development Installation

If you're developing or testing:

```bash
# Clone the repository
git clone https://github.com/icamys/php-sitemap-generator.git
cd php-sitemap-generator

# Install dependencies
composer install
```

### Manual Installation

If you prefer not to use Composer:

#### 1. Download the Library

Download the latest release from [GitHub Releases](https://github.com/icamys/php-sitemap-generator/releases) or clone the repository:

```bash
git clone https://github.com/icamys/php-sitemap-generator.git
```

#### 2. Include the Files

You'll need to manually include the class file:

```php
<?php

require_once 'path/to/php-sitemap-generator/src/SitemapGenerator.php';

use Icamys\SitemapGenerator\SitemapGenerator;
```

#### 3. Manual Autoloading

If you're using PSR-4 autoloading in your project:

```php
// Register the namespace
$loader->addPsr4('Icamys\\SitemapGenerator\\', 'path/to/php-sitemap-generator/src/');
```

## Verification

After installation, verify everything is working:

### Create a Test Script

Create a file `test-sitemap.php`:

```php
<?php

require_once 'vendor/autoload.php';

use Icamys\SitemapGenerator\SitemapGenerator;

try {
    $generator = new SitemapGenerator('https://example.com');
    echo "Installation successful!\n";
    echo "Library version: " . $generator->getVersion() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Run the Test

```bash
php test-sitemap.php
```

Expected output:

```
Installation successful!
Library version: 2.0.0
```

## Troubleshooting

### Common Issues

#### 1. Class Not Found

**Error:**

```
Fatal error: Class 'Icamys\SitemapGenerator\SitemapGenerator' not found
```

**Solution:**

- Ensure you've included the autoloader: `require_once 'vendor/autoload.php';`
- If using manual installation, verify the path to the class file
- Run `composer dump-autoload` to regenerate autoload files

#### 2. SPL Extension Missing

**Error:**

```
Fatal error: Class 'SplFixedArray' not found
```

**Solution:**

- Install or enable the SPL extension (usually enabled by default)
- Check PHP configuration: `php -m | grep SPL`
- Contact your hosting provider if on shared hosting

#### 3. cURL Extension Missing

**Error:**

```
BadMethodCallException: cURL library is needed to do submission
```

**Solution:**

- Install cURL extension:
  - Ubuntu/Debian: `sudo apt-get install php-curl`
  - CentOS/RHEL: `sudo yum install php-curl`
  - Windows: Enable in `php.ini`: `extension=curl`
- Restart web server after installation

#### 4. Permission Issues

**Error:**

```
Warning: fopen(): failed to open stream: Permission denied
```

**Solution:**

- Ensure the output directory is writable:

```bash
chmod 755 /path/to/output/directory
```

- Check directory ownership
- On Windows, ensure the user has write permissions

#### 5. Memory Limit Issues

**Error:**

```
Fatal error: Allowed memory size exhausted
```

**Solution:**

- Increase PHP memory limit in `php.ini`:

```ini
memory_limit = 256M
```

- Or in your script:

```php
ini_set('memory_limit', '256M');
```

- Use smaller `maxURLsPerSitemap` value for large sitemaps

### Getting Help

If you encounter issues not covered here:

1. Check the [Troubleshooting Guide](troubleshooting.md)
2. Search [GitHub Issues](https://github.com/icamys/php-sitemap-generator/issues)
3. Create a new issue with:
   - PHP version
   - Installation method
   - Error messages
   - Steps to reproduce

## Next Steps

Now that you have PHP Sitemap Generator installed:

- Read the [Configuration Guide](configuration.md)
- Explore the [API Documentation](api.md)
- Check out [Best Practices](best-practices.md)
- Review the [Examples](../examples/)
