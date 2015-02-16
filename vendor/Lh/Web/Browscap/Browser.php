<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Browscap;

use Lh\IExchangeable;

/**
 * Class Browser
 *
 * This class represent user browser capabilities. This class not intended created by user, this class instance should be obtained from Browscap class.
 *
 * @package Lh\Web\Browscap
 *
 * @see Browscap
 */
class Browser implements IExchangeable {
	/** @var string Comment */
	protected $comment;
	/** @var string Name */
	protected $name;
	/** @var string Version */
	protected $version = 0;
	/** @var int Major version */
	protected $majorVer = 0;
	/** @var int Minor version */
	protected $minorVer = 0;
	/** @var string Platform */
	protected $platform;
	/** @var string Platform version */
	protected $platformVersion;
	/** @var bool Alpha version */
	protected $alpha = false;
	/** @var bool Beta version */
	protected $beta = false;
	/** @var bool Win16 flag */
	protected $win16 = false;
	/** @var bool Win32 flag */
	protected $win32 = false;
	/** @var bool Win64 flag */
	protected $win64 = false;
	/** @var bool Frame capable flag */
	protected $frame = false;
	/** @var bool IFrame capable flag */
	protected $iFrame = false;
	/** @var bool Table capable flag */
	protected $table = false;
	/** @var bool Cookie capable flag */
	protected $cookie = false;
	/** @var bool Background sound capable flag */
	protected $backgroundSound = false;
	/** @var bool JavaScript capable flag */
	protected $javaScript = false;
	/** @var bool VB Script capable flag */
	protected $vbScript = false;
	/** @var bool Java applet capable flag */
	protected $javaApplet = false;
	/** @var bool ActiveX capable flag */
	protected $activeXControl = false;
	/** @var bool Mobile device flag */
	protected $mobileDevice = false;
	/** @var bool Syndication reader flag */
	protected $syndicationReader = false;
	/** @var bool Web crawler flag */
	protected $crawler = false;
	/** @var int CSS version */
	protected $cssVersion = 0;
	/** @var int AOL version */
	protected $aolVersion = 0;

	/**
	 * Get comment of current browser
	 *
	 * @return string
	 */
	public function getComment() {
		return $this->comment;
	}

	/**
	 * Get browser name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get browser version
	 *
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * Get browser major version
	 *
	 * @return int
	 */
	public function getMajorVersion() {
		return $this->majorVer;
	}

	/**
	 * Get browser minor version
	 *
	 * @return int
	 */
	public function getMinorVersion() {
		return $this->minorVer;
	}

	/**
	 * Get browser platform
	 *
	 * @return string
	 */
	public function getPlatform() {
		return $this->platform;
	}

	/**
	 * Get browser platform version
	 *
	 * @return string
	 */
	public function getPlatformVersion() {
		return $this->platformVersion;
	}

	/**
	 * Does current browser in alpha version
	 *
	 * @return bool
	 */
	public function isAlpha() {
		return $this->alpha;
	}

	/**
	 * Does current browser in beta version
	 *
	 * @return bool
	 */
	public function isBeta() {
		return $this->beta;
	}

	/**
	 * Does current browser run under Win16 environment
	 *
	 * @return bool
	 */
	public function isWin16() {
		return $this->win16;
	}

	/**
	 * Does current browser run under Win32 environment
	 *
	 * @return bool
	 */
	public function isWin32() {
		return $this->win32;
	}

	/**
	 * Does current browser run under Win64 environment
	 *
	 * @return bool
	 */
	public function isWin64() {
		return $this->win64;
	}

	/**
	 * Does current browser support <FRAME>
	 *
	 * @return bool
	 */
	public function isFrameCapable() {
		return $this->frame;
	}

	/**
	 * Does current browser support <IFRAME>
	 *
	 * @return bool
	 */
	public function isIFrameCapable() {
		return $this->iFrame;
	}

	/**
	 * Does current browser support <TABLE>
	 *
	 * @return bool
	 */
	public function isTableCapable() {
		return $this->table;
	}

	/**
	 * Does current browser support cookie
	 *
	 * @return bool
	 */
	public function isCookieCapable() {
		return $this->cookie;
	}

	/**
	 * Does current browser support background sound
	 *
	 * @return bool
	 */
	public function isBackgroundSoundCapable() {
		return $this->backgroundSound;
	}

	/**
	 * Does current browser support JavaScript
	 *
	 * @return bool
	 */
	public function isJavaScriptCapable() {
		return $this->javaScript;
	}

	/**
	 * Does current browser support VB Script
	 *
	 * @return bool
	 */
	public function isVbScriptCapable() {
		return $this->vbScript;
	}

	/**
	 * Does current browser support Java Applet
	 *
	 * NOTE: This will not detect java runtime availability at client side.
	 *
	 * @return bool
	 */
	public function isJavaAppletCapable() {
		return $this->javaApplet;
	}

	/**
	 * Does current browser support ActiveX component
	 *
	 * @return bool
	 */
	public function isActiveXControlCapable() {
		return $this->activeXControl;
	}

	/**
	 * Do the client a mobile device
	 *
	 * @return bool
	 */
	public function isMobileDevice() {
		return $this->mobileDevice;
	}

	/**
	 * Do the client a syndication reader
	 *
	 * @return bool
	 */
	public function isSyndicationReader() {
		return $this->syndicationReader;
	}

	/**
	 * Do the client a crawler
	 *
	 * @return bool
	 */
	public function isCrawler() {
		return $this->crawler;
	}

	/**
	 * Get CSS Version of current browser
	 *
	 * @return int
	 */
	public function getCssVersion() {
		return $this->cssVersion;
	}

	/**
	 * Get AOL Version of current browser
	 *
	 * @return int
	 */
	public function getAolVersion() {
		return $this->aolVersion;
	}

	/**
	 * Create new instance of Browser
	 *
	 * Browser data should be obtained from Browscap
	 *
	 * @param array $values
	 */
	public function __construct(array $values) {
		if (count($values) > 0) {
			$this->exchangeArray($values);
		}
	}

	/**
	 * Create Browser object from browscap.ini compatible array
	 *
	 * @param array $values
	 *
	 * @return Browser
	 */
	public static function fromBrowscapArray($values) {
		$compatibleArray = array(
			"comment" => isset($values["Comment"]) ? $values["Comment"] : null,
			"name" => isset($values["Browser"]) ? $values["Browser"] : null,
			"version" => isset($values["Version"]) ? $values["Version"] : null,
			"majorVer" => isset($values["MajorVer"]) ? $values["MajorVer"] : null,
			"minorVer" => isset($values["MinorVer"]) ? $values["MinorVer"] : null,
			"platform" => isset($values["Platform"]) ? $values["Platform"] : null,
			"platformVersion" => isset($values["Platform_Version"]) ? $values["Platform_Version"] : null,
			"alpha" => isset($values["Alpha"]) ? $values["Alpha"] : false,
			"beta" => isset($values["Beta"]) ? $values["Beta"] : false,
			"win16" => isset($values["Win16"]) ? $values["Win16"] : false,
			"win32" => isset($values["Win32"]) ? $values["Win32"] : false,
			"win64" => isset($values["Win64"]) ? $values["Win64"] : false,
			"frame" => isset($values["Frames"]) ? $values["Frames"] : false,
			"iFrame" => isset($values["IFrames"]) ? $values["IFrames"] : false,
			"table" => isset($values["Tables"]) ? $values["Tables"] : false,
			"cookie" => isset($values["Cookies"]) ? $values["Cookies"] : false,
			"backgroundSound" => isset($values["BackgroundSounds"]) ? $values["BackgroundSounds"] : false,
			"javaScript" => isset($values["JavaScript"]) ? $values["JavaScript"] : false,
			"vbScript" => isset($values["VBScript"]) ? $values["VBScript"] : false,
			"javaApplet" => isset($values["JavaApplets"]) ? $values["JavaApplets"] : false,
			"activeXControl" => isset($values["ActiveXControls"]) ? $values["ActiveXControls"] : false,
			"mobileDevice" => isset($values["isMobileDevice"]) ? $values["isMobileDevice"] : false,
			"syndicationReader" => isset($values["isSyndicationReader"]) ? $values["isSyndicationReader"] : false,
			"crawler" => isset($values["Crawler"]) ? $values["Crawler"] : false,
			"cssVersion" => isset($values["CssVersion"]) ? $values["CssVersion"] : null,
			"aolVersion" => isset($values["AolVersion"]) ? $values["AolVersion"] : null
		);

		return new Browser($compatibleArray);
	}

	/**
	 * Set current instance properties from given array.
	 *
	 * @param array $values
	 *
	 * @return void
	 */
	public function exchangeArray(array $values) {
		$this->comment = $values["comment"];
		$this->name = $values["name"];
		$this->version = $values["version"];
		$this->majorVer = $values["majorVer"];
		$this->minorVer = $values["minorVer"];
		$this->platform = $values["platform"];
		$this->platformVersion = $values["platformVersion"];
		$this->alpha = is_string($values["alpha"]) ? ($values["alpha"] == "true") : (bool)$values["alpha"];
		$this->beta = is_string($values["beta"]) ? ($values["beta"] == "true") : (bool)$values["beta"];
		$this->win16 = is_string($values["win16"]) ? ($values["win16"] == "true") : (bool)$values["win16"];
		$this->win32 = is_string($values["win32"]) ? ($values["win32"] == "true") : (bool)$values["win32"];
		$this->win64 = is_string($values["win64"]) ? ($values["win64"] == "true") : (bool)$values["win64"];
		$this->frame = is_string($values["frame"]) ? ($values["frame"] == "true") : (bool)$values["frame"];
		$this->iFrame = is_string($values["iFrame"]) ? ($values["iFrame"] == "true") : (bool)$values["iFrame"];
		$this->table = is_string($values["table"]) ? ($values["table"] == "true") : (bool)$values["table"];
		$this->cookie = is_string($values["cookie"]) ? ($values["cookie"] == "true") : (bool)$values["cookie"];
		$this->backgroundSound = is_string($values["backgroundSound"]) ? ($values["backgroundSound"] == "true") : (bool)$values["backgroundSound"];
		$this->javaScript = is_string($values["javaScript"]) ? ($values["javaScript"] == "true") : (bool)$values["javaScript"];
		$this->vbScript = is_string($values["vbScript"]) ? ($values["vbScript"] == "true") : (bool)$values["vbScript"];
		$this->javaApplet = is_string($values["javaApplet"]) ? ($values["javaApplet"] == "true") : (bool)$values["javaApplet"];
		$this->activeXControl = is_string($values["activeXControl"]) ? ($values["activeXControl"] == "true") : (bool)$values["activeXControl"];
		$this->mobileDevice = is_string($values["mobileDevice"]) ? ($values["mobileDevice"] == "true") : (bool)$values["mobileDevice"];
		$this->syndicationReader = is_string($values["syndicationReader"]) ? ($values["syndicationReader"] == "true") : (bool)$values["syndicationReader"];
		$this->crawler = is_string($values["crawler"]) ? ($values["crawler"] == "true") : (bool)$values["crawler"];
		$this->cssVersion = $values["cssVersion"];
		$this->aolVersion = $values["aolVersion"];
	}

	/**
	 * Return representation of current object in array format.
	 *
	 * Returned array should be compatible with exchangeArray()
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			"comment" => $this->comment,
			"name" => $this->name,
			"version" => $this->version,
			"majorVer" => $this->majorVer,
			"minorVer" => $this->minorVer,
			"platform" => $this->platform,
			"platformVersion" => $this->platformVersion,
			"alpha" => $this->alpha,
			"beta" => $this->beta,
			"win16" => $this->win16,
			"win32" => $this->win32,
			"win64" => $this->win64,
			"frame" => $this->frame,
			"iFrame" => $this->iFrame,
			"table" => $this->table,
			"cookie" => $this->cookie,
			"backgroundSound" => $this->backgroundSound,
			"javaScript" => $this->javaScript,
			"vbScript" => $this->vbScript,
			"javaApplet" => $this->javaApplet,
			"activeXControl" => $this->activeXControl,
			"mobileDevice" => $this->mobileDevice,
			"syndicationReader" => $this->syndicationReader,
			"crawler" => $this->crawler,
			"cssVersion" => $this->cssVersion,
			"aolVersion" => $this->aolVersion
		);
	}

	/**
	 * Get string representation of current object
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf("%s %s", $this->name, $this->version);
	}
}

// End of File: Browser.php 
