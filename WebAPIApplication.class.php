<?php
/**
 * HomeSrv
 *
 *
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2018, Rainhan System
 * Site: www.rainhan.net/?proj=HomeSrv
 */

namespace Raindrop;


use Swoole\Http\Server;

class WebAPIApplication extends Application
{
	protected $_oSwooleServer;

	protected function _initialize()
	{
		if (!class_exists('Swoole\Server')) {
			die('!!! need swoole extension !!!');
		}

		$this->_oSwooleServer = new Server('127.0.0.1', 80);
	}

	protected function _initializeIdentify()
	{
		// TODO: Implement _initializeIdentify() method.
	}

	protected function _getRequest()
	{
		// TODO: Implement _getRequest() method.
	}

	protected function _run()
	{
		// TODO: Implement _run() method.
	}

	protected function _finish()
	{
		// TODO: Implement _finish() method.
	}
}