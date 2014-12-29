<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result for File Download
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
use Raindrop\InvalidArgumentException;
use Raindrop\mix;
use Raindrop\NotImplementedException;

class File extends ActionResult
{
	/**
	 * Create a ActionResult Object
	 *
	 * @param string $sFileName File name to download
	 * @throws InvalidArgumentException
	 */
	public function __construct($sFileName = null)
	{
		if (empty($sFileName)) {
			throw new InvalidArgumentException('filename');
		}
	}

	/**
	 * Output Result
	 *
	 * @return mix
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