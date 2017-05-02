<?php
/**
 * Raindrop Framework for PHP
 *
 * Schedule Processor
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
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