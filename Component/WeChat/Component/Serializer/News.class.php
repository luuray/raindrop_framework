<?php
/**
 * Raindrop Framework for PHP
 *
 * News Message Serializer for WeChat Component
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

class News implements IMessageSerializer
{
	/**
	 * @var \Raindrop\Component\WeChat\Message\News
	 */
	protected $_oMessage;
	protected $_sXMLTpl = <<<XML
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%d</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%d</ArticleCount>
<Articles>
%s
</Articles>
</xml> 
XML;
	protected $_sArticleTpl = <<<XML
<item>
<Title><![CDATA[%s]]></Title> 
<Description><![CDATA[%s]]></Description>
<PicUrl><![CDATA[%s]]></PicUrl>
<Url><![CDATA[%s]]></Url>
</item>
XML;

	public function __construct(Message $oMessage)
	{
		if($oMessage->getType() != 'News'){
			throw new RuntimeException('not_support:'.$oMessage->getType());
		}

		$this->_oMessage = $oMessage;
	}

	public function __toString()
	{
		$iCount = 0;
		$sArticle = '';

		foreach($this->_oMessage->getResponseData() AS $_item){
			if(array_key_exists('Title', $_item)
				AND array_key_exists('Description', $_item)
				AND array_key_exists('PicUrl', $_item)
				AND array_key_exists('Url', $_item)){
				$sArticle .= sprintf($this->_sArticleTpl, $_item['Title'], $_item['Description'], $_item['PicUrl'], $_item['Url']);

				$iCount++;
			}
		}

		return sprintf($this->_sXMLTpl,
			$this->_oMessage->ToUserName,
			$this->_oMessage->FromUserName,
			$this->_oMessage->CreateTime,
			$iCount, $sArticle);
	}
}