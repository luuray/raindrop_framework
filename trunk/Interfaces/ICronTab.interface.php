<?php
/**
 * Raindrop Framework for PHP
 *
 * CronTab Worker Interface
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2015, Rainhan System
 * Site:raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\Interfaces;


interface ICronTab
{
	public function __construct($iInverval, $sMode);
}