<?php
/**
 * Raindrop Framework for PHP
 *
 * Web Application
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop;

use Raindrop\Exceptions\FatalErrorException;
use Raindrop\Exceptions\NotInitializeException;

require_once 'Exceptions/System.php';
require_once 'Common.func.php';
require_once 'ActionResult.func.php';

abstract class Application
{
	protected static $_sAppType = '';
	protected static $_exLastException = null;
	protected static $_oInstance = null;
	protected static $_bEnableDebug = false;

	protected $_aAppArgs = null;
	protected $_sRequestId = null;
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
			try {
				return (new \ReflectionClass(get_called_class()))->newInstanceArgs(func_get_args());
			} catch (\Exception $ex) {
				@header_remove();
				@header('Uncaught exception', true, 500);
				exit();
			}
		}
	}

	public final static function DebugStart()
	{
		if (self::$_oInstance instanceof Application) {
			return self::$_oInstance;
		} else {
			register_shutdown_function(function () {
				$aError = error_get_last();

				if ($aError['type'] <= E_PARSE) {
					@header_remove();
					@http_response_code(500);
					echo json_encode($aError);
				}
			});

			try {
				self::$_bEnableDebug = true;

				//clean file stat cache
				clearstatcache();

				return (new \ReflectionClass(get_called_class()))->newInstanceArgs(func_get_args());
			} catch (\Exception $ex) {
				@header_remove();
				@header('Uncaught exception:' . $ex->getMessage(), true, 500);
				$sRequestUri =
					isset($_SERVER['HTTP_HOST']) ?
						(((empty($_SERVER['HTTPS']) OR $_SERVER['HTTPS'] == 'off') ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) :
						'Console';
				$sPost       = file_get_contents('php://input');
				$sGet        = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : 'NULL';

				if ($sRequestUri == 'Console') {
					echo <<<EXP
Message: {$ex->getMessage()} \r\n
File: {$ex->getFile()} \r\n
Line: {$ex->getLine()} \r\n
------ Trace Begin ------ \r\n
{$ex->getTraceAsString()}\r\n
------- Trace End -------\r\n
EXP;
				} else {
					echo <<<EXP
<strong>Message:</strong><pre>{$ex->getMessage()}</pre><hr>
<strong>File:</strong>{$ex->getFile()}<br>
<strong>Line:</strong>{$ex->getLine()}<br>
<strong>Trace:</strong><pre>{$ex->getTraceAsString()}<pre><hr>
<strong>Request</strong>
<strong>Uri:</strong>{$sRequestUri} @{$_SERVER['REQUEST_TIME']}
<strong>Query:</strong>{$sGet}
<strong>PostData:</strong><pre>{$sPost}</pre>
EXP;
					exit;
				}
			}
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

	/**
	 * Get Application Type
	 *
	 * @return string
	 */
	public final static function GetAppType()
	{
		return self::$_sAppType;
	}

	/**
	 * Return is Debugging Mode
	 *
	 * @return bool
	 */
	public final static function IsDebugging()
	{
		if (self::$_oInstance instanceof Application) {
			return self::$_bEnableDebug;
		} else {
			trigger_error('application_not_start', E_USER_ERROR);
			die();
		}
	}

	/**
	 * Get Request Object
	 *
	 * @return null|Request
	 * @throws NotInitializeException
	 */
	public final static function GetRequest()
	{
		if (self::$_oInstance instanceof Application) {
			return self::$_oInstance->_oRequest;
		} else {
			throw new NotInitializeException;
		}
	}

	/**
	 * Get Request Id
	 *
	 * @return null|string
	 * @throws NotInitializeException
	 */
	public final static function GetRequestId()
	{
		if (self::$_oInstance instanceof Application) {
			return self::$_oInstance->_sRequestId;
		} else {
			throw new NotInitializeException;
		}
	}

	/**
	 * Get Request Time
	 *
	 * @return null
	 * @throws NotInitializeException
	 */
	public final static function GetRequestTime()
	{
		if (self::$_oInstance instanceof Application) {
			return self::$_oInstance->_oRequest->getRequestTime();
		} else {
			throw new NotInitializeException;
		}
	}

	/**
	 * Get Identify Object
	 *
	 * @return Identify
	 * @throws NotInitializeException
	 */
	public final static function GetIdentify()
	{
		if (self::$_oInstance instanceof Application) {
			return self::$_oInstance->_oIdentify;
		} else {
			throw new NotInitializeException;
		}
	}

	/**
	 * Set Last Exception
	 *
	 * @param \Exception $ex
	 */
	public final static function SetLastException(\Exception $ex)
	{
		self::$_exLastException = $ex;
	}

	/**
	 * Get Last Exception
	 *
	 * @return null
	 */
	public final static function GetLastException()
	{
		return self::$_exLastException;
	}

	protected abstract function _initialize();

	protected abstract function _initializeIdentify();

	protected abstract function _getRequest();

	protected abstract function _run();

	protected abstract function _finish();

	public final function __construct()
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

		//Load Config
		Configuration::Load();

		//Initialize Logger
		$this->_sRequestId = isset($_SERVER['X-Request-Id']) ? $_SERVER['X-Request-Id'] : null;
		Logger::Initialize($this->_sRequestId);

		//Debug Mode
		if (self::$_bEnableDebug == true) {
			Logger::Message('---------- Request Begin ----------');
			if (str_beginwith(php_sapi_name(), 'cli')) {
				$aArgs     = $_SERVER['argv'];
				$sFileName = array_shift($aArgs);
				Logger::Message(sprintf('Console: [%s] %s', $sFileName, implode(' ', $aArgs)));
			} else {
				Logger::Message(sprintf('Request: [%s] %s', $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']));
			}

			//Flush FileCache
			del_recursive(SysRoot . '/cache');
			//Flush CacheHandlers
			Cache::Flush();
		}

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
		if (Loader::Import('bootstrap.class.php', AppDir)) {
			$oBootstrap = AppName . '\Bootstrap';
			$oBootstrap = new $oBootstrap();
			if ($oBootstrap instanceof BootstrapAbstract) {
				$this->_oBootstrap = $oBootstrap;
			} else {
				throw new FatalErrorException('bootstrap_not_match_defined');
			}
		}

		$this->_run();
	}

	public final function __destruct()
	{
		//Call Application Defined Finisher
		$this->_finish();
	}
}