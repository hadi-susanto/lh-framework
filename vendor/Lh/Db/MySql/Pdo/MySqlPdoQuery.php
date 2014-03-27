<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySql\Pdo;

use Exception;
use Lh\Db\Pdo\PdoQuery;

/**
 * Class MySqlPdoQuery
 *
 * @package Lh\Db\MySql\Pdo
 */
class MySqlPdoQuery extends PdoQuery {
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
}

// End of File: MySqlPdoQuery.php 