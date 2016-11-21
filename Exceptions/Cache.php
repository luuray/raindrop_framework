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
	public function __construct($sHandler, $sMessage, $iCode, \Exception $previous=null)
	{
		//TODO Make Message Format Same

		parent::__construct(sprintf('[%s]%s', $sHandler, $sMessage), $iCode, $previous);
	}
}

class CacheMissingException extends CacheFailException
{
	public function __construct($sHandler, $sName)
	{
		Logger::Warning(sprintf('CacheMissing:[%s]  %s', $sHandler, $sName));

		parent::__construct($sHandler, sprintf('CacheMissing:[%s]  %s', $sHandler, $sName), 0, $this);
	}
}