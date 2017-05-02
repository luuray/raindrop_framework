<?php
/**
 * Raindrop Framework for PHP
 *
 * Log4Php Wrapper
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component;

use Raindrop\Configuration;
use Raindrop\Interfaces\ILogger;

class Log4Php implements ILogger
{
	/**
	 * @var \Logger
	 */
	protected $_oLogger = null;
	protected $_sLoggerName = null;

	public function __construct(Configuration $aConfig, $sRequestId=null)
	{
		$this->_sLoggerName = md5(microtime(true) . '-' . mt_rand(100, 999));
		require_once __DIR__ . '/log4php/Logger.php';
		$aConfig['logger'] = array('name' => $this->_sLoggerName);
		\Logger::configure((array)$aConfig);
		$this->_oLogger = \Logger::getLogger($this->_sLoggerName);
	}

	public function Trace($mMsg = null)
	{
		if ($mMsg == null) {
			$mMsg = debug_backtrace();
		}

		$this->_oLogger->trace($mMsg);
	}

	public function Debug($mMsg)
	{
		$this->_oLogger->debug($mMsg);
	}

	public function Message($mMsg)
	{
		$this->_oLogger->info($mMsg);
	}

	public function Warning($mMsg)
	{
		$this->_oLogger->warn($mMsg);
	}

	public function Error($mMsg)
	{
		$this->_oLogger->error($mMsg);
	}

	public function Fatal($mMsg)
	{
		$this->_oLogger->fatal($mMsg);
	}
} 