<?php
/**
 * Raindrop Framework for PHP
 *
 * TaskQueue Interface
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2015, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\Interfaces;


use Raindrop\Configuration;

interface ITaskQueue
{
	public function __construct($sName, Configuration $oConfig);
}