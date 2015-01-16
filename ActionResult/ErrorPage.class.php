<?php
/**
 * Raindrop Framework for PHP
 *
 * Error Page View
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2015,
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\ActionResult;


use Raindrop\ActionResult;
use Raindrop\Application;
use Raindrop\ApplicationException;
use Raindrop\Configuration;
use Raindrop\FatalErrorException;
use Raindrop\FileNotFoundException;
use Raindrop\Loader;
use Raindrop\NotImplementedException;

class ErrorPage extends View
{
	public function __construct($mCode = 404, $mData = null)
	{
		var_dump($mData);
		echo '<pre>';
		debug_print_backtrace();
		echo '</pre>';
		die();
	}

	public function toString()
	{
		throw new NotImplementedException;
	}
}