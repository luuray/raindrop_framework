<?php
/**
 * Raindrop Framework for PHP
 *
 * RabbitMQ Adapter
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2015, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\Component;

use Raindrop\Configuration;
use Raindrop\Interfaces\ITaskQueue;
use Raindrop\Model\QueuedTask;

class RabbitMQ implements ITaskQueue
{
	protected $_sName;
	protected $_aConfig;

	protected $_oConn;

	public function __construct($sName, Configuration $oConfig)
	{
		$oServerConn = new \AMQPConnection([
			'host'     => $oConfig->Get('Host', 'localhost'),
			'port'     => $oConfig->Get('Port', 5672),
			'vhost'    => $oConfig->Get('VHost', '/'),
			'login'    => $oConfig->Get('Login', 'guest'),
			'password' => $oConfig->Get('Password', 'guest')
		]);
		try {
			$oServerConn->connect();
			$oExchange = new \AMQPExchange(new \AMQPChannel($oServerConn));
		} catch (\AMQPException $ex) {

		}
	}

	public function publish($sQueueName, QueuedTask $oTask)
	{
	}

	public function consume()
	{
	}

	public function getName()
	{
		return $this->_sName;
	}

	public function getConfig()
	{
		return $this->_aConfig;
	}
}