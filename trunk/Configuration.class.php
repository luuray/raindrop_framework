<?php
/**
 * Raindrop Framework for PHP
 *
 * Configuration Manager
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


class Configuration
{
	protected static $_oInstance = null;

	protected $_aConfig = array();

	/**
	 * Load Configuration File (Alias of Configuration::GetInstance)
	 */
	public static function Load()
	{
		self::GetInstance();
	}

	public static function GetInstance()
	{
		if (self::$_oInstance === null) {
			new self();
		}

		return self::$_oInstance;
	}

	public static function Get($sKey = null, $mDefaultValue = null)
	{
		if ($sKey == null) {
			return self::GetInstance()->_aConfig;
		}

		$aTree    = preg_split('#[/\\\]+#', trim($sKey));
		$pPointer = self::GetInstance()->_aConfig;

		foreach ($aTree AS $_k) {
			if (array_key_exists($_k, $pPointer)) {
				$pPointer = $pPointer[$_k];
			} else {
				return $mDefaultValue;
			}
		}

		return $pPointer;
	}

	protected function __construct()
	{
		if (Application::IsDebugging()) {
			$this->_aConfig = Loader::Import('config.debug.php', AppDir);
		} else {
			$this->_aConfig = Loader::Import('config.php', AppDir);
		}

		self::$_oInstance = $this;
	}
}