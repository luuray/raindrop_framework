<?php
/**
 * Raindrop Framework for PHP
 *
 * Cache's Exceptions
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

namespace Raindrop\Exceptions\Cache;

use Raindrop\Exceptions\RuntimeException;
use Raindrop\Logger;

class CacheFailException extends RuntimeException
{
	public function __construct($sHandler, $sMessage)
	{
		//TODO Make Message Format Same
		Logger::Error(sprintf('CacheMissing:[%s]  %s', $sHandler, $sMessage));

		parent::__construct(sprintf('[%s]%s', $sHandler, $sMessage), 0, $this);
	}
}

class CacheMissingException extends CacheFailException
{
	public function __construct($sHandler, $sName)
	{
		Logger::Warning(sprintf('CacheMissing:[%s]  %s', $sHandler, $sName));

		parent::__construct(sprintf('CacheMissing[%s]  %s', $sHandler, $sName), 0, $this);
	}
}