<?php
/**
 * Raindrop Framework for PHP
 *
 * MySQL Database Connector
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component;

use PDO;
use PDOException;
use PDOStatement;
use Raindrop\Application;
use Raindrop\Configuration;
use Raindrop\Exceptions\Database\DatabaseConnectionException;
use Raindrop\Exceptions\Database\DatabaseQueryException;
use Raindrop\Exceptions\FileNotFoundException;
use Raindrop\Exceptions\InvalidArgumentException;
use Raindrop\Exceptions\Model\ModelNotFoundException;
use Raindrop\Interfaces\IDbConnector;
use Raindrop\Logger;
use Raindrop\ORM\Model;

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
	protected $_oConfig = array();
	/**
	 * @var null|string
	 */
	protected $_sDSName = null;

	/**
	 * @param array $aConfig
	 * @param string $sDSName
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(Configuration $oConfig, $sDSName)
	{
		if ($oConfig == null) {
			throw new InvalidArgumentException('config');
		}
		if (str_nullorwhitespace($sDSName)) {
			throw new InvalidArgumentException('dsname');
		}

		$this->_oConfig = $oConfig;
		$this->_sDSName = $sDSName;
	}

	#region Status

	/**
	 * Is Connected
	 *
	 * @return bool
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
				$this->_oConfig->ConnectionString,
				$this->_oConfig->User,
				$this->_oConfig->Password,
				[
					PDO::ATTR_EMULATE_PREPARES => false,
					PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION
				]);

			return true;
		} catch (PDOException $ex) {
			//var_dump($ex);
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
	/**
	 * @param string $sQuery
	 * @param null $aParam
	 *
	 * @return int
	 * @throws DatabaseQueryException
	 */
	public function query($sQuery, $aParam = null)
	{
		$oResult = $this->_query($sQuery, $aParam);

		return $oResult->rowCount();
	}

	/**
	 * Query Database and Get Inserted Row's Id
	 *
	 * @param string $sQuery
	 * @param null|array $aParam
	 *
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
	 *
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
	 *
	 * @return mixed
	 */
	public function getVar($sQuery, $aParam = null)
	{
		$oResult = $this->_query($sQuery, $aParam);
		if ($oResult->rowCount() > 0) {
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
	 *
	 * @throws ModelNotFoundException
	 * @return null|Model
	 */
	public function getLine($sQuery, $aParam = null, $sModelName = null)
	{
		$oResult = $this->_query($sQuery, $aParam);
		if ($oResult->rowCount() > 0) {
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
	 *
	 * @param string $sQuery
	 * @param null|array $aParam
	 * @param null|string $sModelName
	 *
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
	 *
	 * @return bool
	 */
	public function beginTransaction($sFlag = null)
	{
		if ($this->isConnected() === false) {
			$this->connect();
		}

		return $this->_oConn->beginTransaction();
	}

	/**
	 * Commit Transaction
	 *
	 * @param null $sFlag
	 *
	 * @return bool
	 */
	public function commitTransaction($sFlag = null)
	{
		if ($this->isConnected() == false) return false;//not connect, no transaction exists.

		return $this->_oConn->commit();
	}

	/**
	 * Rollback Transaction
	 *
	 * @param null $sFlag
	 *
	 * @return bool
	 */
	public function rollbackTransaction($sFlag = null)
	{
		if ($this->isConnected() == false) return false;//not connect, no transaction exists.

		return $this->_oConn->rollBack();
	}
	#endregion

	/**
	 * Query Handler
	 *
	 * @param string $sQuery
	 * @param null|array $aParam
	 *
	 * @throws DatabaseQueryException
	 * @return bool|PDOStatement
	 */
	protected function _query($sQuery, $aParam)
	{
		if ($this->isConnected() === false) {
			$this->connect();
		}
		try {
			$oStat = $this->_oConn->prepare($sQuery);
			//bind values
			if (!empty($aParam) && is_array($aParam)) {
				foreach ($aParam AS $_k => $_v) {
					$sKey = is_int($_k) ? $_k + 1 : ':' . $_k;
					if ($_v === null) {
						$oStat->bindValue($sKey, null, PDO::PARAM_NULL);
					} else if (is_bool($_v)) {
						$oStat->bindValue($sKey, $_v == true ? 1 : 0, PDO::PARAM_INT);
					} else if (is_int($_v)) {
						$oStat->bindValue($sKey, $_v, PDO::PARAM_INT);
					} else {
						$oStat->bindValue($sKey, $_v, PDO::PARAM_STR);
					}
				}
			}

			//execute!
			if ($oStat->execute() === true) {
				$this->_iQueryCount++;

				if (Application::IsDebugging()) {
					Logger::Message(sprintf(
						'dsname: %s, query: %s, param: %s, SUCCESS, affected: %d, backtrace:' . implode(' => ' , backtrace()),
						$this->_sDSName, $sQuery, var_export($aParam, true), $oStat->rowCount()));
				}

				return $oStat;
			} else {
				throw new DatabaseQueryException($this->_sDSName, $sQuery, $aParam, $oStat->errorInfo()[2], $oStat->errorCode());
			}
		} catch (PDOException $ex) {
			throw new DatabaseQueryException($this->_sDSName, $sQuery, $aParam, $ex->errorInfo[2], $ex->getCode(), $ex);
		}
	}
}