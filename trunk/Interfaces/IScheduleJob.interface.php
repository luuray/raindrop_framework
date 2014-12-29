<?php
/**
 * Raindrop Framework for PHP
 *
 * Interface for ScheduleJob
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

namespace Raindrop\Interfaces;


interface IScheduleJob
{
	public function start();

	public function status();

	public function stop();
} 