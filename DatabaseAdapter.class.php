<?php
/**
 * Raindrop Framework for PHP
 *
 * Database Adapter
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

namespace Raindrop;

use Raindrop\Exceptions\Database\DatabaseConnectionException;
use Raindrop\Exceptions\Database\DatabaseException;
use Raindrop\Exceptions\Database\DatabaseQueryException;
use Raindrop\Exceptions\FatalErrorException;
use Raindrop\Exceptions\InvalidArgumentException;
use Raindrop\Exceptions\NotImplementedException;
use Raindrop\Exceptions\RuntimeException;
use Raindrop\Interfaces\IDbConnector;
use Raindrop\ORM\BaseModel;

/**
 * Class DatabaseAdapter
 *
 * @package Raindrop
 *
 * @method static int GetLastId($sQuery, $aParam = null, $sDataSource = 'default')
 * @method static int GetAffectedRowNum($sQuery, $aParam = null, $sDataSource = 'default')
 * @method static mixed GetVar($sQuery, $aParam = null, $sDataSource = 'default')
 * @method static BaseModel GetLine($sQuery, $aParam = null, $sDataSource = 'default')
 * @method static array GetData($sQuery, $aParam = null, $sDataSource = 'default')
 * @method static bool BeginTransaction($sDataSource)
 * @method static bool CommitTransaction($sDataSource)
 * @method static bool RollbackTransaction($sDataSource)
 * @method static int Query($sQuery, $aParam = null, $sDataSource = 'defualt')
 */
final class DatabaseAdapter
{
	/**
	 * @var null|DatabaseAdapter
	 */
	protected static $_oInstance = null;

	/**
	 * Adapter Pool
	 *
	 * @var array
	 */
	protected $_aAdapterPool = array();

	/**
	 * @return DatabaseAdapter
	 */
	public static function GetInstance()
	{
		if (self::$_oInstance === null) {
			new self();
		}

		return self::$_oInstance;
	}

	protected function __construct()
	{
		if (self::$_oInstance instanceof DatabaseAdapter) {
			return self::$_oInstance;
		}

		$aConfig = Configuration::Get('Database');

		if ($aConfig !== null) {
			foreach ($aConfig AS $_name => $_dsn) {
				$_name = strtolower($_name);

				$oRefComp                    = new \ReflectionClass('Raindrop\Component\\' . $_dsn['Component']);
				$this->_aAdapterPool[$_name] = $oRefComp->newInstance($_dsn['Params'], $_name);
			}
		}

		self::$_oInstance = $this;
	}

	public function __destruct()
	{
		if (Application::IsDebugging()) {
			$iCount   = 0;
			$aContent = array();
			foreach ($this->_aAdapterPool AS $_name => $_handler) {
				$i          = $_handler->getQueryCount();
				$aContent[] = "{$_name}({$i})";
				$iCount += $i;
			}

			Debugger::Output("Count({$iCount}), Connections[ " . implode('/', $aContent) . ' ]', 'Database');
		}
	}

	/**
	 * Get Datasource Connector
	 *
	 * @param string $sDatasource
	 * @return \Raindrop\Interfaces\IDbConnector
	 * @throws DatabaseConnectionException
	 */
	protected function _getDatasource($sDatasource)
	{
		$sDatasource = trim(strtolower($sDatasource));

		if (array_key_exists($sDatasource, $this->_aAdapterPool)) {
			return $this->_aAdapterPool[$sDatasource];
		} else {
			throw new DatabaseConnectionException($sDatasource, 'datasource_undefined', -1, null);
		}
	}

	public static function __callStatic($sName, $aArgs)
	{
		try{
			if(count($aArgs) == 1){
				$oConn = self::GetInstance()->_getDatasource($aArgs[0]);
				if($oConn instanceof IDbConnector){
					return $oConn->$sName();
				}
			}
			else if(count($aArgs) == 3){
				$oConn = self::GetInstance()->_getDatasource($aArgs[2]);
				if($oConn instanceof IDbConnector){
					return $oConn->$sName($aArgs[0], $aArgs[1]);
				}
			}
			else{
				throw new InvalidArgumentException;
			}

			throw new NotImplementedException($sName);
		}
		catch(DatabaseConnectionException $ex){
			throw new FatalErrorException('Database Connection:' + $ex->getMessage(), -1, $ex);
		}
		catch(DatabaseQueryException $ex){
			//todo load real message
			throw new RuntimeException('Database Query:' + $ex->getMessage(), -1, $ex);
		}
		catch(DatabaseException $ex){
			throw new FatalErrorException('Database:' + $ex->getMessage(), -1, $ex);
		}
		catch(NotImplementedException $ex){
			throw $ex;
		}
		catch(FatalErrorException $ex){
			throw $ex;
		}
	}
}