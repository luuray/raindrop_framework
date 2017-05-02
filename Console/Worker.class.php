<?php
/**
 * BoostQueue
 *
 *
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\Console;


abstract class Worker
{
	private $_oServer;
	private $_iWorkerId;

	public final function __construct(\swoole_server $oServer)
	{
		$this->_oServer = $oServer;
		
		$this->_initialize();
	}
	
	protected function _initialize()
	{
	}
	
	public function run()
	{
	}

	public final function setWorkerId($iWorkerId)
	{
		$this->_iWorkerId = $iWorkerId;
	}
	
	public final function getServer()
	{
		return $this->_oServer;
	}
	
	public final function getWorkerId()
	{
		return $this->_iWorkerId;
	}

	public function getTicker()
	{
		return null;
	}

	public function getListener()
	{
		return null;
	}
	
	public function setHandler(\swoole_server_port $oHandler)
	{
	}
}