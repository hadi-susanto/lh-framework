<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Auth;

/**
 * Class AuthenticationResult
 *
 * Represent authentication result by Authenticator::authenticate(). Since authentication is a complex mechanism, it's impossible to tell the result only
 * using boolean value. We need more specialized data to inform whether is succeed or failed. Why the authentication failed?
 *
 * @package Lh\Auth
 */
class AuthenticationResult {
	const AUTH_FAILED = 0;
	const AUTH_SUCCESS = 1;
	const AUTH_FAILED_EMPTY_IDENTITY = -1;
	const AUTH_FAILED_EMPTY_CREDENTIAL = -2;
	const AUTH_FAILED_NO_MATCHING_IDENTITY = -3;
	const AUTH_FAILED_INVALID_CREDENTIAL = -4;

	/** @var int Authentication result code. Refer to AuthenticationResult::AUTH_* */
	private $authResult = 0;

	/**
	 * Create new instance of authentication result
	 *
	 * @param int $authResult This value must be derived from AuthenticationResult::AUTH_*
	 */
	public function __construct($authResult) {
		$this->authResult = $authResult;
	}

	/**
	 * Value returned must be checked against AuthenticationResult::AUTH_*
	 *
	 * @return int
	 */
	public function getAuthResult() {
		return $this->authResult;
	}

	/**
	 * Check whether authentication successful or failed
	 *
	 * @return bool
	 */
	public function isValid() {
		return $this->authResult == AuthenticationResult::AUTH_SUCCESS;
	}
}

// End of File: AuthenticationResult.php