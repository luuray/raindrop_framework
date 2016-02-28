<?php
/**
 * Raindrop Framework for PHP
 *
 * Identify's Exceptions
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
namespace Raindrop\Exceptions\Identify;

use Raindrop\Exceptions\ApplicationException;
use Raindrop\Logger;


class IdentifyException extends ApplicationException
{
	public function __construct($message=null)
	{
		parent::__construct($message);

		Logger::Message(parent::__toString());
	}
}

class UnidentifiedException extends IdentifyException
{
}

class NoPermissionException extends IdentifyException
{
}