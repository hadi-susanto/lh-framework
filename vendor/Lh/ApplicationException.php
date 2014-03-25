<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh;

use Exception;

/**
 * Class ApplicationException
 *
 * This class act as base class every Exception defined in this Framework
 *
 * @package Lh\System
 */
class ApplicationException extends Exception {
	/**
	 * Create new instance of ApplicationException
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($message = "", $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}

// End of File: ApplicationException.php 