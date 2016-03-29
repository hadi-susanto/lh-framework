<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db;

use Exception;

/**
 * Class DriverNotFoundException
 *
 * This exception class will be thrown when specific driver required for an adapter not found in current server
 *
 * @package Lh\Db
 */
class DriverNotFoundException extends DbException {
	/** @var string Driver name which required by the adapter */
	protected $requiredDriver;

	/**
	 * DriverNotFoundException constructor.
	 *
	 * @param string         $message
	 * @param int            $code
	 * @param Exception|null $previous
	 */
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}

// End of File: DriverNotFoundException.php