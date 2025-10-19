<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Icamys\SitemapGenerator\SitemapGenerator;

/**
 * Advanced Usage Example
 *
 * This example demonstrates advanced features like:
 * - Custom output directory
 * - GZip compression
 * - Large number of URLs
 * - Custom sitemap names
 */

// Create sitemap with custom base path
$generator = new SitemapGenerator('https://example.com', './output/');

// Enable GZip compression
$generator->createGZipFile = true;

// Set custom filenames
$generator->sitemapFileName = 'my-sitemap.xml';
$generator->sitemapIndexFileName = 'my-sitemap-index.xml';

// Set custom limits
$generator->maxURLsPerSitemap = 10000; // Lower limit for testing

// Add a large number of URLs (for demonstration)
for ($i = 1; $i <= 100; $i++) {
    $generator->addUrl(
        "/page-{$i}",
        new DateTime(),
        'weekly',
        '0.7'
    );
}

// Add blog posts with different dates
$blogDates = [
    '2024-01-01', '2024-01-08', '2024-01-15', '2024-01-22',
    '2024-02-01', '2024-02-08', '2024-02-15', '2024-02-22'
];

foreach ($blogDates as $index => $date) {
    $generator->addUrl(
        "/blog/post-" . ($index + 1),
        new DateTime($date),
        'monthly',
        '0.8'
    );
}

// Add product pages
for ($i = 1; $i <= 50; $i++) {
    $generator->addUrl(
        "/products/product-{$i}",
        new DateTime(),
        'daily',
        '0.9'
    );
}

// Create the sitemap
echo "Creating sitemap...\n";
$generator->createSitemap();

// Write to files
echo "Writing sitemap files...\n";
$generator->writeSitemap();

// Update robots.txt
echo "Updating robots.txt...\n";
$generator->updateRobots();

// Get statistics
$totalUrls = $generator->countUrls();
$sitemaps = $generator->toArray();

echo "\nSitemap Generation Complete!\n";
echo "========================\n";
echo "Total URLs: {$totalUrls}\n";
echo "Number of sitemap files: " . count($sitemaps) . "\n";
echo "GZip compression: " . ($generator->createGZipFile ? 'Enabled' : 'Disabled') . "\n";
echo "Output directory: ./output/\n";

// Display sitemap file list
echo "\nGenerated files:\n";
foreach ($sitemaps as $sitemap) {
    echo "  - {$sitemap[0]}\n";
}
