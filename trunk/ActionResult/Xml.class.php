<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result in XML
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

namespace Raindrop\ActionResult;

use Raindrop\ActionResult;
use Raindrop\NotImplementedException;

class Xml extends ActionResult
{
	/**
	 * Create a ActionResult Object
	 *
	 * @param bool $bAllowGet Allow Request by GET Method
	 * @param mixed $mData Result Data
	 */
	public function __construct($bAllowGet = true, $mData = null)
	{
		// TODO: Implement __construct() method.
	}

	/**
	 * Output Result
	 *
	 * @return mixed
	 */
	public function Output()
	{
		// TODO: Implement Output() method.
	}

	public function toString()
	{
		throw new NotImplementedException();
	}
}