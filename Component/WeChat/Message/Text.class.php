<?php
/**
 * Raindrop Framework for PHP
 *
 * Text Message of WeChat Component
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

/**
 * Class Text
 * @package Raindrop\Component\WeChat\Message
 *
 * @property string $Content
 */
class Text extends Message implements IResponsible
{
	protected $_sContent;

	protected function _initialize($aData=null)
	{
		$this->_sContent = $aData['Content'];
	}

	public function getContent()
	{
		return $this->_sContent;
	}

	public function getResponseData()
	{
		return $this->_sContent;
	}
}