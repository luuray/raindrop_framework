<?php
/**
 * Raindrop Framework for PHP
 *
 * Console Request
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2014, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop;


use Raindrop\Exceptions\NotImplementedException;

class ConsoleRequest extends Request
{
	public function getMethod()
	{
		return Request::METHOD_CLI;
	}

	public function getRequestTime()
	{
		return time();
	}

	public function getRawPost()
	{
		//todo console request write-down
	}

	public function getBaseUri()
	{
		return false;
	}

	public function getFile($sKey)
	{
		throw new NotImplementedException;
	}

	public function isAjax()
	{
		return true;
	}
}