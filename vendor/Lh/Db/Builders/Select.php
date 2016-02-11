<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Builders;

use Lh\Collections\Dictionary;
use Lh\Db\IAdapter;
use Lh\Db\DbException;

/**
 * Class Select
 *
 * Represent SELECT statement
 *
 * @package Lh\Db\Builders
 */
class Select implements ISelect {
	/** @var \Lh\Db\IAdapter Used to retrieve platform object */
	protected $adapter;
	/** @var \Lh\Db\IPlatform Used while compiling query */
	protected $platform;
	/** @var bool DISTINCT keyword flag */
	protected $distinct = false;
	/** @var array Store field(s) to be retrieved */
	protected $fields = array();
	/** @var string Table name */
	protected $table = null;
	/** @var string Table alias name */
	protected $alias = null;
	/** @var Join[] Join clauses */
	protected $joins = array();
	/** @var Where[] Where clauses */
	protected $wheres = array();
	/** @var string[] Group clauses */
	protected $groups = array();
	/** @var Having[] Having clauses */
	protected $having = array();
	/** @var string[] Order by clauses */
	protected $orders = array();
	/** @var int Limit clause */
	protected $limit = -1;
	/** @var int Offset clause */
	protected $offset = -1;
	/** @var ISelect[] Table(s) for UNION query */
	protected $unions = array();

	/**
	 * Create new instance of SELECT statement
	 *
	 * @param null|string|string[] $columns
	 * @param IAdapter             $adapter
	 */
	public function __construct($columns = null, IAdapter $adapter = null) {
		if ($adapter !== null) {
			$this->setAdapter($adapter);
		}
		if ($columns !== null) {
			$this->columns($columns);
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
	 * Compile SQL query as string without parameter. Any value will be compiled directly in the resulting query.
	 *
	 * @throws \Lh\Db\DbException
	 * @return string
	 */
	public function compile() {
		if ($this->adapter === null) {
			throw new DbException("Compiling SQL query require adapter support. No adapter have been specified.");
		}
		if ($this->table === null) {
			throw new DbException("FROM clause is missing, unable to build SELECT query. Please call Select::from() before compiling SQL.");
		}

		return $this->compileWithParameters(null, false);
	}

	/**
	 * Reset all columns definition to empty array.
	 *
	 * @return ISelect
	 */
	public function resetColumns() {
		$this->fields = array();

		return $this;
	}

	/**
	 * Reset DISTINCT keyword flag
	 *
	 * @return ISelect
	 */
	public function resetDistinct() {
		$this->distinct = false;

		return $this;
	}

	/**
	 * Reset table name and aliases to null.
	 *
	 * @return ISelect
	 */
	public function resetFrom() {
		$this->table = null;
		$this->alias = null;

		return $this;
	}

	/**
	 * Reset JOIN keyword. Compiled SQL will not contains JOIN
	 *
	 * @return ISelect
	 */
	public function resetJoin() {
		$this->joins = array();

		return $this;
	}

	/**
	 * Reset WHERE keyword. Compiled SQL will not contains WHERE
	 *
	 * @return ISelect
	 */
	public function resetWhere() {
		$this->wheres = array();

		return $this;
	}

	/**
	 * Reset GROUP BY keyword. Compiled SQL will not contains GROUP BY
	 *
	 * @return ISelect
	 */
	public function resetGroup() {
		$this->groups = array();

		return $this;
	}

	/**
	 * Reset HAVING keyword. Compiled SQL will not contains HAVING
	 *
	 * @return ISelect
	 */
	public function resetHaving() {
		$this->having = array();

		return $this;
	}

	/**
	 * Reset ORDER BY keyword. Compiled SQL will not contains ORDER BY
	 *
	 * @return ISelect
	 */
	public function resetOrder() {
		$this->orders = array();

		return $this;
	}

	/**
	 * Reset LIMIT and OFFSET keyword. Compiled SQL will not contains LIMIT nor OFFSET
	 *
	 * @return ISelect
	 */
	public function resetLimit() {
		$this->limit = $this->offset = -1;

		return $this;
	}

	/**
	 * Reset OFFSET keyword. Compiled SQL will not contains OFFSET
	 *
	 * @return ISelect
	 */
	public function resetOffset() {
		$this->offset = -1;

		return $this;
	}

	/**
	 * Set column(s) / field(s) to be retrieved from a table
	 *
	 * Columns can be string which comma separated or array string
	 *
	 * @param string|string[]|ILiteral|ILiteral[] $columns
	 * @param bool                                $clearExisting Should this new column(s) set replace existing one or append it
	 *
	 * @return ISelect
	 * @throws \InvalidArgumentException
	 */
	public function columns($columns = '*', $clearExisting = true) {
		if (is_string($columns)) {
			$tokens = explode(",", $columns);
			$columns = array();
			foreach ($tokens as $token) {
				$columns[] = trim($token);
			}
		} else if (!is_array($columns)) {
			$columns = array($columns);
		}

		if ($clearExisting) {
			$this->resetColumns();
		}
		if (is_array($columns)) {
			foreach ($columns as $alias => $column) {
				if (is_string($alias)) {
					$this->column($column, $alias);
				} else {
					$this->column($column, null);
				}
			}
		} else {
			throw new \InvalidArgumentException("Invalid argument type for column(s). Expecting string or array only.");
		}

		// Allow chaining
		return $this;
	}

	/**
	 * Compile SQL Query
	 *
	 * Compile SQL query using parameter helper. Resulting query will have parameter place holder and the parameter will stored at $parameterContainer.
	 *
	 * @param Dictionary $parameterContainer
	 * @param bool       $resetContainer
	 *
	 * @return string
	 * @throws \Lh\Db\DbException
	 */
	public function compileWithParameters(Dictionary $parameterContainer = null, $resetContainer = true) {
		if ($this->adapter === null) {
			throw new DbException("Compiling SQL query require adapter support. No adapter have been specified.");
		}
		if ($this->table === null) {
			throw new DbException("FROM clause is missing, unable to build SELECT query. Please call Select::from() before compiling SQL.");
		}

		if ($resetContainer && $parameterContainer !== null) {
			$parameterContainer->clear();
		}

		$fragments = array();
		$buff = array();
		// Basic sql function are allowed without SqlFunction object
		$safeKeywords = array("AVG", "COUNT", "FIRST", "LAST", "MAX", "MIN", "SUM", "(", ")");
		foreach ($this->fields as $alias => $field) {
			if ($field instanceof ILiteral) {
				$field = $field->toString($parameterContainer);
			} else {
				$field = $this->platform->quoteIdentifierList($field, $safeKeywords);
			}

			if (is_string($alias)) {
				$buff[] = $field . " AS " . $this->platform->quoteIdentifier($alias);
			} else {
				$buff[] = $field;
			}
		}
		if ($this->distinct) {
			$fragments[] = "SELECT DISTINCT " . implode(", ", $buff);
		} else {
			$fragments[] = "SELECT " . implode(", ", $buff);
		}
		if ($this->table instanceof ISelect) {
			$fragments[] = "FROM (" . $this->table->compileWithParameters($parameterContainer, false) . ")";
		} else {
			$fragments[] = "FROM " . $this->platform->quoteIdentifier($this->table);
		}
		if ($this->alias !== null) {
			$fragments[] = "AS " . $this->platform->quoteIdentifier($this->alias);
		}

		if (count($this->joins) > 0) {
			foreach ($this->joins as $join) {
				$fragments[] = $join->toString();
			}
			unset($join);
		}

		if (count($this->wheres) > 0) {
			// First where will be always appended
			$fragments[] = "WHERE " . $this->wheres[0]->toString($parameterContainer);
			$max = count($this->wheres);
			for ($i = 1; $i < $max; $i++) {
				$fragments[] = "AND " . $this->wheres[$i]->toString($parameterContainer);
			}
			unset($max, $i);
		}

		if (count($this->groups) > 0) {
			$buff = array();
			foreach ($this->groups as $group) {
				$buff[] = $this->platform->quoteIdentifierList($group);
			}
			$fragments[] = "GROUP BY " . implode(", ", $buff);
			unset($buff, $group);
		}

		if (count($this->having) > 0) {
			// First having will be always appended
			$fragments[] = "HAVING " . $this->having[0]->toString($parameterContainer);
			$max = count($this->having);
			for ($i = 1; $i < $max; $i++) {
				$fragments[] = "AND " . $this->having[$i]->toString($parameterContainer);
			}
			unset($max, $i);
		}

		if (count($this->orders) > 0) {
			$buff = array();
			foreach ($this->orders as $order) {
				if ($order["type"] == ISelect::ORDER_DESC) {
					$buff[] = $this->platform->quoteIdentifierList($order["field"]) . " DESC";
				} else {
					$buff[] = $this->platform->quoteIdentifierList($order["field"]) . " ASC";
				}
			}
			$fragments[] = "ORDER BY " . implode(", ", $buff);
			unset($buff, $order);
		}

		if ($this->limit != -1 && is_int($this->limit)) {
			$fragments[] = "LIMIT " . $this->limit;
		}
		if ($this->offset != -1 && is_int($this->offset)) {
			$fragments[] = "OFFSET " . $this->offset;
		}

		foreach ($this->unions as $union) {
			$fragments[] = "UNION " . $union->compileWithParameters($parameterContainer);
		}

		return implode(" ", $fragments);
	}

	/**
	 * Tell current SELECT to use DISTINCT keyword
	 *
	 * @param bool $flag
	 *
	 * @return ISelect
	 */
	public function distinct($flag = true) {
		$this->distinct = $flag;

		return $this;
	}

	/**
	 * Add single column / field
	 *
	 * Add a column / field which will be retrieved using current select statement
	 *
	 * @param string|ILiteral $field
	 * @param null|string     $alias
	 *
	 * @return ISelect
	 * @throws \InvalidArgumentException
	 */
	public function column($field, $alias = null) {
		if (!is_string($field) && !($field instanceof ILiteral)) {
			throw new \InvalidArgumentException("Select::column() only accept string or ILiteral object as field");
		}
		if ($alias !== null && !is_string($alias)) {
			throw new \InvalidArgumentException("Alias for column should be string");
		}

		if ($alias === null) {
			$this->fields[] = $field;
		} else {
			$this->fields[$alias] = $field;
		}

		return $this;
	}


	/**
	 * Count total field(s) in current SELECT
	 *
	 * Count total field(s) to be retrieved from current statement. IMPORTANT: '*' wildcard will be counted as one field!
	 * Currently there is no way to count '*' wildcard character.
	 *
	 * @return int
	 */
	public function columnCount() {
		return count($this->fields);
	}

	/**
	 * Select source data
	 *
	 * Tell which data source to be used with select statement. Data source usually a table name but in rare case you able to give another
	 * instance of ISelect to perform sub-query.
	 *
	 * @param string|ISelect $table
	 * @param null|string    $alias
	 *
	 * @return $this|ISelect
	 * @throws \InvalidArgumentException
	 */
	public function from($table, $alias = null) {
		if ($table instanceof ISelect && empty($alias)) {
			throw new \InvalidArgumentException("Alias must be specified when table is an instance of ISelect");
		} else if (empty($alias)) {
			$alias = null;
		}

		$this->table = $table;
		$this->alias = $alias;

		// Allow chaining
		return $this;
	}

	/**
	 * Inner Join with another data source
	 *
	 * Similar to from() method, joining can be performed against table name or another instance of ISelect (useful for sub-query).
	 * Implementation of this join should using INNER JOIN statement or equivalent
	 *
	 * @param string|ISelect $table
	 * @param string         $condition
	 * @param null|string    $alias Alias required if $table is an instance of ISelect
	 *
	 * @throws \InvalidArgumentException
	 * @return ISelect
	 */
	public function join($table, $condition, $alias = null) {
		if (!is_string($table) && !($table instanceof ISelect)) {
			throw new \InvalidArgumentException("Parameter table only accept either string or ISelect instance.");
		}
		if (($table instanceof ISelect) && empty($alias)) {
			throw new \InvalidArgumentException("Table alias is required since instance of ISelect given as table");
		}
		$this->joins[] = new Join($this->platform, ISelect::JOIN_INNER, $table, $alias, $condition);

		// Allow chaining
		return $this;
	}

	/**
	 * Left Join with another data source
	 *
	 * Similar to from() method, joining can be performed against table name or another instance of ISelect (useful for sub-query).
	 * Implementation of this join should using LEFT JOIN statement or equivalent
	 *
	 * @param string|ISelect $table
	 * @param string         $condition
	 * @param null|string    $alias Alias required if $table is an instance of ISelect
	 *
	 * @throws \InvalidArgumentException
	 * @return ISelect
	 */
	public function leftJoin($table, $condition, $alias = null) {
		if (!is_string($table) && !($table instanceof ISelect)) {
			throw new \InvalidArgumentException("Parameter table only accept either string or ISelect instance.");
		}
		if (($table instanceof ISelect) && empty($alias)) {
			throw new \InvalidArgumentException("Table alias is required since instance of ISelect given as table");
		}
		$this->joins[] = new Join($this->platform, ISelect::JOIN_LEFT, $table, $alias, $condition);

		// Allow chaining
		return $this;
	}

	/**
	 * Right Join with another data source
	 *
	 * Similar to from() method, joining can be performed against table name or another instance of ISelect (useful for sub-query).
	 * Implementation of this join should using RIGHT JOIN statement or equivalent
	 *
	 * @param string|ISelect $table
	 * @param string         $condition
	 * @param null|string    $alias Alias required if $table is an instance of ISelect
	 *
	 * @throws \InvalidArgumentException
	 * @return ISelect
	 */
	public function rightJoin($table, $condition, $alias = null) {
		if (!is_string($table) && !($table instanceof ISelect)) {
			throw new \InvalidArgumentException("Parameter table only accept either string or ISelect instance.");
		}
		if (($table instanceof ISelect) && empty($alias)) {
			throw new \InvalidArgumentException("Table alias is required since instance of ISelect given as table");
		}
		$this->joins[] = new Join($this->platform, ISelect::JOIN_RIGHT, $table, $alias, $condition);

		// Allow chaining
		return $this;
	}

	/**
	 * Full Join with another data source
	 *
	 * Similar to from() method, joining can be performed against table name or another instance of ISelect (useful for sub-query).
	 * Implementation of this join should using FULL JOIN statement or equivalent
	 *
	 * @param string|ISelect $table
	 * @param string         $condition
	 * @param null|string    $alias Alias required if $table is an instance of ISelect
	 *
	 * @throws \InvalidArgumentException
	 * @return ISelect
	 */
	public function fullJoin($table, $condition, $alias = null) {
		if (!is_string($table) && !($table instanceof ISelect)) {
			throw new \InvalidArgumentException("Parameter table only accept either string or ISelect instance.");
		}
		if (($table instanceof ISelect) && empty($alias)) {
			throw new \InvalidArgumentException("Table alias is required since instance of ISelect given as table");
		}
		$this->joins[] = new Join($this->platform, ISelect::JOIN_FULL, $table, $alias, $condition);

		// Allow chaining
		return $this;
	}

	/**
	 * Cross Join with another data source
	 *
	 * Similar to from() method, joining can be performed against table name or another instance of ISelect (useful for sub-query).
	 * Implementation of this join should using CROSS JOIN statement or equivalent
	 *
	 * @param string|ISelect $table
	 * @param string         $condition
	 * @param null|string    $alias Alias required if $table is an instance of ISelect
	 *
	 * @throws \InvalidArgumentException
	 * @return ISelect
	 */
	public function crossJoin($table, $condition, $alias = null) {
		if (!is_string($table) && !($table instanceof ISelect)) {
			throw new \InvalidArgumentException("Parameter table only accept either string or ISelect instance.");
		}
		if (($table instanceof ISelect) && empty($alias)) {
			throw new \InvalidArgumentException("Table alias is required since instance of ISelect given as table");
		}
		$this->joins[] = new Join($this->platform, ISelect::JOIN_CROSS, $table, $alias, $condition);

		// Allow chaining
		return $this;
	}

	/**
	 * Add a WHERE clause to the SELECT statement
	 *
	 * @param string                        $field
	 * @param array|int|ISelect|null|string $value
	 * @param string                        $operator
	 *
	 * @return ISelect
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

	/**
	 * Add a GROUP BY clause to the SELECT statement
	 *
	 * @param string|string[] $group
	 *
	 * @return $this|ISelect
	 */
	public function groupBy($group) {
		if (is_array($group)) {
			foreach ($group as $value) {
				$this->groupBy($value);
			}
		} else {
			if (($pos = strpos($group, ",")) !== false) {
				$tokens = explode(",", $group);
				foreach ($tokens as $token) {
					$this->groups[] = trim($token);
				}
			} else {
				$this->groups[] = trim($group);
			}
		}

		// Allow chaining
		return $this;
	}

	/**
	 * Add a HAVING clause to the SELECT statement
	 *
	 * @param string                $field
	 * @param array|int|null|string $value
	 * @param string                $operator
	 *
	 * @return ISelect
	 * @throws \InvalidArgumentException
	 */
	public function having($field, $value, $operator = '=') {
		if (empty($field)) {
			throw new \InvalidArgumentException("Field can't be empty for HAVING clause");
		}
		$this->having[] = new Having($this->platform, $field, $value, $operator);

		// Allow chaining
		return $this;
	}

	/**
	 * Add a ORDER BY clause to the SELECT statement
	 *
	 * @param string|string[] $order
	 * @param string          $type
	 *
	 * @return ISelect
	 */
	public function orderBy($order, $type = ISelect::ORDER_ASC) {
		if (is_array($order)) {
			foreach ($order as $value) {
				$this->orderBy($value, $type);
			}
		} else {
			if (($pos = strpos($order, ",")) !== false) {
				$tokens = explode(",", $order);
				foreach ($tokens as $token) {
					$this->orders[] = array("field" => trim($token), "type" => $type);
				}
			} else {
				$this->orders[] = array("field" => trim($order), "type" => $type);
			}
		}

		// Allow chaining
		return $this;
	}

	/**
	 * Add a LIMIT and OFFSET clause to the SELECT statement
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return ISelect
	 * @throws \InvalidArgumentException
	 */
	public function limit($limit, $offset = 0) {
		if ($limit < 0) {
			throw new \InvalidArgumentException("LIMIT should be greater or equals to zero");
		}
		if ($offset < 0) {
			throw new \InvalidArgumentException("OFFSET should be greater or equals to zero");
		}

		$this->limit = (int)$limit;
		$this->offset = ($offset > 0) ? $offset : -1;

		// Allow chaining
		return $this;
	}

	/**
	 * Add a OFFSET clause to the SELECT statement
	 *
	 * @param int $offset
	 *
	 * @throws \InvalidArgumentException
	 * @return ISelect
	 */
	public function offset($offset) {
		if ($offset < 0) {
			throw new \InvalidArgumentException("OFFSET should be greater or equals to zero");
		}

		$this->offset = $offset;

		// Allow chaining
		return $this;
	}

	/**
	 * Perform UNION with other SELECT statement
	 *
	 * @param ISelect $select
	 *
	 * @throws \Lh\Db\DbException
	 *
	 * @return ISelect
	 */
	public function union(ISelect $select) {
		if ($this->columnCount() != $select->columnCount()) {
			throw new DbException("Unable to perform UNION when no of column is different!");
		}
		$this->unions[] = $select;

		return $this;
	}
}

// End of File: Select.php 