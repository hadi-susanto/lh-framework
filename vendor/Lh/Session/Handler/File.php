<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Session\Handler;

use Lh\Io\IoException;
use Lh\Session\ISessionHandler;

/**
 * Class File
 *
 * File based session handling. This class will behave similar to native PHP session handling but with more customized settings.
 * Customization available from options:
 *  - 'savePath'
 *  - 'threshold'
 *  - 'fileExt'
 *  - 'pattern'
 *
 * @package Lh\Session\Handler
 */
class File implements ISessionHandler {
	/** @var string Path for saving session file data */
	private $savePath;
	/** @var int Session threshold */
	private $threshold;
	/** @var string Session file extension */
	private $fileExt = "";
	/** @var string Filename pattern. Should contains [id] */
	private $pattern = "sess_[id]";
	/** @var string contains actual formatter for file-name in session file */
	private $_pattern = "sess_%s";

	/**
	 * Create session handler which use plain text file as persistent storage
	 */
	public function __construct() {
		$this->savePath = session_save_path();
	}

	/**
	 * Set save path for session file(s)
	 *
	 * @param string $savePath
	 *
	 * @throws \Lh\Io\IoException
	 * @throws \InvalidArgumentException
	 */
	private function setSavePath($savePath) {
		if (empty($savePath)) {
			throw new \InvalidArgumentException("Session save path is not allowed to have null or empty string value");
		}
		if (!is_dir($savePath) && !mkdir($savePath, 0777, true)) {
			// Failed to create directory
			throw new IoException(sprintf("Unable to create directory ''. Please check your access permission", $savePath));
		}
		$this->savePath = realpath($savePath) . DIRECTORY_SEPARATOR;
	}

	/**
	 * Get session file(s) save path
	 *
	 * @return string
	 */
	public function getSavePath() {
		return $this->savePath;
	}

	/**
	 * Set threshold in second(s) for purge obsolete session file
	 *
	 * @param int $threshold
	 */
	private function setThreshold($threshold) {
		if ($threshold <= 0 || !is_numeric($threshold)) {
			$this->threshold = -1;
		} else {
			$this->threshold = $threshold;
		}
	}

	/**
	 * Get threshold value for session file
	 *
	 * @return int
	 */
	public function getThreshold() {
		return $this->threshold;
	}

	/**
	 * Set extension for session file
	 *
	 * @param string $fileExt
	 */
	private function setFileExt($fileExt) {
		if (!empty($fileExt) && strpos($fileExt, ".") !== 0) {
			$fileExt = "." . $fileExt;
		}
		$prevExt = $this->fileExt;
		$this->fileExt = $fileExt;
		if (!empty($prevExt)) {
			$pos = strrpos($this->_pattern, $prevExt);
			$this->_pattern = substr($this->_pattern, 0, $pos);
		}
	}

	/**
	 * Get extension for session file
	 *
	 * @return string
	 */
	public function getFileExt() {
		return $this->fileExt;
	}

	/**
	 * Set pattern file name for session file. Placeholder '[id]' will be replaced with session id
	 *
	 * @param string $pattern
	 */
	private function setPattern($pattern) {
		$this->pattern = $pattern;
		$this->_pattern = str_replace("[id]", "%s", $pattern) . $this->getFileExt();
	}

	/**
	 * Get pattern for session file name
	 *
	 * @return string
	 */
	public function getPattern() {
		return $this->pattern;
	}

	/**
	 * Configure current seesion handler
	 *
	 * Available options key:
	 *  - 'savePath'	: Where this handler look for session file
	 *  - 'threshold'	: Session threshold before auto deletion
	 *  - 'fileExt'		: Session file extension
	 *  - 'pattern'		: Session filename pattern. MUST contain '[id]' without quote
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	public function setOptions($options) {
		if (isset($options["savePath"]) && !empty($options["savePath"])) {
			$this->setSavePath($options["savePath"]);
		}
		if (isset($options["threshold"]) && is_numeric($options["threshold"])) {
			$this->setThreshold($options["threshold"]);
		}
		if (isset($options["fileExt"]) && !empty($options["fileExt"])) {
			$this->setFileExt($options["fileExt"]);
		}
		if (isset($options["pattern"]) && !empty($options["pattern"])) {
			$this->setPattern($options["pattern"]);
		}
	}

	/**
	 * Open storage location
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
		if ($this->savePath == null) {
			try {
				$this->setSavePath($savePath);
			} catch (\Exception $ex) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Close storage
	 *
	 * Closes the current session. This function is automatically executed when closing the session, or explicitly via session_write_close().
	 * Don't delete the folder... it will cause another session file deleted. Just do nothing!
	 *
	 * @see session_write_close()
	 *
	 * @return bool
	 */
	public function close() {
		return true;
	}

	/**
	 * Read session file
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
		$file = $this->savePath . sprintf($this->_pattern, $sessionId);

		return is_file($file) ? (string)@file_get_contents($file) : "";
	}

	/**
	 * Write session data into file
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
		return file_put_contents($this->savePath . sprintf($this->_pattern, $sessionId), $data) !== false;
	}

	/**
	 * Destroy session file
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
		return unlink($this->savePath . sprintf($this->_pattern, $sessionId));
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
		foreach (glob($this->savePath . sprintf($this->_pattern, "*")) as $file) {
			if (filemtime($file) + $lifeTime < time() && file_exists($file)) {
				unlink($file);
			}
		}

		return true;
	}
}

// End of File: File.php 