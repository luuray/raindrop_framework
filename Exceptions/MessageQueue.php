<?php
/**
 * Raindrop Framework for PHP
 *
 * Message Queue Exceptions
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Exceptions\MessageQueue;

use Raindrop\Configuration;
use Raindrop\Exceptions\ApplicationException;
use Raindrop\Logger;
use Raindrop\Model\QueuedMessage;

abstract class MessageQueueException extends ApplicationException
{

}

class ConnectionException extends MessageQueueException
{
	public function __construct($sQueue, Configuration $oConfig, \Exception $oPrevious)
	{
		$sMessage = sprintf('[MessageQueue]connection fail. queue: %s, config: %s, exception: %s',
			$sQueue, json_encode($oConfig), $oPrevious->getMessage());

		Logger::Warning($sMessage);
		Logger::Warning($oPrevious);

		parent::__construct($sMessage, null, $oPrevious);
	}
}

class MessageDecodeException extends MessageQueueException
{
}

class PublishException extends  MessageQueueException
{
	public function __construct($sQueue, $sKey, QueuedMessage $oMessage, \Exception $oPrevious)
	{
		$sMsg = sprintf('[MessageQueue]publish fail. queue: %s, key: %s, message: %s, exception: %s',
			$sQueue, $sKey, json_encode($oMessage), $oPrevious->getMessage());

		Logger::Warning($sMsg);
		Logger::Warning($oPrevious);

		parent::__construct($sMsg, null, $oPrevious);
	}
}