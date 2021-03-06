<?php
/**
 * Raindrop Framework for PHP
 *
 * Template Message of WeChat Component
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Message;


use Raindrop\Component\WeChat\Model\IResponsible;
use Raindrop\Component\WeChat\Model\Message;

class Template extends Message implements IResponsible, \JsonSerializable
{
	protected $_sTemplateId = null;
	protected $_sTarget = null;
	protected $_aVariables = [];

	protected function _initialize($aData = null)
	{
		$this->_sTemplateId = isset($aData['template_id']) ? $aData['template_id'] : null;
		$this->_sTarget     = isset($aData['url']) ? $aData['url'] : null;
		$this->_aVariables  = $aData['variables'];
	}

	public function getResponseData()
	{
		// TODO: Implement getResponseData() method.
	}

	public function setTarget($sUrl)
	{
		$this->_sTarget = $sUrl;
	}

	public function assign($sKey, $sValue, $sColor = null)
	{
		$sKey = strtolower($sKey);

		if (array_key_exists($sKey, $this->_aVariables)) {
			$this->_aVariables[$sKey]['value'] = $sValue;
			$this->_aVariables[$sKey]['color'] = $sColor == null ? $this->_aVariables[$sKey]['color'] : $sColor;

			return true;
		}

		return false;
	}

	function jsonSerialize()
	{
		$aResult = [
			'touser'      => $this->ToUserName,
			'template_id' => $this->_sTemplateId,
			'url'         => $this->_sTarget,
			'data'        => []
		];
		foreach ($this->_aVariables AS $_item) {
			$aResult['data'][$_item['name']] = [
				'value' => (string)$_item['value'],
				'color' => $_item['color']
			];
		}

		return $aResult;
	}

}