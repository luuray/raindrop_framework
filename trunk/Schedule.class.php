<?php
/**
 * Raindrop Framework for PHP
 *
 * Schedule Processor
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

abstract class Schedule
{
	const Status_Fault = -1;
	const Status_Idle = 0;
	const Status_Running = 1;

	public static function GetInstance()
	{
	}

	protected function __construct()
	{
	}
} 