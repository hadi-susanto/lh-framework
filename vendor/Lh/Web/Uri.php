<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web;

/**
 * Class Uri
 *
 * This class query string and URL from user request. This class also have responsibility to break the request into segments and camelized segments.
 *
 * @package Lh\Web
 */
class Uri {
	/** @var string Original URI string */
	protected $originalRequestUri;
	/** @var string Path containing controller, method and parameters */
	protected $path;
	/** @var string Query string */
	protected $queryString = null;
	/** @var string[] Segments to determine controller etc */
	protected $segments;
	/** @var string[] Segments in camel cased format */
	protected $camelizedSegments;

	/**
	 * Create new instance of URI
	 *
	 * This class must be instantiated by framework itself. Not intended for user code
	 *
	 * @param string $requestUri
	 * @param string $queryString
	 */
	public function __construct($requestUri, $queryString) {
		$this->originalRequestUri = $requestUri;

		// Remove base patch from request URI
		$temp = Application::getInstance()->getBasePath();
		if (($pos = strpos($requestUri, $temp)) !== false) {
			$requestUri = substr($requestUri, $pos + strlen($temp));
		}
		// Remove main script from path if it's appended
		$temp = Application::getInstance()->getMainScript();
		if (($pos = strpos($requestUri, $temp)) !== false) {
			$requestUri = substr($requestUri, $pos + strlen($temp));
		}
		// Remove '?' from requestUri
		if (($pos = strpos($requestUri, '?')) !== false) {
			$requestUri = substr($requestUri, 0, $pos);
		}
		$this->path = $requestUri;
		$this->queryString = $queryString ?: null;

		// Processing each segments
		$this->processSegments();
	}

	/**
	 * Get original request URI
	 *
	 * @return string
	 */
	public function getOriginalRequestUri() {
		return $this->originalRequestUri;
	}

	/**
	 * Get un-processed path
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Get raw query string
	 *
	 * @return string
	 */
	public function getQueryString() {
		return $this->queryString;
	}

	/**
	 * Get processed path ito segments
	 *
	 * @return string[]
	 */
	public function getSegments() {
		return $this->segments;
	}

	/**
	 * Get camel cased segments
	 *
	 * @return string[]
	 */
	public function getCamelizedSegments() {
		return $this->camelizedSegments;
	}

	/**
	 * Convert path into segments
	 *
	 * Processing path from user request into appropriate segment based url.
	 * This process also camelized the segment for loading purpose
	 */
	private function processSegments() {
		$this->segments = array();
		$this->camelizedSegments = array();
		$segments = explode("/", $this->path);

		foreach ($segments as $segment) {
			$segment = trim($segment);
			if ($segment == "") {
				continue;
			}

			$this->segments[] = $segment;
			// Translate all '-' into ' ' then upper case each word(s)
			$temp = ucwords(strtolower(str_replace("-", " ", $segment)));
			// Now remove the ' '
			$temp = str_replace(" ", "", $temp);
			$this->camelizedSegments[] = $temp;
		}
	}
}

// End of File: Uri.php 