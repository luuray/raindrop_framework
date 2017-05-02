<?php
/**
 * Raindrop Framework for PHP
 *
 * Controller Exceptions
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Exceptions\Controller;

use Raindrop\Exceptions\FileNotFoundException;

class ControllerNotFoundException extends FileNotFoundException
{
}

class ActionNotFoundException extends ControllerNotFoundException
{
}