<?php
/**
 * Raindrop Framework for PHP
 *
 * Debugger Interface
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
namespace Raindrop\Interfaces;
interface IDebugger
{
	public function __construct($aConfig);

	public function output($mMsg, $sLabel = '');
}