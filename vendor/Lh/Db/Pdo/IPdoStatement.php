<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Pdo;

use Lh\Db\IStatement;

/**
 * Interface IPdoStatement
 *
 * @package Lh\Db\Pdo
 */
interface IPdoStatement extends IStatement {
	/**
	 * Get SQL STATE value from previous executed statement
	 *
	 * @return string
	 */
	public function getSqlState();
}

// End of File: IPdoStatement.php 