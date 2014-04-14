<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Http;

use Lh\Collections\Dictionary;
use Lh\Web\Uri;

/**
 * Class HttpRequest
 *
 * This class represent user request from browser. Any value sent from browser are stored here (GET, POST, FILES, etc).
 * IMPORTANT:
 *  - Don't confused with HttpRequest from PHP standard library
 *  - Named parameter(s) from RouteData are copied into here when RouteData dispatched
 *
 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec5.html#sec5
 * @package Lh\Web\Http
 */
class HttpRequest {
	/**#@+
	 * @const string METHOD constant names
	 */
	const METHOD_OPTIONS = 'OPTIONS';
	const METHOD_GET = 'GET';
	const METHOD_HEAD = 'HEAD';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_DELETE = 'DELETE';
	const METHOD_TRACE = 'TRACE';
	const METHOD_CONNECT = 'CONNECT';
	const METHOD_PATCH = 'PATCH';
	const METHOD_PROPFIND = 'PROPFIND';
	/**#@-*/

	/** @var Uri Store associated Uri object */
	private $uri = null;
	/** @var string Request method */
	private $method = self::METHOD_GET;
	/** @var Dictionary Store $_GET in specialized container */
	private $queries;
	/** @var Dictionary Store $_POST in specialized container */
	private $posts;
	/**
	 * Store $_FILES in specialized container.
	 *
	 * Any uploaded file(s) will be stored in $_FILES by php. Because the way PHP handle multiple uploaded file we perform
	 * additional normalization for handling file upload and convert it into FileUpload object for easy upload manipulation
	 *
	 * @see FileUpload
	 *
	 * @var Dictionary
	 */
	private $files;
	/** @var Dictionary Store $_COOKIE in specialized container */
	private $cookies;
	/** @var Dictionary Store $_SERVER in specialized container */
	private $servers;
	/** @var Dictionary Store $_ENV in specialized container */
	private $environments;
	/**
	 * These values are filled from RouteData named parameters. Named parameter(s) which exists at RouteData instance are copied into these when dispatching occurred.
	 * NOTE: These values are retained between dispatch session NOT request session!
	 *
	 * @var Dictionary
	 */
	private $namedParameters;

	/**
	 * HttpRequest will represent user request and their data populated from browser. This class will be treated like 'singleton' since there is only one request each life cycle normally.
	 * Not possible to have different HttpRequest in one user request per session.
	 * NOTE: File upload(s) are normalize and stored in special class for easy file manipulation
	 *
	 * @see FileUpload
	 *
	 * @constructor
	 */
	public function __construct() {
		$this->queries = new Dictionary($_GET ? : null);
		$this->posts = new Dictionary($_POST ? : null);
		$this->cookies = new Dictionary($_COOKIE ? : null);
		$this->namedParameters = new Dictionary();
		$this->servers = new Dictionary($_SERVER ? : null);
		$this->environments = new Dictionary($_ENV ? : null);

		// Normalize $_FILES
		$this->files = new Dictionary();
		if (count($_FILES) > 0) {
			$this->normalizeFiles();
		}

		$this->uri = new Uri($this->servers->get("REQUEST_URI", "/"), $this->servers->get("QUERY_STRING"));
		$this->method = strtoupper($this->servers->get("REQUEST_METHOD", self::METHOD_GET));
	}

	/**
	 * Get associated Uri object
	 *
	 * @return \Lh\Web\Uri
	 */
	public function getUri() {
		return $this->uri;
	}

	/**
	 * Get current method request
	 *
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * Get all query string
	 *
	 * @return Dictionary
	 */
	public function getQueries() {
		return $this->queries;
	}

	/**
	 * Get all post variable
	 *
	 * @return Dictionary
	 */
	public function getPosts() {
		return $this->posts;
	}

	/**
	 * Get all uploaded file(s)
	 *
	 * @return Dictionary
	 */
	public function getFiles() {
		return $this->files;
	}

	/**
	 * Get all cookie(s) associated with current request
	 *
	 * @return Dictionary
	 */
	public function getCookies() {
		return $this->cookies;
	}

	/**
	 * Get all server variable
	 *
	 * @return Dictionary
	 */
	public function getServers() {
		return $this->servers;
	}

	/**
	 * Get all environment variable
	 *
	 * @return Dictionary
	 */
	public function getEnvironments() {
		return $this->environments;
	}

	/**
	 * Get all named parameter(s)
	 *
	 * Named parameter are extracted from URL segment having '/param:value' format
	 *
	 * @return Dictionary
	 */
	public function getNamedParameters() {
		return $this->namedParameters;
	}

	/**
	 * Get specific GET variable based on key
	 *
	 * @param string     $key
	 * @param null|mixed $default
	 *
	 * @return null|mixed
	 */
	public function getQuery($key, $default = null) {
		return $this->queries->get($key, $default);
	}

	/**
	 * Get specific POST variable based on key
	 *
	 * @param string     $key
	 * @param null|mixed $default
	 *
	 * @return null|mixed
	 */
	public function getPost($key, $default = null) {
		return $this->posts->get($key, $default);
	}

	/**
	 * Get specific FileUpload object variable based on key
	 *
	 * @see FileUpload
	 *
	 * @param string $key
	 *
	 * @return null|FileUpload
	 */
	public function getFile($key) {
		return $this->files->get($key);
	}

	/**
	 * Get specific COOKIE variable based on key
	 *
	 * @param string      $key
	 * @param null|string $default
	 *
	 * @return null|string
	 */
	public function getCookie($key, $default = null) {
		return $this->cookies->get($key, $default);
	}

	/**
	 * Get specific SERVER variable based on key
	 *
	 * @param string      $key
	 * @param null|string $default
	 *
	 * @return null|string
	 */
	public function getServer($key, $default = null) {
		return $this->servers->get($key, $default);
	}

	/**
	 * Get specific ENV (environment) variable based on key
	 *
	 * @param string     $key
	 * @param null|mixed $default
	 *
	 * @return null|mixed
	 */
	public function getEnvironment($key, $default = null) {
		return $this->environments->get($key, $default);
	}

	/**
	 * Add or replace specific named parameter
	 *
	 * @param $key
	 * @param $value
	 */
	public function addNamedParameters($key, $value) {
		if ($this->namedParameters->containsKey($key)) {
			$this->namedParameters->set($key, $value);
		} else {
			$this->namedParameters->add($key, $value);
		}
	}

	/**
	 * Get specific named parameter based on key
	 *
	 * @param string     $key
	 * @param null|mixed $default
	 *
	 * @return mixed|null
	 */
	public function getNamedParameter($key, $default = null) {
		return $this->namedParameters->get($key, $default);
	}

	/**
	 * Used to normalized PHP upload file(s). This will be used when user uploaded files in array term (Ex: uploaded[])
	 * PHP will make this pattern $_FILES["uploaded"]["name"][xx] instead of $_FILES["uploaded"][xx]["name"] which can cause chaotic for looping
	 * This method will normalize PHP pattern and create user friendly object for file upload handling. Steps:
	 *  1. Normalize $_FILES["uploaded"]["name"][xx] into $buff["uploaded"][xx]["name"]
	 *  2. Creating object foreach item in $buff into FileUpload
	 *
	 * @see \Lh\Web\Http\FileUpload
	 */
	private function normalizeFiles() {
		// Step 01: Normalize $_FILES
		$buff = array();
		foreach ($_FILES as $name => $properties) {
			$buff[$name] = array();
			foreach ($properties as $property => $value) {
				if (!is_array($value)) {
					// simple uploaded file
					$buff[$name][$property] = $value;
				} else {
					// uploaded in array form (using same name)
					foreach ($value as $idx => $realValue) {
						$this->mapUploadParam($buff[$name], $idx, $property, $realValue);
					}
				}
			}
		}

		// Step 02: Creating object(s)
		unset($name, $properties, $property, $value, $idx, $realValue);
		foreach ($buff as $name => $uploadedFile) {
			if (is_numeric(key($uploadedFile))) {
				// Uploading using same name will be handled using recursive method
				$buffObjects = array();
				$this->createFileUploadObjects($buffObjects, $uploadedFile);
				$this->files->add($name, $buffObjects);
				unset($buffObjects);
			} else {
				// Simple mapping between name and their definitions
				$this->files->add($name, FileUpload::fromArray($uploadedFile));
			}
		}

		unset($buff);
	}

	/**
	 * Internal function which normalize $_FILES into our temporary variable.
	 *
	 * @param array        $array        [REF] Our temporary variable which store name of uploaded file.
	 * @param int          $idx          Index of uploaded file
	 * @param string       $propertyName Upload property ('name'|'type'|'size'|'tmp_name'|'error')
	 * @param array|string $value        Value of current property otherwise an array containing index and their property name
	 */
	private function mapUploadParam(&$array, $idx, $propertyName, $value) {
		if (!is_array($value)) {
			$array[$idx][$propertyName] = $value;
		} else {
			foreach ($value as $i => $realValue) {
				$this->mapUploadParam($array[$idx], $i, $propertyName, $realValue);
			}
		}
	}

	/**
	 * Internal function to crete FileUpload object from array definition(s)
	 *
	 * @param array $target [REF] Temporary variable that store FileUpload object
	 * @param array $array  [REF] Normalized file upload definition(s)
	 */
	private function createFileUploadObjects(&$target, &$array) {
		foreach ($array as $idx => $definitions) {
			if (is_numeric(key($definitions))) {
				$this->createFileUploadObjects($target[$idx], $definitions);
			} else {
				$target[$idx] = FileUpload::fromArray($definitions);
			}
		}
	}

	/**
	 * Is this a CONNECT method request?
	 *
	 * @return bool
	 */
	public function isConnect() {
		return ($this->method === self::METHOD_CONNECT);
	}

	/**
	 * Is this a DELETE method request?
	 *
	 * @return bool
	 */
	public function isDelete() {
		return ($this->method === self::METHOD_DELETE);
	}

	/**
	 * Is this a GET method request?
	 *
	 * @return bool
	 */
	public function isGet() {
		return ($this->method === self::METHOD_GET);
	}

	/**
	 * Is this a HEAD method request?
	 *
	 * @return bool
	 */
	public function isHead() {
		return ($this->method === self::METHOD_HEAD);
	}

	/**
	 * Is this an OPTIONS method request?
	 *
	 * @return bool
	 */
	public function isOptions() {
		return ($this->method === self::METHOD_OPTIONS);
	}

	/**
	 * Is this a PATCH method request?
	 *
	 * @return bool
	 */
	public function isPatch() {
		return ($this->method === self::METHOD_PATCH);
	}

	/**
	 * Is this a POST method request?
	 *
	 * @return bool
	 */
	public function isPost() {
		return ($this->method === self::METHOD_POST);
	}

	/**
	 * Is this a PROPFIND method request?
	 *
	 * @return bool
	 */
	public function isPropFind() {
		return ($this->method === self::METHOD_PROPFIND);
	}

	/**
	 * Is this a PUT method request?
	 *
	 * @return bool
	 */
	public function isPut() {
		return ($this->method === self::METHOD_PUT);
	}

	/**
	 * Is this a TRACE method request?
	 *
	 * @return bool
	 */
	public function isTrace() {
		return ($this->method === self::METHOD_TRACE);
	}


	/**
	 * Is the request a Javascript XMLHttpRequest?
	 *
	 * Should work with Prototype/Script.aculo.us, possibly others.
	 *
	 * @return bool
	 */
	public function isXmlHttpRequest() {
		$requestedWith = $this->servers->get("HTTP_X_REQUESTED_WITH");
		return ($requestedWith == "XMLHttpRequest");
	}

	/**
	 * Alias name for isXmlHttpRequest()
	 *
	 * @see \Lh\Web\Http\HttpRequest::isXmlHttpRequest()
	 *
	 * @return bool
	 */
	public function isAjaxRequest() {
		return $this->isXmlHttpRequest();
	}
}

// End of File: HttpRequest.php