<?php
/**
 * Raindrop Framework for PHP
 *
 * Bootstrap Abstract
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

abstract class BootstrapAbstract
{
	public final function __construct()
	{
	}

	public function beforeRoute(Application $oApplication, Dispatcher $oDispatcher = null)
	{
	}

	public function afterRoute(Application $oApplication, Dispatcher $oDispatcher = null)
	{
	}

	public function beforeDispatch(Application $oApplication, Dispatcher $oDispatcher)
	{
	}

	public function afterDispatch(Application $oApplication, Dispatcher $oDispatcher)
	{
	}
} 