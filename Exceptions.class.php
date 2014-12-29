<?php
/**
 * Raindrop Framework for PHP
 *
 * Exceptions
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2014,
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop;

use Exception;

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

#endregion

#region Component&FileSystem Exceptions
/**
 * Class ComponentNotFoundException
 * @package Raindrop
 */
class ComponentNotFoundException extends ApplicationException
{
}

/**
 * Class FileNotFoundException
 * @package Raindrop
 */
class FileNotFoundException extends ApplicationException
{
}

/**
 * Class ModuleNotFoundException
 * @package Raindrop
 */
class ModuleNotFoundException extends ApplicationException
{
}

/**
 * Class ModelNotFoundException
 * @package Raindrop
 */
class ModelNotFoundException extends ApplicationException
{
	public function __construct($sModelName, Exception $ex)
	{
		parent::__construct('model: ' . $sModelName, 0, $ex);
	}
}

/**
 * Class ResultTypeNotFoundException
 * @package Raindrop
 */
class ResultTypeNotFoundException extends ApplicationException
{
}

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

#region Database Adapter
class DatabaseException extends ApplicationException
{
}

class DatabaseConnectionException extends DatabaseException
{
	/**
	 * @param string $sDataSource
	 * @param string $sMessage
	 * @param int $iErrorCode
	 * @param null|Exception $ePrevious
	 */
	public function __construct($sDataSource = 'default', $sMessage, $iErrorCode, $ePrevious = null)
	{
		parent::__construct(
			sprintf('datasource: %s, message: %s', $sDataSource, $sMessage),
			$iErrorCode, $ePrevious);
	}
}

class DatabaseQueryException extends DatabaseException
{
	/**
	 * @param string $sDataSource
	 * @param int $sQuery
	 * @param Exception $aParam
	 * @param $sMessage
	 * @param $iErrorCode
	 * @param null $ePrevious
	 */
	public function __construct($sDataSource = 'default', $sQuery, $aParam, $sMessage, $iErrorCode, $ePrevious = null)
	{
		if (Application::IsDebugging()) {
			parent::__construct(
				sprintf('datasource: %s, query: %s, param: %s, message: %s',
					$sDataSource, $sQuery, print_r($aParam, true), $sMessage),
				intval($iErrorCode), $ePrevious);
		} else {
			parent::__construct(
				sprintf('datasource: %s, message: %s', $sDataSource, $sMessage),
				$iErrorCode, $ePrevious);
		}
	}
}

#endregion

#region Cache Exceptions
class CacheException extends ApplicationException
{
}

class CacheHandlerException extends CacheException
{
	/**
	 * @param string $sHandler
	 * @param string $sMessage
	 * @param int $iCode
	 * @param Exception $ePrevious
	 */
	public function __construct($sHandler, $sMessage, $iCode, $ePrevious = null)
	{
		parent::__construct(
			sprintf('handler: %s, message: %s', $sHandler, $sMessage),
			$iCode, $ePrevious);
	}
}

#endregion

#region Identify Exceptions
class IdentifyException extends ApplicationException
{
}

class UnidentifiedException extends IdentifyException
{
}

class NoPermissionException extends IdentifyException
{
}

#endregion

#region Notification Exception
class NotificationException extends ApplicationException
{
}

#endregion

#region Action Result Exceptions
class ActionResultException extends ApplicationException
{
}

class ViewNotFound extends ActionResultException
{
	public function __construct($sView)
	{
		parent::__construct('View Not Found:' . $sView);
	}
}
#endregion