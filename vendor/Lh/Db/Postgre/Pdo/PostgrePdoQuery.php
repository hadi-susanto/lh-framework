<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Postgre\Pdo;

use Exception;
use Lh\Db\Pdo\PdoQuery;

/**
 * Class PostgrePdoQuery
 *
 * @package Lh\Db\Postgre\Pdo
 */
class PostgrePdoQuery extends PdoQuery {
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
}

// End of File: PostgrePdoQuery.php 
