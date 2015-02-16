<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (hd.susanto@yahoo.com)
 * @copyright 2014
 */

namespace Lh;

/**
 * Class LazyExceptionTrace
 *
 * Class which stored exception object and stored for further investigation at later time.
 * Any exception which occurred while system initialization MUST NOT halted system and Exception must be logged
 *
 * @package Lh\System
 */
class LazyExceptionTrace {
	/** @var string Source of exception */
	protected $source;
	/** @var int Time occurrence of exception */
	protected $timeOccurrence;
	/** @var \Exception Exception object which thrown */
	protected $exception;

	/**
	 * Create instance of LazyExceptionTrace
	 *
	 * @param \Exception $exception
	 * @param string     $source
	 * @param int|string $timeOccurrence
	 */
	public function __construct($exception, $source, $timeOccurrence = null) {
		$this->exception = $exception;
		$this->source = $source;
		$this->timeOccurrence = $timeOccurrence !== null ?: time();
	}

	/**
	 * Get actual exception object
	 *
	 * @return \Exception
	 */
	public function getException() {
		return $this->exception;
	}

	/**
	 * Get source which throwing exception
	 *
	 * @return string
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * Get occurrence time in int format
	 *
	 * @return int
	 */
	public function getTimeOccurrence() {
		return $this->timeOccurrence;
	}
}

// End of File: LazyExceptionTrace.php