<?php
/**
 * Raindrop Framework for PHP
 *
 * Simple Logger Component
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
use Raindrop\Exceptions\FatalErrorException;
use Raindrop\Interfaces\ILogger;

class SimpleLogger implements ILogger
{
	/**
	 * @var Configuration
	 */
	protected $_oConfig;
	protected $_sRequestId;

	public function __construct(Configuration $oConfig, $sRequestId=null)
	{
		$this->_oConfig = $oConfig;
		$this->_sRequestId = $sRequestId;
	}

	public function Trace($mMsg)
	{
		return $this->_writeLine('Trace', $mMsg);
	}

	public function Debug($mMsg)
	{
		return $this->_writeLine('Debug', $mMsg);
	}

	public function Message($mMsg)
	{
		return $this->_writeLine('Message', $mMsg);
	}

	public function Warning($mMsg)
	{
		return $this->_writeLine('Warning', $mMsg);
	}

	public function Error($mMsg)
	{
		return $this->_writeLine('Error', $mMsg);
	}

	public function Fatal($mMsg)
	{
		return $this->_writeLine('Fatal', $mMsg);
	}

	protected function _writeLine($sLevel, $mMsg)
	{
		$oFilter = $this->_oConfig->LevelFiles;
		$sFilePath = null;
		if ($oFilter instanceof Configuration) {
			$sFilePath = $oFilter->$sLevel;
		}
		$sFilePath == null ? $this->_oConfig->DefualtFile : $sFilePath;

		//black hole
		if ($sFilePath == null) return true;
		$sFilePath = str_replace('$DATE$', date('Y-m-d'), $sFilePath);

		if(!file_exists(pathinfo($sFilePath, PATHINFO_DIRNAME))){
			@mkdir(pathinfo($sFilePath, PATHINFO_DIRNAME), 0755, true);
		}

		$bResult = file_put_contents($sFilePath, '[' . date('Y-m-d H:i:s O') . ", {$sLevel}".($this->_sRequestId==null?null:", {$this->_sRequestId}")."]\t" . (string)$mMsg . PHP_EOL, FILE_APPEND);
		if ($bResult == false) {
			$aErr = error_get_last();
			throw new FatalErrorException($aErr['message']);
		}

		return true;
	}
}