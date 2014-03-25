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
 * Class ClassNotFoundException
 *
 * @package Lh\Exceptions
 */
class ClassNotFoundException extends ApplicationException {
	/** @var string Fully qualified name */
	protected $fqn;
	/** @var null Namespace */
	protected $namespace;
	/** @var string Class name */
	protected $className;

	/**
	 * Create new ClassNotFoundException
	 *
	 * Differ from any other language which compiled. There is no way PHP checking for class existence. Therefore if a request made to un-available
	 * class and its definition couldn't found this exception will be thrown
	 *
	 * @param string    $fqn
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($fqn, $message = "", $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->fqn = $fqn;
		if (($pos = strrpos($fqn, "\\")) !== false) {
			$this->namespace = substr($fqn, 0, $pos);
			$this->className = substr($fqn, $pos + 1);
		} else {
			$this->namespace = null;
			$this->className = $fqn;
		}
	}

	/**
	 * Get fully qualified name
	 *
	 * @return string
	 */
	public function getFqn() {
		return $this->fqn;
	}

	/**
	 * Get namespace
	 *
	 * @return string|null
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * Get class name
	 *
	 * @return string
	 */
	public function getClassName() {
		return $this->className;
	}
}

// End of File: ClassNotFoundException.php 