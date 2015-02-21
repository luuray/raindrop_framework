<?php
/**
 * Raindrop Framework for PHP
 *
 * Identify Abstract
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

use Raindrop\Component\RandomString;


abstract class Identify
{
	protected static $_oInstance = null;

	protected $_bIdentified = false;

	public static function GetInstance()
	{
		if (self::$_oInstance instanceof Identify) {
			//no action
		} else {
			self::$_oInstance = (new \ReflectionClass(get_called_class()))->newInstance();
		}

		return self::$_oInstance;
	}

	/**
	 * @return bool
	 */
	public final static function IsIdentified()
	{
		return false;
	}

	public final function __construct()
	{
		if (self::$_oInstance instanceof Identify) {
			throw new InitializedException;
		}

		$this->_initialize();

		$this->_load();
	}

	public abstract function inSession();

	/**
	 * Load Identify Status
	 * @return bool
	 */
	protected abstract function _load();

	/**
	 * Create Identify
	 *
	 * @return mixed
	 */
	protected abstract function _create();

	/**
	 * Revoke Identify
	 *
	 * @return mixed
	 */
	protected abstract function _revoke();

	protected function _initialize()
	{

	}
}

abstract class Identify_bak
{
	/**
	 * @var null|IdentifyAbstract
	 */
	protected static $_oInstance = null;
	/**
	 * @var bool Identify Symbol
	 */
	protected $_bIsIdentified = false;

	#region Static Getter
	/**
	 * @throws NotImplementedException
	 */
	public static function GetUserId()
	{
		throw new NotImplementedException();
	}

	/**
	 * @throws NotImplementedException
	 */
	public static function GetUsername()
	{
		throw new NotImplementedException();
	}

	/**
	 * @return null|IdentifyAbstract
	 */
	public final static function GetInstance()
	{
		//Create Instance
		if (self::$_oInstance === null) {
			$sIdentifyClass   = get_called_class();
			self::$_oInstance = new $sIdentifyClass;
		}

		return self::$_oInstance;
	}

	/**
	 * @return bool
	 */
	public final static function IsIdentified()
	{
		return self::GetInstance()->_bIsIdentified;
	}
	#endregion

	#region Identify Action

	/**
	 * @return bool
	 */
	protected abstract function _setIdentify();

	/**
	 * @return bool
	 */
	protected abstract function _getIdentify();

	/**
	 * @return bool
	 */
	protected abstract function _unsetIdentify();
	#endregion

	/**
	 * Check Resource Access Permission with Recent User
	 *
	 * @param string|array $mRequired Required Permission
	 * @return bool
	 */
	public abstract function hasPermission($mRequired);

	/**
	 * @return mixed
	 */
	public abstract function getMaxPrivilege();

	/**
	 * Encrypt the plaintext
	 *
	 * @param $sSource
	 * @param null $sSalt
	 * @return string
	 */
	public function Encrypt($sSource, &$sSalt = null)
	{
		if (empty($sSalt)) {
			$sSalt = (new RandomString())->GetString();
		}

		return sha1(sha1($sSource) . $sSalt);
	}

	/**
	 * Initializer
	 *
	 * @return bool
	 */
	protected abstract function _initialize();

	protected final function __construct()
	{
		$this->_bIsIdentified = $this->_getIdentify();

		$this->_initialize();
	}
}