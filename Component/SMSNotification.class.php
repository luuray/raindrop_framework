<?php
/**
 * Raindrop Framework for PHP
 *
 * SMS Notification Component
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

namespace Raindrop\Component;

use Raindrop\Exceptions\InvalidArgumentException;
use Raindrop\Exceptions\RuntimeException;
use Raindrop\Interfaces\INotification;

class SMSNotification implements INotification
{
	protected $_sAPI;
	protected $_sUsername;
	protected $_sPassword;

	protected $_sHandlerName;

	public function __construct($aConfig, $sHandlerName)
	{
		$this->_sAPI      = $aConfig['API'];
		$this->_sUsername = $aConfig['Username'];
		$this->_sPassword = $aConfig['Password'];

		$this->_sHandlerName = $sHandlerName;
	}

	public function send($mReceiver, $sContent, $sTitle = null)
	{
		if (is_array($mReceiver)) {
			foreach ($mReceiver AS &$_v) {
				$_v = $this->_numberValidate($_v);
				if ($_v == false) {
					throw new InvalidArgumentException('receiver');
				}
			}
		} else {
			$mReceiver = $this->_numberValidate($mReceiver);
			if ($mReceiver == false) {
				throw new InvalidArgumentException('receiver');
			}
		}

		$sApi = sprintf(
			'{0}?name={1}&pwd={2}&dst={3}&sender=&time=&txt=ccdx&msg={4}',
			$this->_sAPI,
			urlencode($this->_sUsername),
			urlencode($this->_sPassword),
			array($mReceiver) ? implode(',', $mReceiver) : $mReceiver,
			urlencode($sContent . (empty($sTitle) ? '' : "[{$sTitle}]")));
		$rApi = curl_init($this->_sAPI);

		curl_setopt_array($rApi, array(
			CURLOPT_FRESH_CONNECT  => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPGET        => true,
			CURLOPT_CONNECTTIMEOUT => 10));

		$sResponse = curl_exec($rApi);
		if ($sResponse === false) {
			throw new RuntimeException(curl_error($rApi), curl_errno($rApi));
		}
		$aResult = array();
		parse_str($sResponse, $aResult);
		if (array_key_exists('num', $aResult)) {
			if ($aResult['num'] == 0) {
				return false;
			} else {
				if (is_array($mReceiver) AND count($mReceiver) != $aResult['num']) {
					return false;
				}

				return true;
			}
		}

		return false;
	}

	protected function _numberValidate($sInput)
	{
		$sInput = trim($sInput);
		if (preg_match('/^\d+$/', $sInput)) {
			return $sInput;
		}

		return false;
	}
}