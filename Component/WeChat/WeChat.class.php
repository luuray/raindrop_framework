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
use Raindrop\Exceptions\RuntimeException;
use Raindrop\Logger;

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
	protected $_sToken;
	protected $_sAESKey;

	//access_token
	protected $_oAPIAccessToken;
	protected $_oWebAccessToken;

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

	/**
	 * @return mixed
	 */
	public function getAppId()
	{
		return $this->_sAppId;
	}

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
		sort($aVerify);

		return sha1(implode($aVerify)) == $sSignature;
	}

	#region Message Process
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
		$rAPI = curl_init(sprintf(
			'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
			$this->_sAppId, $this->_sAppSecret));
		curl_setopt($rAPI, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($rAPI, CURLOPT_CONNECTTIMEOUT, 5);//request timeout in 5 sec
		$mResult = curl_exec($rAPI);

		if (Application::IsDebugging()) {
			Logger::Message('wechat_access_key_api[' . $this->_sName . ']:' . $mResult);
		}

		if ($mResult == false) {
			throw new RuntimeException('get_access_token_api');
		}

		$mResult = json_decode($mResult);
		if (!is_object($mResult)) {
			throw new RuntimeException('get_access_token_api:decode');
		}

		if (property_exists($mResult, 'errcode')) {
			throw new RuntimeException('get_access_token_api:' . $mResult->errmsg, $mResult->errcode);
		}

		return new AccessToken($mResult);
	}

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
		if ($oDocument == false) {
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
			array_diff($aData, [
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
		return new CustomerService($this->_oAPIAccessToken, $this->_sAccount, $this->_sAppId, $this->_sAppSecret, $this->_sAESKey);
	}

	/**
	 * @return NewsService
	 */
	public function newsService()
	{
		return new NewsService($this->_oAPIAccessToken, $this->_sAccount, $this->_sAppId, $this->_sAppSecret, $this->_sAESKey);
	}

	/**
	 * @return TemplateService
	 */
	public function templateService()
	{
		return new TemplateService($this->_oAPIAccessToken, $this->_sAccount, $this->_sAppId, $this->_sAppSecret, $this->_sAESKey);
	}

	/**
	 * @return MenuService
	 */
	public function menuService()
	{
		return new MenuService($this->_oAPIAccessToken, $this->_sAccount, $this->_sAppId, $this->_sAppSecret, $this->_sAESKey);
	}

	public function getMessageAdapter()
	{
		return new MessageAdapter($this->_oAPIAccessToken, $this->_sAccount, $this->_sAppId, $this->_sAppSecret, $this->_sAESKey);
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
		$rAPI = curl_init(sprintf(
			'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code',
			$this->_sAppId, $this->_sAppSecret, $sCode));
		curl_setopt($rAPI, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($rAPI, CURLOPT_CONNECTTIMEOUT, 5);//request timeout in 5 sec
		$mResult = curl_exec($rAPI);

		if (Application::IsDebugging()) {
			Logger::Message('wechat_get_access_key_web[' . $this->_sName . ']:' . $mResult);
		}

		if (empty($mResult) OR ($mResult = json_decode($mResult)) == false) {
			throw new RuntimeException('get_access_token_web');
		}

		if (property_exists($mResult, 'errcode')) {
			throw new RuntimeException('get_access_token_web:' . $mResult->errmsg, $mResult->errcode);
		}

		return new WebAccessToken($mResult);
	}

	/**
	 * @param $sRefreshToken
	 *
	 * @return WebAccessToken
	 * @throws RuntimeException
	 */
	public function refreshWebAccessToken($sRefreshToken)
	{
		$rAPI = curl_init(sprintf(
			'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=%s&grant_type=refresh_token&refresh_token=%s',
			$this->_sAppId, $sRefreshToken));
		curl_setopt($rAPI, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($rAPI, CURLOPT_CONNECTTIMEOUT, 5);//request timeout in 5 sec
		$mResult = curl_exec($rAPI);

		if (Application::IsDebugging()) {
			Logger::Message('wechat_refresh_access_key_web[' . $this->_sName . ']:' . $mResult);
		}

		if (empty($mResult) OR ($mResult = json_decode($mResult)) == false) {
			throw new RuntimeException('refresh_access_token_web');
		}

		if (property_exists($mResult, 'errcode')) {
			throw new RuntimeException('get_access_token_web:' . $mResult->errmsg, $mResult->errcode);
		}

		return new WebAccessToken($mResult);
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
		$rAPI = curl_init(sprintf(
			'https://api.weixin.qq.com/sns/auth?access_token=%s&openid=%s',
			$sToken, $sUserId));
		curl_setopt($rAPI, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($rAPI, CURLOPT_CONNECTTIMEOUT, 5);//request timeout in 5 sec
		$mResult = curl_exec($rAPI);

		if (Application::IsDebugging()) {
			Logger::Message('wechat_verify_access_key_web[' . $this->_sName . ']:' . $mResult);
		}

		if (empty($mResult) OR ($mResult = json_decode($mResult)) == false) {
			throw new RuntimeException('refresh_access_token_web');
		}

		return isset($mResult['errcode']) && $mResult['errcode'] == 0 ? true : false;
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
		$rAPI = curl_init(sprintf(
			'https://api.weixin.qq.com/sns/userinfo?access_token=%ss&openid=%s&lang=zh_CN',
			$sToken, $sUserId));
		curl_setopt($rAPI, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($rAPI, CURLOPT_CONNECTTIMEOUT, 5);

		$mResult = curl_exec($rAPI);

		if (Application::IsDebugging()) {
			Logger::Message('wechat_get_userinfo_web[' . $this->_sName . ']:' . $mResult);
		}

		if (empty($mResult) OR ($mResult = json_decode($mResult)) == false) {
			throw new RuntimeException('get_userinfo');
		}

		return new UserInfo($mResult);
	}
	#endregion
}