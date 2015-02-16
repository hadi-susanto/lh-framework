<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySql\Pdo;

use Exception;
use Lh\Db\DbException;

/**
 * Class MySqlPdoException
 *
 * @package Lh\Db\MySql\Pdo
 */
class MySqlPdoException extends DbException {
	/**
	 * Create new instance of MySqlPdoException
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

// End of File: MySqlPdoException.php 