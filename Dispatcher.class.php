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
use Raindrop\Exceptions\ApplicationException;
use Raindrop\Exceptions\Controller\ControllerNotFoundException;
use Raindrop\Exceptions\FatalErrorException;
use Raindrop\Exceptions\FileNotFoundException;
use Raindrop\Exceptions\Identify\IdentifyException;
use Raindrop\Exceptions\Identify\NoPermissionException;
use Raindrop\Exceptions\Identify\UnidentifiedException;

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
		$this->_oRequest = $this->_oRouter->GetRequest();

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
	public function dispatch()
	{
		//get controller
		$sCtrlName =
			AppName . '\\'
			. ($this->_oRequest->getModule() === null ? null : 'Module\\' . $this->_oRequest->getModule() . '\\')
			. 'Controller\\'
			. $this->_oRequest->getController() . 'Controller';

		try {
			try {
				$oRefCtrl = new \ReflectionClass($sCtrlName);
			}catch(FileNotFoundException $ex){
				throw new ControllerNotFoundException($sCtrlName);
			}
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
				$this->_oActionResult = 404;
				$this->_bDispatched   = true;

				return false;
			}
			if (Application::IsDebugging()) {
				Debugger::Output('CallStack:' . $this->_sCallStack, 'Dispatcher');
			}

			//need identify
			if ($this->_identification($sCtrlName::IdentifyRequired(), $sCtrlName::RequiredPermission()) == false) {
				throw new NoPermissionException;
			}

			//Invoke Controller's Instance
			$oController = $oRefCtrl->newInstance();

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

		} catch (ControllerNotFoundException $ex) {
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
				$this->_oActionResult = 401;
			} else {
				$this->_oActionResult = 403;
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
						$oResult = new Json(true, array('status' => false, 'message' => $this->_oActionResult), 404);
					} else if ($this->_oRequest->getType() == 'Xml') {
						$oResult = new Xml(true, array('status' => false, 'message' => 'not_found'));
					} else {
						$oResult = new HttpCode(404);
					}
				} else if ($this->_oActionResult == 204) {
					if ($this->_oRequest->getType() == 'View') {
						$oResult = new ErrorPage(204, array('CallStack' => $this->_sCallStack));
					} else if ($this->_oRequest->getType() == 'Json') {
						$oResult = new Json(true, array('status' => false, 'message' => $this->_oActionResult), 204);
					} else {
						$oResult = new HttpCode(204);
					}
				} else if ($this->_oActionResult == 401) {
					//Unauthorized
					if ($this->_oRequest->getType() == 'View') {
						//redirect to login
						//$oResult = new Redirect('Default', 'Passport', 'SignIn', array('return' => $this->_oRequest->getRequestUri()));
						$oResult = new ErrorPage(401);
					} else if ($this->_oRequest->getType() == 'Json') {
						$oResult = new Json(true, array('status' => false, 'message' => 'unauthorized'), 401);
					} else if ($this->_oRequest->getType() == 'Xml') {
						$oResult = new Xml(true, array('status' => false, 'message' => 'unauthorized'));
					} else {
						$oResult = new HttpCode(401);
					}
				} else if ($this->_oActionResult == 403) {
					//Forbidden
					if ($this->_oRequest->getType() == 'View') {
						//show no permission page
						$oResult = new Redirect(Configuration::Get('System/Identify', '/'));
					} else if ($this->_oRequest->getType() == 'Json') {
						$oResult = new Json(true, array('status' => false, 'message' => 'forbidden'), 403);
					} else if ($this->_oRequest->getType() == 'Xml') {
						$oResult = new Xml(true, array('status' => false, 'message' => 'forbidden'));
					} else {
						$oResult = new HttpCode(403);
					}
				} else if ($this->_oActionResult == 500) {
					if ($this->_oRequest->getType() == 'View') {
						//show no permission page
						$oResult = new ErrorPage(500, $this->_exLastException);
					} else if ($this->_oRequest->getType() == 'Json') {
						$oResult = new Json(true, array('status' => false, 'message' => $this->_exLastException->getMessage()), 500);
					} else if ($this->_oRequest->getType() == 'Xml') {
						$oResult = new Xml(true, array('status' => false, 'message' => $this->_exLastException->getMessage()));
					} else {
						$oResult = new HttpCode(500);
					}
				} else {
					//bad request
					if ($this->_oRequest->getType() == 'View') {
						$oResult = new ErrorPage(400);
					} else if ($this->_oRequest->getType() == 'Json') {
						$oResult = new Json(true, array('status' => false, 'message' => 'bad_request'), 400);
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
				$oResult = new HttpCode(500);
			}
			$oResult->Output();
		}
	}

	protected function _identification($bIdentify, $mPermRequired)
	{
		#region Controller Level
		//without identification
		if ($bIdentify != true) {
			return true;
		}

		//no permission required
		if ($mPermRequired == null) {
			return true;
		}

		//same permission for all actions
		if (is_string($mPermRequired)) {
			if (Application::GetIdentify()->IsIdentified() == false) throw new UnidentifiedException;
			if($mPermRequired == '*') {
				return true;
			}
			else{
				return Application::GetIdentify()->hasRole($mPermRequired);
			}
		}
		#endregion

		#region Action Level
		if (!is_array($mPermRequired)) {
			throw new IdentifyException('invalid_permission_defined');
		}

		$mPermRequired = array_key_case($mPermRequired, CASE_LOWER);
		$sActionFull   = strtolower($this->_oRequest->getMethod() . '_' . $this->_oRequest->getAction());
		$sAction       = strtolower($this->_oRequest->getAction());

		if (array_key_exists($sActionFull, $mPermRequired)) {
			$mActionPerm = $mPermRequired[$sActionFull];
		} else if (array_key_exists($sAction, $mPermRequired)) {
			$mActionPerm = $mPermRequired[$sAction];
		} else {
			$mActionPerm = '*';
		}

		if($mActionPerm == null){
			return true;
		}
		else if (is_string($mActionPerm) AND $mActionPerm == '*') {
			if(Application::GetIdentify()->IsIdentified()) return true;
			throw new UnidentifiedException;
		} else {
			$mActionPerm = is_string($mActionPerm) ? preg_split('/\|,/', $mActionPerm) : (is_array($mActionPerm) ? $mActionPerm : false);

			if ($mActionPerm == false) throw new IdentifyException('invalid_permission_defined');

			//some cleanup
			$mActionPerm = array_values($mActionPerm);
			foreach ($mActionPerm AS $_k => &$_v) {
				$_v = strtolower(trim($_v));
				if (empty($_v)) unset($mActionPerm[$_k]);
			}

			return Application::GetIdentify()->hasRole($mActionPerm);
		}
		#endregion
	}
} 