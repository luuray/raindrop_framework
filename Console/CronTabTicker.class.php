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


use Raindrop\Exceptions\InvalidArgumentException;

final class CronTabTicker
{
	protected $_iInterval;
	protected $_mCallback;

	public function __construct($iInterval, $mCallback)
	{
		if (settype($iInterval, 'int') === false OR $iInterval <= 0) {
			throw new InvalidArgumentException('interval');
		}

		$this->_iInterval = $iInterval;
		$this->_mCallback = $mCallback;
	}

	public function getInterval()
	{
		return $this->_iInterval;
	}

	public function getCallback()
	{
		return $this->_mCallback;
	}
}