<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MsSql\Pdo;

use Lh\Db\Pdo\PdoPlatformBase;

/**
 * Class MsSqlPdoPlatform
 *
 * @package Lh\Db\MsSql\Pdo
 */
class MsSqlPdoPlatform extends PdoPlatformBase {
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
}

// End of File: MsSqlPdoPlatform.php 