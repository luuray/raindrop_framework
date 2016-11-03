<?php
/**
 * Raindrop Framework for PHP
 *
 * WeChat Component
 *
 * @author Luuray
 * @copyright Rainhan System
 * @Id $Id$
 *
 * Copyright (c) 2010-2016, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat;


class WeChat
{
	protected $_sName;
	protected $_sAppId;
	protected $_sAppSecret;
	protected $_sToken;

	//flag
	protected $_sAESKey;
	protected $_bEncrypt;

	//access_token
	protected $_sAccessToken;

	/**
	 * API Verification
	 *
	 * @param string $sToken
	 * @param string $sSignature
	 * @param int $iTimestamp
	 * @param string $sNonce
	 *
	 * @return bool
	 */
	public static function VerifyAPI($sToken, $sSignature, $iTimestamp, $sNonce)
	{
		$aVerify = [$sToken, $iTimestamp, $sNonce];
		sort($aVerify);

		return sha1(implode($aVerify)) == $sSignature;
	}

	public function __construct($sName, $sAppId, $sAppSecret, $sToken, $sAESKey=null, $bEncrypt=false)
	{
		$this->_sName      = $sName;
		$this->_sAppId     = $sAppId;
		$this->_sAppSecret = $sAppSecret;
		$this->_sToken     = $sToken;

		$this->_sAESKey  = $sAESKey;
		$this->_bEncrypt = $bEncrypt;
	}

	public function setToken($sToken, $iExpireTime)
	{
	}

	protected function _getAccessToken()
	{
		//https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET
		$rAPI = curl_init(sprintf(
			'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
			$this->_sAppId, $this->_sAppSecret));
	}
	public function getSignature()
	{
	}
}