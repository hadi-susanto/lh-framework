<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Builders;

/**
 * Interface IUpdate
 *
 * Contract for build UPDATE Statement
 *
 * @package Lh\Db\Builders
 */
interface IUpdate extends ISql {
	/**
	 * Set table name which UPDATE statement executed
	 *
	 * @param string $tableName
	 *
	 * @return IUpdate
	 */
	public function from($tableName);

	/**
	 * Bulk set in UPDATE statement
	 *
	 * Bulk set always remove any previous sets
	 *
	 * @param array $sets
	 *
	 * @return IUpdate
	 */
	public function sets($sets);

	/**
	 * Add a SET column_name = value clause to the UPDATE statement
	 *
	 * @param string              $field
	 * @param string|int|ILiteral $value
	 *
	 * @return IUpdate
	 */
	public function set($field, $value);

	/**
	 * Add a WHERE clause to the UPDATE statement
	 *
	 * @param string|ILiteral               $field
	 * @param null|string|int|array|ISelect $value
	 * @param string                        $operator
	 *
	 * @return IUpdate
	 */
	public function where($field, $value, $operator = '=');
}

// End of File: IUpdate.php 