<?php
/**
 * Raindrop Framework for PHP
 *
 * Bundle Part
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