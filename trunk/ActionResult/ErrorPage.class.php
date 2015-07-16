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
use Raindrop\Exceptions\NotImplementedException;

///TODO Output Error Page
class ErrorPage extends View
{
	public function __construct($mCode = 404, $mData = null)
	{
		header($mCode, true, $mCode);
		var_dump($mCode, $mData);
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