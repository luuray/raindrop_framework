<?php
/**
 * Raindrop Framework for PHP
 *
 * XML Message Serializer for WeChat Module
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Component;

use Raindrop\Exceptions\RuntimeException;

class XMLSerializer
{
	public static function __callStatic($sSerializerType, $aArgs)
	{
		$sSerializer ='Raindrop\Component\WeChat\Component\Serializer\\'.$sSerializerType;

		//decide serializer
		if(class_exists($sSerializer)){
			return (new $sSerializer($aArgs[0]))->__toString();
		}
		else{
			throw new RuntimeException('undefined_serializer:'.strtolower($sSerializer));
		}
	}
}