<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySql;

use Lh\Db\IPlatform;

/**
 * Class MySqlPlatform
 *
 * @package Lh\Db\MySql
 */
class MySqlPlatform implements IPlatform {
	/** @var MySqlAdapter Adapter used to get native connector */
	private $adapter;
	/** @var resource Used in quoting */
	private $nativeConnector;

	/**
	 * Create new instance of MySqlPlatform
	 *
	 * @param MySqlAdapter $adapter
	 */
	public function __construct(MySqlAdapter $adapter) {
		$this->adapter = $adapter;
		$this->nativeConnector = $adapter->getNativeConnector();
	}

	/**
	 * Get associated adapter
	 *
	 * @return MySqlAdapter
	 */
	public function getAssociatedAdapter() {
		return $this->adapter;
	}

	/**
	 * Get symbol used in quoting identifier
	 *
	 * @return string
	 */
	public function getQuoteIdentifierSymbol() {
		return '`';
	}

	/**
	 * Get symbol used in quoting value
	 *
	 * @return string
	 */
	public function getQuoteValueSymbol() {
		return '\'';
	}

	/**
	 * Quote an identifier
	 *
	 * @param string $identifier
	 *
	 * @return string
	 */
	public function quoteIdentifier($identifier) {
		return '`' . str_replace('`', '``', $identifier) . '`';
	}

	/**
	 * Get how parameter handled in current driver
	 *
	 * Return current driver parameterization type support, either NOT SUPPORTED, INDEX BASED or NAMED BASED
	 * Return value must be taken from IPlatform::PARAMETER_*
	 *
	 * @return int
	 */
	public function getParameterType() {
		return IPlatform::PARAMETER_NOT_SUPPORTED;
	}

	/**
	 * Quote a list of identifiers
	 *
	 * @param string $identifiers
	 * @param array  $skipped
	 *
	 * @return string
	 */
	public function quoteIdentifierList($identifiers, array $skipped = null) {
		// regex taken from @link http://dev.mysql.com/doc/refman/5.0/en/identifiers.html
		$parts = preg_split('#([^0-9,a-z,A-Z$_])#', $identifiers, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		foreach ($parts as $i => $part) {
			if ($skipped !== null && in_array(strtoupper($part), $skipped)) {
				continue;
			}
			switch ($part) {
				case ' ':
				case '.':
				case '*':
				case 'AS':
				case 'As':
				case 'aS':
				case 'as':
					break;
				default:
					$parts[$i] = '`' . str_replace('`', '``', $part) . '`';
			}
		}

		return implode('', $parts);
	}

	/**
	 * Quote a value
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function quoteValue($value) {
		return mysql_real_escape_string($value, $this->nativeConnector);
	}

	/**
	 * Format parameter name
	 *
	 * Native PHP MySql don't support parameter
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function formatParameterName($name) {
		return null;
	}
}

// End of File: MySqlPlatform.php 