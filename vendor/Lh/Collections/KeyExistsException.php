<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Collections;

use Exception;
use Lh\ApplicationException;

/**
 * Class KeyExistsException
 * @package Lh\Exceptions
 */
class KeyExistsException extends ApplicationException {
	/**
	 * This will contain the variable name instead of variable value
	 * @var string
	 */
	protected $keyName;

	/**
	 * Create new instance of KeyExistsException
	 *
	 * @param string    $keyName
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($keyName, $message = "", $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->keyName = $keyName;
	}

	/**
	 * Get key name which already exists in a collection
	 *
	 * @return string
	 */
	public function getKeyName() {
		return $this->keyName;
	}
}

// End of File: KeyExistsException.php 