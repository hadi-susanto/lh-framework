<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db;

/**
 * Interface IPlatform
 *
 * Contract for database driver platform. Database platform will tell you about database capabilities and some database specific issues
 *
 * @package Lh\Db
 */
interface IPlatform {
	const PARAMETER_NOT_SUPPORTED = 0;		// Set NOT SUPPORTED value at 0 so it will be evaluated as false.
	const PARAMETER_POSITION_BASED = 1;		// Position based not equal to index where any index in position based will be ignored
	const PARAMETER_NAMED_BASED = 2;
	const PARAMETER_INDEX_BASED = 3;

	/**
	 * Get adapter which this platform created
	 *
	 * @return IAdapter
	 */
	public function getAssociatedAdapter();

	/**
	 * Get symbol used in quoting identifier
	 *
	 * @return string
	 */
	public function getQuoteIdentifierSymbol();

	/**
	 * Get symbol used in quoting value
	 *
	 * @return string
	 */
	public function getQuoteValueSymbol();

	/**
	 * Get how parameter handled in current driver
	 *
	 * Return current driver parameterization type support, either NOT SUPPORTED, INDEX BASED or NAMED BASED
	 * Return value must be taken from IPlatform::PARAMETER_*
	 *
	 * @return int
	 */
	public function getParameterType();

	/**
	 * Quote an identifier
	 *
	 * @param string $identifier
	 *
	 * @return string
	 */
	public function quoteIdentifier($identifier);

	/**
	 * Quote a list of identifiers
	 *
	 * @param string   $identifiers
	 * @param string[] $skipped
	 *
	 * @return string
	 */
	public function quoteIdentifierList($identifiers, array $skipped = null);

	/**
	 * Quote a value
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function quoteValue($value);

	/**
	 * Format parameter name
	 *
	 * Since each driver will handle parameter differently we must able to give proper name to it.
	 * Some database support parameter using name but other only support sequential parameter (without name)
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function formatParameterName($name);
}

// End of File: IPlatform.php 