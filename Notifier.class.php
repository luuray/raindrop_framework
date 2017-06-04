<?php
/**
 * Raindrop Framework for PHP
 *
 * Notifier
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop;


class Notifier
{
	public static function SendMsg($mReceiver, $sContent, $sTitle = null, $sHandler = 'default')
	{
	}

	public static function SendTemplateMsg($mReceiver, $sTemplateName, $aParams, $sHandler)
	{
	}
}