<?php
/**
 * Raindrop Framework for PHP
 *
 * Web Application
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

use Raindrop\Exceptions\NotInitializeException;

require_once CorePath.'/Exceptions/System.php';
require_once 'Common.func.php';
require_once 'ActionResult.func.php';

abstract class Application
{
	protected static $_sAppType = '';
	protected static $_exLastException = null;
	protected static $_oInstance = null;
	protected static $_bEnableDebug = false;

	/**
	 * @var null|Identify
	 */
	protected $_oIdentify = null;
	/**
	 * @var null|Dispatcher
	 */
	protected $_oDispatcher = null;
	/**
	 * @var null|BootstrapAbstract
	 */
	protected $_oBootstrap = null;

	/**
	 * @var null|Request
	 */
	protected $_oRequest = null;

	public final static function Start()
	{
		if (self::$_oInstance instanceof Application) {
			return self::$_oInstance;
		} else {
			$sAppType = get_called_class();
			new $sAppType();
		}
	}

	public final static function DebugStart()
	{
		if (self::$_oInstance instanceof Application) {
			return self::$_oInstance;
		} else {
			self::$_bEnableDebug = true;

			//clean file stat cache
			clearstatcache();

			ob_start();

			$sAppType = get_called_class();
			new $sAppType();
		}
	}

	public final static function GetInstance()
	{
		if (self::$_oInstance instanceof Application) {
			return self::$_oInstance;
		} else {
			throw new NotInitializeException;
		}
	}

	public final static function GetAppType()
	{
		return self::$_sAppType;
	}

	public final static function IsDebugging()
	{
		if (self::$_oInstance instanceof Application) {
			return self::$_bEnableDebug;
		} else {
			trigger_error('application_not_start', E_USER_ERROR);
			die();
		}
	}

	public final static function GetRequest()
	{
		if (self::$_oInstance instanceof Application) {
			return self::$_oInstance->_oRequest;
		} else {
			throw new NotInitializeException;
		}
	}

	public final static function GetRequestTime()
	{
		if (self::$_oInstance instanceof Application) {
			return self::$_oInstance->_oRequest->getRequestTime();
		} else {
			throw new NotInitializeException;
		}
	}

	public final static function GetIdentify()
	{
		if (self::$_oInstance instanceof Application) {
			return self::$_oInstance->_oIdentify;
		} else {
			throw new NotInitializeException;
		}
	}

	public final static function SetLastException(\Exception $ex)
	{
		self::$_exLastException = $ex;
	}

	public final static function GetLastException()
	{
		return self::$_exLastException;
	}

	protected abstract function _initialize();

	protected abstract function _initializeIdentify();

	protected abstract function _getRequest();

	protected abstract function _finish();

	protected final function __construct()
	{
		if (self::$_oInstance instanceof Application) {
			throw new FatalErrorException('application_started');
		}

		//Decide Application Type
		self::$_sAppType = str_replace('Raindrop\\', '', get_called_class());

		//Initialize Loader
		require_once 'Loader.class.php';
		Loader::Initialize();

		self::$_oInstance = $this;

		//Debug Mode
		if (self::$_bEnableDebug == true) {
			//Flush FileCache
			del_recursive(SysRoot.'/cache');
		}

		//Load Config
		Configuration::Load();

		//Initialize Logger
		Logger::Initialize();

		//Initialize Session
		Session::Start();

		//Call Application Defined Initializer
		$this->_initialize();

		//Get Request
		$this->_oRequest = $this->_getRequest();
		//Set RequestTime
		if (defined('SYS_REQUEST_TIME') == false) {
			define('SYS_REQUEST_TIME', Application::GetRequestTime());
		}

		//Initialize Identifier
		$this->_initializeIdentify();

		//Get Bootstrap
		try {
			if (Loader::Import('bootstrap.class.php', AppDir)) {
				$oBootstrap = AppName . '\Bootstrap';
				$oBootstrap = new $oBootstrap();
				if ($oBootstrap instanceof BootstrapAbstract) {
					$this->_oBootstrap = $oBootstrap;
				} else {
					throw new FatalErrorException('bootstrap_not_match_defined');
				}
			}
		} catch (FileNotFoundException $ex) {

		}

		//Prepare to Begin Route
		$this->_oBootstrap->beforeRoute(self::$_oInstance, $this->_oDispatcher);
		//Begin Route
		Router::BeginRoute($this->_oRequest);
		//After Route
		$this->_oBootstrap->afterRoute(self::$_oInstance, $this->_oDispatcher);

		//Dispatcher Initialize
		$this->_oDispatcher = Dispatcher::GetInstance();


		//Prepare to Begin Dispatch
		$this->_oBootstrap->beforeDispatch(self::$_oInstance, $this->_oDispatcher);
		//Dispatch
		$this->_oDispatcher->dispatch();
		//After Dispatch
		$this->_oBootstrap->afterDispatch(self::$_oInstance, $this->_oDispatcher);

		//Output Result
		$this->_oDispatcher->outputResult();
	}

	public final function __destruct()
	{
		//Call Application Defined Finisher
		$this->_finish();
	}
}