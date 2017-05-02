<?php
/**
 * Raindrop Framework for PHP
 *
 * Debugger Interface
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Interfaces;
interface IDebugger
{
	public function __construct($aConfig);

	public function output($mMsg, $sLabel = '');
}