<?php
use Lh\Mvc\ControllerBase;
use Lh\Mvc\IAuthenticationError;
use Lh\Mvc\IBasicError;
use Lh\Mvc\IExceptionError;

/**
 * Class ErrorController
 *
 * This is default error controller class which used to render any pre-defined error from the framework.
 * IMPORTANT:
 *  - This controller should not be deleted since it's required to run framework normally.
 *  - By default all of methods will use 'error/generic.phtml' template
 */
class ErrorController extends ControllerBase implements IBasicError, IExceptionError, IAuthenticationError {
	/** @var bool Determine whether application running in debug mode or not. Route data only printed when debug mode is set. */
	private $isDebug;
	/** @var \Lh\Web\RouteData Store current route data which cause an error */
	private $routeData;
	/** @var string Human readable route data. Only set if application running in debug mode. */
	private $readableRouteData = null;

	public function initialize() {
		parent::initialize();
		$this->pageView->setViewFileName("error/generic");

		$this->isDebug = \Lh\Web\Application::getInstance()->isDebug();
		if ($this->dispatcher->getPreviousInstance() != null) {
			$this->routeData = $this->dispatcher->getPreviousInstance()->getRouteData();
		}
		if ($this->isDebug && $this->routeData != null) {
			foreach ($this->routeData->toArray() as $key => $value) {
				$this->readableRouteData .= sprintf("\n %-17s : %s", "$key", (is_array($value) ? \Lh\Utilities\Collections\ArrayExtension::toString($value) : $value));
			}
		}
	}

	/**
	 * This method will be dispatched when Router unable to determine user request or the request is contains invalid segment(s)
	 * NOTE: This method will be directly called by first instance of dispatcher because in no-match error we unable to determine user request
	 *
	 * @see Router::calculateRoute
	 * @return void
	 */
	public function noMatchAction() {
		$this->pageView->addVar("message", "No route match ! Do you type URL correctly ?");
		$this->pageView->addVar("type", "No Matching Route");
	}

	/**
	 * This method will be dispatched if Dispatcher is unable to find appropriate controller file.
	 * NOTE: This method likely called because if the appropriate controller file not found will be handled by @see Router::calculateRoute
	 *         If you encountered this kind of error then its error must be called manually by your code
	 *
	 * @return void
	 */
	public function noFileAction() {
		if ($this->isDebug) {
			$this->pageView->addVar("message", "There is no controller file while dispatching request. Please contact web administrator about this URL.\nCurrent route:" . $this->readableRouteData);
		} else {
			$this->pageView->addVar("message", "There is no controller file while dispatching request. Please contact web administrator about this URL.");
		}
		$this->pageView->addVar("type", "Controller Class Definition Missing");
	}

	/**
	 * This method will be dispatched when Dispatcher is unable create appropriate class based on RouteData. Reason this method to be called:
	 *  1. Your controller class isn't suffixed by 'Controller'
	 *  2. Your controller class isn't derived from @see ControllerBase
	 *
	 * @return void
	 */
	public function noClassAction() {
		if ($this->isDebug) {
			$this->pageView->addVar("message", "There is no controller class while dispatching request. Please contact web administrator about this URL.\nCurrent route:" . $this->readableRouteData);
		} else {
			$this->pageView->addVar("message", "There is no controller class while dispatching request. Please contact web administrator about this URL.");
		}
		$this->pageView->addVar("type", "Controller Class Name Mismatch");
	}

	/**
	 * This method will be dispatched when Dispatcher is unable to find appropriate method in controller class. Reason this method to be called:
	 *  1. Your method name isn't suffixed by 'Action'
	 *  2. Your controller don't have 'xxxAction' method where xxx is method name defined from RouteData
	 *
	 * @return void
	 */
	public function noMethodAction() {
		if ($this->isDebug) {
			$this->pageView->addVar("message", "There is no associated method in current controller. Please contact web administrator about this URL.\nCurrent route:" . $this->readableRouteData);
		} else {
			$this->pageView->addVar("message", "There is no associated method in current controller. Please contact web administrator about this URL.");
		}
		$this->pageView->addVar("type", "Method Not Found in Controller Class");
	}

	/**
	 * This method will be dispatched when Dispatcher unable to find appropriate view file from @see PageView::viewFileName
	 *
	 * @return void
	 */
	public function noViewAction() {
		if ($this->isDebug) {
			$this->pageView->addVar("message", "There is no associated VIEW file for current request. Please contact web administrator about this URL.\nCurrent route:" . $this->readableRouteData);
		} else {
			$this->pageView->addVar("message", "There is no associated VIEW file for current request. Please contact web administrator about this URL.");
		}
		$this->pageView->addVar("type", "View File Not Found");
	}

	/**
	 * This method will be dispatched when Dispatcher unable to find Master View of current loaded VIEW.
	 * IMPORTANT: If this method called by Dispatcher::dispatchError() then there is an additional named parameter passed which named 'masterViewPath' which type is string
	 *
	 * @see PageView::setMasterView
	 * @see MasterView
	 *
	 * @return void
	 */
	public function noMasterViewAction() {
		$masterViewFile = $this->getRequest()->getNamedParameter("masterViewPath");
		if ($this->isDebug) {
			$this->pageView->addVar("message", "Unable to load Master view '$masterViewFile'. Please contact web administrator about this URL.\nCurrent route:" . $this->readableRouteData);
		} else {
			$this->pageView->addVar("message", "Unable to load Master view '$masterViewFile'. Please contact web administrator about this URL.");
		}
		$this->pageView->addVar("type", "View File Not Found");
	}

	/**
	 * This will be called whenever un-caught exception occurred while dispatching user request. Please examine previous dispatcher for further investigation
	 * When this method called there is additional named parameter included:
	 *  1. 'exception'    => Exception which thrown while code execution
	 *  2. 'source'        => Section name which thrown an exception
	 *
	 * @return void
	 */
	public function unCaughtAction() {
		/** @var Exception $ex */
		$ex = $this->getRequest()->getNamedParameter("exception");
		// Format proper exception message with their stack trace. Identifiable information is replaced by empty string
		if ($ex instanceof Lh\Exceptions\PhpErrorException) {
			$this->pageView->addVar("type", "PHP Error");
			/** @var Lh\Exceptions\PhpErrorException $ex */
			$message = sprintf("Severity  : %s (Code: %s)", $ex->getSeverityAsText(), $ex->getSeverity());
		} else {
			$this->pageView->addVar("type", "Unexpected Exception");
			$message = "Exception : " . get_class($ex);
		}
		$message .= "<br />Message   : " . $ex->getMessage();
		$message .= "<br />" . sprintf("Location  : %s (Line: %s)", $ex->getFile(), $ex->getLine());
		$message .= "<br />Stack Trace: ";
		foreach ($ex->getTrace() as $idx => $trace) {
			if (count($trace["args"]) < 3) {
				$buff = array();
				foreach ($trace["args"] as $arg) {
					if (is_string($arg)) {
						$buff[] = "'$arg'";
					} else if (is_object($arg)) {
						$buff[] = get_class($arg);
					} else if (is_array($arg)) {
						$buff[] = \Lh\Utilities\Collections\ArrayExtension::toString($arg);
					} else if (is_resource($arg)) {
						$buff[] = "Resource: '" . get_resource_type($arg) . "''";
					} else {
						// For other unknown type let PHP convert to string automatically
						$buff[] = @("" . $arg);
					}
				}

				$args = implode(", ", $buff);
			} else {
				$args = "...";
			}
			if (isset($trace["class"])) {
				$message .= sprintf("\n %3s. %s%s%s(%s)", ($idx + 1), $trace["class"], $trace["type"], $trace["function"], $args);
			} else if (isset($trace["function"])) {
				$message .= sprintf("\n %3s. %s(%s)", ($idx + 1), $trace["function"], $args);
			} else {
				$message .= sprintf("\n %3s. Unknown method or function", ($idx + 1));
			}

			if (isset($trace["file"])) {
				$message .= sprintf(" at %s (Line: %s)", $trace["file"], $trace["line"]);
			} else {
				$message .= " at unknown source (Line: n.a.)";
			}
		}
		// Remove any sensitive information
		$this->pageView->addVar("message", str_replace(APPLICATION_PATH, '', $message));

		$previousException = $ex->getPrevious();
		$previousMessage = "";
		while ($previousException instanceof Exception) {
			if ($previousMessage != "") {
				$previousMessage .= "\n\n";
			}

			if ($previousException instanceof Lh\Exceptions\PhpErrorException) {
				$this->pageView->addVar("type", "PHP Error");
				/** @var Lh\Exceptions\PhpErrorException $previousException */
				$previousMessage .= sprintf("Severity  : %s (Code: %s)", $previousException->getSeverityAsText(), $previousException->getSeverity());
			} else {
				$this->pageView->addVar("type", "Unexpected Exception");
				$previousMessage .= "Exception : " . get_class($previousException);
			}
			$previousMessage .= "<br />Message   : " . $previousException->getMessage();
			$previousMessage .= "<br />" . sprintf("Location  : %s (Line: %s)", $previousException->getFile(), $previousException->getLine());
			$previousMessage .= "<br />Stack Trace: ";
			foreach ($previousException->getTrace() as $idx => $trace) {
				if (count($trace["args"]) < 3) {
					$buff = array();
					foreach ($trace["args"] as $arg) {
						if (is_string($arg)) {
							$buff[] = "'$arg'";
						} else if (is_object($arg)) {
							$buff[] = get_class($arg);
						} else if (is_array($arg)) {
							$buff[] = \Lh\Utilities\Collections\ArrayExtension::toString($arg);
						} else if (is_resource($arg)) {
							$buff[] = "Resource: '" . get_resource_type($arg) . "''";
						} else {
							// For other unknown type let PHP convert to string automatically
							$buff[] = @("" . $arg);
						}
					}

					$args = implode(", ", $buff);
				} else {
					$args = "...";
				}
				if (isset($trace["class"])) {
					$previousMessage .= sprintf("\n %3s. %s%s%s(%s)", ($idx + 1), $trace["class"], $trace["type"], $trace["function"], $args);
				} else if (isset($trace["function"])) {
					$previousMessage .= sprintf("\n %3s. %s(%s)", ($idx + 1), $trace["function"], $args);
				} else {
					$previousMessage .= sprintf("\n %3s. Unknown method or function", ($idx + 1));
				}

				if (isset($trace["file"])) {
					$previousMessage .= sprintf(" at %s (Line: %s)", $trace["file"], $trace["line"]);
				} else {
					$previousMessage .= " at unknown source (Line: n.a.)";
				}
			}

			// Check for another previous exception
			$previousException = $previousException->getPrevious();
		}

		$this->pageView->addVar("previousMessage", str_replace(APPLICATION_PATH, '', $previousMessage));
	}

	/**
	 * This method will be called when user manually called Dispatcher::dispatchError() or your configuration file contains any error.
	 * When configuration file contains error then Web Application will call this method instead of requested one.
	 *
	 * @see \Lh\Web\Application::start()
	 *
	 * @return void
	 */
	public function genericAction() {
		/** @var string[] $errorMessages */
		$errorMessage = $this->getRequest()->getNamedParameter("errorMessage");
		// Remove any sensitive information
		$this->pageView->addVar("message", str_replace(APPLICATION_PATH, '', $errorMessage));
		$this->pageView->addVar("type", "Internal Server Error");
	}

	/**
	 * This method will be called when anonymous user trying to access protected / restricted resource.
	 *
	 * @return void
	 */
	public function notAuthenticatedAction() {
		if ($this->isDebug) {
			$this->pageView->addVar("message", "Authentication is required to access this resource.\nCurrent route:" . $this->readableRouteData);
		} else {
			$this->pageView->addVar("message", "Authentication is required to access this resource.");
		}
		$this->pageView->addVar("type", "Authentication Required");
	}

	/**
	 * This method will be called when signed user trying to access resources where its not belong to him
	 *
	 * @return void
	 */
	public function notAuthorizedAction() {
		if ($this->isDebug) {
			$this->pageView->addVar("message", "Sorry you're not authorized to access this resource.\nCurrent route:" . $this->readableRouteData);
		} else {
			$this->pageView->addVar("message", "Sorry you're not authorized to access this resource.");
		}
		$this->pageView->addVar("type", "Not Authorized");
	}
}

// End of File: ErrorController.php 
