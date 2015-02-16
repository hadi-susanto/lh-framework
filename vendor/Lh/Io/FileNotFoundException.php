<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Io;

use Exception;

/**
 * Class FileNotFoundException
 *
 * Exception if required / requested file is not found. IMPORTANT: If requested file exists but un-readable DON'T use this exception.
 *
 * @package Lh\Io
 */
class FileNotFoundException extends IoException {
	/** @var string Requested file location */
	protected $filePath;

	/**
	 * Create new instance of FileNotFoundException
	 *
	 * @param string    $filePath
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($filePath, $message = "", $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->filePath = $filePath;
	}

	/**
	 * Get un-existed file path
	 *
	 * @return string
	 */
	public function getFilePath() {
		return $this->filePath;
	}
}

// End of File: FileNotFoundException.php 