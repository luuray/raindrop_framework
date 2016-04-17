<?php
/**
 * BoostStream
 *
 *
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2016, Rainhan System
 * Site: www.rainhan.net/?proj=BoostStream
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\Console;


final class Listener
{
	protected $_sHost;
	protected $_iPort;
	protected $_iType;

	public function __construct($sHost, $iPort, $iType=SWOOLE_TCP)
	{
		$this->_sHost=$sHost;
		$this->_iPort=$iPort;
		$this->_iType=$iType;
	}

	public function getHost()
	{
		return $this->_sHost;
	}

	public function getPort()
	{
		return $this->_iPort;
	}

	public function getType()
	{
		return $this->_iType;
	}
}