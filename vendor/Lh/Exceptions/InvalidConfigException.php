<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Exceptions;

use Exception;
use Lh\ApplicationException;

/**
 * Class InvalidConfigException
 *
 * @package Lh\Exceptions
 */
class InvalidConfigException extends ApplicationException {
	/** @var string Config file location */
	protected $configFile = null;

	/**
	 * Create new instance of InvalidConfigException
	 *
	 * @param string    $configFile
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($configFile, $message = "", $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->configFile = $configFile;
	}

	/**
	 * Get config file which cause trouble
	 *
	 * @return string
	 */
	public function getConfigFile() {
		return $this->configFile;
	}
}

// End of File: InvalidConfigException.php 