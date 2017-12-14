<?php
/**
 * DTeacher
 *
 *
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: www.rainhan.net/?proj=DTeacher
 */

namespace Raindrop\Component\WeChat;

use Raindrop\Application;
use Raindrop\Component\WeChat\Exceptions\APIRequestException;
use Raindrop\Component\WeChat\Exceptions\APIResponseException;
use Raindrop\Component\WeChat\Exceptions\InvalidAccessTokenException;
use Raindrop\Component\WeChat\Model\AccessToken;
use Raindrop\Component\WeChat\Model\UserInfo;
use Raindrop\Component\WeChat\Model\WebAccessToken;
use Raindrop\Exceptions\RuntimeException;
use Raindrop\Loader;
use Raindrop\Logger;

Loader::Import('Exceptions.php', __DIR__);

class SSOLogin
{
	/**
	 * @var string
	 */
	protected $_sAppId;
	/**
	 * @var string
	 */
	protected $_sAppSecret;

	/**
	 * @var null|AccessToken
	 */
	protected $_oToken;


	public function __construct($sAppId, $sAppSecret, AccessToken $oToken = null)
	{
		$this->_sAppId     = $sAppId;
		$this->_sAppSecret = $sAppSecret;

		$this->_oToken = $oToken;
	}

	public function setAccessToken(AccessToken $oToken)
	{
		if ($oToken->ExpireTime < time()) {
			throw new InvalidAccessTokenException('expired');
		}

		$this->_oToken = $oToken;
	}

	public function getAccessToken($sCode)
	{
		try {
			$oResult = $this->_apiRequest(
				sprintf('https://api.weixin.qq.com/sns/oauth2/access_token?appid=$APPID$&secret=$SECRET$&code=%s&grant_type=authorization_code', $sCode));

			$oAccessToken = new WebAccessToken($oResult);

			$this->_oToken = $oAccessToken;

			return $oAccessToken;
		} catch (RuntimeException $ex) {
			throw $ex;
		}
	}

	public function refreshToken(AccessToken $oToken = null)
	{
		try {
			$oResult = $this->_apiRequest(
				sprintf('https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=$APPID$&grant_type=%s&refresh_token=REFRESH_TOKEN', $oToken->RefreshToken));

			$oRefreshedToken = new WebAccessToken($oResult);

			$this->_oToken = $oRefreshedToken;

			return $oRefreshedToken;
		} catch (RuntimeException $ex) {
			throw $ex;
		}
	}

	public function validate($sOpenId, AccessToken $oToken = null)
	{
		try {
			if($oToken == null){
				if($this->_oToken == null) {
					throw new RuntimeException('undefined_access_token');
				}
				else{
					$oToken = $this->_oToken;
				}
			}
			else{
				$this->setAccessToken($oToken);
			}


			$this->_apiRequest(
				sprintf('https://api.weixin.qq.com/sns/auth?access_token=%s&openid=%s', $oToken->AccessToken, $sOpenId));

			return true;
		} catch (RuntimeException $ex) {
			throw $ex;
		}
	}

	public function getUserInfo($sOpenId, AccessToken $oToken = null)
	{
		try {
			if($oToken == null){
				if($this->_oToken == null) {
					throw new RuntimeException('undefined_access_token');
				}
				else{
					$oToken = $this->_oToken;
				}
			}
			else{
				$this->setAccessToken($oToken);
			}


			$oResult = $this->_apiRequest(
				sprintf('https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s', $oToken->AccessToken, $sOpenId));

			return new UserInfo($oResult);
		} catch (RuntimeException $ex) {
			throw $ex;
		}
	}

	protected function _apiRequest($sTarget)
	{
		$sTarget = str_replace(['$APPID$', '$SECRET$'], [$this->_sAppId, $this->_sAppSecret], $sTarget);

		$rAPI = curl_init($sTarget);
		curl_setopt($rAPI, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($rAPI, CURLOPT_CONNECTTIMEOUT, 5);//request timeout in 5 sec

		$mResult = @curl_exec($rAPI);
		$sError  = curl_error($rAPI);

		curl_close($rAPI);

		if (Application::IsDebugging()) {
			Logger::Message(
				'[WeChatSSO]:request=>' . $sTarget . ', response=>' . $mResult
				. ' length=>' . strlen($mResult) . ($mResult == false ? ' error=>' . $sError : null) . ' CallStack=>' . implode('=>', backtrace()));
		}

		if ($mResult == false) {
			Logger::Warning(
				'[WeChatSSO]:request=>' . $sTarget . ', response=>' . $mResult
				. ' length=>' . strlen($mResult) . ($mResult == false ? ' error=>' . $sError : null) . ' CallStack=>' . implode('=>', backtrace()));

			throw new APIRequestException($sError);
		}

		if (empty($mResult) OR ($mDecoded = json_decode($mResult)) == false) {
			throw new APIResponseException('invalid_response');
		}

		if (property_exists($mDecoded, 'errcode') AND $mDecoded->errcode != 0) {
			Logger::Warning(
				'[WeChatSSO]:request=>(' . $sTarget . ')' . ', response=>' . $mResult
				. ' =>length: ' . strlen($mResult) . ($mResult == false ? ' error=>' . $sError : null) . ' CallStack=>' . implode('=>', backtrace()));

			throw new APIResponseException($mDecoded->errmsg, $mDecoded->errcode);
		}


		return $mDecoded;
	}
}