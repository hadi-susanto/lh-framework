<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Session\Handler;

use Lh\Db\IPlatform;
use Lh\Exceptions\InvalidConfigException;
use Lh\Session\ISessionHandler;
use Lh\Web\Application;

/**
 * Class Database
 *
 * Handling session data using database engine. Very useful if your application deployed in server farm.
 * In server farm there is no assurance that same server will handle same browser, Session file will be stored in each computer.
 * Although our file session engine can be configured to store file in network storage but it's not advisable.
 * This session engine will utilize DbManager for storing and retrieving data. Customization options:
 *  - 'adapterName'
 *  - 'tableName'
 *
 * @package Lh\Session\Handler
 */
class Database implements ISessionHandler {
	/** @var \Lh\Db\DbManager Used to retrieve default adapter or custom adapter */
	private $dbManager;
	/** @var \Lh\Db\IAdapter Adapter used in session handling operations */
	private $adapter;
	/** @var \Lh\Db\Builders\IFactory Factory for generating SQL query */
	private $factory;
	/** @var bool Do current adapter is capable using parameter */
	private $parameterCapable = false;
	/** @var string Table which store session data */
	private $tableName = "session_data";

	/**
	 * Create session handler which use database storage as persistent storage
	 */
	public function __construct() {
		$this->dbManager = Application::getInstance()->getServiceLocator()->getDbManager();
	}

	/**
	 * Settings for Database session handler. Available option(s) key:
	 *  - 'adapterName'		=> Specify which database adapter to be used. It'll use default adapter when not specified
	 *  - 'tableName'		=> Specify sessions table name
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	public function setOptions($options) {
		if (isset($options["adapterName"]) && !empty($options["adapterName"])) {
			$this->setAdapter($options["adapterName"]);
		} else {
			$this->setAdapter(null);
		}
		if (isset($options["tableName"]) && !empty($options["tableName"])) {
			$this->tableName = $options["tableName"];
		}
		// ToDo: Checking table existence
	}

	/**
	 * Set adapter name
	 *
	 * After adapter name set then it will get the adapter from db manager object. If no name specified then default adapter used.
	 *
	 * @param string $adapterName
	 *
	 * @throws \Lh\Exceptions\InvalidConfigException
	 */
	private function setAdapter($adapterName) {
		if ($adapterName === null) {
			// Use default adapter
			$this->adapter = $this->dbManager->getDefaultAdapter();
			if ($this->adapter == null) {
				throw new InvalidConfigException(APPLICATION_PATH . "config/system/application.config.php", "Unable to use Database session engine since there is no default adapter. Please give at least one adapter at 'dbManager' config key");
			}
		} else {
			$this->adapter = $this->dbManager->getAdapter($adapterName);
			if ($this->adapter == null) {
				throw new InvalidConfigException(APPLICATION_PATH . "config/system/application.config.php", "Adapter '" . $adapterName . "' not found in DbManager adapter(s) collection.");
			}
		}

		$this->factory = $this->adapter->getBuilderFactory();
		$this->parameterCapable = ($this->adapter->getPlatform()->getParameterType() !== IPlatform::PARAMETER_NOT_SUPPORTED);
	}

	/**
	 * Open session storage
	 *
	 * Re-initialize existing session, or creates a new one. Called when a session starts or when session_start() is invoked.
	 *
	 * @see session_start()
	 *
	 * @param string $savePath
	 * @param string $sessionName
	 *
	 * @return bool
	 */
	public function open($savePath, $sessionName) {
		if ($this->adapter->isOpened()) {
			return true;
		} else {
			return $this->adapter->open();
		}
	}

	/**
	 * Close session storage
	 *
	 * Closes the current session. This function is automatically executed when closing the session, or explicitly via session_write_close().
	 * NOTE: Don't close database connection from this place
	 *
	 * @see session_write_close()
	 *
	 * @return bool
	 */
	public function close() {
		return true;
	}

	/**
	 * Read session data
	 *
	 * Reads the session data from the session storage, and returns the results. Called right after the session starts or when session_start() is called.
	 * Please note that before this method is called ISessionHandler::open() is invoked.
	 *
	 * @see session_start()
	 * @see ISessionHandler::open()
	 *
	 * @param string $sessionId
	 *
	 * @return string
	 */
	public function read($sessionId) {
		$select = $this->factory->select("session")
			->from($this->tableName)
			->where("id", $sessionId);

		if ($this->parameterCapable && ($statement = $this->adapter->prepareQuery($select)) !== null) {
			$query = $statement->execute();
		} else {
			// Either driver not able to use parameter of fallback since prepare query is failed
			$query = $this->adapter->query($select);
		}

		if ($query === null || $query->getNumRows() != 1) {
			return null;
		}

		$row = $query->fetchAssoc();

		return $row["session"];
	}

	/**
	 * Write session data
	 *
	 * Writes the session data to the session storage. Called by session_write_close(), when session_register_shutdown() fails, or during a normal shutdown.
	 * Note:
	 *  - ISessionHandler::close() is called immediately after this function.
	 *  - Note this method is normally called by PHP after the output buffers have been closed unless explicitly called by session_write_close()
	 *
	 * @see session_write_close()
	 * @see session_register_shutdown()
	 * @see ISessionHandler::close()
	 *
	 * @param string $sessionId
	 * @param string $data
	 *
	 * @return bool
	 */
	public function write($sessionId, $data) {
		$update = $this->factory->update($this->tableName)
			->set("session", $data)
			->set("last_accessed", date("Y-m-d H:i:s"))
			->where("id", $sessionId);

		if ($this->parameterCapable && ($statement = $this->adapter->prepareQuery($update)) !== null) {
			$query = $statement->execute();
		} else {
			// Either driver not able to use parameter of fallback since prepare query is failed
			$query = $this->adapter->query($update);
		}

		if ($query === null) {
			return false;
		}

		if ($query->getNumRows() == 0) {
			// It's assumed that session data have been never written
			$insert = $this->factory->insert($this->tableName)
				->value("id", $sessionId)
				->value("created_at", date("Y-m-d H:i:s"))
				->value("last_accessed", date("Y-m-d H:i:s"))
				->value("session", $data);

			// Try to insert
			if ($this->parameterCapable && ($statement = $this->adapter->prepareQuery($insert)) !== null) {
				$query = $statement->execute();
			} else {
				// Either driver not able to use parameter of fallback since prepare query is failed
				$query = $this->adapter->query($insert);
			}

			if ($query === null) {
				return false;
			}
		}

		// Success update / insert
		return true;
	}

	/**
	 * Destroy session
	 *
	 * Destroys a session. Called by session_regenerate_id() (with $destroy = TRUE), session_destroy() and when session_decode() fails.
	 *
	 * @see session_regenerate_id()
	 * @see session_destroy()
	 * @see session_decode()
	 *
	 * @param string $sessionId
	 *
	 * @return bool
	 */
	public function destroy($sessionId) {
		$delete = $this->factory->delete($this->tableName)
			->where("id", $sessionId);

		if ($this->parameterCapable && ($statement = $this->adapter->prepareQuery($delete)) !== null) {
			$query = $statement->execute();
		} else {
			// Either driver not able to use parameter of fallback since prepare query is failed
			$query = $this->adapter->query($delete);
		}

		if ($query === null || $query->getNumRows() == 0) {
			return false;
		}

		return true;
	}

	/**
	 * Garbage collect
	 *
	 * Cleans up expired sessions. Called by session_start(), based on session.gc_divisor, session.gc_probability and session.gc_lifetime settings.
	 *
	 * @see session_start()
	 *
	 * @param int $lifeTime
	 *
	 * @return bool
	 */
	public function gc($lifeTime) {
		$delete = $this->factory->delete($this->tableName)
			->where("last_accessed", date("Y-m-d H:i:s", time() - $lifeTime), "<=");

		if ($this->parameterCapable && ($statement = $this->adapter->prepareQuery($delete)) !== null) {
			$query = $statement->execute();
		} else {
			// Either driver not able to use parameter of fallback since prepare query is failed
			$query = $this->adapter->query($delete);
		}

		if ($query === null) {
			return false;
		}

		return true;
	}
}

// End of File: Database.php 