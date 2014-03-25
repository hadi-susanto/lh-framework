<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh;

use Lh\Auth\AuthenticationManager;
use Lh\Db\DbManager;
use Lh\Exceptions\ErrorManager;
use Lh\Loader\LoaderManager;
use Lh\Session\SessionManager;
use Lh\Web\Router;

/**
 * Class ServiceLocator
 *
 * This class will store all service instance class. Although each class is not Singleton, we applying singleton instance for these.
 * ServiceLocator instance can be accessed from ApplicationBase instance. Currently service locator unable to provide user service class,
 * but these restriction will be removed in later version of framework
 *
 * @see ApplicationBase
 *
 * @package Lh
 */
class ServiceLocator {
	/** @var LoaderManager Auto loader manager */
	private $loaderManager;
	/** @var DbManager Adapter manager */
	private $dbManager;
	/** @var ErrorManager Error handler manager */
	private $errorManager;
	/** @var SessionManager Session handler manager */
	private $sessionManager;
	/** @var AuthenticationManager Authentication manager */
	private $authManager;
	/** @var Router Used to determine user request */
	private $router;

	/**
	 * Set LoaderManager instance
	 *
	 * @param \Lh\Loader\LoaderManager $loaderManager
	 */
	public function setLoaderManager(LoaderManager $loaderManager = null) {
		$this->loaderManager = $loaderManager;
	}

	/**
	 * Get LoaderManager instance
	 *
	 * @return \Lh\Loader\LoaderManager
	 */
	public function getLoaderManager() {
		return $this->loaderManager;
	}

	/**
	 * Set DbManager instance
	 *
	 * @param \Lh\Db\DbManager $dbManager
	 */
	public function setDbManager(DbManager $dbManager) {
		$this->dbManager = $dbManager;
	}

	/**
	 * Get DbManager instance
	 *
	 * @return \Lh\Db\DbManager
	 */
	public function getDbManager() {
		return $this->dbManager;
	}

	/**
	 * Set ErrorManager instance
	 *
	 * @param \Lh\Exceptions\ErrorManager $errorManager
	 */
	public function setErrorManager(ErrorManager $errorManager = null) {
		$this->errorManager = $errorManager;
	}

	/**
	 * Get ErrorManager instance
	 *
	 * @return \Lh\Exceptions\ErrorManager
	 */
	public function getErrorManager() {
		return $this->errorManager;
	}

	/**
	 * Set SessionManager instance
	 *
	 * @param \Lh\Session\SessionManager $sessionManager
	 */
	public function setSessionManager(SessionManager $sessionManager = null) {
		$this->sessionManager = $sessionManager;
	}

	/**
	 * Get SessionManager instance
	 *
	 * @return \Lh\Session\SessionManager
	 */
	public function getSessionManager() {
		return $this->sessionManager;
	}

	/**
	 * Set AuthenticationManager instance
	 *
	 * @param \Lh\Auth\AuthenticationManager $authManager
	 */
	public function setAuthManager(AuthenticationManager $authManager) {
		$this->authManager = $authManager;
	}

	/**
	 * Get AuthenticationManager instance
	 *
	 * @return \Lh\Auth\AuthenticationManager
	 */
	public function getAuthenticationManager() {
		return $this->authManager;
	}

	/**
	 * Set Router instance
	 *
	 * @param \Lh\Web\Router $router
	 */
	public function setRouter(Router $router) {
		$this->router = $router;
	}

	/**
	 * Get Router instance
	 *
	 * @return \Lh\Web\Router
	 */
	public function getRouter() {
		return $this->router;
	}
}

// End of File: ServiceLocator.php