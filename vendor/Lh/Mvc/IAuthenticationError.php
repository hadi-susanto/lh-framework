<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Mvc;

/**
 * Interface IAuthError
 *
 * This interface defining any error which can be occurred when user trying to access restricted resource.
 *
 * @package Lh\Mvc
 */
interface IAuthenticationError {
	/**
	 * This method will be called when anonymous user trying to access protected / restricted resource.
	 *
	 * @return void
	 */
	public function notAuthenticatedAction();

	/**
	 * This method will be called when signed user trying to access resources where its not belong to him
	 *
	 * @return void
	 */
	public function notAuthorizedAction();
}

// End of File: IAuthError.php 