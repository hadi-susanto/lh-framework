<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Postgre\Builders;

use Lh\Db\Builders\IFactory;
use Lh\Db\IAdapter;

/**
 * Class PostgreFactory
 *
 * @package Lh\Db\Postgre\Builders
 */
class PostgreFactory implements IFactory {
	/** @var IAdapter Adapter used for ISql object instantiation */
	private $adapter;

	/**
	 * Create new instance of PostgreFactory
	 *
	 * @param IAdapter $adapter
	 */
	public function __construct(IAdapter $adapter) {
		$this->adapter = $adapter;
	}


	/**
	 * Create object represent SELECT statement
	 *
	 * @param string|string[]|\Lh\Db\Builders\ILiteral|\Lh\Db\Builders\ILiteral[] $columns
	 *
	 * @return PostgreSelect
	 */
	public function select($columns = null) {
		return new PostgreSelect($columns, $this->adapter);
	}

	/**
	 * Create object represent INSERT INTO statement
	 *
	 * @param string $tableName
	 *
	 * @return PostgreInsert
	 */
	public function insert($tableName = null) {
		return new PostgreInsert($tableName, $this->adapter);
	}

	/**
	 * Create object represent UPDATE statement
	 *
	 * @param string $tableName
	 *
	 * @return PostgreUpdate
	 */
	public function update($tableName = null) {
		return new PostgreUpdate($tableName, $this->adapter);
	}

	/**
	 * Create object represent DELETE statement
	 *
	 * @param string $tableName
	 *
	 * @return PostgreDelete
	 */
	public function delete($tableName = null) {
		return new PostgreDelete($tableName, $this->adapter);
	}
}

// End of File: PostgreFactory.php 
