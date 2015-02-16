<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Auth;

/**
 * Interface IAuthorization
 * @package Lh\Auth
 */
interface IAuthorization {
	/**
	 * Allow a permission to current object
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function allow($permission);

	/**
	 * Deny a permission to current object
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function deny($permission);

	/**
	 * Revoke a permission from current object
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function revoke($permission);

	/**
	 * Check whether current user have a specific permission or not
	 *
	 * This method ONLY check for permission existence in current object. This SHOULD NOT check for their value.
	 * It always return true when a specific permission found (either ALLOW or DENY)
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function hasPermission($permission);

	/**
	 * Check whether user have access to specific permission or not
	 *
	 * This method MUST used to determine whether current object is have ALLOW permission or not.
	 * When specific permission is not exists then it SHOULD return false. Most restrictive access rule applied
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function isGranted($permission);
}

// End of File: IAuthorization.php 