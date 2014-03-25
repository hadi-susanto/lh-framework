<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Pdo;

use Lh\Db\IPlatform;

/**
 * Class PdoPlatformBase
 *
 * Platform for PDO essentially shared since PDO itself is an abstract implementation. LH Framework extending the abstraction
 * to provide more features. Platform class used to quoting purpose (prevent sql injection)
 *
 * @package Lh\Db\Pdo
 */
abstract class PdoPlatformBase implements IPlatform {
	/** @var PdoAdapterBase Adapter to get native driver object. Quoting support provided by native driver */
	protected $adapter;
	/** @var \PDO Native PDO driver */
	protected $pdo;

	/**
	 * Create new instance of PDO platform
	 *
	 * @param PdoAdapterBase $adapter
	 */
	public function __construct(PdoAdapterBase $adapter) {
		$this->adapter = $adapter;
		$this->pdo = $adapter->getNativeConnector();
	}

	/**
	 * Get adapter which this platform created
	 *
	 * @return \Lh\Db\IAdapter|PdoAdapterBase
	 */
	public function getAssociatedAdapter() {
		return $this->adapter;
	}

	/**
	 * Quote a value
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function quoteValue($value) {
		return $this->pdo->quote($value);
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
		return IPlatform::PARAMETER_NAMED_BASED;
	}


	/**
	 * Format parameter name
	 *
	 * Since each driver will handle parameter differently we must able to give proper name to it.
	 * Some database support parameter using name but other only support sequential parameter (without name)
	 *
	 * @param string $name
	 *
	 * @see IPlatform::getParameterType()
	 *
	 * @return string
	 */
	public function formatParameterName($name) {
		if (strpos($name, ".") !== false) {
			$names = explode(".", $name);

			return ":" . end($names);
		}

		return ":" . $name;
	}


}

// End of File: PdoPlatformBase.php 