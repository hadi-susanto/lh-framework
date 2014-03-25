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
 * Class MethodNotFoundException
 *
 * @package Lh\Exceptions
 */
class MethodNotFoundException extends ApplicationException {
	/** @var string Method name */
	protected $methodName;

	/**
	 * Create new instance of MethodNotFoundException
	 *
	 * @param string    $methodName
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($methodName, $message = "", $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->methodName = $methodName;
	}

	/**
	 * Get method name
	 *
	 * @return string
	 */
	public function getMethodName() {
		return $this->methodName;
	}
}

// End of File: MethodNotFoundException.php 