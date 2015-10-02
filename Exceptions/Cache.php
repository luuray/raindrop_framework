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

use Raindrop\Exceptions\ApplicationException;
use Raindrop\Logger;

class CacheFailException extends ApplicationException
{
}

class CacheMissingException extends CacheFailException
{
	public function __construct($sHandler, $sName)
	{
		$sMessage = sprintf('CacheMissing:[%s]%s', $sHandler, $sName);

		Logger::Warning($sMessage);

		parent::__construct($sMessage, 0, null);
	}
}