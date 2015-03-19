<?php
/**
 * Raindrop Framework for PHP
 *
 * Debugger
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
use Raindrop\Exceptions\FileNotFoundException;

class Debugger
{
	/**
	 * @var null|Debugger
	 */
	protected static $_oInstance = null;
	/**
	 * @var IDebugger
	 */
	protected $_oDebugger = null;

	public static function Initialize()
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

	public static function Output($sMessage, $sLabel = null)
	{
		self::GetInstance()->_oDebugger->Output($sMessage, $sLabel);
	}

	protected function __construct()
	{
		$aConfig = Configuration::Get('Debugger', null);

		if ($aConfig !== null && !empty($aConfig['Component'])) {
			try {
				$this->_oDebugger = new \ReflectionClass('Raindrop\Component\\' . $aConfig['Component']);
				$this->_oDebugger = $this->_oDebugger->newInstance(empty($aConfig['Params']) ? null : $aConfig['Params']);
			} catch (FileNotFoundException $ex) {
				throw new ComponentNotFoundException('Raindrop\Component\\' . $aConfig['Component']);
			}
		}

		self::$_oInstance = $this;
	}
}