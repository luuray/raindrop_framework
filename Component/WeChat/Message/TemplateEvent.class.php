<?php
/**
 * Raindrop Framework for PHP
 *
 * Template Event of WeChat Module
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

class TemplateEvent extends Message
{
	///event: TEMPLATEEVENTSENDJOBFINISH
	///status: success, failed:user block, failed: system failed
	protected function _initialize($aData = null)
	{
		// TODO: Implement _initialize() method.
	}
}