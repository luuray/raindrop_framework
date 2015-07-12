<?php
/**
 * Raindrop Framework for PHP
 *
 * QuickResponseCode(aka QRCode) Drawer
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

namespace Raindrop\Component;


class QRImage
{
	protected static $_oInstance = null;

	protected static function _getInstance()
	{
		if(self::$_oInstance == null){
			self::$_oInstance = new self();
		}

		return self::$_oInstance;
	}

	protected function __construct()
	{
		require_once __DIR__.'/QrCode/QrCode.php';
		require_once __DIR__.'/QrCode/Exceptions/DataDoesntExistsException.php';
		require_once __DIR__.'/QrCode/Exceptions/ImageFunctionUnknownException.php';
		require_once __DIR__.'/QrCode/Exceptions/ImageSizeTooLargeException.php';
		require_once __DIR__.'/QrCode/Exceptions/VersionTooLargeException.php';
	}

	public static function png($sData, $sFile=null)
	{

	}
}