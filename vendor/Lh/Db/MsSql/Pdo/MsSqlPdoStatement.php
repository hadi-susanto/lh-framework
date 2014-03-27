<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MsSql\Pdo;

use Exception;
use Lh\Db\Pdo\LhPdoStatement;

/**
 * Class MsSqlPdoStatement
 *
 * @package Lh\Db\MsSql\Pdo
 */
class MsSqlPdoStatement extends LhPdoStatement {
	/**
	 * Create appropriate exception for specific driver
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return \Lh\Db\DbException
	 */
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new MsSqlPdoException($message, $code, $previousException);
	}

	/**
	 * Create specialized PdoQuery object
	 *
	 * @param \PdoStatement $statement
	 * @param int           $fetchMode
	 *
	 * @return MsSqlPdoQuery
	 */
	protected function createPdoQuery(\PdoStatement &$statement, &$fetchMode) {
		return new MsSqlPdoQuery($statement, $fetchMode);
	}
}

// End of File: MsSqlPdoStatement.php 