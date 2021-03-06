<?php
/**
 * Raindrop Framework for PHP
 *
 * Click Event of WeChat Module
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

class ClickEvent extends Message
{
	protected $_aEventParams;

	protected function _initialize($aData = null)
	{
		$this->_aEventParams = $aData;
	}

	public function getEventKey()
	{
		return $this->_aEventParams['EventKey'];
	}
}