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


use Raindrop\Application;
use Raindrop\Component\WeChat\Component\CustomerService;
use Raindrop\Component\WeChat\Component\MenuService;
use Raindrop\Component\WeChat\Component\MessageAdapter;
use Raindrop\Component\WeChat\Component\NewsService;
use Raindrop\Component\WeChat\Component\TemplateService;
use Raindrop\Component\WeChat\Model\AccessToken;
use Raindrop\Component\WeChat\Model\Message;
use Raindrop\Component\WeChat\Model\UserInfo;
use Raindrop\Component\WeChat\Model\WebAccessToken;
use Raindrop\Exceptions\InvalidArgumentException;
use Raindrop\Exceptions\RuntimeException;
use Raindrop\Logger;

/**
 * Class WeChat
 * @package Raindrop\Component\WeChat
 *
 * @property string $Name
 * @property string $Account
 * @property string $AppId
 * @property string $AppSecret
 * @property string $AESKey
 * @property string $Token
 * @property null|AccessToken $APIToken
 * @property null|WebAccessToken $WebToken
 */
class WeChat
{
	protected $_aMsgType = [
		'text'       => 'Text',
		'image'      => 'Image',
		'voice'      => 'Voice',
		'video'      => 'Video',
		'shortvideo' => 'ShortVideo',
		'location'   => 'Location',
		'link'       => 'Link'
	];
	protected $_aEventType = [
		'subscribe'   => 'SubscribeEvent',
		'unsubscribe' => 'UnsubscribeEvent',
		'scan'        => 'ScanEvent',
		'location'    => 'LocationEvent',
		'click'       => 'ClickEvent',
		'view'        => 'ViewEvent'
	];

	//config
	protected $_sName;
	protected $_sAccount;
	protected $_sAppId;
	protected $_sAppSecret;
	protected $_sAESKey;
	protected $_sToken;


	//access_token
	protected $_oAPIAccessToken = null;
	protected $_oWebAccessToken = null;

	/**
	 * WeChat constructor.
	 *
	 * @param $sName
	 * @param $sAccount
	 * @param $sAppId
	 * @param $sAppSecret
	 * @param $sToken
	 * @param null $sAESKey
	 */
	public function __construct($sName, $sAccount, $sAppId, $sAppSecret, $sToken, $sAESKey = null)
	{
		$this->_sName      = $sName;
		$this->_sAccount   = $sAccount;
		$this->_sAppId     = $sAppId;
		$this->_sAppSecret = $sAppSecret;
		$this->_sToken     = $sToken;

		$this->_sAESKey = $sAESKey;
	}

	public function __get($sKey)
	{
		if (method_exists($this, 'get' . $sKey)) {
			$sKey = 'get' . $sKey;

			return $this->$sKey();
		}

		throw new InvalidArgumentException($sKey);
	}

	#region Getters
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->_sName;
	}

	/**
	 * @return string
	 */
	public function getAccount()
	{
		return $this->_sAccount;
	}

	/**
	 * @return string
	 */
	public function getAppId()
	{
		return $this->_sAppId;
	}

	/**
	 * @return string
	 */
	public function getAppSecret()
	{
		return $this->_sAppSecret;
	}

	/**
	 * @return null|string
	 */
	public function getAESKey()
	{
		return $this->_sAESKey;
	}

	/**
	 * @return string
	 */
	public function getToken()
	{
		return $this->_sToken;
	}

	/**
	 * @return null|AccessToken
	 */
	public function getAPIToken()
	{
		return clone $this->_oAPIAccessToken;
	}

	/**
	 * @return null|WebAccessToken
	 */
	public function getWebToken()
	{
		return clone $this->_oWebAccessToken;
	}
	#endregion

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
	public function VerifyAPI($sSignature, $iTimestamp, $sNonce)
	{
		$aVerify = [$this->_sToken, $iTimestamp, $sNonce];
		sort($aVerify, SORT_STRING);

		return sha1(implode($aVerify)) == $sSignature;
	}

	#region API Access Token
	/**
	 * Set Access Token
	 *
	 * @param AccessToken $oToken
	 *
	 * @throws RuntimeException
	 */
	public function setAPIAccessToken(AccessToken $oToken)
	{
		if ($oToken->ExpireTime <= time()) {
			throw new RuntimeException('token_expire');
		}

		$this->_oAPIAccessToken = $oToken;
	}

	/**
	 * Get Access Token from Server
	 *
	 * @return AccessToken
	 * @throws RuntimeException
	 */
	public function getAPIAccessToken()
	{
		try {
			$oResult = $this->ApiGetRequest(sprintf(
				'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
				$this->_sAppId, $this->_sAppSecret));

			if (property_exists($oResult, 'errcode')) {
				throw new RuntimeException($oResult->errmsg, $oResult->errcode);
			}

			return new AccessToken($oResult);
		} catch (RuntimeException $ex) {
			throw new RuntimeException('get_api_access_token:' . $ex->getMessage());
		}
	}
	#endregion

	#region Message Process
	/**
	 * Decode Received Message
	 *
	 * @param $sMessage
	 *
	 * @return Message
	 *
	 * @throws RuntimeException
	 */
	public function receivedMessage($sMessage)
	{
		$oDocument = @simplexml_load_string($sMessage, 'SimpleXMLElement', LIBXML_NOCDATA);

		if ($oDocument instanceof \SimpleXMLElement) {
		} else {
			throw new RuntimeException('decode_failed');
		}

		$aData         = get_object_vars($oDocument);
		$sMessageModel = null;
		$iMsgId        = null;

		if (isset($aData['MsgType'], $aData['ToUserName'], $aData['FromUserName'])) {
			$sMsgType = strtolower($aData['MsgType']);

			if ($sMsgType == 'event') {
				//event message
				if (!isset($aData['Event'])) {
					throw new RuntimeException('missing_event_type');
				}

				$sEvent = strtolower($aData['Event']);
				if (isset($this->_aEventType[$sEvent])) {
					$sMessageModel = $this->_aEventType[$sEvent];
				} else {
					throw new RuntimeException('undefined_event');
				}

			} else {
				//normal message
				if (!isset($aData['MsgId'])) {
					throw new RuntimeException('missing_msg_id');
				}
				$iMsgId = $aData['MsgId'];
				if (isset($this->_aMsgType[$sMsgType])) {
					$sMessageModel = $this->_aMsgType[$sMsgType];
				}
			}
		} else {
			throw new RuntimeException('messing_param');
		}

		$oRefClass = new \ReflectionClass('Raindrop\Component\WeChat\Message\\' . $sMessageModel);
		$oMessage  = $oRefClass->newInstance(
			$aData['FromUserName'],
			$aData['ToUserName'],
			$aData['CreateTime'],
			$iMsgId,
			array_diff_key($aData, [
				'FromUserName' => '',
				'ToUserName'   => '',
				'CreateTime'   => '',
				'MsgId'        => '']));
		if ($oMessage instanceof Message) {
			return $oMessage;
		} else {
			throw new RuntimeException('undefined');
		}
	}

	/**
	 * @param Message $oMessage
	 */
	public function sendMessage(Message $oMessage)
	{

	}

	/**
	 * @return CustomerService
	 */
	public function customerService()
	{
		return new CustomerService($this);
	}

	/**
	 * @return NewsService
	 */
	public function newsService()
	{
		return new NewsService($this);
	}

	/**
	 * @return TemplateService
	 */
	public function templateService()
	{
		return new TemplateService($this);
	}

	/**
	 * @return MenuService
	 */
	public function menuService()
	{
		return new MenuService($this);
	}

	public function getMessageAdapter()
	{
		return new MessageAdapter($this);
	}
	#endregion

	#region Web Token, Web UserInfo
	/**
	 * @param $sRedirect
	 * @param bool $bUserInfo
	 * @param null $sState
	 *
	 * @return object
	 */
	public function webAuthRedirect($sRedirect, $bUserInfo = false, $sState = null)
	{
		if ($bUserInfo == true) {
			return Redirect(sprintf(
				'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_userinfo&state=%s#wechat_redirect',
				$this->_sAppId, urlencode($sRedirect), urlencode($sState)));
		} else {
			return Redirect(sprintf(
				'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base&state=%s#wechat_redirect',
				$this->_sAppId, urlencode($sRedirect), urlencode($sState)));
		}
	}

	/**
	 * @param $sCode
	 *
	 * @return WebAccessToken
	 * @throws RuntimeException
	 */
	public function getWebAccessToken($sCode)
	{
		try {
			$oResult = $this->ApiGetRequest(sprintf(
				'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code',
				$this->_sAppId, $this->_sAppSecret, $sCode));

			if (property_exists($oResult, 'errcode')) {
				throw new RuntimeException($oResult->errmsg, $oResult->errcode);
			}

			return new WebAccessToken($oResult);
		} catch (RuntimeException $ex) {
			throw new RuntimeException('get_web_access_token:' . $ex->getMessage());
		}
	}

	/**
	 * @param $sRefreshToken
	 *
	 * @return WebAccessToken
	 * @throws RuntimeException
	 */
	public function refreshWebAccessToken($sRefreshToken)
	{
		try {
			$oResult = $this->ApiGetRequest(sprintf(
				'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=%s&grant_type=refresh_token&refresh_token=%s',
				$this->_sAppId, $sRefreshToken));

			if (property_exists($oResult, 'errcode')) {
				throw new RuntimeException($oResult->errmsg, $oResult->errcode);
			}

			return new WebAccessToken($oResult);
		} catch (RuntimeException $ex) {
			throw new RuntimeException('get_access_token_web:' . $ex->getMessage());
		}
	}

	/**
	 * @param $sToken
	 * @param $sUserId
	 *
	 * @return bool
	 * @throws RuntimeException
	 */
	public function verifyWebAccessToken($sToken, $sUserId)
	{
		try {
			$oResult = $this->ApiGetRequest(sprintf(
				'https://api.weixin.qq.com/sns/auth?access_token=%s&openid=%s',
				$sToken, $sUserId));

			return property_exists($oResult, 'errcode') && $oResult->errcode == 0 ? true : false;
		} catch (RuntimeException $ex) {
			throw new RuntimeException('verify_web_access_token:' . $ex->getMessage());
		}
	}

	/**
	 * @param $sToken
	 * @param $sUserId
	 *
	 * @return UserInfo
	 * @throws RuntimeException
	 */
	public function getUserInfo($sToken, $sUserId)
	{
		try {
			$oResult = $this->ApiGetRequest(sprintf(
				'https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s&lang=zh_CN',
				$sToken, $sUserId));

			return new UserInfo($oResult);
		} catch (RuntimeException $ex) {
			throw new RuntimeException('get_user_info:' . $ex->getMessage());
		}
	}

	#endregion

	#region Api Request
	/**
	 * @param $sTarget
	 *
	 * @return mixed
	 * @throws RuntimeException
	 */
	public function ApiGetRequest($sTarget)
	{
		$rAPI = curl_init($sTarget);
		curl_setopt($rAPI, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($rAPI, CURLOPT_CONNECTTIMEOUT, 5);//request timeout in 5 sec
		$mResult = @curl_exec($rAPI);

		if (Application::IsDebugging()) {
			$aDebugBacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1];

			Logger::Message(
				$aDebugBacktrace['class'] . $aDebugBacktrace['type'] . $aDebugBacktrace['function']
				. '[' . $this->_sName . ']:request=>' . $sTarget . ', response=>' . $mResult . ' =>length: ' . strlen($mResult));
		}

		if (empty($mResult) OR ($mResult = json_decode($mResult)) == false) {
			throw new RuntimeException('invalid_response');
		}

		return $mResult;
	}

	/**
	 * @param $sTarget
	 * @param $sContent
	 *
	 * @return mixed
	 * @throws RuntimeException
	 */
	public function ApiPostRequest($sTarget, $sContent)
	{
		$rAPI = curl_init($sTarget);
		curl_setopt($rAPI, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($rAPI, CURLOPT_POST, true);
		curl_setopt($rAPI, CURLOPT_CONNECTTIMEOUT, 5);//request timeout in 5 sec
		curl_setopt($rAPI, CURLOPT_POSTFIELDS, $sContent);

		$mResult = @curl_exec($rAPI);

		if (Application::IsDebugging()) {
			$aDebugBacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1];

			Logger::Message(
				$aDebugBacktrace['class'] . $aDebugBacktrace['type'] . $aDebugBacktrace['function']
				. '[' . $this->_sName . ']:request=>(' . $sTarget . ')' . $sContent . ', response=>' . $mResult . ' =>length: ' . strlen($mResult));
		}

		if (empty($mResult) OR ($mResult = json_decode($mResult, true)) == false) {
			throw new RuntimeException('invalid_response');
		}

		if (isset($mResult['errcode']) AND $mResult['errcode'] != 0) {
			return false;
		}

		return $mResult;
	}
	#endregion
}