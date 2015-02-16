<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Pdo;

use Lh\Db\IAdapter;
use Lh\Db\IQuery;

/**
 * Class IPdoAdapter
 *
 * Contract for PDO based driver. Its contract add PDO specific features
 *
 * @package Lh\Db\Pdo
 */
interface IPdoAdapter extends IAdapter {
	/**
	 * Get SQL STATE value
	 *
	 * @return string
	 */
	public function getSqlState();

	/**
	 * Get dsn prefix used in PDO connection
	 *
	 * @return string
	 */
	public function getDsnPrefix();

	/**
	 * Set attribute for current PDO object
	 *
	 * @param int   $key
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function setAttribute($key, $value);

	/**
	 * Get attribute value from current PDO object
	 *
	 * @param int $key
	 *
	 * @return mixed
	 */
	public function getAttribute($key);
}

// End of File: IPdoAdapter.php 