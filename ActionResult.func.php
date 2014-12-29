<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result Functional Call
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

function View($sTplName = null, $mData = null)
{
	return new \Raindrop\ActionResult\View($sTplName, $mData);
}

function Json($bAllowGet = false, $mData = null)
{
	return new \Raindrop\ActionResult\Json($bAllowGet, $mData);
}

function SendFile($sFileName)
{
	return new \Raindrop\ActionResult\File($sFileName);
}

function HttpCode($iCode, $mParams = null)
{
	return new \Raindrop\ActionResult\HttpCode($iCode, $mParams);
}

function Xml($bAllowGet = true, $mData = null)
{
	return new \Raindrop\ActionResult\Xml($bAllowGet, $mData);
}

function Redirect()
{
	$oRef = new ReflectionClass('\Raindrop\ActionResult\Redirect');

	return $oRef->newInstanceArgs(func_get_args());
}