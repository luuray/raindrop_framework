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

namespace Raindrop\Console;

use Raindrop\Logger;

final class CronTab extends Worker
{
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