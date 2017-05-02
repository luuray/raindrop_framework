<?php
/**
 * Raindrop Framework for PHP
 *
 * Message Queue Interface
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
use Raindrop\Model\QueuedMessage;

interface IMessageQueue
{
	public function __construct($sQueue, Configuration $oConfig);

	/**
	 * Publish Message
	 *
	 * @param $sKey
	 * @param QueuedMessage $oMessage
	 *
	 * @return bool
	 * @throws ConnectionException
	 * @throws PublishException
	 */
	public function publish($sKey, QueuedMessage $oMessage);

	/**
	 * Close Connection
	 * 
	 * @return null
	 */
	public function disconnect();
}