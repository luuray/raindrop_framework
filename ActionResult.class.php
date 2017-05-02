<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result Abstract
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop;


abstract class ActionResult
{
	/**
	 * Create a ActionResult Object
	 */
	public abstract function __construct();

	public function __toString()
	{
		return $this->toString();
	}

	public abstract function toString();

	/**
	 * Output Result
	 *
	 * @return mixed
	 */
	public abstract function output();
}