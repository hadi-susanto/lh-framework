<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh;

/**
 * Class Framework
 *
 * Contains information related to Framework such as version and tools for version comparing
 *
 * @package Lh
 */
class Framework {
	const VERSION = "1.0.0";
	const VERSION_TYPE = "Release";

	/**
	 * Get current framework version
	 *
	 * @return string
	 */
	public static function getVersion() {
		if (Framework::VERSION_TYPE != "") {
			return Framework::VERSION . "-" . Framework::VERSION_TYPE;
		} else {
			return Framework::VERSION;
		}
	}

	/**
	 * Compare given version with current framework version
	 *
	 * This will compare given version number with current framework version. Comparing done by version_compare() method.
	 * It will return -1 if given version lower than current, 0 if same version, 1 if given version is higher
	 *
	 * @param string $version
	 *
	 * @return int
	 */
	public static function compareVersion($version) {
		return version_compare($version, Framework::getVersion());
	}
}

// End of File: Framework.php 
