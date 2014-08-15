<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Builders;

/**
 * Interface IDelete
 *
 * Contract for build DELETE Statement
 *
 * @package Lh\Db\Builders
 */
interface IDelete extends ISql {
	/**
	 * Set table name which DELETE statement executed
	 *
	 * @param string $tableName
	 *
	 * @return IDelete
	 */
	public function from($tableName);

	/**
	 * Add a WHERE clause to the DELETE statement
	 *
	 * @param string|ILiteral               $field
	 * @param null|string|int|array|ISelect $value
	 * @param string                        $operator
	 *
	 * @return IDelete
	 */
	public function where($field, $value, $operator = '=');
}

// End of File: IDelete.php 