<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Browscap;

/**
 * Interface IBrowscapReader
 *
 * @package Lh\Web\Browscap
 */
interface IBrowscapReader {
	const ERROR_IGNORE = "ignore";
	const ERROR_EXCEPTION = "exception";

	const BROWSCAP_VERSION_KEY = "GJK_Browscap_Version";
	const DEFAULT_PROPERTIES_KEY = "DefaultProperties";

	/**
	 * Get browscap.ini file version
	 *
	 * @return string
	 */
	public function getVersion();

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
	public function getBrowser($userAgent);
}

// End of File: IBrowscapReader.php 
