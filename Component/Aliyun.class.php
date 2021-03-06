<?php
/**
 * Raindrop Framework for PHP
 *
 * Aliyun Wrapper
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component;


use Raindrop\Component\Aliyun\MNS;
use Raindrop\Configuration;
use Raindrop\Exceptions\ConfigurationMissingException;

class Aliyun
{
	public static function getMNS($sConfig, $sName)
	{
		$oConfig = Configuration::Get($sConfig);
		if($oConfig == null){
			throw new ConfigurationMissingException($sConfig);
		}

		return new MNS($oConfig, $sName);
	}

	public static function getACE()
	{
	}

	public static function getOSS()
	{
	}
}