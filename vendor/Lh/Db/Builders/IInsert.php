<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Builders;

/**
 * Interface IInsert
 *
 * Contract for build INSERT INTO Statement
 *
 * @package Lh\Db\Builders
 */
interface IInsert extends ISql {
	/**
	 * Target table name
	 *
	 * @param string $tableName
	 *
	 * @return IInsert
	 */
	public function into($tableName);

	/**
	 * Bulk set field(s) and their value(s)
	 *
	 * Bulk set for field(s) and their value(s) using array format. Array should be key value pair, the key will be used as field name.
	 * IMPORTANT: This will always reset previous set unlike SELECT which able to preserve previous field(s)
	 *
	 * @param array $values
	 *
	 * @return IInsert
	 */
	public function values($values);

	/**
	 * Add a field and value to be inserted
	 *
	 * @param string     $field
	 * @param string|int $value
	 *
	 * @return IInsert
	 */
	public function value($field, $value);

	/**
	 * Generate INSERT INTO using SELECT statement
	 *
	 * This method tell that data source will be use another SELECT statement. Therefore any values from values() and value() will be ignored.
	 * IMPORTANT: The select statement should have same number of field with the $fields parameter
	 *
	 * @param string[] $fields
	 * @param ISelect  $select
	 *
	 * @return IInsert
	 */
	public function fromTable($fields, ISelect $select);
}

// End of File: IInsert.php 