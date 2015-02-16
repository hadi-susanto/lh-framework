<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Collections;

use Exception;
use Lh\ApplicationException;

/**
 * Class KeyNotExistsException
 * @package Lh\Collections
 */
class KeyNotFoundException extends ApplicationException {
	/**
	 * This will contain the variable name instead of variable value
	 * @var string
	 */
	protected $keyName;

	/**
	 * Create new instance of KeyNotFoundException
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
	 * Get key name which not-exists from requested collection
	 *
	 * @return string
	 */
	public function getKeyName() {
		return $this->keyName;
	}
}

// End of File: KeyNotFoundException.php