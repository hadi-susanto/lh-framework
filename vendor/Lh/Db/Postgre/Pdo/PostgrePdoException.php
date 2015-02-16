<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Postgre\Pdo;

use Exception;
use Lh\Db\DbException;

/**
 * Class PostgrePdoException
 *
 * @package Lh\Db\Postgre\Pdo
 */
class PostgrePdoException extends DbException {
	/**
	 * Create new instance of PostgrePdoException
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

// End of File: PostgrePdoException.php 
