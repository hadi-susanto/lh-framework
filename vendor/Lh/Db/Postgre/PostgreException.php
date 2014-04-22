<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Postgre;

use Exception;
use Lh\Db\DbException;

/**
 * Class PostgreException
 *
 * @package Lh\Db\Postgre
 */
class PostgreException extends DbException {
	/**
	 * Create new instance of PostgreException
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

// End of File: PostgreException.php 
