<?php
/**
 * Raindrop Framework for PHP
 *
 * System Exceptions
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Exceptions;

use Exception;
use Raindrop\Application;
use Raindrop\Logger;

if (PHP_MAJOR_VERSION < 7) {
	require_once 'PHP7Compatible.php';
}

/**
 * Class ApplicationException(Base Exception)
 * @package Raindrop
 */
abstract class ApplicationException extends Exception
{
	public function __construct($message = '', $code = 0, Exception $previous = null)
	{
		Application::SetLastException($this);

		$message = empty($message) ?
			('Exception:' . get_called_class() . '; Line:' . $this->line .
				(Application::IsDebugging() ? '; File:' . $this->file : null)) : $message;

		parent::__construct($message, intval($code), $previous);
	}
}

#region Standard Exceptions
/**
 * Class FatalErrorException
 * @package Raindrop
 */
class FatalErrorException extends ApplicationException
{
	public function __construct($message = '', $code = 0, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);

		Logger::Fatal(parent::__toString());
	}
}

class RuntimeException extends ApplicationException
{
	public function __construct($message = '', $code = 0, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);

		Logger::Message(parent::__toString());
	}
}

class ConfigurationMissingException extends FatalErrorException
{
	protected $_sSection;

	public function __construct($sSection)
	{
		$this->_sSection = $sSection;
		parent::__construct(sprintf('Configuration section "%s" is missing', $sSection));
	}

	public final function getSection()
	{
		return $this->_sSection;
	}
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
	public function __construct($sMessage = null)
	{
		if ($sMessage === null) {
			$sFunc    = $this->getTrace()[0]['function'];
			$sMessage = isset($this->getTrace()[0]['class']) ?
				sprintf('%s%s%s', $this->getTrace()[0]['class'], $this->getTrace()[0]['type'], $sFunc) : $sFunc;

			Logger::Warning(
				sprintf('[NotImplementedException] File: %s, Line: %d, Call: %s, Args: %s',
					empty($this->getTrace()[0]['file']) ? $this->file : $this->getTrace()[0]['file'],
					empty($this->getTrace()[0]['line']) ? $this->line : $this->getTrace(),
					$sMessage,
					!empty($this->getTrace()[0]['args']) ? $this->getTrace()[0]['args'][0] . ', ...' : 'null'));
		} else {
			Logger::Warning(sprintf('[NotImplementedException] %s', $sMessage));
		}
		parent::__construct('NotImplemented:' . $sMessage);
	}
}

/**
 * Class ComponentUndefinedException
 * @package Raindrop\Exceptions
 */
class ComponentUndefinedException extends ApplicationException
{
	/**
	 * ComponentUndefinedException constructor.
	 *
	 * @param string $sComponent
	 * @param int $sDefine
	 */
	public function __construct($sComponent, $sDefine)
	{
		$sMsg = sprintf('[ComponentUndefined]component: %s, define: %s', $sComponent, $sDefine);
		Logger::Warning($sMsg);

		parent::__construct($sMsg, -1, null);
	}
}

#endregion

#region Argument Exceptions
/**
 * Class InvalidArgumentException
 * @package Raindrop
 */
class InvalidArgumentException extends RuntimeException
{
	public function __construct($sArgument, $sRequire = null, $sProvide = null)
	{
		parent::__construct($sArgument . ($sRequire == null ? null : ', require type: ' . $sRequire) . ($sProvide == null ? null : ', provide type: ' . $sProvide));
	}
}

/**
 * Class ArgumentNullException
 * @package Raindrop
 */
class ArgumentNullException extends InvalidArgumentException
{
	public function __construct($sArgument, $sType = null)
	{
		parent::__construct($sArgument, $sType);
	}
}

#endregion