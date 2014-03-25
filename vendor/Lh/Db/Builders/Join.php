<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Builders;

use Lh\Collections\Dictionary;
use Lh\Db\DbException;
use Lh\Db\IPlatform;

/**
 * Class Join
 *
 * @package Lh\Db\Builders
 */
class Join implements ILiteral {
	/** @var IPlatform Used while compiling query */
	private $platform;
	/** @var string Join type. Refer to ISql::JOIN_* values */
	private $joinType;
	/** @var string|ISelect Join source */
	private $table;
	/** @var string Alias for table */
	private $alias;
	/** @var string Join condition */
	private $conditions;

	/**
	 * Create new Join clause
	 *
	 * @param IPlatform      $platform
	 * @param string         $joinType
	 * @param string|ISelect $table
	 * @param null|string    $alias
	 * @param null|string    $conditions
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(IPlatform $platform, $joinType, $table, $alias = null, $conditions = null) {
		$joinType = strtoupper($joinType);
		if (!defined("\\Lh\\Db\\Builders\\ISql::JOIN_" . $joinType)) {
			throw new \InvalidArgumentException("Invalid JOIN type, unknown join: " . $joinType);
		}
		if (empty($alias)) {
			if ($table instanceof ISelect) {
				throw new \InvalidArgumentException("Joining table using ISelect require table alias to be specified.");
			}
			$alias = null;
		}

		$this->platform = $platform;
		$this->joinType = $joinType;
		$this->table = $table;
		$this->alias = $alias;
		if ($conditions !== null && !is_array($conditions)) {
			$this->conditions = array($conditions);
		} else {
			$this->conditions = $conditions;
		}
	}

	/**
	 * Build string representation of current JOIN clause
	 *
	 * This method not intended called by user code. It's should be called by other ISql object to create fully functional SQL string.
	 * Passing an instance of Dictionary then it will assuming resulting query must be paramterized.
	 *
	 * @param Dictionary $parameterContainer
	 *
	 * @return string
	 * @throws \Lh\Db\DbException
	 */
	public function toString(Dictionary $parameterContainer = null) {
		$fragments = array();
		$fragments[] = $this->joinType . " JOIN";
		$fragments[] = ($this->table instanceof ISelect) ? "(" . $this->table->compileWithParameters($parameterContainer) . ")" : $this->platform->quoteIdentifier($this->table);
		if ($this->alias !== null) {
			$fragments[] = "AS " . $this->platform->quoteIdentifier($this->alias);
		}
		if ($this->conditions !== null && count($this->conditions) > 0) {
			$idx = 0;
			foreach ($this->conditions as $condition) {
				$prefix = ($idx == 0) ? "ON " : "AND ";
				if (is_string($condition)) {
					$fragments[] = $prefix . $this->platform->quoteIdentifierList($condition, array("="));
				} else if ($condition instanceof ILiteral) {
					$fragments[] = $prefix . $condition->toString($parameterContainer);
				} else {
					throw new DbException("Unable to form JOIN clause from given condition(s). Acceptable condition(s) are: string or ILiteral object (array of them also allowed)");
				}
			}
		}

		return implode(" ", $fragments);
	}

	/**
	 * Provide direct string printing using magic method
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->toString();
	}
}

// End of File: Join.php 