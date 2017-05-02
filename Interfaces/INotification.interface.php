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

interface INotification
{
	public function __construct($aConfig, $sHandlerName);

	public function send($mReceiver, $sContent, $sTitle = 'no subject');
}