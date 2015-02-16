<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Exceptions;

use Exception;
use Lh\ApplicationException;

/**
 * Class PropertyNotFoundException
 * @package Lh\Exceptions
 */
class PropertyNotFoundException extends ApplicationException {
	/** @var string Property name which not found in faulting object */
	protected $propertyName;
	/** @var mixed Object instance which faulting */
	protected $faultingObject;

	/**
	 * Create new instance of PropertyNotFoundException
	 *
	 * @param mixed     $faultingObject
	 * @param string    $propertyName
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($faultingObject, $propertyName, $message = "", $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->faultingObject = $faultingObject;
		$this->propertyName = $propertyName;
	}

	/**
	 * Get property name which not exists at faulting object
	 *
	 * @return string
	 */
	public function getPropertyName() {
		return $this->propertyName;
	}

	/**
	 * Get the faulting object
	 *
	 * @return mixed
	 */
	public function getFaultingObject() {
		return $this->faultingObject;
	}
}

// End of File: PropertyNotFoundException.php