<?php
/**
 * LH Framework
 *
 * @author    Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Exceptions;

use Exception;

/**
 * Class DefaultHandler
 *
 * This default handler will try its best to mimicking PHP error handler
 *
 * @package Lh\Exceptions
 */
class DefaultHandler implements IErrorHandler {
	/**
	 * Setting any options to affect current error handler behaviour.
	 *
	 * @param $options
	 *
	 * @return void
	 */
	public function setOptions($options) {
		// No specific options for default handler
	}

	/**
	 * Handler PHP error
	 *
	 * This method will be registered as PHP error handler and will be called every time PHP error occurred.
	 * Default PHP error handler will be called if this method return false
	 *
	 * @param int    $code
	 * @param string $message
	 * @param string $file
	 * @param int    $line
	 * @param array  $context
	 *
	 * @throws PhpErrorException
	 *
	 * @return bool return false to propagate into default PHP error handler
	 */
	public function handleError($code, $message, $file, $line, $context) {
		throw new PhpErrorException($message, $code, $file, $line);
	}

	/**
	 * Handle un-expected exception
	 *
	 * Since this default handler should mimic original PHP then it's always re-throw the exception.
	 *
	 * @param Exception $ex
	 *
	 * @throws \Exception
	 * @return void
	 */
	public function handleException(Exception $ex) {
		throw $ex;
	}
}

// End of File: DefaultHandler.php 