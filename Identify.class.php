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
	 * Login
	 *
	 * @param $sUser
	 * @param $sPassword
	 * @return bool
	 */
	public abstract function login($sUser, $sPassword);

	/**
	 * Logout
	 *
	 * @return bool
	 */
	public abstract function logout();

	/**
	 * Sign Up
	 *
	 * @param $sAccount
	 * @param $sPassword
	 * @param null $aProperties
	 * @return mixed
	 */
	public abstract function signUp($sAccount, $sPassword, $aProperties = null);

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