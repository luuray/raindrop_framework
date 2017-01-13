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
	public static function HasHandler($sHandler)
	{
		return array_key_exists(
			strtolower($sHandler),
			self::GetInstance()->_aHandlerPool);
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
	 * @param $sKey
	 * @param $mValue
	 * @param int $iLifetime
	 * @param string $sHandler
	 *
	 * @return mixed
	 */
	public static function Set($sKey, $mValue, $iLifetime = 0, $sHandler = 'default')
	{
		$oHandler = self::GetInstance()->getHandler($sHandler);

		return $oHandler->set($sKey, $mValue, $iLifetime);
	}

	/**
	 * @param string $sKey
	 * @param string $sHandler
	 *
	 * @return mixed
	 */
	public static function Del($sKey, $sHandler = 'default')
	{
		$oHandler = self::GetInstance()->getHandler($sHandler);

		return $oHandler->del($sKey);
	}

	/**
	 * @param null|string $sHandler
	 * @param null|int $iLevel
	 *
	 * @return bool
	 */
	public static function Flush($sHandler = null, $iLevel = null)
	{
		//flush handler
		if ($sHandler !== null) {
			$oHandler = self::GetInstance()->getHandler($sHandler);

			return $oHandler->flush();
		} else {
			$iLevel = $iLevel === null ? Configuration::GetRoot('System/CacheLevel') : (int)$iLevel;

			return self::GetInstance()->flushByLevel($iLevel);
		}
	}

#endregion

	protected function __construct()
	{
		$oHandlersConfig = Configuration::Get('Cache');

		if ($oHandlersConfig instanceof Configuration) {
			foreach ($oHandlersConfig AS $_name => $_config) {
				$_name      = strtolower($_name);
				$sComponent = $_config->Component;

				//black hole
				if ($sComponent == null) {
					Logger::Warning(sprintf('[Cache]Handler "%s" not defined component, use "BlackHole"', $_name));
					$sComponent = 'BlackHoleCache';
				}

				$oRefComp                    = new \ReflectionClass('Raindrop\Component\\' . $sComponent);
				$this->_aHandlerPool[$_name] = [
					'handler' => $oRefComp->newInstance($_config->Params, $_name),
					'level'   => $_config->Level];
			}
		}
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
			return $this->_aHandlerPool[$sName]['handler'];
		} else {
			Logger::Warning(sprintf('[Cache]Handler "%s" undefined, return "BlackHole"', $sName));

			return new BlackHoleCache(null, $sName);
		}
	}

	/**
	 * @param $sName
	 *
	 * @return null
	 */
	public function getHandlerLevel($sName)
	{
		$sName = strtolower($sName);
		if (array_key_exists($sName, $this->_aHandlerPool)) {
			return $this->_aHandlerPool[$sName]['level'];
		} else {
			Logger::Warning(sprintf('[Cache]Handler "%s" undefined, return "BlackHole"', $sName));

			return null;
		}
	}

	/**
	 * @param null|int $iLevel
	 */
	public function flushByLevel($iLevel = null)
	{
		foreach ($this->_aHandlerPool AS $_handler) {
			if ($iLevel == null OR $_handler['level'] >= $iLevel) $_handler['handler']->flush();
		}

		return true;
	}
}