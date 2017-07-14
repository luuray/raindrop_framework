<?php
/**
 * Raindrop Framework for PHP
 *
 * Customer Service Component of WeChat Module
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
 * Customer Services
 *
 * TODO Finish Coding
 * @ref: http://mp.weixin.qq.com/wiki/1/70a29afed17f56d537c833f89be979c9.html
 *
 * @package Raindrop\Component\WeChat\Component
 */
class CustomerService extends Service
{
	protected function _initialize()
	{
	}

	#region
	public function customerServerAdd($sAccount, $sNickName, $sPassword)
	{
		///TODO POST: https://api.weixin.qq.com/customservice/kfaccount/add?access_token=ACCESS_TOKEN
	}

	public function customerServerEdit($sAccount, $sNickName, $sPassword)
	{
		///TODO POST: https://api.weixin.qq.com/customservice/kfaccount/update?access_token=ACCESS_TOKEN
	}

	public function customerServerDel($sAccount, $sNickName, $sPassword)
	{
		///TODO POST: https://api.weixin.qq.com/customservice/kfaccount/del?access_token=ACCESS_TOKEN
	}

	public function customerServerAvatar($sAccount, $sAvatarPath)
	{
		///TODO POST/FORM: http://api.weixin.qq.com/customservice/kfaccount/uploadheadimg?access_token=ACCESS_TOKEN&kf_account=KFACCOUNT
	}

	public function customerServerList()
	{
		///TODO GET: https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=ACCESS_TOKEN
	}

	public function customerSendMessage()
	{
		///TODO POST: https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=ACCESS_TOKEN
	}
	#endregion
}