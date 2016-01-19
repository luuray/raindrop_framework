<?php
/**
 * Raindrop Framework for PHP
 *
 *  Aliyun ACE Cache Handler
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2016, Rainhan System
 * Site: www.rainhan.net/?proj=FruitShop
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\Component\Aliyun;

use Raindrop\Configuration;
use Raindrop\Interfaces\ICache;

class ACECache implements ICache
{
	protected $_sHandlerName;
	protected $_oConfig;

	public function __construct(Configuration $oConfig, $sName)
	{
		$this->_sHandlerName = $sName;
		$this->_oConfig = $oConfig;
	}

	public function get($sName)
	{
		$sResult = Alibaba::Cache($this->_oConfig->Name)->get($sName);
		if($sResult != null && ($sResult=@unserialize($sResult))!=false){
			return $sResult;
		}
		return false;
	}

	public function del($sName)
	{
		return Alibaba::Cache($this->_oConfig->Name)->delete($sName);
	}

	public function set($sName, $mValue)
	{
		return Alibaba::Cache($this->_oConfig->Name)->set($sName, serialize($mValue));
	}

	public function flush()
	{
		return false;
	}
}