<?php
/**
 * Raindrop Framework for PHP
 *
 * Message Queue
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop;

use Raindrop\Exceptions\ComponentUndefinedException;
use Raindrop\Exceptions\MessageQueue\ConnectionException;
use Raindrop\Exceptions\MessageQueue\PublishException;
use Raindrop\Interfaces\IMessageQueue;
use Raindrop\Model\QueuedMessage;

class MessageQueue
{
	protected $_aQueues = array();

	protected function __construct()
	{
		$aConfig = Configuration::GetRoot('Queue');

		foreach ($aConfig AS $_name => $_cfg) {
			$this->_aQueues[strtolower($_name)] =
				(new \ReflectionClass(sprintf('Raindrop\Component\%s', $_cfg->Component)))->newInstance($_name, $_cfg->Params);
		}
	}

	protected static function _GetInstance()
	{
		static $oInstance = null;
		if ($oInstance == null) {
			$oInstance = new self();
		}

		return $oInstance;
	}

	/**
	 * @param $sKey
	 * @param QueuedMessage $oMessage
	 * @param string $sQueue
	 *
	 * @return mixed
	 * @throws ComponentUndefinedException
	 * @throws PublishException
	 * @throws ConnectionException
	 */
	public static function Publish($sKey, QueuedMessage $oMessage, $sQueue = 'default')
	{
		if (Application::IsDebugging()) {
			Logger::Message(
				'MessagePublish: Queue:' . $sQueue
				. ', Key:' . $sKey
				. ', Message:'
				. (string)$oMessage
				. ', Result:' . (self::_GetInstance()->getQueue($sQueue)->publish($sKey, $oMessage) ? 'True' : 'False'));
		} else {
			return self::_GetInstance()->getQueue($sQueue)->publish($sKey, $oMessage);
		}
	}

	/**
	 * @param $sName
	 *
	 * @return IMessageQueue
	 * @throws ComponentUndefinedException
	 */
	public function getQueue($sName)
	{
		$sName = strtolower(trim($sName));
		if (array_key_exists($sName, $this->_aQueues)) {
			return $this->_aQueues[$sName];
		}

		throw new ComponentUndefinedException('MessageQueue', $sName);
	}
}