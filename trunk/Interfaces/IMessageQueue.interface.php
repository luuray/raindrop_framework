<?php
/**
 * Raindrop Framework for PHP
 *
 * Message Queue Component Interface
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

namespace Raindrop\Interfaces;


use Raindrop\Configuration;
use Raindrop\Model\QueuedMessage;

interface IMessageQueue
{
	public function __construct($sQueue, Configuration $oConfig);

	public function publish($sKey, QueuedMessage $oMessage);
}