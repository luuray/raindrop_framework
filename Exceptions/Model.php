<?php
/**
 * Raindrop Framework for PHP
 *
 * Model's Exceptions
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
namespace Raindrop\Exceptions\Model;

use Raindrop\Exceptions\FileNotFoundException;


/**
 * Class ModelNotFoundException
 * @package Raindrop
 */
class ModelNotFoundException extends FileNotFoundException
{
	public function __construct($sModelName, Exception $ex)
	{
		parent::__construct('model: ' . $sModelName, 0, $ex);
	}
}