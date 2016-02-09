<?php
/**
 * LH Framework
 *
 * @author    Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2016
 */

namespace Lh\Exceptions;

use Exception;
use Lh\ApplicationException;

/**
 * Class NullReferenceException
 *
 * This exception is thrown when user trying to access null reference
 *
 * @package Lh\Exceptions
 */
class NullReferenceException extends ApplicationException {
	/**
	 * NullReferenceException constructor.
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}