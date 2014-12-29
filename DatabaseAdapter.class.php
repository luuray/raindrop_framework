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

#region Query Methods
	public static function Query($sQuery, $aParam = null, $sDatasource = 'default')
	{
		return self::GetInstance()->_getDatasource($sDatasource)->query($sQuery, $aParam);
	}

	/**
	 * @param $sQuery
	 * @param null $aParam
	 * @param string $sDatasource
	 * @return mixed
	 */
	public static function GetLastId($sQuery, $aParam = null, $sDatasource = 'default')
	{
		return self::GetInstance()->_getDatasource($sDatasource)->getLastId($sQuery, $aParam);
	}

	/**
	 * @param $sQuery
	 * @param null $aParam
	 * @param string $sDatasource
	 * @return mixed
	 */
	public static function GetAffectedRowNum($sQuery, $aParam = null, $sDatasource = 'default')
	{
		return self::GetInstance()->_getDatasource($sDatasource)->getAffectedRowNum($sQuery, $aParam);
	}

	/**
	 * @param $sQuery
	 * @param null $aParam
	 * @param string $sDatasource
	 * @return mixed
	 */
	public static function GetVar($sQuery, $aParam = null, $sDatasource = 'default')
	{
		return self::GetInstance()->_getDatasource($sDatasource)->getVar($sQuery, $aParam);
	}

	/**
	 * @param $sQuery
	 * @param null $aParam
	 * @param string $sDatasource
	 * @param null $sModel
	 * @return mixed
	 */
	public static function GetLine($sQuery, $aParam = null, $sDatasource = 'default', $sModel = null)
	{
		return self::GetInstance()->_getDatasource($sDatasource)->getLine($sQuery, $aParam, $sModel);
	}

	/**
	 * @param $sQuery
	 * @param null $aParam
	 * @param string $sDatasource
	 * @param null $sModel
	 * @return mixed
	 */
	public static function GetData($sQuery, $aParam = null, $sDatasource = 'default', $sModel = null)
	{
		return self::GetInstance()->_getDatasource($sDatasource)->getData($sQuery, $aParam, $sModel);
	}
#endregion

#region Transaction Operations
	/**
	 * @param string $sDatasource
	 * @return bool
	 */
	public static function BeginTransaction($sDatasource = 'default')
	{
		return self::GetInstance()->_getDatasource($sDatasource)->beginTransaction();
	}

	/**
	 * @param string $sDatasource
	 * @return bool
	 */
	public static function RollbackTransaction($sDatasource = 'default')
	{
		return self::GetInstance()->_getDatasource($sDatasource)->rollbackTransaction();
	}

	/**
	 * @param string $sDatasource
	 * @return bool
	 */
	public static function CommitTransaction($sDatasource = 'default')
	{
		return self::GetInstance()->_getDatasource($sDatasource)->commitTransaction();
	}
#endregion
}