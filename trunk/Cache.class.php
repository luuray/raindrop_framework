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

use Raindrop\Component\BlackHoleCache;
use Raindrop\Exceptions\Cache\CacheFailException;
use Raindrop\Interfaces\ICache;

/**
 * Class Cache
 * @package Raindrop
 */
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
	 *
	 * @return mixed
	 * @throws CacheFailException|CacheMissingException
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
		$oHandlersConfig    = Configuration::Get('Cache');

		if ($oHandlersConfig instanceof Configuration) {
			foreach ($oHandlersConfig AS $_name => $_config) {
				$_name = strtolower($_name);
				$sComponent = $_config->Component;

				//black hole
				if ($sComponent == null) {
					Logger::Warning(sprintf('[Cache]Handler "%s" not defined component, use "BlackHole"', $_name));
					$sComponent = 'BlackHoleCache';
				}

				$oRefComp                    = new \ReflectionClass('Raindrop\Component\\' . $sComponent);
				$this->_aHandlerPool[$_name] = $oRefComp->newInstance($_config->Params, $_name);
			}
		}
	}

	/**
	 * @param string $sName
	 * @return bool
	 */
	public function isHandlerExists($sName)
	{
		$sName = strtolower($sName);

		return array_key_exists($sName, $this->_aHandlerPool);
	}

	/**
	 * @param $sName
	 *
	 * @return ICache|null
	 */
	public function getHandler($sName)
	{
		$sName = strtolower($sName);
		if (array_key_exists($sName, $this->_aHandlerPool)) {
			return $this->_aHandlerPool[$sName];
		} else {
			Logger::Warning(sprintf('[Cache]Handler "%s" undefined, return "BlackHole"', $sName));

			return new BlackHoleCache(null, $sName);
		}
	}

	/**
	 * @param null|string $sName
	 */
	public function clean($sName = null)
	{
		if ($sName == null) {
			foreach ($this->_aHandlerPool AS $_handler) {
				$_handler instanceof BlackHoleCache ? null : $_handler->flush();
			}
		} else {
			$this->getHandler($sName) instanceof BlackHoleCache ? null : $this->getHandler($sName)->flush();
		}
	}
}