<?php/** * Raindrop Framework for PHP * * Message Adapter for Wechat Module * * @author Luuray * @copyright Rainhan System * @id $Id$ * * Copyright (c) 2010-2016, Rainhan System * Site: raindrop-php.rainhan.net */namespace Raindrop\Component\WeChat\Component;use Raindrop\Component\WeChat\Message\News;use Raindrop\Component\WeChat\Message\Text;use Raindrop\Component\WeChat\Model\Message;class MessageAdapter extends Service{	public static function toXML(Message $oMessage)	{		return call_user_func('Raindrop\Component\WeChat\Component\XMLSerializer::'.$oMessage->getType(), $oMessage);	}	public static function toJSON(Message $oMessage)	{	}	protected function _initialize()	{	}	/**	 * @param $sToUser	 * @param $sContent	 *	 * @return Text	 */	public function createText($sToUser, $sContent)	{		return new Text($this->_sAccount, $sToUser, time(), null, $sContent);	}	public function createNews($sToUser, $aArticles=null)	{		return new News($this->_sAccount, $sToUser, time(), null, $aArticles);	}}