<?php

namespace Icamys\SitemapGenerator;

/**
 * SitemapGenerator - A PHP class for generating XML sitemaps
 *
 * This class provides functionality to generate XML sitemaps following
 * the sitemaps.org protocol specifications. It supports multiple sitemaps,
 * sitemap index files, and gzip compression.
 *
 * @package Icamys\SitemapGenerator
 * @version 2.0.0
 * @license MIT
 */
class SitemapGenerator
{
    const MAX_FILE_SIZE = 10485760; // 10MB in bytes
    const MAX_URLS_PER_SITEMAP = 50000;

    const URL_PARAM_LOC = 0;
    const URL_PARAM_LASTMOD = 1;
    const URL_PARAM_CHANGEFREQ = 2;
    const URL_PARAM_PRIORITY = 3;

    /**
     * Name of sitemap file
     * @var string
     */
    public $sitemapFileName = "sitemap.xml";

    /**
     * Name of sitemap index file
     * @var string
     */
    public $sitemapIndexFileName = "sitemap-index.xml";

    /**
     * Robots file name
     * @var string
     */
    public $robotsFileName = "robots.txt";

    /**
     * Quantity of URLs per single sitemap file.
     * According to specification max value is 50,000.
     * If your links are very long, sitemap file can exceed 10MB,
     * in this case use a smaller value.
     * @var int
     */
    public $maxURLsPerSitemap = self::MAX_URLS_PER_SITEMAP;

    /**
     * Quantity of sitemaps per index file.
     * According to specification max value is 50,000
     * @see http://www.sitemaps.org/protocol.html
     * @var int
     */
    public $maxSitemaps = 50000;

    /**
     * If true, two sitemap files (.xml and .xml.gz) will be created.
     * The .gz file will be submitted to search engines.
     * @var bool
     */
    public $createGZipFile = false;

    /**
     * URL to your site (with trailing slash)
     * @var string
     */
    private $baseURL;

    /**
     * Base path relative to script location
     * @var string
     */
    private $basePath;

    /**
     * Version of this class
     * @var string
     */
    private $classVersion = "2.0.0";

    /**
     * Search engines URLs for sitemap submission
     * @var array
     */
    private $searchEngines = array(
        "http://www.google.com/webmasters/tools/ping?sitemap=",
        "http://www.bing.com/webmaster/ping.aspx?siteMap="
    );

    /**
     * Array with URLs
     * @var \SplFixedArray
     */
    private $urls;

    /**
     * Array with sitemaps
     * @var array
     */
    private $sitemaps;

    /**
     * Array with sitemap index
     * @var array
     */
    private $sitemapIndex;

    /**
     * Current sitemap full URL
     * @var string
     */
    private $sitemapFullURL;

    /**
     * @var \DOMDocument
     */
    private $document;

    /**
     * Constructor
     *
     * @param string $baseURL Your site URL, with / at the end
     * @param string $basePath Relative path where sitemap and robots should be stored
     * @throws \InvalidArgumentException If baseURL is empty
     */
    public function __construct($baseURL, $basePath = "")
    {
        if (empty($baseURL)) {
            throw new \InvalidArgumentException("Base URL cannot be empty.");
        }

        $this->urls = new \SplFixedArray();
        $this->baseURL = rtrim($baseURL, '/') . '/';
        $this->basePath = $basePath;
        $this->document = new \DOMDocument("1.0", "UTF-8");
        $this->document->preserveWhiteSpace = false;
        $this->document->formatOutput = true;
    }

    /**
     * Add multiple URLs at once
     *
     * @param array $urlsArray Array of URLs, each can have 1 to 4 fields
     * @throws \InvalidArgumentException
     */
    public function addUrls($urlsArray)
    {
        if (!is_array($urlsArray)) {
            throw new \InvalidArgumentException("Array as argument should be given.");
        }
        foreach ($urlsArray as $url) {
            $this->addUrl(
                isset($url[0]) ? $url[0] : null,
                isset($url[1]) ? $url[1] : null,
                isset($url[2]) ? $url[2] : null,
                isset($url[3]) ? $url[3] : null
            );
        }
    }

    /**
     * Add a single URL to sitemap
     *
     * @param string $url URL path (relative to baseURL)
     * @param \DateTime|null $lastModified When it was modified (ISO 8601)
     * @param string|null $changeFrequency How often search engines should revisit
     *                                      (always|hourly|daily|weekly|monthly|yearly|never)
     * @param string|null $priority Priority of URL (0.0 to 1.0)
     * @throws \InvalidArgumentException
     */
    public function addUrl($url, \DateTime $lastModified = null, $changeFrequency = null, $priority = null)
    {
        if ($url == null) {
            throw new \InvalidArgumentException("URL is mandatory. At least one argument should be given.");
        }

        $urlLength = extension_loaded('mbstring') ? mb_strlen($url) : strlen($url);
        if ($urlLength > 2048) {
            throw new \InvalidArgumentException(
                "URL length can't be bigger than 2048 characters. " .
                "Note, that precise url length check is guaranteed only using mb_string extension."
            );
        }

        // Validate change frequency if provided
        if ($changeFrequency !== null) {
            $validFrequencies = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];
            if (!in_array($changeFrequency, $validFrequencies)) {
                throw new \InvalidArgumentException(
                    "Invalid change frequency. Must be one of: " . implode(', ', $validFrequencies)
                );
            }
        }

        // Validate priority if provided
        if ($priority !== null) {
            $priority = (float) $priority;
            if ($priority < 0.0 || $priority > 1.0) {
                throw new \InvalidArgumentException("Priority must be between 0.0 and 1.0");
            }
        }

        $tmp = new \SplFixedArray(1);
        $tmp[self::URL_PARAM_LOC] = $url;

        if (isset($lastModified)) {
            $tmp->setSize(2);
            $tmp[self::URL_PARAM_LASTMOD] = $lastModified->format(\DateTime::ATOM);
        }

        if (isset($changeFrequency)) {
            $tmp->setSize(3);
            $tmp[self::URL_PARAM_CHANGEFREQ] = $changeFrequency;
        }

        if (isset($priority)) {
            $tmp->setSize(4);
            $tmp[self::URL_PARAM_PRIORITY] = $priority;
        }

        if ($this->urls->getSize() === 0) {
            $this->urls->setSize(1);
        } else {
            if ($this->urls->getSize() === $this->urls->key()) {
                $this->urls->setSize($this->urls->getSize() * 2);
            }
        }

        $this->urls[$this->urls->key()] = $tmp;
        $this->urls->next();
    }

    /**
     * Create the sitemap(s)
     *
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function createSitemap()
    {
        if (!isset($this->urls)) {
            throw new \BadMethodCallException("To create sitemap, call addUrl or addUrls function first.");
        }

        if ($this->maxURLsPerSitemap > self::MAX_URLS_PER_SITEMAP) {
            throw new \InvalidArgumentException(
                "More than " . self::MAX_URLS_PER_SITEMAP . " URLs per single sitemap is not allowed."
            );
        }

        $generatorInfo = '<!-- generated-on="' . date('c') . '" generator="PHP-Sitemap-Generator/' .
                         $this->classVersion . '" -->';

        $sitemapHeader = '<?xml version="1.0" encoding="UTF-8"?>' . $generatorInfo . '
                            <urlset
                                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
                                http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
                                xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
                         </urlset>';

        $sitemapIndexHeader = '<?xml version="1.0" encoding="UTF-8"?>' . $generatorInfo . '
                                <sitemapindex
                                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
                                    http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd"
                                    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
                              </sitemapindex>';

        // Count non-null URLs
        $nullUrls = 0;
        foreach ($this->urls as $url) {
            if (is_null($url)) {
                $nullUrls++;
            }
        }

        $nonEmptyUrls = $this->urls->getSize() - $nullUrls;

        if ($nonEmptyUrls === 0) {
            throw new \BadMethodCallException("No URLs added. Cannot create empty sitemap.");
        }

        $chunks = ceil($nonEmptyUrls / $this->maxURLsPerSitemap);

        // Generate sitemap chunks
        for ($chunkCounter = 0; $chunkCounter < $chunks; $chunkCounter++) {
            $xml = new \SimpleXMLElement($sitemapHeader);
            for ($urlCounter = $chunkCounter * $this->maxURLsPerSitemap;
                 $urlCounter < ($chunkCounter + 1) * $this->maxURLsPerSitemap && $urlCounter < $nonEmptyUrls;
                 $urlCounter++
            ) {
                $row = $xml->addChild('url');
                $row->addChild(
                    'loc',
                    htmlspecialchars($this->baseURL . $this->urls[$urlCounter][self::URL_PARAM_LOC], ENT_QUOTES, 'UTF-8')
                );

                if ($this->urls[$urlCounter]->getSize() > 1) {
                    $row->addChild('lastmod', $this->urls[$urlCounter][self::URL_PARAM_LASTMOD]);
                }
                if ($this->urls[$urlCounter]->getSize() > 2) {
                    $row->addChild('changefreq', $this->urls[$urlCounter][self::URL_PARAM_CHANGEFREQ]);
                }
                if ($this->urls[$urlCounter]->getSize() > 3) {
                    $row->addChild('priority', $this->urls[$urlCounter][self::URL_PARAM_PRIORITY]);
                }
            }

            if (strlen($xml->asXML()) > self::MAX_FILE_SIZE) {
                throw new \LengthException(
                    "Sitemap size (" . strlen($xml->asXML()) . " bytes) exceeds 10MB limit (" .
                    self::MAX_FILE_SIZE . " bytes). Please decrease maxURLsPerSitemap."
                );
            }
            $this->sitemaps[] = $xml->asXML();
        }

        if (count($this->sitemaps) > $this->maxSitemaps) {
            throw new \LengthException(
                "Sitemap index can contain {$this->maxSitemaps} sitemaps. " .
                "You are trying to submit too many maps."
            );
        }

        // Handle multiple sitemaps with index
        if (count($this->sitemaps) > 1) {
            for ($i = 0; $i < count($this->sitemaps); $i++) {
                $this->sitemaps[$i] = array(
                    str_replace(".xml", ($i + 1) . ".xml", $this->sitemapFileName),
                    $this->sitemaps[$i]
                );
            }

            $xml = new \SimpleXMLElement($sitemapIndexHeader);
            foreach ($this->sitemaps as $sitemap) {
                $row = $xml->addChild('sitemap');
                $row->addChild('loc', $this->baseURL . $this->getSitemapFileName(htmlentities($sitemap[0])));
                $row->addChild('lastmod', date('c'));
            }

            $this->sitemapFullURL = $this->baseURL . $this->sitemapIndexFileName;
            $this->sitemapIndex = array(
                $this->sitemapIndexFileName,
                $xml->asXML()
            );
        } else {
            $this->sitemapFullURL = $this->baseURL . $this->getSitemapFileName();
            $this->sitemaps[0] = array(
                $this->sitemapFileName,
                $this->sitemaps[0]
            );
        }
    }

    /**
     * Get created sitemaps as array of strings
     *
     * @return array Array of sitemap data
     */
    public function toArray()
    {
        if (isset($this->sitemapIndex)) {
            return array_merge(array($this->sitemapIndex), $this->sitemaps);
        } else {
            return $this->sitemaps;
        }
    }

    /**
     * Write sitemaps to files
     *
     * @throws \BadMethodCallException
     */
    public function writeSitemap()
    {
        if (!isset($this->sitemaps)) {
            throw new \BadMethodCallException("To write sitemap, call createSitemap function first.");
        }

        if (isset($this->sitemapIndex)) {
            $this->document->loadXML($this->sitemapIndex[1]);
            $this->writeFile($this->document->saveXML(), $this->basePath, $this->sitemapIndex[0], true);
            foreach ($this->sitemaps as $sitemap) {
                $this->writeFile($sitemap[1], $this->basePath, $sitemap[0]);
            }
        } else {
            $this->document->loadXML($this->sitemaps[0][1]);
            $this->writeFile($this->document->saveXML(), $this->basePath, $this->sitemaps[0][0], true);
            $this->writeFile($this->sitemaps[0][1], $this->basePath, $this->sitemaps[0][0]);
        }
    }

    /**
     * Get sitemap filename with optional gzip extension
     *
     * @param string|null $name Filename
     * @return string Filename with extension
     */
    private function getSitemapFileName($name = null)
    {
        if (!$name) {
            $name = $this->sitemapFileName;
        }
        if ($this->createGZipFile) {
            $name .= ".gz";
        }
        return $name;
    }

    /**
     * Save file to disk
     *
     * @param string $content File content
     * @param string $filePath Directory path
     * @param string $fileName File name
     * @param bool $noGzip Skip gzip compression
     * @return bool Success status
     */
    private function writeFile($content, $filePath, $fileName, $noGzip = false)
    {
        if (!$noGzip && $this->createGZipFile) {
            return $this->writeGZipFile($content, $filePath, $fileName);
        }

        $fullPath = $filePath . $fileName;
        $file = fopen($fullPath, 'w');
        if ($file === false) {
            throw new \RuntimeException("Cannot open file for writing: " . $fullPath);
        }
        fwrite($file, $content);
        return fclose($file);
    }

    /**
     * Save GZipped file to disk
     *
     * @param string $content File content
     * @param string $filePath Directory path
     * @param string $fileName File name
     * @return bool Success status
     */
    private function writeGZipFile($content, $filePath, $fileName)
    {
        $fileName .= '.gz';
        $fullPath = $filePath . $fileName;
        $file = gzopen($fullPath, 'w');
        if ($file === false) {
            throw new \RuntimeException("Cannot open gzip file for writing: " . $fullPath);
        }
        gzwrite($file, $content);
        return gzclose($file);
    }

    /**
     * Update or create robots.txt file with sitemap information
     *
     * @throws \BadMethodCallException
     */
    public function updateRobots()
    {
        if (!isset($this->sitemaps)) {
            throw new \BadMethodCallException("To update robots.txt, call createSitemap function first.");
        }

        $sampleRobotsFile = "User-agent: *\nAllow: /";
        $robotsPath = $this->basePath . $this->robotsFileName;

        if (file_exists($robotsPath)) {
            $robotsFile = explode("\n", file_get_contents($robotsPath));
            $robotsFileContent = "";

            foreach ($robotsFile as $value) {
                if (substr($value, 0, 8) !== 'Sitemap:') {
                    $robotsFileContent .= $value . "\n";
                }
            }

            $robotsFileContent .= "Sitemap: $this->sitemapFullURL";
            if (!isset($this->sitemapIndex)) {
                $robotsFileContent .= "\nSitemap: " . $this->getSitemapFileName($this->sitemapFullURL);
            }
            file_put_contents($robotsPath, $robotsFileContent);
        } else {
            $sampleRobotsFile = $sampleRobotsFile . "\n\nSitemap: " . $this->sitemapFullURL;
            if (!isset($this->sitemapIndex)) {
                $sampleRobotsFile .= "\nSitemap: " . $this->getSitemapFileName($this->sitemapFullURL);
            }
            file_put_contents($robotsPath, $sampleRobotsFile);
        }
    }

    /**
     * Submit sitemap to search engines
     *
     * @return array Results from each search engine
     * @throws \BadMethodCallException
     */
    public function submitSitemap()
    {
        if (!isset($this->sitemaps)) {
            throw new \BadMethodCallException("To submit sitemap, call createSitemap function first.");
        }

        if (!extension_loaded('curl')) {
            throw new \BadMethodCallException("cURL library is needed to do submission.");
        }

        $result = array();
        foreach ($this->searchEngines as $searchEngine) {
            $submitUrl = $searchEngine . urlencode($this->sitemapFullURL);
            $submitSite = curl_init($submitUrl);
            curl_setopt($submitSite, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($submitSite, CURLOPT_TIMEOUT, 30);
            $responseContent = curl_exec($submitSite);
            $response = curl_getinfo($submitSite);

            $submitSiteShort = array_reverse(explode(".", parse_url($searchEngine, PHP_URL_HOST)));

            $result[] = array(
                "site" => $submitSiteShort[1] . "." . $submitSiteShort[0],
                "fullsite" => $submitUrl,
                "http_code" => $response['http_code'],
                "message" => str_replace("\n", " ", strip_tags($responseContent))
            );
            curl_close($submitSite);
        }
        return $result;
    }

    /**
     * Get all URLs as array
     *
     * @return array Array of URLs with their parameters
     */
    public function getUrls()
    {
        $urls = $this->urls->toArray();

        foreach ($urls as $key => $urlSplArr) {
            if (!is_null($urlSplArr)) {
                $urlArr = $urlSplArr->toArray();
                $url = [];
                foreach ($urlArr as $paramIndex => $paramValue) {
                    switch ($paramIndex) {
                        case static::URL_PARAM_LOC:
                            $url['loc'] = $paramValue;
                            break;
                        case static::URL_PARAM_CHANGEFREQ:
                            $url['changefreq'] = $paramValue;
                            break;
                        case static::URL_PARAM_LASTMOD:
                            $url['lastmod'] = $paramValue;
                            break;
                        case static::URL_PARAM_PRIORITY:
                            $url['priority'] = $paramValue;
                            break;
                        default:
                            break;
                    }
                }
                $urls[$key] = $url;
            }
        }

        return array_filter($urls);
    }

    /**
     * Get the count of URLs
     *
     * @return int Number of URLs
     */
    public function countUrls()
    {
        return $this->urls->getSize();
    }

    /**
     * Get the version of this class
     *
     * @return string Version number
     */
    public function getVersion()
    {
        return $this->classVersion;
    }
}
