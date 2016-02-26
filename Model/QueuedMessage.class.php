<?php
/**
 * Raindrop Framework for PHP
 *
 * Queued Message Model
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

namespace Raindrop\Model;


use Raindrop\Component\RandomString;
use Raindrop\Exceptions\MessageQueueDecodeException;

class QueuedMessage implements \JsonSerializable
{
	protected $_sTaskId;
	protected $_iPublishTime;

	protected $_aData = array();

	public function __construct(\stdClass $oDecoded = null)
	{
		if ($oDecoded == null) {
			$this->_sTaskId      = RandomString::GetString(8);
			$this->_iPublishTime = time();
		} else {
			$aData = get_object_vars($oDecoded);
			if (empty($aData['task_id']) OR empty($aData['publish_time'])) {
				throw new MessageQueueDecodeException;
			}
			$this->_sTaskId      = $aData['task_id'];
			$this->_iPublishTime = $aData['publish_time'];

			$this->_aData = !empty($aData['data']) ? $aData['data'] : array();
		}
	}

	public function __get($sKey)
	{
		$sKey = strtolower($sKey);

		return array_key_exists($sKey, $this->_aData) ? $this->_aData[$sKey] : null;
	}

	public function __set($sKey, $mValue)
	{
		$sKey                = strtolower($sKey);
		$this->_aData[$sKey] = $mValue;
	}

	public function getTaskId()
	{
		return $this->_sTaskId;
	}

	public function getPublishTime()
	{
		return $this->_iPublishTime;
	}

	public function __toString()
	{
		return json_encode($this);
	}

	public function jsonSerialize()
	{
		return [
			'task_id'      => $this->_sTaskId,
			'publish_time' => $this->_iPublishTime,
			'data'         => $this->_aData
		];
	}
}