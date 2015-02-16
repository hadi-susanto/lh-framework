<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Auth;

use Lh\IExchangeable;
use Serializable as ISerializable;

/**
 * Class Role
 *
 * Represent a role / group for user. Each role can have their own permission
 *
 * @package Lh\Auth
 */
class Role implements IAuthorization, IExchangeable, ISerializable {
	/** @var string Role name */
	private $name;
	/** @var string Role description */
	private $description;
	/** @var bool[] Permission collections */
	protected $permissions;

	/**
	 * Create a new Role
	 *
	 * @param string      $name
	 * @param null|string $description
	 */
	public function __construct($name, $description = null) {
		$this->name = $name;
		$this->description = $description;
	}

	/**
	 * Set role name
	 *
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Get role name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set role description
	 *
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * Get role description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * Add allow permission into current role
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
	 * Add deny permission into current role
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
	 * Remove permission from current role
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
	 * Check whether user have access to specific permission or not
	 *
	 * This method MUST used to determine whether current object is have ALLOW permission or not.
	 * When specific permission is not exists then it SHOULD return false. Most restrictive access rule applied
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function isGranted($permission) {
		if ($this->hasPermission($permission)) {
			return $this->permissions[$permission];
		} else {
			return false;
		}
	}

	/**
	 * Set role property from array
	 *
	 * Acceptable array keys:
	 *  - 'name' bound to setName
	 *  - 'description' bound to setDescription
	 *  - 'permissions' bound to permission
	 *
	 * @param array $values
	 */
	public function exchangeArray(array $values) {
		if (isset($values["name"])) {
			$this->name = $values["name"];
		}
		if (isset($values["descpription"])) {
			$this->description = $values["description"];
		}
		if (isset($values["permissions"]) && is_array($values["permissions"])) {
			$this->permissions = $values["permissions"];
		}
	}

	/**
	 * Return array representation of current role object
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			"name" => $this->name,
			"description" => $this->description,
			"permissions" => $this->permissions
		);
	}

	/**
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 *
	 * @return string the string representation of the object or null
	 */
	public function serialize() {
		return serialize($this->toArray());
	}

	/**
	 * Constructs the object from string
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

// End of File: Role.php 