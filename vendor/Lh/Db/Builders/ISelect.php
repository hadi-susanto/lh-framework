<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Builders;

/**
 * Interface ISelect
 *
 * Contract for build SELECT statement
 *
 * @package Lh\Db\Builders
 */
interface ISelect extends ISql {
	const ORDER_ASC = 'ASC';
	const ORDER_DESC = 'DESC';

	/**
	 * Count total field(s) in current SELECT
	 *
	 * Count total field(s) to be retrieved from current statement. IMPORTANT: '*' wildcard will be counted as one field!
	 * Currently there is no way to count '*' wildcard character.
	 *
	 * @return int
	 */
	public function columnCount();

	/**
	 * Set column(s) / field(s) to be retrieved from a table
	 *
	 * Columns can be string which comma separated or array string
	 *
	 * @param string|string[]|ILiteral|ILiteral[] $columns
	 * @param bool                                $clearExisting Should this new column(s) set replace existing one or append it
	 *
	 * @return ISelect
	 */
	public function columns($columns = '*', $clearExisting = true);

	/**
	 * Add single column / field
	 *
	 * Add a column / field which will be retrieved using current select statement
	 *
	 * @param string|ILiteral $field
	 * @param null|string     $alias
	 *
	 * @return ISelect
	 */
	public function column($field, $alias = null);

	/**
	 * Select source data
	 *
	 * Tell which data source to be used with select statement. Data source usually a table name but in rare case you able to give another
	 * instance of ISelect to perform sub-query
	 *
	 * @param string|ISelect $table
	 * @param null|string    $alias
	 *
	 * @return ISelect
	 */
	public function from($table, $alias = null);

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
	 * @return ISelect
	 */
	public function join($table, $condition, $alias = null);

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
	 * @return ISelect
	 */
	public function leftJoin($table, $condition, $alias = null);

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
	 * @return ISelect
	 */
	public function rightJoin($table, $condition, $alias = null);

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
	 * @return ISelect
	 */
	public function fullJoin($table, $condition, $alias = null);

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
	 * @return ISelect
	 */
	public function crossJoin($table, $condition, $alias = null);

	/**
	 * Add a WHERE clause to the SELECT statement
	 *
	 * @param string                        $field
	 * @param null|string|int|array|ISelect $value
	 * @param string                        $operator
	 *
	 * @return ISelect
	 */
	public function where($field, $value, $operator = '=');

	/**
	 * Add a GROUP BY clause to the SELECT statement
	 *
	 * @param string|string[] $group
	 *
	 * @return ISelect
	 */
	public function groupBy($group);

	/**
	 * Add a HAVING clause to the SELECT statement
	 *
	 * @param string                $field
	 * @param null|string|int|array $value
	 * @param string                $operator
	 *
	 * @return ISelect
	 */
	public function having($field, $value, $operator = '=');

	/**
	 * Add a ORDER BY clause to the SELECT statement
	 *
	 * @param string|string[] $order
	 * @param string      $type
	 *
	 * @return ISelect
	 */
	public function orderBy($order, $type = ISelect::ORDER_ASC);

	/**
	 * Add a LIMIT and OFFSET clause to the SELECT statement
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return ISelect
	 */
	public function limit($limit, $offset = 0);

	/**
	 * Add a OFFSET clause to the SELECT statement
	 *
	 * @param int $offset
	 *
	 * @return ISelect
	 */
	public function offset($offset);

	/**
	 * Perform UNION with other SELECT statement
	 *
	 * @param ISelect $select
	 *
	 * @return ISelect
	 */
	public function union(ISelect $select);
}

// End of File: ISelect.php 