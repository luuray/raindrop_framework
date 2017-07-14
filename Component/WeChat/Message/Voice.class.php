<?php
/**
 * Raindrop Framework for PHP
 *
 * Voice Message of WeChat Component
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Message;


use Raindrop\Component\WeChat\Model\IResponsible;
use Raindrop\Component\WeChat\Model\Message;
use Raindrop\Exceptions\NotImplementedException;

class Voice extends Message implements IResponsible
{
	protected $_sMediaId;
	protected $_sFormat;

	protected function _initialize($mData = null)
	{
		$this->_sMediaId = $mData['MediaId'];
		$this->_sFormat = $mData['Format'];
	}

	public function getMedia()
	{
		return $this->_sMediaId;
	}

	public function getResponseData()
	{
		throw new NotImplementedException();
	}
}
