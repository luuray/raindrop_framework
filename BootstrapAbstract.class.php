<?php
/**
 * Raindrop Framework for PHP
 *
 * Bootstrap Abstract
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
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