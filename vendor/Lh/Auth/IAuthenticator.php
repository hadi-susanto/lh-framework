<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Auth;

/**
 * Interface IAuthenticator
 * Contract for class will provide authentication mechanism.
 *
 * @package Lh\Auth
 */
interface IAuthenticator {
	/**
	 * Set authenticator behaviour based on given option(s). Option retrieved from config file (key = 'options').
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	public function setOptions($options);

	/**
	 * Return hashed credential based on identity and plain credential
	 *
	 * It's essential thing that we store user password in hashed form instead of plain text. Hashed password are one way 'encryption' therefore
	 * there is no such way to retrieve user password. There is no recover password BUT reset password.
	 *
	 * @param mixed $identity
	 * @param mixed $credential plain credential
	 * @param array $options
	 *
	 * @return string
	 */
	public function hashCredential($identity, $credential, array $options = null);

	/**
	 * Perform authentication
	 *
	 * @param mixed $identity
	 * @param mixed $credential
	 * @param array $options
	 *
	 * @return AuthenticationResult
	 */
	public function authenticate($identity, $credential, array $options = null);

	/**
	 * Return last identity which used for authentication process
	 *
	 * @return mixed
	 */
	public function getLastIdentity();

	/**
	 * Return last credential used for authentication process
	 *
	 * @return mixed
	 */
	public function getLastCredential();

	/**
	 * Return raw data from authenticate() method.
	 *
	 * Array returned in key value pair. Key name will correspond to column name
	 * NOTE: This method should remove any sensitive data such as password, hashed password, etc
	 *
	 * @return array
	 */
	public function getRawData();

	/**
	 * Return authenticated user object based on last authenticate() method call.
	 *
	 * @return User
	 */
	public function getUser();

	/**
	 * Clear any identity stored in current authenticator
	 *
	 * This will clear User object and raw data. IMPORTANT: clear identity data from authenticator
	 * WILL NOT logged-out current user although User stored in authenticator is same user with AuthenticationManager.
	 * User log-out sequence performed by AuthenticationManager::saveUser(null). Yes give null value to perform log-out
	 *
	 * @return bool
	 */
	public function clearIdentity();
}

// End of File: IAuthenticator.php 