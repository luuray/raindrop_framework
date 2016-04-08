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

use Raindrop\Configuration;
use Raindrop\Exceptions\Cache\CacheFailException;
use Raindrop\Exceptions\Cache\CacheMissingException;
use Raindrop\Exceptions\ConfigurationMissingException;
use Raindrop\Exceptions\InvalidArgumentException;
use Raindrop\Interfaces\ICache;

class MemCache implements ICache
{
	/**
	 * @var string
	 */
	protected $_sHandlerName;

	/**
	 * @var null|Configuration
	 */
	protected $_oConfig = null;
	/**
	 * @var string
	 */
	protected $_sPrefix = null;

	/**
	 * @var null|int
	 */
	protected $_iLifetime = null;
	/**
	 * @var \Memcache
	 */
	protected $_oMemcache;

	/**
	 * Construct Cache Adapter
	 *
	 * @param Configuration $oConfig
	 * @param string $sName
	 *
	 * @throws CacheFailException
	 * @throws InvalidArgumentException
	 */
	public function __construct(Configuration $oConfig, $sName)
	{
		if ($oConfig == null) {
			throw new InvalidArgumentException('config');
		}
		if (empty($sName)) {
			throw new InvalidArgumentException('name');
		}

		$this->_oConfig   = $oConfig;

		$this->_sHandlerName = $sName;
		$this->_sPrefix   = $oConfig->Prefix == null ? '' : $oConfig->Prefix;
		$this->_iLifetime = $oConfig->Lifetime === null ? 0 : (int)$oConfig->Lifetime;

		if ($oConfig->Server == null) {
			throw new ConfigurationMissingException(sprintf('Cache\%s\Params\Server', $sName));
		}
		$this->_oMemcache = new \Memcache();
		if (str_beginwith($oConfig->Server, 'unix://')) {
			$bConnected = $this->_oMemcache->connect($oConfig->Server, 0);
		} else {
			$bConnected = @$this->_oMemcache->connect(
				$oConfig->Server,
				$oConfig->Port == null ? 11211 : intval($oConfig->Port));
		}

		if ($bConnected !== true) {
			$this->_oMemcache = null;
			$aErr             = error_get_last();
			throw new CacheFailException($sName, 'handler_error: connect_fail(' . $aErr['message'] . ')');
		}
	}

	/**
	 * Set a Value to Cache
	 *
	 * @param string $sName
	 * @param mixed $mValue
	 *
	 * @return bool
	 * @throws CacheFailException
	 */
	public function set($sName, $mValue)
	{
		if ($this->_oMemcache == null) {
			throw new CacheFailException($this->_sHandlerName, 'handler_error: not_connect', 0);
		}

		return $this->_oMemcache->set($this->_sPrefix . strtolower($sName), $mValue, 0, $this->_iLifetime);
	}

	/**
	 * @param string $sName
	 *
	 * @return string
	 * @throws CacheFailException
	 * @throws CacheMissingException
	 */
	public function get($sName)
	{
		if ($this->_oMemcache == null) {
			throw new CacheFailException($this->_sHandlerName, 'handler_error: not_connect', 0);
		}

		$sKey = $this->_sPrefix . strtolower($sName);

		$sResult = $this->_oMemcache->get($sKey);

		if ($sResult === false) {
			throw new CacheMissingException($this->_sHandlerName, $sKey);
		}

		return $sResult;
	}

	/**
	 * Delete a Item
	 *
	 * @param string $sName
	 *
	 * @return bool
	 * @throws CacheFailException
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
	 * @return bool
	 * @throws CacheFailException
	 */
	public function flush()
	{
		if ($this->_oMemcache == null) {
			throw new CacheFailException($this->_sHandlerName, 'handler_error: not_connect', 0);
		}

		return $this->_oMemcache->flush();
	}
}