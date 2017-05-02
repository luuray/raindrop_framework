<?php
/**
 * Raindrop Framework for PHP
 *
 * Logger
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop;

use Raindrop\Exceptions\ComponentNotFoundException;

class Logger
{
	protected static $_oLogger = null;
	protected static $_oInstance = null;
	protected $_sRequestId = null;

	public static function GetInstance($sRequestId=null)
	{
		if (self::$_oInstance === null) {
			new self($sRequestId);
		}
	}

	public static function Initialize($sRequestId=null)
	{
		self::GetInstance($sRequestId);
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

	protected function __construct($sRequestId)
	{
		$aConfig = Configuration::Get('Logger', null);
		$this->_sRequestId = $sRequestId;

		if ($aConfig != null) {
			try {
				$oRefComp       = new \ReflectionClass('Raindrop\Component\\' . $aConfig['Component']);
				self::$_oLogger = $oRefComp->newInstance($aConfig['Params'], $sRequestId);
			} catch (FileNotFoundException $ex) {
				throw new ComponentNotFoundException('Raindrop\Component\\' . $aConfig['Component']);
			}
		}

		self::$_oInstance = $this;
	}
}