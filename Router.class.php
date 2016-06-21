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

	protected static $_oStaticRoute = [];

	protected $_sRequest = '';

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
	 * @param string $sRule
	 * @param null|string $sModule
	 * @param string $sController
	 * @param string $sAction
	 */
	public static function RegisterRule($sRule, $sTarget)
	{
		$sRule                       = trim($sRule);
		self::$_oStaticRoute[$sRule] = $sTarget;
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
	 * Router constructor.
	 *
	 * @param Request $oRequest
	 */
	protected function __construct(Request $oRequest)
	{
		//Load special route
		Loader::Import('config.route.php', AppDir);

		$this->_oRequest = $oRequest;
/*
		if ($this->_matchStaticRoute() == false) {
			$this->_sRequest =
				strtolower((!isset($_SERVER['PATH_INFO']) OR $_SERVER['PATH_INFO'] == '/') ? '/Default/' : $_SERVER['PATH_INFO']);
		}
*/
		if ($this->_matchStaticRoute() == false) {
			$this->_sRequest = strtolower(!isset($_SERVER['PATH_INFO']) ? null : $_SERVER['PATH_INFO']);
		}

		$this->_decodeRoute();

		self::$_oInstance = $this;
	}

	protected function _matchStaticRoute()
	{
		$sRequest = empty($_SERVER['PATH_INFO']) ? '/' : $_SERVER['PATH_INFO'];

		foreach (self::$_oStaticRoute AS $_rule => $_target) {
			if (preg_match('#' . $_rule . '#i', $sRequest)) {
				$this->_sRequest = @preg_replace('#' . $_rule . '#i', $_target, $sRequest);

				//parse query params
				if(($aQuery = parse_url($this->_sRequest, PHP_URL_QUERY))!==null){
					$this->_sRequest = parse_url($this->_sRequest, PHP_URL_PATH);
					parse_str($aQuery, $aQuery);
					$this->_oRequest->setQuery($aQuery);
				}

				if (Application::IsDebugging()) {
					Debugger::Output('RouteMatch:' . $_rule);
				}

				return true;
			}
		}
	}

	protected function _decodeRoute()
	{
		$aMatch = array();
		$sType = null;

		if($this->_sRequest == '/' OR $this->_sRequest == null){
			$this->_oRequest->setController('default');
			$this->_oRequest->setAction('index');
			$this->_oRequest->setType('View');
		}
		else if ($iMatch = preg_match(
			'#^/(?<Match1>[a-z]+[a-z0-9\-_]*)(|\.(?<Match1Ext>[a-z0-9]+)|/(|(?<Match2>[a-z]+[a-z0-9\-_]*)(|\.(?<Match2Ext>[a-z0-9]+)|/(|(?<Match3>[a-z]+[a-z0-9\-_]*)(|\.(?<Match3Ext>[a-z0-9]+))))))$#i',
			$this->_sRequest, $aMatch)
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
					$this->_oRequest->setType('FileStream');
				}
			}
		} else {
			//redirect to wildcard controller
			$this->_oRequest->setController('_');
			$this->_oRequest->setAction('_');

			Logger::Warning('route_default_unmatched:' . $this->_sRequest);
		}

		//Debugger
		if (Application::IsDebugging()) {
			Debugger::Output(sprintf(
				'Path: %s, MatchResult: %s, Module: %s, Controller: %s, Action: %s, Type: %s, Method: %s',
				$this->_sRequest, $iMatch, $this->_oRequest->getModule(), $this->_oRequest->getController(),
				$this->_oRequest->getAction(), $this->_oRequest->getType(), $this->_oRequest->getMethod()), 'Route');
		}
	}
} 