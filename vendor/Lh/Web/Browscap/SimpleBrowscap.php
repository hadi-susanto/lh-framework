<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Browscap;

use Lh\Exceptions\UnauthorizedException;
use Lh\Io\FileNotFoundException;
use Lh\Web\Application;

/**
 * Class SimpleBrowscap
 *
 * Basic class for handling browscap.ini file. This file containing data for browser detection based on user agent. This class will read this file and give an
 * appropriate result based on given user agent. This class WILL NOT optimize any pattern from browscap.ini therefore performance may be loss compared to optimized
 * one. You could find browscap implementation which use optimized pattern by GaretJax at his GitHub
 *
 * @link https://github.com/GaretJax/phpbrowscap
 *
 * @package Lh\Web\Browscap
 */
class SimpleBrowscap implements IBrowscapReader {
	/** @var string Location of browscap.ini file */
	private $iniFileLocation;
	/** @var string How error(s) are handled by this class */
	private $errorHandling;
	/** @var string Browscap file version */
	private $version;
	/** @var array Raw data from browscap.ini (read using parse_ini_file function) */
	private $rawBrowsers = array();
	/** @var array Mapping between Parent Browser name and their index in $rawBrowsers variable */
	private $parentBrowserMaps = array();
	/** @var array Mapping between pattern and their index in $rawBrowsers variable */
	private $patternIndexes = array();
	/** @var string[] User agent regex pattern(s) */
	private $patterns;

	/**
	 * Create new instance of Browscap class
	 *
	 * @param array $options
	 */
	public function __construct($options = array()) {
		$this->setOptions($options);
		$this->preliminaryCheck();
	}

	/**
	 * Get browscap.ini file version
	 *
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * Setting options for Browscap class
	 *
	 * This will configure your Browscap class behaviour such as browscap.ini file location, error handling, etc
	 *
	 * @param $options
	 */
	private function setOptions($options) {
		if (isset($options["file"]) && is_string($options["file"])) {
			if (strpos($options["file"], "/") === 0) {
				$this->iniFileLocation = $options["file"];
			} else {
				$this->iniFileLocation = Application::getInstance()->getApplicationPath() . "config/user/" . $options["file"];
			}
		} else {
			$this->iniFileLocation = Application::getInstance()->getApplicationPath() . "config/user/browscap.ini";
		}

		if (isset($options["error"]) && is_string($options["error"])) {
			$this->errorHandling = $options["error"];
		} else {
			$this->errorHandling = IBrowscapReader::ERROR_EXCEPTION;
		}
	}

	/**
	 * Check user options
	 *
	 * This will check basic requirement and made sure that everything is an OK
	 *
	 * @throws \Lh\Exceptions\UnauthorizedException
	 * @throws \Lh\Io\FileNotFoundException
	 */
	private function preliminaryCheck() {
		if (!is_file($this->iniFileLocation) && $this->errorHandling == IBrowscapReader::ERROR_EXCEPTION) {
			throw new FileNotFoundException($this->iniFileLocation, "Browscap file database not found at: '" . str_replace(APPLICATION_PATH, '', $this->iniFileLocation) . "'");
		}
		if (!is_readable($this->iniFileLocation)) {
			if ($this->errorHandling == IBrowscapReader::ERROR_EXCEPTION) {
				throw new UnauthorizedException("Unable to read database file! Check current user access privilege.");
			} else {
				$this->iniFileLocation = null;
			}
		}
	}

	/**
	 * Get browser detection based on user agent
	 *
	 * This will detect client browser capabilities and type by their user agent string. Although user agent can be easily manipulated but only a few people do these
	 * and only mobile browser have capability to change user agent string. User agent can be retrieved from HttpRequest object.
	 *
	 * @param string $userAgent
	 *
	 * @see \Lh\Web\Http\HttpRequest
	 *
	 * @return Browser
	 */
	public function getBrowser($userAgent) {
		if ($this->version === null && !$this->loadCache()) {
			$this->readBrowscapIni();
			$this->createCache();
		}

		foreach ($this->patterns as $idx => $pattern) {
			if (preg_match($pattern . "i", $userAgent) === 1) {
				// Obtain raw browser index of current pattern. We store pattern <=> raw browser index at $patternIndexes
				$rawBrowserIdx = $this->patternIndexes[$idx];
				// Obtain browser data
				$browser = $this->rawBrowsers[$rawBrowserIdx];

				$parentCode = isset($browser["Parent"]) ? $browser["Parent"] : null;
				if ($parentCode !== null) {
					$parentIdx = $this->parentBrowserMaps[$parentCode];
					$browser += $this->rawBrowsers[$parentIdx];
				}

				return Browser::fromBrowscapArray($browser);
			}
		}

		return null;
	}

	/**
	 * Read browscap.ini file based on configuration
	 *
	 * Reading all pattern from browscap.ini and prepare pattern for regex, parent browser link, etc
	 *
	 * @return bool
	 */
	private function readBrowscapIni() {
		if ($this->iniFileLocation === null) {
			return false;
		}

		$browsers = parse_ini_file($this->iniFileLocation, true, INI_SCANNER_RAW);

		$this->version = $browsers[IBrowscapReader::BROWSCAP_VERSION_KEY]["Version"];

		// Remove unnecessary key
		unset($browsers[IBrowscapReader::BROWSCAP_VERSION_KEY]);
		unset($browsers[IBrowscapReader::DEFAULT_PROPERTIES_KEY]);

		$idx = -1;
		foreach ($browsers as $pattern => $properties) {
			$idx++;

			$this->rawBrowsers[$idx] = $properties;

			if (strpos($pattern, "*") === false && strpos($pattern, "?") === false) {
				// This pattern don't contain any wild card character. Probably parent property
				$this->parentBrowserMaps[$pattern] = $idx;
			} else {
				$this->patternIndexes[] = $idx;
				$this->patterns[] = $this->convertPatternToRegex($pattern);
			}
		}

		return true;
	}

	/**
	 * Convert pattern to be regex compatible
	 *
	 * Pattern provided from browscap.ini only using '*' and '?' as wildcard. It's not PCRE regex compatible yet. This method used to create compatible regex pattern
	 * based on those wildcard.
	 *
	 * @param string $pattern
	 * @return string
	 */
	private function convertPatternToRegex($pattern) {
		$pattern = preg_quote($pattern, "@");

		// the \\x replacement is a fix for "Der gro\xdfe BilderSauger 2.00u" user agent match
		return "@^" . str_replace(array('\*', '\?', '\\x'), array('.*', '.', '\\\\x'), $pattern) . "$@";
	}

	/**
	 * Create cached pattern(s) data
	 *
	 * Although reading process take less than a second it's better to create cache since based on test it can improve performance between 4-5x than re-read
	 * the whole browscap.ini and processing them. Cache will stored next to your browscap.ini file. Cache file size will be bigger than browscap.ini since it
	 * will contains processed data and their link(s)
	 *
	 * @return int
	 */
	private function createCache() {
		$timestamp = filemtime($this->iniFileLocation);
		$cacheFilename = dirname($this->iniFileLocation) . DIRECTORY_SEPARATOR . "browscap.cache.php";
		$content = array(
			"cacheVersion" => "1",
			"version" => $this->version,
			"raw" => $this->rawBrowsers,
			"patterns" => $this->patterns,
			"patternIndexes" => $this->patternIndexes,
			"parentBrowserMaps" => $this->parentBrowserMaps
		);

		if (file_put_contents($cacheFilename, serialize($content), LOCK_EX)) {
			touch($cacheFilename, $timestamp);

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Load cache data if available
	 *
	 * Read cache data and restore its state. Cache data contains processed pattern and their link to raw browser data. Reading cache will improve performance
	 * significantly (4-5x). Note cache only loaded whenever modification not detected. When cache timestamp differ from browscap.ini file then it is assumed that
	 * cache data is invalid since when cache created its timestamp will be set same as browscap.ini file
	 *
	 * @return bool
	 */
	private function loadCache() {
		$cacheFilename = dirname($this->iniFileLocation) . DIRECTORY_SEPARATOR . "browscap.cache.php";
		if (!file_exists($cacheFilename) || !is_readable($cacheFilename)) {
			return false;
		}

		// If cache not valid then we must re-create cache
		$browscapTimestamp = filemtime($this->iniFileLocation);
		$cacheTimestamp = filemtime($cacheFilename);
		if (abs($cacheTimestamp - $browscapTimestamp) > 60) {
			// Assume difference more than 1 minute as invalid.
			return false;
		}

		$content = unserialize(file_get_contents($cacheFilename));
		if (is_array($content) && $content["cacheVersion"] === "1") {
			$this->version = $content["version"];
			$this->rawBrowsers = $content["raw"];
			$this->patterns = $content["patterns"];
			$this->patternIndexes = $content["patternIndexes"];
			$this->parentBrowserMaps = $content["parentBrowserMaps"];

			return true;
		} else {
			return false;
		}
	}
}

// End of File: SimpleBrowscap.php
