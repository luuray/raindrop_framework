<?php
/**
 * *
 *  * DTeacher
 *  *
 *  *
 *  *
 *  * @author Luuray
 *  * @copyright Rainhan System
 *  * @id $Id$
 *  *
 *  * Copyright (c) 2010-2017, Rainhan System
 *  * Site: www.rainhan.net/?proj=DTeacher
 *
 */

/**
 * Raindrop Framework for PHP
 *
 * Subscribe Event of WeChat Module
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Message;


use Raindrop\Component\WeChat\Model\Message;

class SubscribeEvent extends Message
{
	protected $_sEventKey = null;
	protected $_sTicket = null;

	protected function _initialize($mData = null)
	{
		if (is_array($mData) AND isset($mData['EventKey'])) {
			$this->_sEventKey = $mData['EventKey'];
			$this->_sTicket   = isset($mData['Ticket']) ? $mData['Ticket'] : null;
		}
	}

	public function getEventKey()
	{
		return $this->_sEventKey;
	}

	public function getTicket()
	{
		return $this->_sTicket;
	}
}