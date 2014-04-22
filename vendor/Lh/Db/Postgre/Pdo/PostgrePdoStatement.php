<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Postgre\Pdo;

use Exception;
use Lh\Db\Pdo\LhPdoStatement;

class PostgrePdoStatement extends LhPdoStatement {

	/**
	 * Create appropriate exception for specific driver
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return PostgrePdoException
	 */
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new PostgrePdoException($message, $code, $previousException);
	}

	/**
	 * Create specialized PdoQuery object
	 *
	 * @param \PdoStatement $statement
	 * @param int           $fetchMode
	 *
	 * @return PostgrePdoQuery
	 */
	protected function createPdoQuery(\PdoStatement &$statement, &$fetchMode) {
		return new PostgrePdoQuery($statement, $fetchMode);
	}
}

// End of File: PostgrePdoStatement.php 
