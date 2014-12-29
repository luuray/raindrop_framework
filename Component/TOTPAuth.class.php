<?php
/**
 * Raindrop Framework for PHP
 *
 * TOTP Authentication
 *
 * @author $Author$
 * @copyright Rainhan system
 * @date $Date$
 *
 * Copyright (c) 2010-2014, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\Component;


class TOTPAuth
{
	protected $_sKey;

	public function __construct($sKey = null)
	{
		$this->_sKey = $sKey;
	}

	public function generateKey()
	{
	}

	public function verifySecret($sSubject)
	{
		return false;
	}

	public function getKey()
	{
	}
} 