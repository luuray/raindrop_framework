<?php
/**
 * Raindrop Framework for PHP
 *
 * Service Interface of WeChat Module
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Component;


use Raindrop\Component\WeChat\WeChat;

abstract class Service
{
	/**
	 * @var WeChat
	 */
	protected $_oComponent;

	public final function __construct(WeChat $oParent)
	{
		$this->_oComponent = $oParent;

		$this->_initialize();
	}

	protected abstract function _initialize();
}