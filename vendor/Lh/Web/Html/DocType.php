<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Html;

/**
 * Class DocType
 *
 * Represent <DOCTYPE> element which should be the first line of any HTML output
 *
 * @package Lh\Web\Html
 */
class DocType extends Element {
	const HTML5 = '<!DOCTYPE html>';
	const HTML401_STRICT = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
	const HTML401_TRANSITIONAL = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
	const HTML401_FRAMESET = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
	const XHTML10_STRICT = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	const XHTML10_TRANSITIONAL = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	const XHTML10_FRAMESET = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
	const XHTML11 = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';

	/** @var array Well known doc type */
	private $docTypes = array(
		"HTML5" => DocType::HTML5,
		"HTML401_STRICT" => DocType::HTML401_STRICT,
		"HTML401_TRANSITIONAL" => DocType::HTML401_TRANSITIONAL,
		"HTML401_FRAMESET" => DocType::HTML401_FRAMESET,
		"XHTML10_STRICT" => DocType::XHTML10_STRICT,
		"XHTML10_TRANSITIONAL" => DocType::XHTML10_TRANSITIONAL,
		"XHTML10_FRAMESET" => DocType::XHTML10_FRAMESET,
		"XHTML11" => DocType::XHTML11,
	);
	/** @var null|string Doctype value */
	private $docType = null;

	/**
	 * Create new instance of Doctype
	 *
	 * @param string $type
	 */
	public function __construct($type) {
		parent::__construct("!DOCTYPE", null);
		if (array_key_exists(strtoupper($type), $this->docTypes)) {
			$this->docType = $this->docTypes[strtoupper($type)];
		} else {
			if (strpos(strtoupper($type), "<!DOCTYPE") === false) {
				$this->docType = "<!DOCTYPE $type>";
			} else {
				$this->docType = $type;
			}
		}

		$this->setShortStyleAllowed(false);
	}


	/**
	 * Compile current Element as string
	 *
	 * Compiling this element into string. Resulting string must be a valid html element with opening and closing tag.
	 *
	 * @param bool $forceShortStyle
	 *
	 * @return string
	 */
	public function toString($forceShortStyle = false) {
		return $this->docType;
	}
}

// End of File: DocType.php 