<?php
/**
 * Raindrop Framework for PHP
 *
 * Template Service of WeChat Module
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Component;

use Raindrop\Component\WeChat\Exceptions\APIRequestException;
use Raindrop\Component\WeChat\Message\Template;
use Raindrop\Exceptions\RuntimeException;

/**
 * Class TemplateService
 *
 * @ref http://mp.weixin.qq.com/wiki/17/304c1885ea66dbedf7dc170d84999a9d.html
 * @package Raindrop\Component\WeChat\Component
 */
class TemplateService extends Service
{
	protected function _initialize()
	{
		// TODO: Implement _initialize() method.
	}

	#region Send Template Message
	//ref: http://mp.weixin.qq.com/wiki/17/304c1885ea66dbedf7dc170d84999a9d.html
	public function setIndustry()
	{
		///TODO POST: https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token=ACCESS_TOKEN
	}

	public function getTemplateLongId($sShortId)
	{
		///TODO POST: https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token=ACCESS_TOKEN
	}

	public function sendTemplateMsg(Template $oMessage)
	{
		$oAccessToken = $this->_oComponent->getAPIToken();
		if($oAccessToken == null){
			throw new RuntimeException('api_token_uninitialized');
		}

		try {
			return $this->_oComponent->ApiPostRequest(
				sprintf('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=%s', $oAccessToken->AccessToken),
				MessageAdapter::toJSON($oMessage));
		}catch(APIRequestException $ex){
			throw new RuntimeException($ex->getMessage(), $ex->getCode());
		}
	}
	#endregion
}