<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Http;

/**
 * Class HttpHeader
 *
 * Handle header in HttpResponse class, instantiation of this class will not automatically sent header to client.
 * Headers are sent when dispatcher begin to send response
 *
 * @see Dispatcher::dispatch()
 * @package Lh\Web\Http
 */
class HttpHeader {
	/** @var string Header name */
	protected $name;
	/** @var string Header value */
	protected $value;
	/** @var int Header code */
	protected $code;
	/** @var bool Do current header will override existing one? */
	protected $override = true;

	/**
	 * Create new instance of HttpHeader
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __construct($name, $value) {
		$this->name = $name;
		$this->value = $value;
	}

	/**
	 * Set header name
	 *
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Get header name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set header value
	 *
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * Get header value
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Set header code
	 *
	 * @param int $code
	 */
	public function setCode($code) {
		$this->code = $code;
	}

	/**
	 * Get header code
	 *
	 * @return int
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Set override behaviour
	 *
	 * @param bool $override
	 */
	public function setOverride($override) {
		$this->override = $override;
	}

	/**
	 * Get override behaviour
	 *
	 * @return bool
	 */
	public function getOverride() {
		return $this->override;
	}
}

// End of File: HttpHeader.php 