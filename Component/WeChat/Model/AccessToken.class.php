<?php
/**
 * Raindrop Framework for PHP
 *
 * Wechat Access Token Model
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Model;


use Raindrop\Exceptions\NotImplementedException;

/**
 * Class AccessToken
 * @package Raindrop\Component\WeChat\Model
 *
 * @property string $AccessToken
 * @property int $ExpireTime
 * @property int $ExpiresIn
 */
class AccessToken implements \Serializable,\JsonSerializable
{
	protected $_sAccessToken;
	protected $_iExpiresIn;
	protected $_iExpireTime;

	public function __construct($oObj)
	{
		$this->_sAccessToken = $oObj->access_token??$oObj->ticket;
		$this->_iExpiresIn = $oObj->expires_in;
		$this->_iExpireTime = $oObj->expires_in + time();
	}

	public function __get($sKey)
	{
		$sKey = strtolower($sKey);

		switch ($sKey){
			case 'access_token':
			case 'accesstoken':
				return $this->_sAccessToken;
			case 'expires_in':
			case 'expiresin':
				return $this->_iExpiresIn;
			case 'expire_time':
			case 'expiretime':
				return $this->_iExpireTime;
			default:
				return null;
		}
	}

	public function __set($sKey, $mValue)
	{
		throw new NotImplementedException();
	}

	/**
	 * @return string
	 */
	public function serialize()
	{
		return serialize([
			'access_token'=>$this->_sAccessToken,
			'expires_in'=>$this->_iExpiresIn,
			'expire_time'=>$this->_iExpireTime
		]);
	}

	/**
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		$aResult = @unserialize($serialized);

		$this->_sAccessToken = $aResult['access_token'];
		$this->_iExpiresIn = $aResult['expires_in'];
		$this->_iExpireTime = $aResult['expire_time'];
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'access_token'=>$this->_sAccessToken,
			'expires_in'=>$this->_iExpiresIn,
			'expire_time'=>$this->_iExpireTime
		];
	}
}