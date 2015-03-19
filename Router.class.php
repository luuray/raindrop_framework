<?php
/**
 * Raindrop Framework for PHP
 *
 * Router
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

namespace Raindrop;


use Raindrop\Exceptions\InitializedException;
use Raindrop\Exceptions\NotImplementedException;
use Raindrop\Exceptions\NotInitializeException;

class Router
{
	/**
	 * @var null|Router
	 */
	protected static $_oInstance = null;

	/**
	 * @var null|Request
	 */
	protected $_oRequest = null;

	/**
	 * @var bool
	 */
	protected $_bIsRouted = false;

	/**
	 * @throws NotInitializeException
	 */
	public static function GetInstance()
	{
		if (self::$_oInstance === null) {
			throw new NotInitializeException();
		} else {
			return self::$_oInstance;
		}
	}

	/**
	 * @throws InitializedException
	 */
	public static function BeginRoute(Request $oRequest)
	{
		if (self::$_oInstance instanceof Router) {
			throw new InitializedException();
		} else {
			new self($oRequest);
		}
	}

	/**
	 * @throws NotImplementedException
	 */
	public static function RegisterRule()
	{
		///TODO Register Special Rule
		throw new NotImplementedException();
	}

	/**
	 * @return null|Request
	 * @throws NotImplementedException
	 */
	public static function GetRequest()
	{
		if (self::$_oInstance === null) {
			throw new NotImplementedException();
		}

		return self::$_oInstance->_oRequest;
	}

	/**
	 *
	 */
	protected function __construct(Request $oRequest)
	{
		//Load special route
		Loader::Import('config.route.php', AppDir);

		$this->_oRequest = $oRequest;

		$this->_defaultRoute();

		self::$_oInstance = $this;
	}

	protected function _defaultRoute()
	{
		$sPath =
			strtolower((!isset($_SERVER['PATH_INFO']) OR $_SERVER['PATH_INFO'] == '/') ?
				'/Default/' :
				$_SERVER['PATH_INFO']);

		$aMatch = array();
		$sType  = null;

		if ($iMatch = preg_match(
			'#^/(?<Match1>[a-z]+[a-z0-9\-_]*)(|\.(?<Match1Ext>[a-z0-9]+)|/(|(?<Match2>[a-z]+[a-z0-9\-_]*)(|\.(?<Match2Ext>[a-z0-9]+)|/(|(?<Match3>[a-z]+[a-z0-9\-_]*)(|\.(?<Match3Ext>[a-z0-9]+))))))$#i',
			$sPath, $aMatch)
		) {
			#/Module/Controller/
			#/Module/Controller/Action
			#/Module/Controller/Action.Ext
			if (array_key_exists('Match2', $aMatch) && empty($aMatch['Match2Ext']) && !empty($aMatch[6])) {
				$this->_oRequest->setModule($aMatch['Match1']);
				$this->_oRequest->setController($aMatch['Match2']);
				!empty($aMatch['Match3']) ? $this->_oRequest->setAction($aMatch['Match3']) : null;

				$sType = !empty($aMatch['Match3Ext']) ? $aMatch['Match3Ext'] : null;
			}
			#/Controller
			#/Controller/
			#/Controller/Action
			#/Controller/Action.ext
			else if (empty($aMatch['Match1Ext'])) {
				$this->_oRequest->setController($aMatch['Match1']);
				!empty($aMatch['Match2']) ? $this->_oRequest->setAction($aMatch['Match2']) : null;

				$sType = !empty($aMatch['Match2Ext']) ? $aMatch['Match2Ext'] : null;
			} #/Action.Ext
			else if (!empty($aMatch['Match1Ext'])) {
				$this->_oRequest->setAction($aMatch['Match1']);

				$sType = $aMatch['Match1Ext'];
			}

			//Result Type Detect
			if ($sType === null) {
				//detect by header
				if ($this->_oRequest->isAjax()) {
					//default ajax request type is JSON
					$this->_oRequest->setType('Json');
				} else {
					//default request type is View(HTML)
					$this->_oRequest->setType('View');
				}
			} //detect by extension
			else {
				if (in_array($sType, array('htm', 'html'))) {
					$this->_oRequest->setType('View');
				} else if (in_array($sType, array('json', 'jsonp'))) {
					$this->_oRequest->setType('Json');
				} else if ($sType == 'xml') {
					$this->_oRequest->setType('Xml');
				} //other extension make it as a download file
				else {
					$this->_oRequest->setType('File');
				}
			}
		} else {
			Logger::Warning('route_default_unmatched:' . $sPath);
		}

		//Debugger
		if (Application::IsDebugging()) {
			Debugger::Output(sprintf(
				'Mode: Default, Path: %s, MatchResult: %s, Module: %s, Controller: %s, Action: %s, Type: %s, Method: %s',
				$sPath, $iMatch, $this->_oRequest->getModule(), $this->_oRequest->getController(),
				$this->_oRequest->getAction(), $this->_oRequest->getType(), $this->_oRequest->getMethod()), 'Route');
		}
	}
} 