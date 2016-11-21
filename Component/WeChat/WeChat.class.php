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
use Raindrop\Component\WeChat\Component\NewsService;
use Raindrop\Component\WeChat\Component\TemplateService;
use Raindrop\Component\WeChat\Model\AccessToken;
use Raindrop\Component\WeChat\Model\Message;
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
	protected $_sAppId;
	protected $_sAppSecret;
	protected $_sToken;
	protected $_sAESKey;

	//access_token
	protected $_oAccessToken;

	/**
	 * WeChat constructor.
	 *
	 * @param $sName
	 * @param $sAppId
	 * @param $sAppSecret
	 * @param $sToken
	 * @param null $sAESKey
	 */
	public function __construct($sName, $sAppId, $sAppSecret, $sToken, $sAESKey = null)
	{
		$this->_sName      = $sName;
		$this->_sAppId     = $sAppId;
		$this->_sAppSecret = $sAppSecret;
		$this->_sToken     = $sToken;

		$this->_sAESKey = $sAESKey;
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

	/**
	 * Set Access Token
	 *
	 * @param AccessToken $oToken
	 *
	 * @throws RuntimeException
	 */
	public function setAccessToken(AccessToken $oToken)
	{
		if ($oToken->ExpireTime <= time()) {
			throw new RuntimeException('token_expire');
		}

		$this->_oAccessToken = $oToken;
	}

	/**
	 * Get Access Token from Server
	 *
	 * @return AccessToken
	 * @throws RuntimeException
	 */
	public function getAccessToken()
	{
		$rAPI = curl_init(sprintf(
			'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
			$this->_sAppId, $this->_sAppSecret));
		curl_setopt($rAPI, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($rAPI, CURLOPT_CONNECTTIMEOUT, 5);//request timeout in 5 sec
		$mResult = curl_exec($rAPI);

		if(Application::IsDebugging()){
			Logger::Message('wechat_access_key_'.$this->_sAppId.'_'.$mResult);
		}

		if ($mResult == false) {
			throw new RuntimeException('get_access_token');
		}

		$mResult = json_decode($mResult);
		if (!is_object($mResult)) {
			throw new RuntimeException('get_access_token:decode');
		}

		if (property_exists($mResult, 'errcode')) {
			throw new RuntimeException('get_access_token:' . $mResult->errmsg, $mResult->errcode);
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
		$oDocument = @simplexml_load_string($sMessage, 'SimpleXMLElement');
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
					$sMessageModel = $this->_aEventType[$sMsgType];
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
		return new CustomerService($this->_oAccessToken, $this->_sAppId, $this->_sAppSecret, $this->_sAESKey);
	}

	/**
	 * @return NewsService
	 */
	public function newsService()
	{
		return new NewsService($this->_oAccessToken, $this->_sAppId, $this->_sAppSecret, $this->_sAESKey);
	}

	/**
	 * @return TemplateService
	 */
	public function templateService()
	{
		return new TemplateService($this->_oAccessToken, $this->_sAppId, $this->_sAppSecret, $this->_sAESKey);
	}
}