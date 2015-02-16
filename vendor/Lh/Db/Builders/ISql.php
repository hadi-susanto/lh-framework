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

/**
 * Interface ISql
 *
 * Define contract for all type of SQL query or fragment
 * ToDo: ALTER, CREATE statement
 *
 * @package Lh\Db
 */
interface ISql {
	const JOIN_INNER = 'INNER';
	const JOIN_LEFT = 'LEFT';
	const JOIN_RIGHT = 'RIGHT';
	const JOIN_FULL = 'FULL';
	const JOIN_CROSS = 'CROSS';

	/**
	 * Set Adapter and their Platform
	 *
	 * @param IAdapter $adapter
	 *
	 * @return void
	 */
	public function setAdapter(IAdapter $adapter);

	/**
	 * Get associated adapter
	 *
	 * @return IAdapter
	 */
	public function getAdapter();

	/**
	 * Compile SQL Query
	 *
	 * Compile SQL query as string without parameter. Any value will be compiled directly in the resulting query.
	 *
	 * @return string
	 */
	public function compile();

	/**
	 * Compile SQL Query
	 *
	 * Compile SQL query using parameter helper. Resulting query will have parameter place holder and the parameter will stored at $parameterContainer.
	 *
	 * @see IStatement::execute()
	 *
	 * @param Dictionary $parameterContainer
	 * @param bool       $resetContainer
	 *
	 * @return string
	 */
	public function compileWithParameters(Dictionary $parameterContainer, $resetContainer = true);
}

// End of File: ISql.php