<?php
/**
 * Raindrop Framework for PHP
 *
 * Identify Abstract
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop;


use Raindrop\Exceptions\NotImplementedException;
use Raindrop\ORM\Model;

abstract class Identify
{
	protected static $_bInitialized = false;
	protected $_bIdentified = false;

	/**
	 * Initializer
	 */
	protected function _initialize()
	{

	}

	/**
	 * Get Identify Instance
	 *
	 * @return Identify
	 */
	public final static function GetInstance()
	{
		if(self::$_bInitialized != false){
			return Application::GetIdentify();
		}
		else{
			self::$_bInitialized = true;
			return (new \ReflectionClass(get_called_class()))->newInstance();
		}
	}

	/**
	 * Get Identify Status
	 *
	 * @return bool
	 */
	public abstract function IsIdentified();

	/**
	 * Has Role
	 *
	 * @param string|array $mRolesRequire
	 * @return bool
	 */
	public abstract function hasRole($mRolesRequire);

	public final function __construct()
	{
		if (self::$_bInitialized instanceof Identify) {
			throw new InitializedException;
		}

		$this->_initialize();

		$this->_loadSession();
	}

	#region Sign Operators(SignUp, SignIn, SignOut)
	/**
	 * SignUp
	 *
	 * @param $sAccount
	 * @param $sPassword
	 * @return bool|Model
	 */
	public abstract function SignUp($sAccount, $sPassword);

	/**
	 * SignIn
	 *
	 * @param string $sAccount
	 * @param string $sPassword
	 * @param null|string $sToken
	 * @return bool
	 * @throws NotImplementedException
	 */
	public abstract function SignIn($sAccount, $sPassword, $sToken=null);

	public abstract function OAuthSignIn($sApp, $sAccount, $sOAuthToken, $bEnableSession=true);

	/**
	 * SignOut
	 *
	 * @return null
	 */
	public abstract function SignOut();
	#endregion

	#region Status Methods
	/**
	 * Get TOTP Status
	 *
	 * @param $sAccount
	 * @return bool
	 */
	public abstract function GetTOTPStatus($sAccount);

	/**
	 * Is In Session
	 * 
	 * @return bool
	 * @throws NotImplementedException
	 */
	public abstract function IsInSession();
	#endregion

	#region Validators&Verificators
	/**
	 * Check Account
	 *
	 * @param $sAccount
	 * @param bool $bCheckUnique
	 * @return bool
	 * @throws NotImplementedException
	 */
	public abstract function ValidateAccount($sAccount, $bCheckUnique=false);

	/**
	 * Verify TOTPCode for Identified Account
	 *
	 * @param $sCode
	 * @return bool
	 * @throws NotImplementedException
	 */
	public abstract function VerifyTOTPCode($sCode);

	/**
	 * Verify Password for Identified Account
	 *
	 * @param $sPassword
	 * @return bool
	 * @throws NotImplementedException
	 */
	public abstract function VerifyPassword($sPassword);
	#endregion

	#region Getters
	/**
	 * Get UserId
	 */
	public abstract function GetUserId();

	/**
	 * Get Account
	 */
	public abstract function GetAccount();

	/**
	 * Get DisplayName
	 */
	public abstract function GetDisplayName();

	/**
	 * Get User Model
	 */
	public abstract function GetUser();

	/**
	 * Get User Roles
	 *
	 * @return array|null
	 */
	public abstract function GetRoles();
	#endregion

	#region User Identify
	/**
	 * Get User Identify
	 *
	 * @param mixed $mAppId
	 */
	public abstract function GetUserIdentify($mAppId=null);

	/**
	 * @param $mAppId
	 * @param null $iExpireTime
	 *
	 * @return mixed
	 */
	public abstract function CreateUserIdentify($mAppId, $iExpireTime=null);
	#endregion

	#region Session Operators
	/**
	 * Load Identify Status
	 *
	 * @return bool
	 */
	protected abstract function _loadSession();

	/**
	 * Create Identify
	 *
	 * @return mixed
	 */
	protected abstract function _createSession();

	/**
	 * Revoke Identify
	 *
	 * @return mixed
	 */
	protected abstract function _destroySession();
	#endregion
}