<?php
/**
 * Raindrop Framework for PHP
 *
 * Cache Interface
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

use Raindrop\Configuration;

interface ICache
{
	/**
	 * Construct Cache Adapter
	 *
	 * @param Configuration $oConfig Adapter Configuration
	 * @param string $sName Adapter Identify Name
	 */
	public function __construct(Configuration $oConfig, $sName);

	/**
	 * Get a Item
	 *
	 * @param string $sName Item Name
	 * @return mixed
	 */
	public function get($sName);

	/**
	 * Delete a Item
	 *
	 * @param string $sName Item Name
	 * @return mixed
	 */
	public function del($sName);

	/**
	 * Set a Value to Cache
	 *
	 * @param string $sName Item Name
	 * @param mixed $mValue Item
	 * @param null $iLifetime
	 *
	 * @return mixed
	 */
	public function set($sName, $mValue, $iLifetime = null);

	/**
	 * Delete All
	 *
	 * @return bool
	 */
	public function flush();
}