<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Mvc;

/**
 * Interface IExceptionError
 *
 * Contract for un-caught exception in user code (Controller, Model or View)
 *
 * @see Dispatcher::dispatch
 * @package Lh\Mvc
 */
interface IExceptionError {
	/**
	 * This will be called whenever un-caught exception occurred while dispatching user request. Please examine previous dispatcher for further investigation
	 * When this method called there is additional named parameter included:
	 *  1. 'exception'	=> Exception which thrown while code execution
	 *  2. 'source'		=> Section name which thrown an exception
	 *
	 * @return void
	 */
	public function unCaughtAction();
}

// End of File: IExceptionError.php 