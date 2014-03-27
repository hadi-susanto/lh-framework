<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySql\Pdo;

use Exception;
use Lh\Db\Pdo\LhPdoStatement;

/**
 * Class MySqlPdoStatement
 *
 * @package Lh\Db\MySql\Pdo
 */
class MySqlPdoStatement extends LhPdoStatement {
	/**
	 * Create appropriate exception for specific driver
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return MySqlPdoException
	 */
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new MySqlPdoException($message, $code, $previousException);
	}

	/**
	 * Create specialized PdoQuery object
	 *
	 * @param \PDOStatement $statement
	 * @param int           $fetchMode
	 *
	 * @return MySqlPdoQuery
	 */
	protected function createPdoQuery(\PDOStatement &$statement, &$fetchMode) {
		return new MySqlPdoQuery($statement, $fetchMode);
	}
}

// End of File: MySqlPdoStatement.php 