<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Postgre;

use Lh\Db\IPlatform;

class PostgrePlatform implements IPlatform {
	/** @var bool Should we use pg_escape_identifier() when it's available */
	private static $useIdentifierEscape;
	/** @var bool Should we use pg_escape_literal() instead of pg_escape_string() */
	private static $useLiteralEscape;
	/** @var int Counter for parameter name. It's required since Postgre SQL use index based parameter */
	private static $paramCounter = 0;

	/** @var PostgreAdapter Adapter used to get native connector */
	private $adapter;
	/** @var resource pgsql resource handle */
	private $nativeDriver;

	/**
	 * Create new instance of MsSqlPlatform
	 *
	 * @param PostgreAdapter $adapter
	 */
	public function __construct(PostgreAdapter $adapter) {
		$this->adapter = $adapter;
		$this->nativeDriver = $adapter->getNativeConnector();

		if (PostgrePlatform::$useIdentifierEscape === null) {
			PostgrePlatform::$useIdentifierEscape = function_exists("pg_escape_identifier");
		}
		if (PostgrePlatform::$useLiteralEscape === null) {
			PostgrePlatform::$useLiteralEscape = function_exists("pg_escape_literal");
		}
	}

	/**
	 * Get adapter which this platform created
	 *
	 * @return PostgreAdapter
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
		return '"';
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
		return IPlatform::PARAMETER_INDEX_BASED;
	}

	/**
	 * Quote an identifier
	 *
	 * @param string $identifier
	 *
	 * @return string
	 */
	public function quoteIdentifier($identifier) {
		if (PostgrePlatform::$useIdentifierEscape) {
			return pg_escape_identifier($this->nativeDriver, $identifier);
		} else {
			return '"' . str_replace('"', '\\' . '"', $identifier) . '"';
		}
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
		// regex taken from Zend Framework 2 Zend\Db\Adapter\Platform\Postgresql class
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
					if (PostgrePlatform::$useIdentifierEscape) {
						$parts[$i] = pg_escape_identifier($this->nativeDriver, $part);
					} else {
						$part[$i] = '"' . str_replace('"', '\\' . '"', $part) . '"';
					}
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
		if (PostgrePlatform::$useLiteralEscape) {
			return pg_escape_literal($value);
		} else {
			return "'" . pg_escape_string($value) . "'";
		}
	}

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
	public function formatParameterName($name) {
		PostgrePlatform::$paramCounter++;

		return '$' . PostgrePlatform::$paramCounter;
	}
}

// End of File: PostgrePlatform.php 
