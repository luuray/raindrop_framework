<?php
/**
 * Raindrop Framework for PHP
 *
 * Queued Task Model
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

class QueuedTask implements \JsonSerializable
{
	protected $_sTaskId;
	protected $_iCreateTime;

	public function __construct()
	{
		$this->_sTaskId = RandomString::GetString(8);
		$this->_iCreateTime = time();
	}

	public function jsonSerialize()
	{
		return [
			'task_id'     => $this->_sTaskId,
			'create_time' => $this->_iCreateTime
		];
	}
}