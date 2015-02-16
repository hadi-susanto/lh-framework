<?php
/**
 * LH Framework
 *
 * @author    Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Exceptions;

use Exception;

/**
 * Class PhpErrorException
 *
 * This class will represent PHP Error, Warning, Notice, etc caused while PHP interpret codes. By default PHP error will not stop execution and will continue to
 * next line. This behaviour is not appropriate in LH Framework since a tiny warning or error would lead to another un-expected error or exception. So in our default
 * error handler we will casting PHP error as exception using this class instance
 *
 * @package Lh\Exceptions
 */
class PhpErrorException extends \ErrorException {
	/** @var string[] Contain(s) human readable error message from PHP error code */
	protected $errorTypes = array(
		E_ERROR => 'Error',
		E_WARNING => 'Warning',
		E_PARSE => 'Parsing Error',
		E_NOTICE => 'Notice',
		E_CORE_ERROR => 'Core Error',
		E_CORE_WARNING => 'Core Warning',
		E_COMPILE_ERROR => 'Compile Error',
		E_COMPILE_WARNING => 'Compile Warning',
		E_USER_ERROR => 'User Error',
		E_USER_WARNING => 'User Warning',
		E_USER_NOTICE => 'User Notice',
		E_STRICT => 'Runtime Notice',
		E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
		E_DEPRECATED => 'Core Method or Function Deprecated',
		E_USER_DEPRECATED => 'Method or Function Deprecated'
	);
	/** @var string Human readable error code message */
	protected $severityText = null;

	/**
	 * Create new instance of PhpErrorException
	 *
	 * @param string     $message
	 * @param int        $severity
	 * @param int|string $filename
	 * @param int|string $line
	 * @param Exception  $previous
	 */
	public function __construct($message, $severity = 1, $filename = __FILE__, $line = __LINE__, Exception $previous = null) {
		parent::__construct($message, 0, $severity, $filename, $line, $previous);
		if (isset($this->errorTypes[$severity])) {
			$this->severityText = $this->errorTypes[$severity];
		} else {
			$this->severityText = "Unknown severity code: $severity (Unable to perform translation)" ;
		}
	}

	/**
	 * Get severity code as human readable text
	 *
	 * Get php error code as human readable message since it is not possible to remember it by code.
	 *
	 * @return string
	 */
	public final function getSeverityAsText() {
		return $this->severityText;
	}
}

// End of File: PhpErrorException.php 