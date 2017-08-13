<?php
/**
 * Raindrop Framework for PHP
 *
 * UserInfo Model for WeChat Module
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Model;


class UserInfo implements \Serializable, \JsonSerializable
{
	protected $_aData;

	public function __construct($oObj)
	{
		$this->_aData = array_key_case(get_object_vars($oObj), CASE_LOWER);
	}

	public function __get($sKey)
	{
		$sKey = strtolower($sKey);
		if (array_key_exists($sKey, $this->_aData)) {
			return $this->_aData[$sKey];
		}

		return null;
	}

	public function serialize()
	{
		return serialize($this->_aData);
	}

	public function unserialize($serialized)
	{
		$this->_aData = unserialize($serialized);
	}

	function jsonSerialize()
	{
		return $this->_aData;
	}

}