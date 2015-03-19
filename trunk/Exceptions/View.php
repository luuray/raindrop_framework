<?php
/**
 * Raindrop Framework for PHP
 *
 * Controller's Exceptions
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
namespace Raindrop\Exceptions\View;
use Raindrop\Exceptions\FileNotFoundException;

class ViewNotFoundException extends FileNotFoundException
{
}

class LayoutNotFoundException extends FileNotFoundException
{
}

class PartialNotFoundException extends FileNotFoundException
{
}