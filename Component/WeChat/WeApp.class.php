<?php
/**
 * Raindrop Framework for PHP
 *
 * WeChat Mini App Component
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat;


class WeApp
{
	public function __construct()
	{
	}

	public static function VerifyAPI($sSignature, $iTimestamp, $sNonce, $sToken)
	{
		$aVerify = [$sToken, $iTimestamp, $sNonce];
		sort($aVerify, SORT_STRING);

		return sha1(implode($aVerify)) == $sSignature;
	}
}