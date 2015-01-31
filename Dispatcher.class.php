<?php
/**
 * Raindrop Framework for PHP
 *
 * Dispatcher
 *
 * @author $Author$
 * @copyright
 * @date $Date$
 *
 * Copyright (c) 2010-2014,
 * Site:
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop;

use Raindrop\ActionResult\ErrorPage;
use Raindrop\ActionResult\HttpCode;
use Raindrop\ActionResult\Json;
use Raindrop\ActionResult\Redirect;
use Raindrop\ActionResult\Xml;

final class Dispatcher
{
	/**
	 * @var Dispatcher
	 */
	protected static $_oInstance = null;

	#region Application's Elements
	/**
	 * @var Router
	 */
	protected $_oRouter = null;

	/**
	 * @var Request
	 */
	protected $_oRequest = null;

	/**
	 * @var ActionResult
	 */
	protected $_oActionResult = null;

	protected $_sCallStack = null;

	protected $_exLastException = null;
	#endregion

	#region Symbol Properties
	protected $_bDispatched = false;
	#endregion

	/**
	 *
	 */
	protected function __construct()
	{
		$this->_oRouter  = Router::GetInstance();
		$this->_oRequest = $this->_oRouter->GetRequest();;

		self::$_oInstance = $this;
	}

	/**
	 * @return Dispatcher
	 */
	public static function GetInstance()
	{
		if (self::$_oInstance === null) {
			new self();
		}

		return self::$_oInstance;
	}

	/**
	 * @return Application
	 */
	public function getApplication()
	{
		return Application::GetApplication();
	}

	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->_oRequest;
	}

	/**
	 * @return Router
	 */
	public function getRouter()
	{
		return $this->_oRouter;
	}

	/**
	 * @return ActionResult
	 */
	public function getResult()
	{
		return $this->_oActionResult;
	}

	/**
	 * Dispatch Request
	 */
	public function dispatch(Request $oRequest = null)
	{
		//change request
		if ($oRequest !== null) {
			$this->_oRequest = $oRequest;
		}

		//get controller
		$sCtrlName =
			AppName . '\\'
			. ($this->_oRequest->getModule() === null ? null : 'Module\\' . $this->_oRequest->getModule() . '\\')
			. 'Controller\\'
			. $this->_oRequest->getController() . 'Controller';

		try {
			$oRefCtrl = new \ReflectionClass($sCtrlName);
			if ($oRefCtrl->isSubclassOf('Raindrop\Controller') == false) {
				throw new FatalErrorException('not_controller_object');
			}
			//detect action
			$oRefAct = null;

			$sActName          = sprintf('Action_%s_%s', $this->_oRequest->getMethod(), $this->_oRequest->getAction());
			$sActFailOver      = sprintf('Action_%s', $this->_oRequest->getAction());
			$this->_sCallStack = "{$sCtrlName}\\{$sActName}";

			if ($oRefCtrl->hasMethod($sActName)) {
				$oRefAct = $oRefCtrl->getMethod($sActName);
			} else if ($oRefCtrl->hasMethod($sActFailOver)) {
				$this->_sCallStack = "{$sCtrlName}\\{$sActFailOver}";

				$oRefAct = $oRefCtrl->getMethod($sActFailOver);
			} else {
				//$this->_oActionResult = HttpCode(404, 'not_found: call_stack=' . $this->_sCallStack);
				$this->_oActionResult = 'not_found';
				$this->_bDispatched   = true;

				return false;
			}
			if (Application::IsDebugging()) {
				Debugger::Output('CallStack:' . $this->_sCallStack, 'Dispatcher');
			}

			//Invoke Controller's Instance
			$oController = $oRefCtrl->newInstance();

			//permission check
			if ($oController->identifyRequired()) {
				$oIdentify = Application::GetIdentify();

				//get action's permission required
				//and check recent identify has required permission
				if ($oIdentify::IsIdentified()) {
					$aPermReq = $oController->requiredPermission();

					//null=>just need login
					if ($aPermReq != null) {
						if (is_array($aPermReq)) {
							$aPermReq = array_key_case($aPermReq, CASE_LOWER);
						} else {
							throw new FatalErrorException('identify_invalid_permission_format');
						}

						$sAct          = strtolower($this->_oRequest->getAction());
						$aRequiredPerm = null;
						if (array_key_exists($sAct, $aPermReq)) $aRequiredPerm = $aPermReq[$sAct];
						else if (array_key_exists('*', $aPermReq)) $aRequiredPerm = $aPermReq['*'];

						$aRequiredPerm = str_nullorwhitespace($aRequiredPerm) ? array() : explode(',', $aRequiredPerm);
						array_walk($aRequiredPerm,
							function (&$_v, $_k) {
								$_v = trim($_v);
							});
						if ($oIdentify->hasPermission($aRequiredPerm) == false) {
							throw new NoPermissionException($this->_sCallStack);
						}
					}
				} else {
					throw new UnidentifiedException($this->_sCallStack);
				}
			}

			//prepare
			$oPrepareResult = $oController->prepare();
			if ($oPrepareResult instanceof ActionResult) {
				$this->_oActionResult = $oPrepareResult;
				$this->_bDispatched   = true;

				return;
			}
			//params
			$aActParams     = $oRefAct->getParameters();
			$aActCallParams = array();
			foreach ($aActParams AS $_p) {
				$aActCallParams[] = $this->_oRequest->getQuery(
					strtolower($_p->getName()),
					$_p->isDefaultValueAvailable() ? $_p->getDefaultValue() : null);
			}

			//Invoke Target
			$this->_oActionResult = $oRefAct->invokeArgs($oController, $aActCallParams);

		} catch (FileNotFoundException $ex) {
			if (Application::IsDebugging()) {
				Logger::Warning(sprintf(
					"module: %s, controller: %s, action: %s\r\n%s",
					$this->_oRequest->getModule(),
					$this->_oRequest->getController(),
					$this->_oRequest->getAction(),
					$ex));
			}
			$this->_oActionResult = 404;
		} catch (IdentifyException $ex) {
			if ($ex instanceof NoPermissionException) {
				$this->_oActionResult = 403;
			} else {
				$this->_oActionResult = 401;
			}
		} catch (ApplicationException $ex) {
			$this->_oActionResult   = 500;
			$this->_exLastException = $ex;
		}

		if ($this->_oActionResult === null) {
			$this->_oActionResult = 204;
		}

		$this->_bDispatched = true;
	}

	/**
	 * Output Result
	 */
	public function outputResult()
	{
		if ($this->_bDispatched == false) {
			throw new FatalErrorException('not_dispatched');
		}

		try {
			//Action Result Model
			if ($this->_oActionResult instanceof ActionResult) {
				$this->_oActionResult->Output();
			} //Text-base Defined Result, switch by RequestType
			else {
				$oResult = null;
				if ($this->_oActionResult == 404) {
					if ($this->_oRequest->getType() == 'View') {
						$oResult = new ErrorPage(404);
					} else if ($this->_oRequest->getType() == 'Json') {
						$oResult = new Json(true, array('status' => false, 'message' => $this->_oActionResult));
					} else if ($this->_oRequest->getType() == 'Xml') {
						$oResult = new Xml(true, array('status' => false, 'message' => 'not_found'));
					} else {
						$oResult = new HttpCode(404);
					}
				} else if ($this->_oActionResult == 204) {
					if ($this->_oRequest->getType() == 'View') {
						$oResult = new ErrorPage(204, array('CallStack' => $this->_sCallStack));
					} else if ($this->_oRequest->getType() == 'Json') {
						$oResult = new Json(true, array('status' => false, 'message' => $this->_oActionResult));
					} else {
						$oResult = new HttpCode(204);
					}
				} else if ($this->_oActionResult == 401) {
					if ($this->_oRequest->getType() == 'View') {
						//redirect to login
						$oResult = new Redirect('Default', 'Account', 'Login', array('return' => $this->_oRequest->getRequestUri()));
					} else if ($this->_oRequest->getType() == 'Json') {
						$oResult = new Json(true, array('status' => false, 'message' => 'not_login'));
					} else if ($this->_oRequest->getType() == 'Xml') {
						$oResult = new Xml(true, array('status' => false, 'message' => 'not_login'));
					} else {
						$oResult = new HttpCode(401);
					}
				} else if ($this->_oActionResult == 403) {
					if ($this->_oRequest->getType() == 'View') {
						//show no permission page
						$oResult = new ErrorPage(403);
					} else if ($this->_oRequest->getType() == 'Json') {
						$oResult = new Json(true, array('status' => false, 'message' => 'no_permission'));
					} else if ($this->_oRequest->getType() == 'Xml') {
						$oResult = new Xml(true, array('status' => false, 'message' => 'no_permission'));
					} else {
						$oResult = new HttpCode(403);
					}
				} else if ($this->_oActionResult == 500) {
					if ($this->_oRequest->getType() == 'View') {
						//show no permission page
						$oResult = new ErrorPage(500, $this->_exLastException);
					} else if ($this->_oRequest->getType() == 'Json') {
						$oResult = new Json(true, array('status' => false, 'message' => $this->_exLastException->getMessage()));
					} else if ($this->_oRequest->getType() == 'Xml') {
						$oResult = new Xml(true, array('status' => false, 'message' => $this->_exLastException->getMessage()));
					} else {
						$oResult = new HttpCode(403);
					}
				} else {
					//bad request
					if ($this->_oRequest->getType() == 'View') {
						$oResult = new ErrorPage(400);
					} else if ($this->_oRequest->getType() == 'Json') {
						$oResult = new Json(true, array('status' => false, 'message' => 'bad_request'));
					} else if ($this->_oRequest->getType() == 'Xml') {
						$oResult = new Xml(true, array('status' => false, 'message' => 'bad_request'));
					} else {
						$oResult = new HttpCode(400);
					}
				}
				$oResult->Output();
			}
		} catch (ApplicationException $ex) {
			if ($this->_oRequest->getType() == 'View') {
				//show no permission page
				$oResult = new ErrorPage(500, $ex);//$this->_exLastException);
			} else if ($this->_oRequest->getType() == 'Json') {
				$oResult = new Json(true, array('status' => false, 'message' => $this->_exLastException->getMessage()));
			} else if ($this->_oRequest->getType() == 'Xml') {
				$oResult = new Xml(true, array('status' => false, 'message' => $this->_exLastException->getMessage()));
			} else {
				$oResult = new HttpCode(403);
			}
			$oResult->Output();
		}
	}
} 