<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Exceptions;

use Exception;

/**
 * Interface IErrorHandler
 * @package Lh\Exceptions
 */
interface IErrorHandler {
	/**
	 * Setting any options to affect current error handler behaviour.
	 *
	 * @param $options
	 *
	 * @return void
	 */
	public function setOptions($options);

	/**
	 * This method will be registered as PHP error handler and will be called every time PHP error occurred.
	 * Default PHP error handler will be called if this method return false
	 *
	 * @param int    $code
	 * @param string $message
	 * @param string $file
	 * @param int    $line
	 * @param array  $context
	 *
	 * @return bool return false to propagate into default PHP error handler
	 */
	public function handleError($code, $message, $file, $line, $context);

	/**
	 * This method will be called if ErrorManager is set to trap Exception (which not activated by default).
	 * This will rarely used since any un-caught exception in your code will be handled by Dispatcher and it will perform appropriate action.
	 *
	 * @see Dispatcher::dispatch()
	 * @param Exception $ex
	 *
	 * @return void
	 */
	public function handleException(Exception $ex);
}

// End of File: IErrorHandler.php 