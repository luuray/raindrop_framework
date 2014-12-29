<?php
/**
 * Raindrop Framework for PHP
 *
 * Cache
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

namespace Raindrop;

use Raindrop\Interfaces\ICache;

final class Cache
{
	protected static $_oInstance = null;

	protected $_aHandlerPool = array();

	/**
	 * @return Cache
	 */
	public static function GetInstance()
	{
		if (self::$_oInstance === null) {
			self::$_oInstance = new self();
		}

		return self::$_oInstance;
	}

#region Operation Methods
	public static function HasHandler($sHandler = 'default')
	{
		return self::GetInstance()->isHandlerExists($sHandler);
	}

	/**
	 * @param string $sKey
	 * @param string $sHandler
	 * @return mixed
	 */
	public static function Get($sKey, $sHandler = 'default')
	{
		$oHandler = self::GetInstance()->getHandler($sHandler);

		return $oHandler->get($sKey);
	}

	/**
	 * @param string $sKey
	 * @param mixed $mValue
	 * @param int $iLifetime
	 * @param string $sHandler
	 * @return mixed
	 */
	public static function Set($sKey, $mValue, $iLifetime = -1, $sHandler = 'default')
	{
		$oHandler = self::GetInstance()->getHandler($sHandler);

		return $oHandler->set($sKey, $mValue, $iLifetime);
	}

	/**
	 * @param string $sKey
	 * @param string $sHandler
	 * @return mixed
	 */
	public static function Del($sKey, $sHandler = 'default')
	{
		$oHandler = self::GetInstance()->getHandler($sHandler);

		return $oHandler->del($sKey);
	}

	/**
	 * @param string $sHandler
	 * @return bool
	 */
	public static function Flush($sHandler = 'default')
	{
		$oHandler = self::GetInstance()->getHandler($sHandler);

		return $oHandler->flush();
	}

	/**
	 * @return void
	 */
	public static function FlushAll()
	{
		self::GetInstance()->clean();
	}

#endregion

	protected function __construct()
	{
		$aHandlersConfig = Configuration::Get('Cache');
		if (!empty($aHandlersConfig) AND is_array($aHandlersConfig)) {
			foreach ($aHandlersConfig AS $_name => $_param) {
				$_name = strtolower($_name);

				$oRefComp                    = new \ReflectionClass('Raindrop\Component\\' . $_param['Component']);
				$this->_aHandlerPool[$_name] = $oRefComp->newInstance($_param['Params'], $_name);
			}
		}
	}

	/**
	 * @param string $sName
	 * @return bool
	 */
	public function isHandlerExists($sName)
	{
		return array_key_exists(strtolower($sName), $this->_aHandlerPool);
	}

	/**
	 * @param string $sName Handler Name
	 * @return ICache
	 * @throws CacheHandlerException
	 */
	public function getHandler($sName)
	{
		$sName = strtolower($sName);
		if (array_key_exists($sName, $this->_aHandlerPool)) {
			return $this->_aHandlerPool[$sName];
		} else {
			throw new CacheHandlerException($sName, 'handler_undefined', -1);
		}
	}

	/**
	 * @param null|string $sName
	 */
	public function clean($sName = null)
	{
		if ($sName == null) {
			foreach ($this->_aHandlerPool AS $_h) {
				$_h->flush();
			}
		} else {
			$this->getHandler($sName)->flush();
		}
	}
}