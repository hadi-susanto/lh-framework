<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySql\Pdo;

use Lh\Db\Pdo\PdoPlatformBase;

/**
 * Class MySqlPdoPlatform
 *
 * @package Lh\Db\MySql\Pdo
 */
class MySqlPdoPlatform extends PdoPlatformBase {
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
}

// End of File: MySqlPdoPlatform.php 