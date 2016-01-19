<?php
/**
 * BoostQueue
 *
 *
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2015, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\Component;


use Raindrop\AbstractClass\Worker;
use Raindrop\Logger;
use Raindrop\Model\CronTabTicker;

final class CronTab extends Worker
{
	public function __construct()
	{
	}

	public function reload()
	{
	}

	/**
	 * Register Ticker
	 *
	 * @return CronTabTicker
	 */
	public function getTicker()
	{
		return new CronTabTicker(1000, [$this, 'tick']);
	}

	/**
	 *
	 */
	public function tick()
	{
		Logger::Trace('Beeeeeep');
	}
}