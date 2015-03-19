<?php
/**
 * Raindrop Framework for PHP
 *
 * Web Application
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

use Raindrop\Exceptions\FatalErrorException;
use Raindrop\Exceptions\FileNotFoundException;

require_once 'Application.class.php';

final class WebApplication extends Application
{
	protected function _initialize()
	{
		if (self::IsDebugging()) {
			//Initialize Debugger
			Debugger::Initialize();
			Debugger::Output(Configuration::Get(), 'Configuration');
		}
	}

	protected function _initializeIdentify()
	{
		try {
			if (Loader::Import('web.identify.php', AppDir)) {
				$sIdentifyName = AppName . '\WebIdentify';

				$this->_oIdentify = $sIdentifyName::GetInstance();

				if ($this->_oIdentify instanceof Identify) {
				} else {
					throw new FatalErrorException('identify_not_defined');
				}
			} else {
				throw new FatalErrorException('identify_not_defined');
			}
		} catch (FileNotFoundException $ex) {
			throw new FatalErrorException('identify_not_defined');
		}
	}

	protected function _getRequest()
	{
		return new WebRequest();
	}

	protected function _finish()
	{
		if (self::IsDebugging()) {
			//Output Runtime CountInfo
			$sRunStats = 'Mem:' . byte2string(memory_get_usage()) . ', Time:' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']);
			Logger::Debug('Performance: ' . $sRunStats);
			Debugger::Output($sRunStats, 'Performance');
		}
	}
}