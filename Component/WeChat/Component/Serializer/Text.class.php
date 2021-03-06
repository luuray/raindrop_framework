<?php
/**
 * Raindrop Framework for PHP
 *
 * Text Message Serializer for WeChat Module
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Component\Serializer;


use Raindrop\Component\WeChat\Model\IMessageSerializer;
use Raindrop\Component\WeChat\Model\Message;
use Raindrop\Exceptions\RuntimeException;

class Text implements IMessageSerializer
{
	protected $_oMessage;
	protected $_sTpl = <<<XML
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%d</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>
XML;


	public function __construct(Message $oMessage)
	{
		if ($oMessage->getType() != 'Text') {
			throw new RuntimeException('not_support:' . $oMessage->getType());
		}

		$this->_oMessage = $oMessage;
	}

	public function __toString()
	{
		$aMeta    = $this->_oMessage->getMeta();
		$sContent = $this->_oMessage->getResponseData();

		return sprintf(
			$this->_sTpl,
			$aMeta['ToUserName'],
			$aMeta['FromUserName'],
			time(),
			str_replace(['<![', ']]'], ['〈！［', '］］'], $sContent));
	}
}