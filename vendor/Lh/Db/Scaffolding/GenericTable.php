<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Db\Scaffolding;

use Lh\Db\IAdapter;
use Lh\Web\Application;

/**
 * Class GenericTable
 *
 * Concrete class for defining a table from database. It's only provide basic CRUD operation. If you want to provide more usage of the class please extend from
 * AbstractTable class instead of this class
 *
 * @package Lh\Db\Scaffolding
 */
class GenericTable extends AbstractTable {
	protected static $serviceLocator;

	/**
	 * Create a generic table
	 *
	 * Create a new generic table based on given table name. If adapter not provided then default adapter will be used.
	 *
	 * @param string        $tableName
	 * @param IAdapter|null $adapter
	 */
	public function __construct($tableName, IAdapter $adapter = null) {
		if ($adapter == null) {
			if (self::$serviceLocator == null) {
				self::$serviceLocator = Application::getInstance()->getServiceLocator();
			}

			$adapter = self::$serviceLocator->getDbManager()->getDefaultAdapter();
		}

		parent::__construct($tableName, $adapter);
	}
}

// End of File: GenericTable.php 