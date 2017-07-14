<?php
/**
 * Raindrop Framework for PHP
 *
 * Message Adapter for Wechat Module
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Component;


use Raindrop\Component\WeChat\Message\News;
use Raindrop\Component\WeChat\Message\Template;
use Raindrop\Component\WeChat\Message\Text;
use Raindrop\Component\WeChat\Model\Message;
use Raindrop\Exceptions\FatalErrorException;

class MessageAdapter extends Service
{
	public static function toXML(Message $oMessage)
	{
		return call_user_func('Raindrop\Component\WeChat\Component\XMLSerializer::' . $oMessage->getType(), $oMessage);
	}

	public static function toJSON(Message $oMessage)
	{
		if ($oMessage instanceof \JsonSerializable) {
			return json_encode($oMessage);
		} else {
			throw new FatalErrorException('invalid_message_define:' . $oMessage->getType());
		}
	}

	protected function _initialize()
	{
	}

	/**
	 * @param $sToUser
	 * @param $sContent
	 *
	 * @return Text
	 */
	public function createText($sToUser, $sContent)
	{
		return new Text($this->_oComponent->Account, $sToUser, time(), null, ['Content'=>$sContent]);
	}

	/**
	 * @param $sToUser
	 * @param null $aArticles
	 *
	 * @return News
	 */
	public function createNews($sToUser, $aArticles = null)
	{
		return new News($this->_oComponent->Account, $sToUser, time(), null, $aArticles);
	}

	/**
	 * @param $sToUser
	 * @param $sTemplateId
	 * @param $aParams
	 * @param null $sTargetUrl
	 *
	 * @return Template
	 */
	public function createTemplateMsg($sToUser, $sTemplateId, $aParams, $sTargetUrl = null)
	{
		return new Template($this->_oComponent->Account, $sToUser, time(), null, ['template_id' => $sTemplateId, 'variables'=>$aParams, 'url' => $sTargetUrl]);
	}
}