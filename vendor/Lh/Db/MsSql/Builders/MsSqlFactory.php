<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MsSql\Builders;

use Lh\Db\Builders\IFactory;
use Lh\Db\IAdapter;

/**
 * Class MsSqlFactory
 *
 * This factory or builder will return classes which represent SQL statement for SQL Server instance.
 *
 * @package Lh\Db\MsSql\Builders
 */
class MsSqlFactory implements IFactory {
	/** @var IAdapter This adapter will passed to ISql instance */
	private $adapter;

	/**
	 * Create new instance of MsSqlFactory
	 *
	 * @param IAdapter $adapter
	 */
	public function __construct(IAdapter $adapter) {
		$this->adapter = $adapter;
	}

	/**
	 * Create object represent SELECT statement
	 *
	 * @param string $columns
	 *
	 * @return MsSqlSelect
	 */
	public function select($columns = null) {
		return new MsSqlSelect($columns, $this->adapter);
	}

	/**
	 * Create object represent INSERT INTO statement
	 *
	 * @param string $tableName
	 *
	 * @return MsSqlInsert
	 */
	public function insert($tableName = null) {
		return new MsSqlInsert($tableName, $this->adapter);
	}

	/**
	 * Create object represent UPDATE statement
	 *
	 * @param string $tableName
	 *
	 * @return MsSqlUpdate
	 */
	public function update($tableName = null) {
		return new MsSqlUpdate($tableName, $this->adapter);
	}

	/**
	 * Create object represent DELETE statement
	 *
	 * @param string $tableName
	 *
	 * @return MsSqlDelete
	 */
	public function delete($tableName = null) {
		return new MsSqlDelete($tableName, $this->adapter);
	}
}

// End of File: MsSqlFactory.php 
