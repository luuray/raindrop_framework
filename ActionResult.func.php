<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result Functional Caller
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

function View($sTplName = null, $mData = null)
{
	return new \Raindrop\ActionResult\View($sTplName, $mData);
}

function Json($bAllowGet = false, $mData = null)
{
	return new \Raindrop\ActionResult\Json($bAllowGet, $mData);
}

function JsonP($bAllowGet=false, $sCallback, $mData=null)
{
	return new \Raindrop\ActionResult\JsonP($bAllowGet, $sCallback, $mData);
}

function FileStream($sFileName)
{
	return new \Raindrop\ActionResult\FileStream($sFileName);
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

function RawStream($pStream, $iCode=200)
{
	return new \Raindrop\ActionResult\RawStream($pStream, $iCode);
}