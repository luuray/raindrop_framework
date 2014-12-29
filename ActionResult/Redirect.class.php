<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result to Redirect
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2014, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\ActionResult;

use Raindrop\ActionResult;
use Raindrop\Application;
use Raindrop\ArgumentNullException;
use Raindrop\InvalidArgumentException;
use Raindrop\NotImplementedException;

class Redirect extends ActionResult
{
	protected $_sTarget = null;

	/**
	 * Create a ActionResult Object
	 * !!! if first argument begin with "http://" or "https://" will redirect to "outside site" target !!!
	 * Args:
	 *  action
	 *  controller, action
	 *  module, controller, action
	 *  module, controller, action, params
	 * @throws InvalidArgumentException
	 */
	public function __construct()
	{
		$iArgsNum = func_num_args();
		$aArgs    = func_get_args();

		if ($iArgsNum == 0) {
			throw new InvalidArgumentException();
		}

		//jump out, jump out with queryString, recent module-controller's action, recent
		if ($iArgsNum <= 2) {
			if (str_beginwith($aArgs[0], 'http://') || str_beginwith($aArgs[0], 'https://')) {
				$this->_sTarget = $aArgs[0];

				if ($iArgsNum == 2) {
					$aParam = array();
					parse_str(parse_url($aArgs[0], PHP_URL_QUERY), $aParam);
					list($this->_sTarget) = explode('?', $aArgs[0]);
					$aParam = is_array($aParam) ? array_merge_replace($aParam, $aArgs[1]) : $aArgs[1];
					foreach ($aParam AS $_k => $_v) {
						$sParams[] = urlencode($_k) . '=' . urlencode($_v);
					}
					$this->_sTarget = $this->_sTarget . '?' . implode('&', $sParams);
				}
			} else {
				if ($iArgsNum == 1) {
					$this->_sTarget = Application::GetRequest()->getBaseUri() . '/' . $aArgs[0];
				} else {
					if ($this->_nameChecker($aArgs[0]) == false) {
						throw new InvalidArgumentException('redirect_controller_name');
					}
					if ($this->_nameChecker($aArgs[1]) == false) {
						throw new InvalidArgumentException('redirect_controller_action_name');
					}

					$this->_sTarget = $this->_toController($aArgs[0], $aArgs[1]);
				}
			}
		} //module controller action
		else if ($iArgsNum == 3) {

			$this->_sTarget = $this->_toModule($aArgs[0], $aArgs[1], $aArgs[2], null);
		} //fully defined with parameters or use null to skip module/controller define
		else if ($iArgsNum == 4) {
			if ($aArgs[0] !== null && $this->_nameChecker($aArgs[0]) == false) {
				throw new InvalidArgumentException('redirect_module_name');
			}
			if ($aArgs[1] !== null && $this->_nameChecker($aArgs[1] == false)) {
				throw new InvalidArgumentException('redirect_controller_name');
			}
			if ($aArgs[2] !== null && $this->_nameChecker($aArgs[2] == false)) {
				throw new InvalidArgumentException('redirect_action_name');
			}
			//check parameter's subject
			$sParams = null;
			if (!empty($aArgs[3])) {
				var_dump($aArgs[3]);
				if (!is_array($aArgs[3])) {
					throw new InvalidArgumentException('redirect_target_parameter');
				} else {
					foreach ($aArgs[3] AS $_k => $_v) {
						$sParams[] = urlencode($_k) . '=' . urlencode($_v);
					}
					$sParams = implode('&', $sParams);
				}
			}

			//skip module and controller
			if ($aArgs[0] === null && $aArgs[1] === null) {
				$this->_sTarget = $this->_toAction($aArgs[2], $sParams);
			} //skip module
			else if ($aArgs[0] == null) {
				$this->_sTarget = $this->_toController($aArgs[1], $aArgs[2], $sParams);
			} //fully defined
			else {
				$this->_sTarget = $this->_toModule($aArgs[0], $aArgs[1], $aArgs[2], $sParams);
			}
		} //undefined
		else {
			throw new ArgumentNullException('parameter_to_much');
		}
	}

	/**
	 * Output Result
	 *
	 * @return mixed
	 */
	public function Output()
	{
		//echo 'redirect:' . $this->_sTarget;
		if (headers_sent()) {
			$oResult         = new View('/shared/redirect.phtml');
			$oResult->Target = $this->_sTarget;
			$oResult->output();
		} else {
			header('Location: ' . $this->_sTarget);
		}
	}

	public function toString()
	{
		throw new NotImplementedException();
	}

	/**
	 * To Recent Controller's Action
	 *
	 * @param string $sActName Action Name
	 * @param null|string $sParam Parameters
	 * @return string
	 */
	protected function _toAction($sActName, $sParam = null)
	{
		$oRequest = Application::GetRequest();
		$aStack   = array();

		$oRequest->getModule() != null ? ($aStack[] = $oRequest->getModule()) : null;
		$aStack[] = $oRequest->getController();
		$aStack[] = $sActName . ($sParam == null ? null : '?' . $sParam);

		return $oRequest->getBaseUri() . '/' . implode('/', $aStack);
	}

	/**
	 * To Recent Module's Controller's Action
	 *
	 * @param string $sCtrl Controller Name
	 * @param string $sAct Action NAme
	 * @param null|string $sParam Parameters
	 * @return string
	 */
	protected function _toController($sCtrl, $sAct, $sParam = null)
	{
		$oRequest = Application::GetRequest();
		$aStack   = array();

		$oRequest->getModule() != null ? ($aStack[] = $oRequest->getModule()) : null;
		$aStack[] = $sCtrl;
		$aStack[] = $sAct . ($sParam == null ? null : '?' . $sParam);

		return $oRequest->getBaseUri() . '/' . implode('/', $aStack);
	}

	/**
	 * To Full-Defined Target
	 *
	 * @param null|string $sModule Controller Name
	 * @param string $sCtrl Controller Name
	 * @param string $sAct Action Name
	 * @param null|string $sParam Parameters
	 * @return string
	 */
	protected function _toModule($sModule, $sCtrl, $sAct, $sParam = null)
	{
		///TODO Better Default Module Progress
		strtolower($sModule) != 'default' ? ($aStack[] = $sModule) : null;
		$aStack[] = $sCtrl;
		$aStack[] = $sAct . ($sParam == null ? null : '?' . $sParam);

		return Application::GetRequest()->getBaseUri() . '/' . implode('/', $aStack);
	}

	/**
	 * Check Subject Name
	 *
	 * @param $sName
	 * @return bool
	 */
	protected function _nameChecker($sName)
	{
		return preg_match('/^[a-z]+[a-z0-9\-_]*$/i', $sName) == 1;
	}
} 