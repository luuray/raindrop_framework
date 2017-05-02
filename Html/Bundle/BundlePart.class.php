<?php
/**
 * Raindrop Framework for PHP
 *
 * Bundle Part
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Html\Bundle;


use Raindrop\NotImplementedException;

abstract class BundlePart
{
	protected $_sName = '';

	protected $_aFiles = array();
	protected $_aContents = array();

	public final function __construct($sBundleName)
	{
		$this->_sName = strtolower(trim($sBundleName));
	}

	public final function __get($sPropName)
	{
		$sPropName = strtolower($sPropName);
		if ($sPropName == 'name' OR $sPropName == 'bundlename') {
			return $this->_sName;
		}
	}

	public final function __set($sPropName, $mValue)
	{
		throw new NotImplementedException;
	}

	public final function __toString()
	{
		return $this->toString();
	}

	public final function addFile($sPath)
	{
	}

	public final function addContent($sContent)
	{
	}

	public abstract function toString();

	public abstract function toArray();

	public abstract function hasChanged();
}