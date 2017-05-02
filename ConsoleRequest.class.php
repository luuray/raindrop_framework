<?php
/**
 * Raindrop Framework for PHP
 *
 * Console Request
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
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