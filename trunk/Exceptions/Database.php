<?php
/**
 * Raindrop Framework for PHP
 *
 * Database's Exceptions
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
namespace Raindrop\Exceptions\Database;

use Raindrop\Application;
use Raindrop\Exceptions\ApplicationException;

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
