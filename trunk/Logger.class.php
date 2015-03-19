<?php
/**
 * Raindrop Framework for PHP
 *
 * Logger
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

use Raindrop\Exceptions\ComponentNotFoundException;

class Logger
{
	protected static $_oLogger = null;
	protected static $_oInstance = null;

	public static function GetInstance()
	{
		if (self::$_oInstance === null) {
			new self();
		}
	}

	public static function Initialize()
	{
		self::GetInstance();
	}

	public static function Trace($mMsg)
	{
		if (self::$_oLogger != null) {
			self::$_oLogger->Trace($mMsg);
		}
	}

	public static function Debug($mMsg)
	{
		if (self::$_oLogger != null) {
			self::$_oLogger->Debug($mMsg);
		}
	}

	public static function Message($mMsg)
	{
		if (self::$_oLogger != null) {
			self::$_oLogger->Message($mMsg);
		}
	}

	public static function Warning($mMsg)
	{
		if (self::$_oLogger != null) {
			self::$_oLogger->Warning($mMsg);
		}
	}

	public static function Error($mMsg)
	{
		if (self::$_oLogger != null) {
			self::$_oLogger->Error($mMsg);
		}
	}

	public static function Fatal($mMsg)
	{
		if (self::$_oLogger != null) {
			self::$_oLogger->Fatal($mMsg);
		}
	}

	protected function __construct()
	{
		$aConfig = Configuration::Get('Logger', null);

		if ($aConfig != null) {
			try {
				$oRefComp       = new \ReflectionClass('Raindrop\Component\\' . $aConfig['Component']);
				self::$_oLogger = $oRefComp->newInstance($aConfig['Params']);
			} catch (FileNotFoundException $ex) {
				throw new ComponentNotFoundException('Raindrop\Component\\' . $aConfig['Component']);
			}
		}

		self::$_oInstance = $this;
	}
}