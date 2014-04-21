<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MsSql;

use Lh\Db\IPlatform;

/**
 * Class MsSqlPlatform
 *
 * @package Lh\Db\MsSql
 */
class MsSqlPlatform implements IPlatform {
	/** @var MsSqlAdapter Adapter used to get native connector */
	private $adapter;

	/**
	 * Create new instance of MsSqlPlatform
	 *
	 * @param MsSqlAdapter $adapter
	 */
	public function __construct(MsSqlAdapter $adapter) {
		$this->adapter = $adapter;
	}

	/**
	 * Get adapter which this platform created
	 *
	 * @return MsSqlAdapter
	 */
	public function getAssociatedAdapter() {
		return $this->adapter;
	}

	/**
	 * Get symbol used in quoting identifier
	 *
	 * @return string[]
	 */
	public function getQuoteIdentifierSymbol() {
		return array('[', ']');
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
	 * Get how parameter handled in current driver
	 *
	 * Return current driver parameterization type support, either NOT SUPPORTED, INDEX BASED or NAMED BASED
	 * Return value must be taken from IPlatform::PARAMETER_*
	 *
	 * @return int
	 */
	public function getParameterType() {
		return IPlatform::PARAMETER_POSITION_BASED;
	}

	/**
	 * Quote an identifier
	 *
	 * @param string $identifier
	 *
	 * @return string
	 */
	public function quoteIdentifier($identifier) {
		return '[' . $identifier . ']';
	}

	/**
	 * Quote a list of identifiers
	 *
	 * @param string   $identifiers
	 * @param string[] $skipped
	 *
	 * @return string
	 */
	public function quoteIdentifierList($identifiers, array $skipped = null) {
		// regex taken from Zend Framework 2 Zend\Db\Adapter\Platform\SqlServer class
		$parts = preg_split('#([\.\s\W])#', $identifiers, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
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
					$parts[$i] = '[' . $part . ']';
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
		// Don't found any method / function in sqlsrv driver which provide escaping a value.
		return '\'' . str_replace('\'', '\'\'', $value) . '\'';
	}

	/**
	 * Format parameter name
	 *
	 * Since sqlsrv only support position based parameter then it will be use '?' sign as parameter location
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function formatParameterName($name) {
		return "?";
	}
}

// End of File: MsSqlPlatform.php
