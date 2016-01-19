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
 * Copyright (c) 2014-2015, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\ActionResult;

use Raindrop\ActionResult;
use Raindrop\Application;
use Raindrop\Exceptions\NotImplementedException;

///TODO Output Error Page
class ErrorPage extends View
{
	public function __construct($mCode = 404, $mData = null)
	{
		header($mCode, true, $mCode);

		if(Application::IsDebugging()){
			var_dump($mCode, $mData);
			echo '<pre>';
			debug_print_backtrace();
			echo '</pre>';
		}
		die($mCode);
	}

	public function toString()
	{
		throw new NotImplementedException;
	}
}