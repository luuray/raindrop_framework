<?php
/**
 * Raindrop Framework for PHP
 *
 * News Service of WeChat Module
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Component;

/**
 * Class NewsService
 *
 * @ref http://mp.weixin.qq.com/wiki/15/5380a4e6f02f2ffdc7981a8ed7a40753.html
 * @package Raindrop\Component\WeChat\Component
 */
class NewsService extends Service
{
	protected function _initialize()
	{
	}

	#region Send Group Message
	//ref:
	public function uploadNewsImage($sImagePath)
	{
		///TODO POST/FORM: https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=ACCESS_TOKEN
	}

	public function createNews()
	{
		///TODO POST: https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=ACCESS_TOKEN
	}

	public function sendNewsByGroup()
	{
		///TODO POST: https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=ACCESS_TOKEN
	}

	public function sendNewsByUser()
	{
		///TODO POST: https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=ACCESS_TOKEN
	}

	public function sendNewsPreview()
	{
		///TODO POST: https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=ACCESS_TOKEN
	}

	public function queryNewsSendStatus()
	{
		///TODO POST: https://api.weixin.qq.com/cgi-bin/message/mass/get?access_token=ACCESS_TOKEN
	}

	public function delSendMessage()
	{
		///TODO POST: https://api.weixin.qq.com/cgi-bin/message/mass/delete?access_token=ACCESS_TOKEN
	}
	#endregion
}