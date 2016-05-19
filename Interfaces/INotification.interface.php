<?php
/**
 * Raindrop Framework for PHP
 *
 * Notification Interface
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2014, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */
namespace Raindrop\Interfaces;

interface INotification
{
	public function __construct($aConfig, $sHandlerName);

	public function send($mReceiver, $sContent, $sTitle = 'no subject');
}