<?php
/**
 * Raindrop Framework for PHP
 *
 * WeChat Mini App Component
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat;

use Raindrop\Application;
use Raindrop\Component\WeChat\Exceptions\APIRequestException;
use Raindrop\Component\WeChat\Exceptions\APIResponseException;
use Raindrop\Component\WeChat\Exceptions\InvalidAccessTokenException;
use Raindrop\Component\WeChat\Model\AccessToken;
use Raindrop\Component\WeChat\Model\SessionKey;
use Raindrop\Component\WeChat\Model\UserInfo;
use Raindrop\Exceptions\RuntimeException;
use Raindrop\Loader;
use Raindrop\Logger;

Loader::Import('Exceptions.php', __DIR__);

class WeApp
{
	protected $_sName;

	protected $_sAppId;
	protected $_sSecret;
	protected $_sAESKey;

	protected $_sSessionKey = null;
	/**
	 * @var AccessToken
	 */
	protected $_oAccessToken = null;

	public function __construct($sName, $sAppId, $sSecret, $sAESKey = null)
	{
		$this->_sName = $sName;

		$this->_sAppId  = $sAppId;
		$this->_sSecret = $sSecret;
		$this->_sAESKey = $sAESKey;
	}

	public static function VerifyAPI($sSignature, $iTimestamp, $sNonce, $sToken)
	{
		$aVerify = [$sToken, $iTimestamp, $sNonce];
		sort($aVerify, SORT_STRING);

		return sha1(implode($aVerify)) == $sSignature;
	}

	public function getAppId()
	{
		return $this->_sAppId;
	}

	public function getAppSecret()
	{
		return $this->_sSecret;
	}

	public function getAccessToken()
	{
		if($this->_oAccessToken == null OR $this->_oAccessToken->ExpireTime<=time()) {
			$oResult = $this->_apiGetRequest(sprintf(
				'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
				$this->_sAppId, $this->_sSecret));

			$oAccessToken = new AccessToken($oResult);

			$this->_oAccessToken = $oAccessToken;

			return $oAccessToken;
		}
		else{
			return $this->_oAccessToken;
		}
	}

	public function setAccessToken(AccessToken $oAccessToken)
	{
		if ($oAccessToken->ExpireTime <= time()) {
			throw new InvalidAccessTokenException('token_expired');
		}

		$this->_oAccessToken = $oAccessToken;
	}

	public function getSessionKey($sCode)
	{
		$oResult = $this->_apiGetRequest(sprintf(
			'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code',
			$this->_sAppId, $this->_sSecret, $sCode));

		$oSessionKey = new SessionKey($oResult);

		$this->_sSessionKey = $oSessionKey->SessionKey;

		return $oSessionKey;
	}

	public function setSessionKey($sSessionKey)
	{
		$this->_sSessionKey = $sSessionKey;
	}

	public function decryptData($sEncryptData, $sIv)
	{
		if ($this->_sSessionKey === null) {
			throw new RuntimeException('invalid_session_key');
		}

		return openssl_decrypt(
			base64_decode($sEncryptData),
			'AES-128-CBC',
			base64_decode($this->_sSessionKey), 1,
			base64_decode($sIv));
	}

	public function decryptUserInfo($sEncryptData, $sIv)
	{
		$sUserInfo = $this->decryptData($sEncryptData, $sIv);

		if($sUserInfo == false){
			return false;
		}

		$oUserInfo  =json_decode($sUserInfo);
		if($oUserInfo == false){
			throw new RuntimeException('userinfo_decoding:'.json_last_error_msg());
		}

		return new UserInfo($oUserInfo);
	}

	protected function _apiGetRequest($sTarget)
	{
		$rAPI = curl_init($sTarget);
		curl_setopt($rAPI, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($rAPI, CURLOPT_CONNECTTIMEOUT, 5);//request timeout in 5 sec

		$mResult = @curl_exec($rAPI);

		curl_close($rAPI);

		if (Application::IsDebugging()) {
			$aDebugBacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1];

			Logger::Message(
				$aDebugBacktrace['class'] . $aDebugBacktrace['type'] . $aDebugBacktrace['function']
				. '[' . $this->_sName . ']:request=>' . $sTarget . ', response=>' . $mResult
				. ' length=>' . strlen($mResult) . ($mResult === false ? ' error=>' . @curl_errno($rAPI) : null));
		}

		if ($mResult === false) {
			throw new APIRequestException(@curl_error($rAPI), @curl_errno($rAPI));
		}

		$mResult = json_decode($mResult);
		if ($mResult === false) {
			throw new APIResponseException(json_last_error_msg(), json_last_error());
		}

		return $mResult;
	}
}