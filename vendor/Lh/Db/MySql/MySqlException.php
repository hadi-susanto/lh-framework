<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySql;

use Exception;
use Lh\Db\DbException;

/**
 * Class MySqlException
 *
 * @package Lh\Db\MySql
 */
class MySqlException extends DbException {
	/**
	 * Create instance of MySqlException
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->dbEngine = __NAMESPACE__;
	}
}

// End of File: MySqlException.php 