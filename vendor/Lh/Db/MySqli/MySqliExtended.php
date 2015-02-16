<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\MySqli;

use mysqli;

/**
 * Class MySqliExtended
 *
 * This class provided for init command support in mysqli. To achieve init command in mysqli there is two way: procedural and object oriented.
 * Extending mysqli class is required since these framework trying to implements full Object Oriented style. This approach used to prevent call of mysqli_init() which is procedural style.
 * Although mysqli_init() will return mysqli object but this is not a static method. We trying to apply all in object oriented style
 *
 * @package Lh\Db\MySqli
 */
class MySqliExtended extends mysqli {
	/** @var array mysqli init options */
	protected $initOptions;

	/**
	 * Create new instance of MySqliExtended
	 *
	 * @param string $host
	 * @param string $username
	 * @param string $password
	 * @param string $dbName
	 * @param int    $port
	 * @param string $socket
	 * @param array  $initOptions
	 *
	 * @throws MySqliException
	 */
	public function __construct($host, $username, $password, $dbName, $port, $socket, $initOptions) {
		parent::init();

		$this->initOptions = $initOptions;
		foreach ($this->initOptions as $key => $value) {
			if (!parent::options($key, $value)) {
				throw new MySqliException("Failed to set init options !");
			}
		}

		parent::real_connect($host, $username, $password, $dbName, $port, $socket);
	}
}

// End of File: MySqliExtended.php 