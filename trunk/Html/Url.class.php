<?php
/**
 * Raindrop Framework for PHP
 *
 * Url Parser
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
namespace Raindrop\Html;

use Raindrop\Application;
use Raindrop\Exceptions\InvalidArgumentException;

class Url extends Html
{
	protected static $_oInstance = null;


	protected static function _GetInstance()
	{
		if ((self::$_oInstance instanceof Url) == false) {
			new self();
		}

		return self::$_oInstance;
	}

	protected function __construct()
	{
		self::$_oInstance = $this;
	}

	/**
	 *
	 */
	public static function Action()
	{
		$iArgsNum  = func_num_args();
		$aArgs     = func_get_args();
		$oInstance = self::_GetInstance();

		if ($iArgsNum == 0) {
			throw new InvalidArgumentException();
		}

		if ($iArgsNum == 1) {
			if ($oInstance->_nameChecker($aArgs[0]) == false) {
				throw new InvalidArgumentException('redirect_action_name');
			}

			return $oInstance->_toAction($aArgs[0]);
		} //default module's controller and action
		else if ($iArgsNum == 2) {
			if ($oInstance->_nameChecker($aArgs[0]) == false) {
				throw new InvalidArgumentException('redirect_controller_name');
			}
			if ($oInstance->_nameChecker($aArgs[1]) == false) {
				throw new InvalidArgumentException('redirect_controller_action_name');
			}

			return $oInstance->_toController($aArgs[0], $aArgs[1]);
		} //module controller action
		else if ($iArgsNum == 3) {

			return $oInstance->_toModule($aArgs[0], $aArgs[1], $aArgs[2], null);
		} //fully defined with parameters or use null to skip module/controller define
		else if ($iArgsNum == 4) {
			if ($aArgs[0] !== null && $oInstance->_nameChecker($aArgs[0]) == false) {
				throw new InvalidArgumentException('redirect_module_name');
			}
			if ($aArgs[1] !== null && $oInstance->_nameChecker($aArgs[1] == false)) {
				throw new InvalidArgumentException('redirect_controller_name');
			}
			if ($aArgs[2] !== null && $oInstance->_nameChecker($aArgs[2] == false)) {
				throw new InvalidArgumentException('redirect_action_name');
			}
			//check parameter's subject
			$sParams = null;
			if (!empty($aArgs[3])) {
				//var_dump($aArgs[3]);
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
				return $oInstance->_toAction($aArgs[2], $sParams);
			} //skip module
			else if ($aArgs[0] == null) {
				return $oInstance->_toController($aArgs[1], $aArgs[2], $sParams);
			} //fully defined
			else {
				return $oInstance->_toModule($aArgs[0], $aArgs[1], $aArgs[2], $sParams);
			}


		} //undefined
		else {
			throw new InvalidArgumentException('parameter_to_much');
		}
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
	 * To Default Module's Controller's Action
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

		//$oRequest->getModule() != null ? ($aStack[] = $oRequest->getModule()) : null;
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
		($sModule != null OR strtolower($sModule) != 'default') ? ($aStack[] = $sModule) : null;
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