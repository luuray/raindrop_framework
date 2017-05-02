<?php
/**
 * Raindrop Framework for PHP
 *
 * View Exceptions
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
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