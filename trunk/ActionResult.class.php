<?php
/**
 * BoostCenter
 *
 *
 *
 * @author $Author$
 * @copyright
 * @date $Date$
 *
 * Copyright (c) 2010-2014,
 * Site:
 *
 * $Id$
 *
 * @version $Rev$
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