<?php
/**
 * Raindrop Framework for PHP
 *
 * Controller Interface
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


interface IController
{
	public function identifyRequired();

	public function prepare();
}