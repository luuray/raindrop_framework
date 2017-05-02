<?php
/**
 * Raindrop Framework for PHP
 *
 * Identify Exceptions
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
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