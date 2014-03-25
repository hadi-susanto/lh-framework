<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySql\Builders;

use Lh\Db\Builders\Select;
use Lh\Db\MySql\MySqlException;

/**
 * Class MySqlSelect
 *
 * Unfortunately MySQL don't support FULL join.
 *
 * @package Lh\Db\MySql\Builders
 */
class MySqlSelect extends Select {

	/**
	 * FULL JOIN for MySQL is not supported yet
	 *
	 * Until this driver coded, MySQL still don't have support for FULL JOIN
	 * ToDo: Workaround for FULL JOIN using UNION between LEFT JOIN and RIGHT JOIN
	 *
	 * @param \Lh\Db\Builders\ISelect|string $tableName
	 * @param string                         $condition
	 * @param null                           $alias
	 *
	 * @return \Lh\Db\Builders\ISelect|void
	 * @throws \Lh\Db\MySql\MySqlException
	 */
	public function fullJoin($tableName, $condition, $alias = null) {
		throw new MySqlException("MySQL doesn't support FULL JOIN");
	}
}

// End of File: MySqlSelect.php 