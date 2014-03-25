<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Builders;

/**
 * Interface IFactory
 *
 * Factory for create DDL statement which specific for each database driver
 *
 * @package Lh\Db\Builders
 */
interface IFactory {
	/**
	 * Create object represent SELECT statement
	 *
	 * @param string $columns
	 *
	 * @return ISelect
	 */
	public function select($columns = null);

	/**
	 * Create object represent INSERT INTO statement
	 *
	 * @param string $tableName
	 *
	 * @return IInsert
	 */
	public function insert($tableName = null);

	/**
	 * Create object represent UPDATE statement
	 *
	 * @param string $tableName
	 *
	 * @return IUpdate
	 */
	public function update($tableName = null);

	/**
	 * Create object represent DELETE statement
	 *
	 * @param string $tableName
	 *
	 * @return IDelete
	 */
	public function delete($tableName = null);
}

// End of File: IFactory.php