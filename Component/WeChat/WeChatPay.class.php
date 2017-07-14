<?php
/**
 * Raindrop Framework for PHP
 *
 * WeChat Payment Component
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
use Raindrop\Component\RandomString;
use Raindrop\Exceptions\InvalidArgumentException;
use Raindrop\Exceptions\RuntimeException;
use Raindrop\Logger;

class WeChatPay
{
	const TRADE_JSAPI = 'JSAPI';
	const TRADE_NATIVE = 'NATIVE';
	const TRADE_APP = 'APP';

	protected $_sAppId;
	protected $_sMCH_Id;
	protected $_sMCH_Key;
	protected $_sCallback;

	public function __construct($sAppId, $sMCH_Id, $sMCH_Key, $sAPICallback)
	{
		$this->_sAppId    = $sAppId;
		$this->_sMCH_Id   = $sMCH_Id;
		$this->_sMCH_Key  = $sMCH_Key;
		$this->_sCallback = $sAPICallback;
	}

	public function getUnifiedOrder($sOrderNumber, $sBody, $fAmount, $sTradeType, $sAttach = null, $sDetail = null, $sOpenId = null)
	{
		if (!is_numeric($sOrderNumber)) {
			throw new InvalidArgumentException('order_number');
		}
		if (settype($fAmount, 'double') == false OR $fAmount <= 0) {
			throw new InvalidArgumentException('order_amount');
		}
		$fAmount = intval($fAmount * 100);
		if ($sTradeType == WeChatPay::TRADE_JSAPI AND empty($sOpenId)) {
			throw new InvalidArgumentException('mission_openid');
		}

		$sNonceStr   = RandomString::GetString(16);
		$sRemoteAddr = Application::GetRequest()->getRemoteAddress();
		$sTimeStart  = date('YmdHis', Application::GetRequestTime());
		$sTimeExpire = date('YmdHis', Application::GetRequestTime() + 86400);

		$aData = [
			'appid'            => $this->_sAppId,
			'body'             => $sBody,
			'mch_id'           => $this->_sMCH_Id,
			'nonce_str'        => $sNonceStr,
			'notify_url'       => $this->_sCallback,
			'time_start'       => $sTimeStart,
			'time_expire'      => $sTimeExpire,
			'out_trade_no'     => $sOrderNumber,
			'spbill_create_ip' => $sRemoteAddr,
			'total_fee'        => $fAmount,
			'trade_type'       => $sTradeType,
			'sign_type'        => 'MD5'
		];

		if ($sAttach != null) {
			$aData['attach'] = $sAttach;
		}

		if ($sDetail != null) {
			$aData['detail'] = $sDetail;
		}

		if ($sOpenId != null) {
			$aData['openid'] = $sOpenId;
		}

		ksort($aData);
		$sSign = [];
		foreach ($aData AS $_k => $_v) {
			$sSign[] = "{$_k}={$_v}";
		}
		$sSign = implode('&', $sSign);
		$sSign .= '&key=' . $this->_sMCH_Key;
		$sSign = strtoupper(md5($sSign));

		$sTpl = "<xml>"
			. "<appid>{$this->_sAppId}</appid>"
			. "<attach>{$sAttach}</attach>"
			. "<body>{$sBody}</body>"
			. "<mch_id>{$this->_sMCH_Id}</mch_id>"
			. "<detail><![CDATA[{$sDetail}]]></detail>"
			. "<nonce_str>{$sNonceStr}</nonce_str>"
			. "<notify_url>{$this->_sCallback}</notify_url>"
			. "<time_start>{$sTimeStart}</time_start>"
			. "<time_expire>{$sTimeExpire}</time_expire>"
			. "<openid>{$sOpenId}</openid>"
			. "<out_trade_no>{$sOrderNumber}</out_trade_no>"
			. "<spbill_create_ip>{$sRemoteAddr}</spbill_create_ip>"
			. "<total_fee>{$fAmount}</total_fee>"
			. "<trade_type>{$sTradeType}</trade_type>"
			. "<sign_type>MD5</sign_type>"
			. "<sign>{$sSign}</sign>"
			. "</xml>";

		$rRequest = curl_init('https://api.mch.weixin.qq.com/pay/unifiedorder');
		curl_setopt($rRequest, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($rRequest, CURLOPT_POST, true);
		curl_setopt($rRequest, CURLOPT_CONNECTTIMEOUT, 5);//request timeout in 5 sec
		curl_setopt($rRequest, CURLOPT_POSTFIELDS, $sTpl);

		$sResponse = @curl_exec($rRequest);

		if (Application::IsDebugging()) {
			Logger::Message('WeChatPay(Request)=>' . $sTpl . ', (Response)=>' . $sResponse . ', result=>' . ($sResponse == false ? ('error' . curl_error($rRequest)) : 'success'));
		}

		if ($sResponse == false) {
			$sErr = curl_error($rRequest);
			throw new RuntimeException('wechat_pay_gateway:' . $sErr);
		}
	}
}