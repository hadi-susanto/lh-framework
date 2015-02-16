<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Session;

/**
 * Interface ISessionHandler
 *
 * This interface similar to SessionHandlerInterface from PHP >= 5.4.0 which defines contract for manual session storage handling
 *
 * @package Lh\Session\Handler
 */
interface ISessionHandler {
	/**
	 * Setting option(s) for current Session Handler instance. This method will be called after object instantiation.
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	public function setOptions($options);

	/**
	 * Re-initialize existing session, or creates a new one. Called when a session starts or when session_start() is invoked.
	 *
	 * @see session_start()
	 *
	 * @param string $savePath
	 * @param string $sessionName
	 *
	 * @return bool
	 */
	public function open($savePath, $sessionName);

	/**
	 * Closes the current session. This function is automatically executed when closing the session, or explicitly via session_write_close().
	 *
	 * @see session_write_close()
	 *
	 * @return bool
	 */
	public function close();

	/**
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
	public function read($sessionId);

	/**
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
	public function write($sessionId, $data);

	/**
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
	public function destroy($sessionId);

	/**
	 * Cleans up expired sessions. Called by session_start(), based on session.gc_divisor, session.gc_probability and session.gc_lifetime settings.
	 *
	 * @see session_start()
	 *
	 * @param int $lifeTime
	 *
	 * @return bool
	 */
	public function gc($lifeTime);
}

// End of File: ISessionHandler.php