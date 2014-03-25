<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Http;

use Lh\Collections\Dictionary;
use Lh\Exceptions\InvalidStateException;
use Lh\Web\Application;
use Lh\Web\Dispatcher;
use Lh\Web\RouteData;

/**
 * Class HttpResponse
 *
 * This class will represent response to client. By default response code will be 200 and sent by this class.
 * Differ from HttpRequest this class is not singleton BUT will be instantiated each dispatch request made.
 *
 * @see     Dispatcher::createInstance()
 * @see     Dispatcher::dispatch()
 * @link    http://www.w3.org/Protocols/rfc2616/rfc2616-sec6.html#sec6
 * @package Lh\Web\Http
 */
class HttpResponse {
	/**#@+
	 * @const int Status codes
	 */
	const STATUS_CODE_100 = 100;
	const STATUS_CODE_101 = 101;
	const STATUS_CODE_102 = 102;
	const STATUS_CODE_200 = 200;
	const STATUS_CODE_201 = 201;
	const STATUS_CODE_202 = 202;
	const STATUS_CODE_203 = 203;
	const STATUS_CODE_204 = 204;
	const STATUS_CODE_205 = 205;
	const STATUS_CODE_206 = 206;
	const STATUS_CODE_207 = 207;
	const STATUS_CODE_208 = 208;
	const STATUS_CODE_300 = 300;
	const STATUS_CODE_301 = 301;
	const STATUS_CODE_302 = 302;
	const STATUS_CODE_303 = 303;
	const STATUS_CODE_304 = 304;
	const STATUS_CODE_305 = 305;
	const STATUS_CODE_306 = 306;
	const STATUS_CODE_307 = 307;
	const STATUS_CODE_400 = 400;
	const STATUS_CODE_401 = 401;
	const STATUS_CODE_402 = 402;
	const STATUS_CODE_403 = 403;
	const STATUS_CODE_404 = 404;
	const STATUS_CODE_405 = 405;
	const STATUS_CODE_406 = 406;
	const STATUS_CODE_407 = 407;
	const STATUS_CODE_408 = 408;
	const STATUS_CODE_409 = 409;
	const STATUS_CODE_410 = 410;
	const STATUS_CODE_411 = 411;
	const STATUS_CODE_412 = 412;
	const STATUS_CODE_413 = 413;
	const STATUS_CODE_414 = 414;
	const STATUS_CODE_415 = 415;
	const STATUS_CODE_416 = 416;
	const STATUS_CODE_417 = 417;
	const STATUS_CODE_418 = 418;
	const STATUS_CODE_422 = 422;
	const STATUS_CODE_423 = 423;
	const STATUS_CODE_424 = 424;
	const STATUS_CODE_425 = 425;
	const STATUS_CODE_426 = 426;
	const STATUS_CODE_428 = 428;
	const STATUS_CODE_429 = 429;
	const STATUS_CODE_431 = 431;
	const STATUS_CODE_500 = 500;
	const STATUS_CODE_501 = 501;
	const STATUS_CODE_502 = 502;
	const STATUS_CODE_503 = 503;
	const STATUS_CODE_504 = 504;
	const STATUS_CODE_505 = 505;
	const STATUS_CODE_506 = 506;
	const STATUS_CODE_507 = 507;
	const STATUS_CODE_508 = 508;
	const STATUS_CODE_511 = 511;
	/**#@-*/

	/** @var string HTTP Version */
	protected $httpVersion = "1.1";
	/** @var int HTTP status code */
	protected $statusCode = self::STATUS_CODE_200;
	/** @var string HTTP status message */
	protected $statusMessage;
	/** @var Dictionary Header collections */
	protected $headers;
	/** @var Dictionary Cookie collections */
	protected $cookies;
	/** @var string Base path of user application */
	private $baseUrl;
	/** @var bool Header sent flag */
	private $headerSent = false;
	/** @var bool Cookie sent flag */
	private $cookieSent = false;

	/**
	 * @var string[] Recommended Reason Phrases
	 */
	protected $recommendedReasonPhrases = array(
		// INFORMATIONAL CODES
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		// SUCCESS CODES
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-status',
		208 => 'Already Reported',
		// REDIRECTION CODES
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Switch Proxy', // Deprecated
		307 => 'Temporary Redirect',
		// CLIENT ERROR
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Large',
		415 => 'Unsupported Media Type',
		416 => 'Requested range not satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Unordered Collection',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		// SERVER ERROR
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Time-out',
		505 => 'HTTP Version not supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		511 => 'Network Authentication Required',
	);

	/**
	 * Create new instance of HttpResponse
	 */
	public function __construct() {
		$this->headers = new Dictionary();
		$this->cookies = new Dictionary();
		$this->statusMessage = $this->recommendedReasonPhrases[$this->statusCode];
		$this->baseUrl = Application::getInstance()->getBasePath();
	}

	/**
	 * Set HTTP version
	 *
	 * @param string $httpVersion
	 */
	public function setHttpVersion($httpVersion) {
		$this->httpVersion = $httpVersion;
	}

	/**
	 * Get HTTP version
	 *
	 * @return string
	 */
	public function getHttpVersion() {
		return $this->httpVersion;
	}

	/**
	 * Set HTTP status code
	 *
	 * @param int  $statusCode
	 * @param null $message
	 *
	 * @throws \Lh\Exceptions\InvalidStateException
	 * @throws \InvalidArgumentException
	 */
	public function setStatusCode($statusCode, $message = null) {
		if ($this->isHeadersSent()) {
			throw new InvalidStateException("Headers already sent! Unable to change HTTP status code");
		}
		if (!array_key_exists($statusCode, $this->recommendedReasonPhrases) && empty($message)) {
			throw new \InvalidArgumentException("Message text is required since your status code is not well known. Unknown status code: $statusCode");
		}
		$this->statusCode = $statusCode;
		if (!empty($message)) {
			$this->setStatusMessage($message);
		} else {
			$this->setStatusMessage($this->recommendedReasonPhrases[$statusCode]);
		}
	}

	/**
	 * Get HTTP status code
	 *
	 * @return int
	 */
	public function getStatusCode() {
		return $this->statusCode;
	}

	/**
	 * Set HTTP status message
	 *
	 * @param string $statusMessage
	 *
	 * @throws \Lh\Exceptions\InvalidStateException
	 */
	public function setStatusMessage($statusMessage) {
		if ($this->isHeadersSent()) {
			throw new InvalidStateException("Headers already sent! Unable to change HTTP status message");
		}
		$this->statusMessage = $statusMessage;
	}

	/**
	 * Get HTTP status message
	 *
	 * @return string
	 */
	public function getStatusMessage() {
		return $this->statusMessage;
	}

	/**
	 * Get HTTP header(s)
	 *
	 * @return Dictionary
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * Add HTTP header
	 *
	 * @param HttpHeader $header
	 *
	 * @throws \Lh\Exceptions\InvalidStateException
	 */
	public function addHeader(HttpHeader $header) {
		if ($this->isHeadersSent()) {
			throw new InvalidStateException("Headers already sent! Unable to add another header");
		}
		$this->headers->add($header->getName(), $header);
	}

	/**
	 * Get specific header
	 *
	 * @param string $name
	 *
	 * @return HttpHeader|null
	 */
	public function getHeader($name) {
		return $this->headers->get($name);
	}

	/**
	 * Remove a header before being sent
	 *
	 * @param string $name
	 *
	 * @throws \Lh\Exceptions\InvalidStateException
	 */
	public function removeHeader($name) {
		if ($this->isHeadersSent()) {
			throw new InvalidStateException("Headers already sent! Unable to remove header");
		}
		$this->headers->remove($name);
	}

	/**
	 * Remove all header
	 *
	 * @throws \Lh\Exceptions\InvalidStateException
	 */
	public function clearHeaders() {
		if ($this->isHeadersSent()) {
			throw new InvalidStateException("Headers already sent! Unable to clear headers");
		}
		$this->headers->clear();
	}

	/**
	 * Get HTTP Cookie(s) which will be sent to browser
	 *
	 * @return Dictionary
	 */
	public function getCookies() {
		return $this->cookies;
	}

	/**
	 * Add HTTP Cookie which to sent
	 *
	 * @param HttpCookie $cookie
	 *
	 * @throws \Lh\Exceptions\InvalidStateException
	 */
	public function addCookie(HttpCookie $cookie) {
		if ($this->isCookieSent()) {
			throw new InvalidStateException("Cookies already sent! Unable to add another cookie");
		}
		$this->cookies->add($cookie->getName(), $cookie);
	}

	/**
	 * Get HTTP Cookie based on name
	 *
	 * @param string $name
	 *
	 * @return HttpCookie|null
	 */
	public function getCookie($name) {
		return $this->cookies->get($name);
	}

	/**
	 * Remove a HTTP Cookie
	 *
	 * @param string $name
	 *
	 * @throws \Lh\Exceptions\InvalidStateException
	 */
	public function removeCookie($name) {
		if ($this->isCookieSent()) {
			throw new InvalidStateException("Cookies already sent! Unable to add remove cookie");
		}
		$this->cookies->remove($name);
	}

	/**
	 * Remove all HTTP Cookie(s)
	 *
	 * @throws \Lh\Exceptions\InvalidStateException
	 */
	public function clearCookies() {
		if ($this->isCookieSent()) {
			throw new InvalidStateException("Cookies already sent! Unable to add another cookie");
		}
		$this->cookies->clear();
	}

	/**
	 * Check whether HTTP header(s) is already sent or not
	 *
	 * @return bool
	 */
	public function isHeadersSent() {
		return $this->headerSent;
	}

	/**
	 * Check whether HTTP cookie(s) is already sent or not
	 *
	 * @return bool
	 */
	public function isCookieSent() {
		return $this->cookieSent;
	}

	/**
	 * Check whether current error code is classified as Client Error
	 *
	 * @return bool
	 */
	public function isClientError() {
		return ($this->statusCode >= 400 && $this->statusCode < 500);
	}

	/**
	 * Check whether current error code is classified as Forbidden Error
	 *
	 * @return bool
	 */
	public function isForbidden() {
		return ($this->statusCode == 403);
	}

	/**
	 * Check whether current error code is classified as Informational message
	 *
	 * @return bool
	 */
	public function isInformational() {
		return ($this->statusCode >= 100 && $this->statusCode < 200);
	}

	/**
	 * Check whether current error code is classified as Not Found Error
	 *
	 * @return bool
	 */
	public function isNotFound() {
		return ($this->statusCode == 404);
	}

	/**
	 * Check whether current error code is classified as OK
	 *
	 * @return bool
	 */
	public function isOk() {
		return ($this->statusCode == 200);
	}

	/**
	 * Check whether current error code is classified as Server Error
	 *
	 * @return bool
	 */
	public function isServerError() {
		return ($this->statusCode >= 500 && $this->statusCode < 600);
	}

	/**
	 * Check whether current error code is classified as Redirect
	 *
	 * @return bool
	 */
	public function isRedirect() {
		return ($this->statusCode >= 300 && $this->statusCode < 400);
	}

	/**
	 * Check whether current error code is classified as Success
	 *
	 * @return bool
	 */
	public function isSuccess() {
		return ($this->statusCode >= 200 && $this->statusCode < 300);
	}

	/**
	 * Send header using PHP native header() function
	 *
	 * @param bool $force
	 * @see header()
	 */
	public function sendHeaders($force = false) {
		if (!$force && $this->isHeadersSent()) {
			return;
		}

		// Sent HTTP Status
		header(sprintf("HTTP/%s %d %s", $this->getHttpVersion(), $this->getStatusCode(), $this->getStatusMessage()), true, $this->getStatusCode());
		// Sent additional header
		foreach ($this->getHeaders() as $header) {
			/** @var HttpHeader $header */
			header(sprintf("%s: %s", $header->getName(), $header->getValue()), $header->getOverride(), $header->getCode());
		}

		$this->headerSent = true;
	}

	/**
	 * Send cookie using PHP native setcookie() function
	 *
	 * @param bool $force
	 * @see setcookie()
	 */
	public function sendCookies($force = false) {
		if (!$force && $this->isCookieSent()) {
			return;
		}

		foreach ($this->getCookies() as $cookie) {
			/** @var HttpCookie $cookie */
			setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpires(), $cookie->getPath());
		}
	}

	/**
	 * Perform URL redirection based on RouteData
	 *
	 * @param RouteData $routeData
	 * @param bool      $useSecureConnection
	 * @param bool      $terminateCurrent
	 */
	public function redirectRoute(RouteData $routeData, $useSecureConnection = false, $terminateCurrent = true) {
		$this->redirectUrl($routeData->toUrl(), $useSecureConnection, $terminateCurrent);
	}

	/**
	 * Perform URL redirection based on plain string
	 *
	 * @param string $url
	 * @param bool   $useSecureConnection
	 * @param bool   $terminateCurrent
	 */
	public function redirectUrl($url, $useSecureConnection = false, $terminateCurrent = true) {
		$tokens = array("http://", "https://", "ftp://", "ftps://");
		$absoluteUrl = false;
		foreach ($tokens as $token) {
			if (strpos($url, $token) === 0) {
				$absoluteUrl = true;
				break;
			}
		}

		if (!$absoluteUrl) {
			if (($pos = strpos($url, "/")) === 0) {
				$url = substr($url, 1);
			}

			$url = sprintf("%s://%s",
				$useSecureConnection ? "https" : "http",
				//HttpRequest::getInstance()->getServer("HTTP_HOST"),
				$_SERVER["HTTP_HOST"] .$this->baseUrl . $url);
		}

		$this->prepareRedirectHeader($url, $terminateCurrent);
	}

	/**
	 * Internal function that prepare URL redirection header. This will clear any header(s) before add an redirect header.
	 *
	 * @param string $url
	 * @param bool   $terminateCurrent
	 */
	protected function prepareRedirectHeader($url, $terminateCurrent) {
		$this->headers->clear();
		$this->setStatusCode(302);
		$this->addHeader(new HttpHeader("Location", $url));
		$this->sendHeaders();

		if ($terminateCurrent) {
			foreach (Dispatcher::getInstances() as $dispatcher) {
				$dispatcher->setCompleted(true);
			}
		}
	}
}

// End of File: HttpResponse.php