<?php
/**
 * Raindrop Framework for PHP
 *
 * FirePHP Debugger Wrapper
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component;

use Raindrop\Interfaces\IDebugger;
use Raindrop\NotInitializeException;

if (!defined('SysRoot')) {
	@header('HTTP/1.1 404 Not Found');
	die('Access Forbidden');
}

class FirePhp implements IDebugger
{
	/**
	 * @var \FirePHP|null
	 */
	protected $_oFirePhp = null;

	/**
	 * @param $aConfig
	 */
	public function __construct($aConfig)
	{
		require_once __DIR__ . '/firephp/FirePHP.class.php';
		$this->_oFirePhp = \FirePHP::getInstance(true);
		$this->_oFirePhp->setOptions((array)$aConfig);
	}

	/**
	 * @param $mMsg
	 * @param string $sLabel
	 */
	public function Output($mMsg, $sLabel = '')
	{
		$this->_oFirePhp->info($mMsg, $sLabel);
	}
}