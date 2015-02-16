<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Http;

/**
 * Class HttpCookie
 *
 * Create cookie for HttpResponse object. This class will defer cookie creation until Dispatcher begin to send response to client.
 *
 * @see Dispatcher::dispatch()
 * @package Lh\Web\Http
 */
class HttpCookie {
	/** @var string Cookie name */
	protected $name;
	/** @var null|string Cookie value */
	protected $value;
	/** @var null|int Expire time in second(s) */
	protected $expires = null;
	/** @var string Cookie path */
	protected $path;

	/**
	 * Create new instance of HttpCookie
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __construct($name, $value = null) {
		$this->name = $name;
		$this->value = $value;
	}

	/**
	 * Set cookie name
	 *
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Get cookie name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set cookie value
	 *
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * Get cookie value
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Set cookie expiry
	 *
	 * @param int $expires
	 */
	public function setExpires($expires) {
		$this->expires = $expires;
	}

	/**
	 * Get cookie expiry
	 *
	 * @return int
	 */
	public function getExpires() {
		return $this->expires;
	}


	/**
	 * Set cookie path
	 *
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}

	/**
	 * Get cookie path
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}
}

// End of File: HttpCookie.php 