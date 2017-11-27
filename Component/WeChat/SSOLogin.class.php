<?php
/**
 * DTeacher
 *
 *
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: www.rainhan.net/?proj=DTeacher
 */

namespace Raindrop\Component\WeChat;


use Raindrop\Component\WeChat\Model\AccessToken;

class SSOLogin
{
	public function __construct($sAppId, $sAppSecret, AccessToken $oToken=null)
	{
	}

	public function getAccessToken($sCode)
	{
	}

	public function refreshToken(AccessToken $oToken=null)
	{
	}

	public function validate($sOpenId, AccessToken $oToken=null)
	{
	}

	public function getUserInfo($sOpenId, AccessToken $oToken=null)
	{
	}

	protected function _apiRequest()
	{

	}
}