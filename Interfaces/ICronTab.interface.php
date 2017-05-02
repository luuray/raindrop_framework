<?php
/**
 * Raindrop Framework for PHP
 *
 * CronTab Worker Interface
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Interfaces;


interface ICronTab
{
	public function __construct($iInverval, $sMode);
}