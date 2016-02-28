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
use Raindrop\Exceptions\MessageQueue\ConnectionException;
use Raindrop\Exceptions\MessageQueue\PublishException;
use Raindrop\Interfaces\IMessageQueue;
use Raindrop\Model\QueuedMessage;

class AMQPClient implements IMessageQueue
{
	/**
	 * @var string
	 */
	protected $_sName;
	/**
	 * @var Configuration
	 */
	protected $_oConfig;

	/**
	 * @var \AMQPExchange
	 */
	protected $_oExchange = null;
	/**
	 * @var null|\AMQPConnection
	 */
	protected $_oConnection = null;

	public function __construct($sQueue, Configuration $oConfig)
	{
		$this->_sName   = $sQueue;
		$this->_oConfig = $oConfig;

		if ($this->_oConfig->Get('LazyConnect', false) != true) {
			$this->_connect();
		}
	}

	public function publish($sKey, QueuedMessage $oMessage)
	{
		if ($this->_oConnection == null || $this->_oConnection->isConnected() == false) {
			$this->_connect();
		}

		try {
			return $this->_oExchange->publish($oMessage, $sKey, ['delivery_mode' => 2]);
		} catch (\AMQPExchangeException $ex) {
			throw new PublishException($this->_sName, $sKey, $oMessage, $ex);
		} catch (\AMQPConnectionException $ex) {
			throw new ConnectionException($this->_sName, $this->_oConfig, $ex);
		}
	}

	protected function _connect()
	{
		if ($this->_oConnection != null AND $this->_oConnection->isConnected()) return true;

		$this->_oConnection = new \AMQPConnection([
			'host'            => $this->_oConfig->Get('Server', 'localhost'),
			'port'            => $this->_oConfig->Get('Port', 5672),
			'vhost'           => $this->_oConfig->Get('VHost', '/'),
			'login'           => $this->_oConfig->Get('Username', 'guest'),
			'password'        => $this->_oConfig->Get('Password', 'guest'),
			'read_timeout'    => $this->_oConfig->Get('Timeout', 5),
			'write_timeout'   => $this->_oConfig->get('Timeout', 5),
			'connect_timeout' => $this->_oConfig->get('Timeout', 5)
		]);

		try {
			if ($this->_oConfig->Persistent == true) {
				$this->_oConnection->pconnect();
			} else {
				$this->_oConnection->connect();
			}

			$this->_oExchange = new \AMQPExchange(new \AMQPChannel($this->_oConnection));
			$this->_oExchange->setName($this->_oConfig->Exchange);
			$this->_oExchange->setType($this->_oConfig->Get('Type', AMQP_EX_TYPE_TOPIC));
			$this->_oExchange->setFlags($this->_oConfig->Get('Flags', AMQP_DURABLE));

			$this->_oExchange->declareExchange();
		} catch (\AMQPException $ex) {
			throw new ConnectionException($this->_sName, $this->_oConfig, $ex);
		}
	}
}