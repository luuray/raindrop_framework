<?php
/**
 * Raindrop Framework for PHP
 *
 * Database Connector Interface
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
namespace Raindrop\Interfaces;

use Raindrop\ModelAbstract;

interface IDbConnector
{
	/**
	 * Constructor
	 *
	 * @param array $aConfig
	 * @param string $sDataSourceName
	 */
	public function __construct($aConfig, $sDataSourceName);

	#region Status
	/**
	 * Is Connected
	 *
	 * @return bool
	 */
	public function isConnected();

	/**
	 * Get Executed Query Count
	 *
	 * @return int
	 */
	public function getQueryCount();
	#endregion

	#region Connection Control
	/**
	 * Connect to Database Server
	 *
	 * @return bool
	 */
	public function connect();

	/**
	 * Disconnect
	 *
	 * @return bool
	 */
	public function disconnect();
	#endregion

	#region Query Actions
	/**
	 * Query Database
	 *
	 * @param string $sQuery
	 * @param null|array $aParam
	 * @return bool
	 */
	public function query($sQuery, $aParam = null);

	/**
	 * Query Database and Get Inserted Row's Id
	 *
	 * @param string $sQuery
	 * @param null|array $aParam
	 * @return int|bool
	 */
	public function getLastId($sQuery, $aParam = null);

	/**
	 * Query Database and Get Affected Row's Number
	 *
	 * @param string $sQuery
	 * @param null|array $aParam
	 * @return int
	 */
	public function getAffectedRowNum($sQuery, $aParam = null);

	/**
	 * Query Database and Get First Line's First Column's Value
	 *
	 * @param string $sQuery
	 * @param null|array $aParam
	 * @return mixed
	 */
	public function getVar($sQuery, $aParam = null);

	/**
	 * Query Database and Get First Line
	 *
	 * @param string $sQuery
	 * @param null|array $aParam
	 * @param string $sModelName
	 * @return array|ModelAbstract
	 */
	public function getLine($sQuery, $aParam = null, $sModelName = null);

	/**
	 * Query Database and Get All Result
	 * @param string $sQuery
	 * @param null|array $aParam
	 * @param string $sModelName
	 * @return array|ModelAbstractArray
	 */
	public function getData($sQuery, $aParam = null, $sModelName = null);
	#endregion

	#region Transaction
	/**
	 * Begin Transaction
	 *
	 * @param null $sFlag
	 * @return bool
	 */
	public function beginTransaction($sFlag = null);

	/**
	 * Commit Transaction
	 * @param null $sFlag
	 * @return bool
	 */
	public function commitTransaction($sFlag = null);

	/**
	 * Rollback Transaction
	 *
	 * @param null $sFlag
	 * @return bool
	 */
	public function rollbackTransaction($sFlag = null);
	#endregion
}