<?php
/**
 * Raindrop Framework for PHP
 *
 * System Exceptions
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
namespace Raindrop\Exceptions;

use Exception;
use Raindrop\Application;

/**
 * Class ApplicationException(Base Exception)
 * @package Raindrop
 */
abstract class ApplicationException extends Exception
{
	public function __construct($message = '', $code = 0, Exception $previous = null)
	{
		Application::SetLastException($this);

		parent::__construct($message, $code, $previous);
	}
}

#region Standard Exceptions
/**
 * Class FatalErrorException
 * @package Raindrop
 */
class FatalErrorException extends ApplicationException
{
}

class RuntimeException extends ApplicationException
{
}

#endregion

#region File(Component, Module)
/**
 * Class FileNotFoundException
 * @package Raindrop
 */
class FileNotFoundException extends FatalErrorException
{
}

/**
 * Class ComponentNotFoundException
 * @package Raindrop
 */
class ComponentNotFoundException extends FileNotFoundException
{
}

/**
 * Class ModuleNotFoundException
 * @package Raindrop
 */
class ModuleNotFoundException extends FileNotFoundException
{
}

#endregion

#region Objective
/**
 * Class NotInitializeException
 * @package Raindrop
 */
class NotInitializeException extends ApplicationException
{
}

/**
 * Class InitializedException
 * @package Raindrop
 */
class InitializedException extends ApplicationException
{
}

/**
 * Class NotImplementedException
 * @package Raindrop
 */
class NotImplementedException extends ApplicationException
{
}

#endregion

#region Argument Exceptions
/**
 * Class InvalidArgumentException
 * @package Raindrop
 */
class InvalidArgumentException extends ApplicationException
{
}

/**
 * Class ArgumentNullException
 * @package Raindrop
 */
class ArgumentNullException extends InvalidArgumentException
{
}

#endregion

#region Cache
class CacheFailException extends ApplicationException
{
}
#endregion