<?php
/**
 * Raindrop Framework for PHP
 *
 * ScheduleJob Interface
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Interfaces;


interface IScheduleJob
{
	public function start();

	public function status();

	public function stop();
}