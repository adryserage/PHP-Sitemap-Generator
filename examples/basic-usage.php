<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Icamys\SitemapGenerator\SitemapGenerator;

/**
 * Basic Usage Example
 *
 * This example demonstrates the basic usage of the PHP Sitemap Generator
 */

// Create sitemap generator
$generator = new SitemapGenerator('https://example.com');

// Add URLs one by one
$generator->addUrl('/', new DateTime(), 'daily', '1.0');
$generator->addUrl('/about', new DateTime(), 'monthly', '0.8');
$generator->addUrl('/contact', new DateTime(), 'monthly', '0.8');

// Add multiple URLs at once
$urls = [
    ['/blog', new DateTime('2024-01-01'), 'weekly', '0.9'],
    ['/products', new DateTime('2024-01-15'), 'daily', '0.9'],
    ['/services', new DateTime('2024-01-10'), 'weekly', '0.8'],
];
$generator->addUrls($urls);

// Create the sitemap
$generator->createSitemap();

// Write sitemap to file
$generator->writeSitemap();

// Update robots.txt
$generator->updateRobots();

echo "Sitemap generated successfully!\n";
echo "Total URLs: " . $generator->countUrls() . "\n";

// Optional: Submit to search engines
// Uncomment the following lines to submit
/*
try {
    $results = $generator->submitSitemap();
    foreach ($results as $result) {
        echo "Submitted to {$result['site']}: HTTP {$result['http_code']}\n";
    }
} catch (Exception $e) {
    echo "Error submitting sitemap: " . $e->getMessage() . "\n";
}
*/
