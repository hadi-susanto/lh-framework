<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MsSql\Pdo;

use Exception;
use Lh\Db\Pdo\PdoQuery;

/**
 * Class MsSqlPdoQuery
 *
 * @package Lh\Db\MsSql\Pdo
 */
class MsSqlPdoQuery extends PdoQuery {
	/**
	 * Create appropriate exception for specific driver
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previousException
	 *
	 * @return MsSqlPdoException
	 */
	protected function createException($message, $code = 0, Exception $previousException = null) {
		return new MsSqlPdoException($message, $code, $previousException);
	}
}

// End of File: MsSqlPdoQuery.php 