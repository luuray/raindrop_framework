<?php
/**
 * Raindrop Framework for PHP
 *
 * HTML Bundle
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Html;


use Raindrop\Application;
use Raindrop\Html\Bundle\BundlePart;

class Bundle
{
	protected function __construct()
	{
		$bDebug = Application::IsDebugging();
	}

	public static function Add(BundlePart $oBundle)
	{

	}
}
