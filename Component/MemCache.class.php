<?php
/**
 * Raindrop Framework for PHP
 *
 * MemCache Cache Adapter
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

namespace Raindrop\Component;

use Raindrop\Exceptions\CacheFailException;
use Raindrop\Exceptions\InvalidArgumentException;
use Raindrop\Interfaces\ICache;

class MemCache implements ICache
{
	/**
	 * @var string
	 */
	protected $_sHandlerName;
	/**
	 * @var string
	 */
	protected $_sPrefix = null;
	/**
	 * @var \Memcache
	 */
	protected $_oMemcache;

	/**
	 * Construct Cache Adapter
	 *
	 * @param array $aConfig Adapter Params
	 * @param string $sName Adapter Identify Name
	 * @throws CacheHandlerException
	 * @throws InvalidArgumentException
	 */
	public function __construct($aConfig, $sName)
	{
		if (empty($aConfig)) {
			throw new InvalidArgumentException('config');
		}
		if (empty($sName)) {
			throw new InvalidArgumentException('name');
		}

		$this->_sHandlerName = $sName;
		$this->_sPrefix      = !empty($aConfig['Prefix']) ? $aConfig['Prefix'] : '';
		if (empty($aConfig['Server'])) {
			throw new CacheHandlerException($sName, 'missing_param: server', -1);
		}
		$this->_oMemcache = new \Memcache();
		if (str_beginwith($aConfig['Server'], 'unix://')) {
			$bConnected = $this->_oMemcache->connect($aConfig['Server'], 0);
		} else {
			$bConnected = @$this->_oMemcache->connect(
				$aConfig['Server'],
				empty($aConfig['Port']) ? 11211 : intval($aConfig['Port']));
		}

		if ($bConnected !== true) {
			$this->_oMemcache = null;
			$aErr             = error_get_last();
			throw new CacheFailException($sName, 'handler_error: connect_fail(' . $aErr['message'] . ')', 0);
		}
	}

	/**
	 * Set a Value to Cache
	 *
	 * @param string $sName Item Name
	 * @param mixed $mValue Item
	 * @param int $iLifetime Lifetime
	 * @throws CacheHandlerException
	 * @return mixed
	 */
	public function set($sName, $mValue, $iLifetime = 0)
	{
		if ($this->_oMemcache == null) {
			throw new CacheFailException($this->_sHandlerName, 'handler_error: not_connect', 0);
		}

		return $this->_oMemcache->set($this->_sPrefix . strtolower($sName), $mValue, 0, $iLifetime < 0 ? 0 : $iLifetime);
	}

	/**
	 * Get a Item
	 *
	 * @param string $sName Item Name
	 * @throws CacheHandlerException
	 * @return mixed
	 */
	public function get($sName)
	{
		if ($this->_oMemcache == null) {
			throw new CacheFailException($this->_sHandlerName, 'handler_error: not_connect', 0);
		}

		return $this->_oMemcache->get($this->_sPrefix . strtolower($sName));
	}

	/**
	 * Delete a Item
	 *
	 * @param string $sName Item Name
	 * @throws CacheHandlerException
	 * @return mixed
	 */
	public function del($sName)
	{
		if ($this->_oMemcache == null) {
			throw new CacheFailException($this->_sHandlerName, 'handler_error: not_connect', 0);
		}

		return $this->_oMemcache->delete($this->_sPrefix . strtolower($sName));
	}

	/**
	 * Delete All
	 *
	 * @throws CacheHandlerException
	 * @return bool
	 */
	public function flush()
	{
		if ($this->_oMemcache == null) {
			throw new CacheFailException($this->_sHandlerName, 'handler_error: not_connect', 0);
		}

		return $this->_oMemcache->flush();
	}
}