<?php
/**
 * Raindrop Framework for PHP
 *
 *
 *
 * @author $Author$
 * @copyright
 * @date $Date$
 *
 * Copyright (c) 2010-2014,
 * Site:
 *
 * $Id$
 *
 * @version $Rev$
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
