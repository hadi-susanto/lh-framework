<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db;

use Exception;
use Lh\ApplicationException;

/**
 * Class DbException
 *
 * Base class of every exceptions in Db namespace
 *
 * @package Lh\Db
 */
class DbException extends ApplicationException {
	/** @var string Which engine causing exception */
	protected $dbEngine = null;

	/**
	 * Create new instance of DbException
	 *
	 * NOTE: It's recommended to create a derived class for each driver.
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Get driver engine name
	 *
	 * @return string
	 */
	public function getDbEngine() {
		return $this->dbEngine;
	}

	/**
	 * Set driver engine name
	 *
	 * @param $engine
	 */
	protected function setDbEngine($engine) {
		$this->dbEngine = $engine;
	}
}

// End of File: DbException.php 