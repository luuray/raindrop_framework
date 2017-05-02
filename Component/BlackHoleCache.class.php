<?php
/**
 * Raindrop Framework for PHP
 *
 * BlackHole Cache Component
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component;


use Raindrop\Configuration;
use Raindrop\Interfaces\ICache;

/**
 * Class BlackHoleCache
 *
 * @package Raindrop\Component
 */
class BlackHoleCache implements ICache
{

	/**
	 * Construct Cache Adapter
	 *
	 * @param Configuration $oConfig Adapter Configuration
	 * @param string $sName Adapter Identify Name
	 */
	public function __construct(Configuration $oConfig = null, $sName)
	{

	}

	/**
	 * Get a Item
	 *
	 * @param string $sName Item Name
	 *
	 * @return mixed
	 */
	public function get($sName)
	{
		return null;
	}

	/**
	 * Delete a Item
	 *
	 * @param string $sName Item Name
	 *
	 * @return mixed
	 */
	public function del($sName)
	{
		return true;
	}

	/**
	 * Set a Value to Cache
	 *
	 * @param string $sName Item Name
	 * @param mixed $mValue Item
	 * @param int $iLifetime Lifetime
	 *
	 * @return mixed
	 */
	public function set($sName, $mValue, $iLifetime = 0)
	{
		return true;
	}

	/**
	 * Delete All
	 *
	 * @return bool
	 */
	public function flush()
	{
		return true;
	}
}