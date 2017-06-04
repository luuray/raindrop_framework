<?php
/**
 * Raindrop Framework for PHP
 *
 * Notification Interface
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Interfaces;

use Raindrop\Configuration;

interface INotification
{
	public function __construct(Configuration $oConfig, $sHandlerName);

	public function sendMsg($mReceiver, $sContent, $sTitle);
	public function sendTemplateMsg($mReceiver, $sTemplateName, $aParams);

	public function getName();
}