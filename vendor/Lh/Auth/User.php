<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Auth;

use Lh\IExchangeable;
use Serializable as ISerializable;

/**
 * Class User
 * Represent authenticated user in our system. Each user will have permission and can have different role.
 * IMPORTANT: User permission have higher priority rather than role permission. This framework utilize most restrictive permission.
 *
 * @package Lh\Auth
 */
class User implements IExchangeable, IAuthorization, ISerializable {
	/** @var string|int User Unique ID (usually auto increment value) */
	protected $id;
	/** @var string User login name */
	protected $identity;
	/** @var string Real user name */
	protected $name;
	/** @var Role[] User role(s) or group(s) */
	protected $roles = array();
	/** @var bool[] Any registered permission(s) for current user. Permission(s) either allowed or denied */
	protected $permissions = array();

	/**
	 * Set user unique ID
	 *
	 * @param string|int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * Get user unique ID
	 *
	 * @return string|int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set user identity
	 *
	 * @param string $identity
	 */
	public function setIdentity($identity) {
		$this->identity = $identity;
	}

	/**
	 * Get user identity
	 *
	 * @return string
	 */
	public function getIdentity() {
		return $this->identity;
	}

	/**
	 * Set user real name
	 *
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Get user real name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Add or replace a role for current user.
	 *
	 * All permission from role automatically applied to user. Permission from user object have more priority compared to Role priority.
	 * A user can have multiple role hence same permission from different role may be collided. In that case denied permission take higher priority.
	 * In Short: User Permission > Role Permission and denied permission will take over allow permission
	 *
	 * @param string|Role $role
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function addRole($role) {
		if (is_string($role)) {
			$role = new Role($role);
		}
		if (!($role instanceof Role)) {
			throw new \InvalidArgumentException("Role must be string or instance of Role class");
		}
		$this->roles[strtolower($role->getName())] = $role;

		return true;
	}

	/**
	 * Remove a role from current user
	 *
	 * Removing a role will automatically removing associated permission and new permission effective immediately.
	 *
	 * @param string|Role $role
	 *
	 * @return bool
	 */
	public function removeRole($role) {
		if ($role instanceof Role) {
			$role = $role->getName();
		}
		$role = strtolower($role);
		if (!$this->hasRole($role)) {
			return false;
		}
		unset($this->roles[$role]);

		return true;
	}

	/**
	 * Check whether current user have the given role or not
	 *
	 * @param string|Role $role
	 *
	 * @return bool
	 */
	public function hasRole($role) {
		if ($role instanceof Role) {
			$role = $role->getName();
		}
		$role = strtolower($role);

		return isset($this->roles[$role]);
	}

	/**
	 * Add allow permission to current user
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function allow($permission) {
		$this->permissions[$permission] = true;

		return true;
	}

	/**
	 * Add deny permission to current user
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function deny($permission) {
		$this->permissions[$permission] = false;

		return true;
	}

	/**
	 * Revoke a permission from current user
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function revoke($permission) {
		if (!$this->hasPermission($permission)) {
			return false;
		}
		unset($this->permissions[$permission]);

		return true;
	}

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
	public function hasPermission($permission) {
		return isset($this->permissions[$permission]);
	}

	/**
	 * Check whether current user have allow permission or not
	 *
	 * This method MUST used to determine whether current object is have ALLOW permission or not.
	 * When specific permission is not exists then it SHOULD return false. Most restrictive access rule applied.
	 * Algorithm to determine access:
	 *  1. Check whether current user have requested permission or not. If they have then read from current user
	 *  2. Check for each user role(s) and determine permission from it. All role MUST state ALLOW PERMISSION to give access to user
	 *     If one of the role state DENY PERMISSION then access di DISALLOWED
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function isGranted($permission) {
		if ($this->hasPermission($permission)) {
			// Current user have specific permission. This will override Role based permission
			return $this->permissions[$permission];
		} else {
			// Current user doesn't have specific permission. Look up at his role(s)
			// Most restrictive access applied. Access should be true at first when roles >= 1
			$granted = count($this->roles) > 0;
			foreach ($this->roles as $role) {
				$granted = ($granted && $role->isGranted($permission));
				if (!$granted) {
					break;
				}
			}

			return $granted;
		}
	}

	/**
	 * Fill user properties from array
	 *
	 * Fill each User properties from given array, array should be key value pair. Accepted key:
	 *  - id
	 *  - identity
	 *  - name
	 *  - roles
	 *
	 * @param array $values
	 */
	public function exchangeArray(array $values) {
		if (isset($values["id"])) {
			$this->setId($values["id"]);
		}
		if (isset($values["identity"])) {
			$this->setIdentity($values["identity"]);
		}
		if (isset($values["name"])) {
			$this->setName($values["name"]);
		}
		if (isset($values["roles"]) && is_array($values["roles"])) {
			foreach ($values["roles"] as $role) {
				$this->addRole($role);
			}
		}
	}

	/**
	 * Return array representation of current User object
	 *
	 * Array will be returned in key value pair. Available keys:
	 *  - id
	 *  - identity
	 *  - name
	 *  - roles
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			"id" => $this->id,
			"identity" => $this->identity,
			"name" => $this->name,
			"roles" => $this->roles
		);
	}

	/**
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize() {
		return serialize($this->toArray());
	}

	/**
	 * Constructs the object
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 *
	 * @param string $serialized <p>The string representation of the object.</p>
	 *
	 * @return void
	 */
	public function unserialize($serialized) {
		$this->exchangeArray(unserialize($serialized));
	}
}

// End of File: User.php 