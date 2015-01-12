<?php
/**
 * Raindrop Framework for PHP
 *
 * MySQL Database Connector
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

namespace Raindrop\Component;

use PDO;
use PDOException;
use PDOStatement;
use Raindrop\Application;
use Raindrop\DatabaseConnectionException;
use Raindrop\DatabaseQueryException;
use Raindrop\FileNotFoundException;
use Raindrop\Interfaces\IDbConnector;
use Raindrop\Interfaces\ModelAbstract;
use Raindrop\InvalidArgumentException;
use Raindrop\Logger;
use Raindrop\ModelNotFoundException;

class MySQL implements IDbConnector
{
	/**
	 * @var PDO
	 */
	protected $_oConn = null;

	/**
	 * @var int
	 */
	protected $_iQueryCount = 0;

	/**
	 * @var array
	 */
	protected $_aConfig = array();
	/**
	 * @var null|string
	 */
	protected $_sDSName = null;

	/**
	 * @param array $aConfig
	 * @param string $sDSName
	 * @throws InvalidArgumentException
	 */
	public function __construct($aConfig, $sDSName)
	{
		if (!is_array($aConfig)) {
			throw new InvalidArgumentException('config');
		}
		if (str_nullorwhitespace($sDSName)) {
			throw new InvalidArgumentException('dsname');
		}

		$this->_aConfig = $aConfig;
		$this->_sDSName = $sDSName;
	}

	#region Status
	/**
	 * Is Connected
	 *
	 * @return bool|void
	 */
	public function isConnected()
	{
		return $this->_oConn instanceof PDO;
	}

	/**
	 * Get Executed Query Count
	 *
	 * @return int
	 */
	public function getQueryCount()
	{
		return $this->_iQueryCount;
	}
	#endregion

	#region Connection Control
	/**
	 * Connect to Database Server
	 *
	 * @return bool
	 * @throws DatabaseConnectionException
	 */
	public function connect()
	{
		if ($this->_oConn instanceof PDO) {
			return true;
		}

		try {
			$this->_oConn = new PDO(
				$this->_aConfig['ConnectionString'],
				$this->_aConfig['User'],
				$this->_aConfig['Password']);

			return true;
		} catch (PDOException $ex) {
			var_dump($ex);
			throw new DatabaseConnectionException($this->_sDSName, $ex->getMessage(), $ex->getCode());
		}
	}

	/**
	 * Disconnect
	 *
	 * @return bool
	 */
	public function disconnect()
	{
		if ($this->_oConn instanceof PDO) {
			$this->_oConn = null;
		}

		return true;
	}
	#endregion

	#region Query Interfaces
	public function query($sQuery, $aParam = null)
	{
		return $this->_query($sQuery, $aParam);
	}

	/**
	 * Query Database and Get Inserted Row's Id
	 *
	 * @param string $sQuery
	 * @param null|array $aParam
	 * @return int|false
	 */
	public function getLastId($sQuery, $aParam = null)
	{
		if ($this->_query($sQuery, $aParam)) {
			return $this->_oConn->lastInsertId();
		} else {
			return false;
		}
	}

	/**
	 * Query Database and Get Affected Row's Number
	 *
	 * @param string $sQuery
	 * @param null|array $aParam
	 * @return int
	 */
	public function getAffectedRowNum($sQuery, $aParam = null)
	{
		$oResult = $this->_query($sQuery, $aParam);

		return $oResult->rowCount();
	}

	/**
	 * Query Database and Get First Line's First Column's Value
	 *
	 * @param string $sQuery
	 * @param null|array $aParam
	 * @return mixed
	 */
	public function getVar($sQuery, $aParam = null)
	{
		$oResult = $this->_query($sQuery, $aParam);
		if ($oResult->columnCount() > 0) {
			return $oResult->fetchColumn();
		} else {
			return null;
		}
	}

	/**
	 * Query Database and Get First Line
	 *
	 * @param string $sQuery
	 * @param null|array $aParam
	 * @param null|string $sModelName Model FullName with Namespace
	 * @throws ModelNotFoundException
	 * @return null|ModelAbstract
	 */
	public function getLine($sQuery, $aParam = null, $sModelName = null)
	{
		$oResult = $this->_query($sQuery, $aParam);
		if ($oResult->columnCount() > 0) {
			try {
				if ($sModelName !== null && class_exists($sModelName)) {
					$oResult->setFetchMode(PDO::FETCH_CLASS, $sModelName, null);

					return $oResult->fetch();
				} else {
					return $oResult->fetch(PDO::FETCH_OBJ);
				}
			} catch (FileNotFoundException $ex) {
				throw new ModelNotFoundException($sModelName, $ex);
			}
		}

		return null;
	}

	/**
	 * Query Database and Get All Result
	 * @param string $sQuery
	 * @param null|array $aParam
	 * @param null|string $sModelName
	 * @throws ModelNotFoundException
	 * @return array
	 */
	public function getData($sQuery, $aParam = null, $sModelName = null)
	{
		$oResult = $this->_query($sQuery, $aParam);
		if ($oResult->columnCount() > 0) {
			try {
				if ($sModelName !== null && class_exists($sModelName)) {
					return $oResult->fetchAll(PDO::FETCH_CLASS, $sModelName, null);
				} else {
					return $oResult->fetchAll(PDO::FETCH_OBJ);
				}
			} catch (FileNotFoundException $ex) {
				throw new ModelNotFoundException($sModelName, $ex);
			}
		}

		return null;
	}
	#endregion

	#region Transaction Actions
	/**
	 * Begin Transaction
	 *
	 * @param null $sFlag
	 * @return bool
	 */
	public function beginTransaction($sFlag = null)
	{
		return $this->_oConn->beginTransaction();
	}

	/**
	 * Commit Transaction
	 * @param null $sFlag
	 * @return bool
	 */
	public function commitTransaction($sFlag = null)
	{
		return $this->_oConn->commit();
	}

	/**
	 * Rollback Transaction
	 *
	 * @param null $sFlag
	 * @return bool
	 */
	public function rollbackTransaction($sFlag = null)
	{
		return $this->_oConn->rollBack();
	}
	#endregion

	/**
	 * Query Handler
	 *
	 * @param string $sQuery
	 * @param null|array $aParam
	 * @throws DatabaseQueryException
	 * @return bool|PDOStatement
	 */
	protected function _query($sQuery, $aParam)
	{
		if ($this->isConnected() === false) {
			$this->connect();
		}

		$oStat = $this->_oConn->prepare($sQuery);
		//prepare fail
		if ($oStat === false) {
			throw new DatabaseQueryException($this->_sDSName, $sQuery, $aParam, $oStat->errorInfo()[2], $oStat->errorCode());
		}
		//bind values
		if (!empty($aParam) && is_array($aParam)) {
			foreach ($aParam AS $_k => $_v) {
				if ($_v === null) {
					$oStat->bindValue(':' . $_k, null, PDO::PARAM_NULL);
				} else if (is_bool($_v)) {
					$oStat->bindValue(':' . $_k, $_v, PDO::PARAM_BOOL);
				} else if (is_int($_v)) {
					$oStat->bindValue(':' . $_k, $_v, PDO::PARAM_INT);
				} else {
					$oStat->bindValue(':' . $_k, $_v, PDO::PARAM_STR);
				}
			}
		}

		if (Application::IsDebugging()) {
			Logger::Message(sprintf('query: %s, param: %s', $sQuery, print_r($aParam, true)));
		}

		//execute!
		if ($oStat->execute() === true) {
			$this->_iQueryCount++;

			return $oStat;
		} else {
			throw new DatabaseQueryException($this->_sDSName, $sQuery, $aParam, $oStat->errorInfo()[2], $oStat->errorCode());
		}
	}
}