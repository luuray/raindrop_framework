<?php
/**
 * Raindrop Framework for PHP
 *
 * WeChat Model of Web Access Token
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Model;


use Raindrop\Exceptions\InvalidArgumentException;

/**
 * Class WebAccessToken
 * @package Raindrop\Component\WeChat\Model
 *
 * @property string $AccessToken
 * @property string $RefreshToken
 * @property string $OpenId
 * @property string $Scope
 * @property string $UnionId
 * @property int $ExpiresIn
 * @property int $ExpireTime
 */
class WebAccessToken implements \Serializable, \JsonSerializable
{
	protected $_sAccessToken;
	protected $_sRefreshToken;
	protected $_sOpenId;
	protected $_sScope;
	protected $_sUnionId;
	protected $_iExpiresIn;
	protected $_iExpireTime;

	public function __construct($oObj)
	{
		$this->_sAccessToken  = $oObj->access_token;
		$this->_sRefreshToken = $oObj->refresh_token;
		$this->_sOpenId       = $oObj->openid;
		$this->_sScope        = $oObj->scope;
		$this->_sUnionId      = property_exists($oObj, 'unionid') ? $oObj->unionid : null;
		$this->_iExpiresIn    = $oObj->expires_in;
		$this->_iExpireTime   = $oObj->expires_in + time();
	}

	public function __get($sKey)
	{
		$sKey = strtolower($sKey);

		switch ($sKey) {
			case 'access_token':
			case 'accesstoken':
				return $this->_sAccessToken;
			case 'refresh_token':
			case 'refreshtoken':
				return $this->_sRefreshToken;
			case 'open_id':
			case 'openid':
				return $this->_sOpenId;
			case 'scope':
				return $this->_sScope;
			case 'union_id':
			case 'unionid':
				return $this->_sUnionId;
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
		$sKey = strtolower($sKey);
		switch ($sKey) {
			case 'access_token':
			case 'accesstoken':
				$this->_sAccessToken = $mValue;
				break;
			case 'refresh_token':
			case 'refreshtoken':
				$this->_sRefreshToken = $mValue;
				break;
			case 'expires_in':
			case 'expiresin':
				$this->_iExpiresIn  = (int)$mValue;
				$this->_iExpireTime = (int)$mValue + time();
				break;
			default:
				throw new InvalidArgumentException($sKey);
		}
	}

	/**
	 * @return string
	 */
	public function serialize()
	{
		return serialize([
			'access_token'  => $this->_sAccessToken,
			'refresh_token' => $this->_sRefreshToken,
			'open_id'       => $this->_sOpenId,
			'scope'         => $this->_sScope,
			'union_id'      => $this->_sUnionId,
			'expires_in'    => $this->_iExpiresIn,
			'expire_time'   => $this->_iExpireTime
		]);
	}

	/**
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		$aResult = @unserialize($serialized);

		$this->_sAccessToken  = $aResult['access_token'];
		$this->_sRefreshToken = $aResult['refresh_token'];
		$this->_sOpenId       = $aResult['open_id'];
		$this->_sScope        = $aResult['scope'];
		$this->_sUnionId      = $aResult['union_id'];
		$this->_iExpiresIn    = $aResult['expires_in'];
		$this->_iExpireTime   = $aResult['expire_time'];
	}

	/**
	 * @return array
	 */
	function jsonSerialize()
	{
		return [
			'access_token'  => $this->_sAccessToken,
			'refresh_token' => $this->_sRefreshToken,
			'open_id'       => $this->_sOpenId,
			'scope'         => $this->_sScope,
			'union_id'      => $this->_sUnionId,
			'expires_in'    => $this->_iExpiresIn,
			'expire_time'   => $this->_iExpireTime
		];
	}
}