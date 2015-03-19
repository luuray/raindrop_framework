<?php
/**
 * Raindrop Framework for PHP
 *
 * Identify Interface
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

namespace Raindrop\Interfaces;


interface IIdentify
{
	/**
	 * @return mixed
	 */
	public static function GetUserId();

	/**
	 * @return mixed
	 */
	public static function GetAccount();

	/**
	 * @param $sIdentify
	 * @param $sPassword
	 * @return mixed
	 */
	public static function SignIn($sIdentify, $sPassword);

	/**
	 * @return mixed
	 */
	public static function SignOut();

	/**
	 * @param $sAccount
	 * @param $sPassword
	 * @param $aParams
	 * @return mixed
	 */
	public static function SignUp($sAccount, $sPassword, $aParams);

	public function getRoles();

	public function hasRole($mRole);
} 