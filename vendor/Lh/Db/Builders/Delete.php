<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Builders;

use Lh\Collections\Dictionary;
use Lh\Db\DbException;
use Lh\Db\IAdapter;

/**
 * Class Delete
 *
 * Represent DELETE SQL statement
 *
 * @package Lh\Db\Builders
 */
class Delete implements IDelete {
	/** @var \Lh\Db\IAdapter Used to retrieve platform object */
	protected $adapter;
	/** @var \Lh\Db\IPlatform Used while compiling query */
	protected $platform;
	/** @var string Table name */
	protected $tableName;
	/** @var Where[] Where clauses */
	protected $wheres = array();

	/**
	 * Create Delete Statement
	 *
	 * @param string   $tableName
	 * @param IAdapter $adapter
	 */
	public function __construct($tableName = null, IAdapter $adapter = null) {
		if ($tableName !== null) {
			$this->from($tableName);
		}
		if ($adapter !== null) {
			$this->setAdapter($adapter);
		}
	}

	/**
	 * Set Adapter and their Platform
	 *
	 * @param IAdapter $adapter
	 *
	 * @return void
	 */
	public function setAdapter(IAdapter $adapter) {
		$this->adapter = $adapter;
		$this->platform = $adapter->getPlatform();
	}

	/**
	 * Get associated adapter
	 *
	 * @return IAdapter
	 */
	public function getAdapter() {
		return $this->adapter;
	}

	/**
	 * Compile SQL Query
	 *
	 * Compile SQL query as string without parameter. Any value will be compiled directly in the resulting query
	 *
	 * @throws \Lh\Db\DbException
	 * @return string
	 */
	public function compile() {
		if ($this->adapter === null) {
			throw new DbException("Compiling SQL query require adapter support. No adapter have been specified.");
		}
		if (empty($this->tableName)) {
			throw new DbException("There is no table in DELETE statement.");
		}

		return $this->compileWithParameters(null, false);
	}

	/**
	 * Compile SQL Query
	 *
	 * Compile SQL query using parameter helper. Resulting query will have parameter place holder and the parameter will stored at $parameterContainer.
	 *
	 * @param Dictionary $parameterContainer
	 * @param bool       $resetContainer
	 *
	 * @see IStatement::execute()
	 *
	 * @throws \Lh\Db\DbException
	 * @return string
	 */
	public function compileWithParameters(Dictionary $parameterContainer = null, $resetContainer = true) {
		if ($this->adapter === null) {
			throw new DbException("Compiling SQL query require adapter support. No adapter have been specified.");
		}
		if (empty($this->tableName)) {
			throw new DbException("There is no table in DELETE statement.");
		}

		if ($resetContainer && $parameterContainer !== null) {
			$parameterContainer->clear();
		}

		$fragments = array();
		$fragments[] = "DELETE FROM " . $this->platform->quoteIdentifier($this->tableName);

		if (count($this->wheres) > 0) {
			// First where will be always appended
			$fragments[] = "WHERE " . $this->wheres[0]->toString($parameterContainer);
			$max = count($this->wheres);
			for ($i = 1; $i < $max; $i++) {
				$fragments[] = "AND " . $this->wheres[$i]->toString($parameterContainer);
			}
			unset($max, $i);
		}

		return implode(" ", $fragments);
	}


	/**
	 * Set table name which DELETE statement executed
	 *
	 * @param string $tableName
	 *
	 * @return IDelete
	 */
	public function from($tableName) {
		$this->tableName = $tableName;

		return $this;
	}

	/**
	 * Add a WHERE clause to the DELETE statement
	 *
	 * @param string|ILiteral               $field
	 * @param array|int|ISelect|null|string $value
	 * @param string                        $operator
	 *
	 * @return IDelete
	 * @throws \InvalidArgumentException
	 */
	public function where($field, $value, $operator = '=') {
		if (empty($field)) {
			throw new \InvalidArgumentException("Field can't be empty for WHERE clause");
		}
		$this->wheres[] = new Where($this->platform, $field, $value, $operator);

		// Allow chaining
		return $this;
	}
}

// End of File: Delete.php