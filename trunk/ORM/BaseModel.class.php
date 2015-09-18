<?php
/**
 * Raindrop Framework for PHP
 *
 * ORM Base Model
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
namespace Raindrop\ORM;

use Raindrop\DatabaseAdapter;
use Raindrop\Exceptions\Database\DatabaseException;
use Raindrop\InvalidArgumentException;
use Raindrop\Logger;

/**
 * Class BaseModel
 *
 * @package Raindrop\ORM
 *
 * @TODO Exception Progress
 */
abstract class BaseModel implements \Serializable, \JsonSerializable
{
	const ACTION_INSERT = 1;
	const ACTION_UPDATE = 2;

	/**
	 * @var null|TableSchema
	 */
	protected $_oTableSchema = null;

	protected $_iAction;
	protected $_aChangedColumn = array();

	public function __construct($iAction = self::ACTION_INSERT)
	{
		$this->_iAction      = $iAction;
		$this->_oTableSchema = TableSchema::GetSchema($this->getTableName(), $this->getDbConnect());
	}

	public final function __set($sProperty, $mValue)
	{
		if (property_exists($this, $sProperty)) {
			$this->$sProperty = $mValue;

			return true;
		} else {
			if ($this->_oTableSchema->hasColumn($sProperty)) {
				$this->_oTableSchema->setValue($sProperty, $mValue);
				//mark change
				$this->_aChangedColumn[] = $sProperty;

				return true;
			}
		}

		return false;
	}

	public final function __get($sProperty)
	{
		//echo "get";
		//var_dump($sProperty, $this->_oTableSchema->hasColumn($sProperty), $this->_oTableSchema->$sProperty->Value);
		if (property_exists($this, $sProperty)) {
			return $this->$sProperty;
		} else if ($this->_oTableSchema->hasColumn($sProperty)) {
			//echo 'CE'.'-'.$sProperty;
			return $this->_oTableSchema->getValue($sProperty);
		} else if (method_exists($this, 'get' . $sProperty)) {
			$sPropertyGetter = 'get' . $sProperty;

			return $this->$sPropertyGetter();
		} else {
			//echo 'NE'.'-'.$sProperty;
			return false;
		}
	}

	public function serialize()
	{
		return serialize(array('schema' => $this->_oTableSchema, 'action' => $this->_iAction, 'changed' => $this->_aChangedColumn));
	}

	public function unserialize($sData)
	{
		$aData = unserialize($sData);

		$this->_oTableSchema   = $aData['schema'];
		$this->_iAction        = $aData['action'];
		$this->_aChangedColumn = $aData['changed'];
	}

	public function jsonSerialize()
	{
		return $this->_oTableSchema->getColumnsName();
	}

	public function setColumnsValue($mSource, $bResetChangeFlag = true)
	{
		if (is_array($mSource)) {
			foreach ($mSource AS $_name => $_value) {
				$_name        = strtolower($_name);
				$this->$_name = $_value;
			}
		} else if (is_object($mSource)) {
			foreach (get_object_vars($mSource) AS $_name => $_value) {
				$_name        = strtolower($_name);
				$this->$_name = $_value;
			}
		} else {
			throw new InvalidArgumentException();
		}

		if ($bResetChangeFlag == true) {
			$this->_aChangedColumn = array();
		}
	}

	//property in method
	public static function getTableName()
	{
		return get_called_class();
	}

	public static function getDbConnect()
	{
		return 'default';
	}

	public static function getPkName()
	{
		return null;
	}

	public function beginTransaction()
	{
		return Transaction::BeginTransaction($this->getDbConnect());
	}

	public function save()
	{
		try {
			if ($this->_iAction == self::ACTION_INSERT) {
				return $this->_insert();
			} else if ($this->_iAction == self::ACTION_UPDATE) {
				return $this->_update();
			} else {
			}
		} catch (DatabaseException $ex) {

		}
	}

	public function del()
	{
		try {
			if ($this->_iAction == self::ACTION_INSERT) {
				return false;
			} else if ($this->_iAction == self::ACTION_UPDATE) {
				return $this->_del();
			} else {
			}
		} catch (DatabaseException $ex) {

		}
	}

	protected final function _insert()
	{
		$mPk            = $this->getPkName();
		$aSchemaDefPk   = array();
		$aBindColName   = array();
		$aQuotedColName = array();
		$aQueryParams   = array();
		foreach ($this->_oTableSchema->getColumnsName() AS $_col) {
			$oColumn = $this->_oTableSchema->$_col;
			if ($oColumn->IsPrimaryKey == true) {
				$aSchemaDefPk[] = $oColumn->Name;
				//Skip Set Value for AutoInc PK
				if ($oColumn->AutoIncrement == true) {
					continue;
				}
			}

			$aBindColName[]               = ':' . $oColumn->Name;
			$aQuotedColName[]             = '`' . $oColumn->Name . '`';
			$aQueryParams[$oColumn->Name] = $oColumn->Value;
		}

		//pk decision
		if ($mPk == null AND !empty($aSchemaDefPk) AND count($aSchemaDefPk) == 1) {
			$mPk = $aSchemaDefPk[0];
		}
		if (is_string($mPk) AND $this->_oTableSchema->hasColumn($mPk) AND $this->_oTableSchema->$mPk->AutoIncrement == true) {
			$iInsertId = DatabaseAdapter::GetLastId(
				sprintf('INSERT INTO `%s` (%s) VALUE (%s)', $this->getTableName(), implode(',', $aQuotedColName), implode(',', $aBindColName)),
				$aQueryParams, $this->getDbConnect());

			if ($iInsertId !== false) {
				$this->_oTableSchema->setValue($mPk, $iInsertId);
			}
		} else {
			$iAffectedRow = DatabaseAdapter::GetAffectedRowNum(
				sprintf('INSERT INTO `%s` (%s) VALUE (%s)', $this->getTableName(), implode(',', $aQuotedColName), implode(',', $aBindColName)),
				$aQueryParams, $this->getDbConnect());
			if ($iAffectedRow <= 0) {
				return false;
			}
		}
		//if change model to update
		$this->_iAction = self::ACTION_UPDATE;

		return true;
	}

	protected final function _update()
	{
		//nothing changed
		if (empty($this->_aChangedColumn)) {
			return true;
		}

		$mPk          = $this->getPkName();
		$aChanged     = array();
		$aQueryParams = array();
		$aConditions  = array();

		//pk undefined
		if ($mPk == null) {
			$aSchemaColumns = $this->_oTableSchema->getColumnsName();
			foreach ($aSchemaColumns AS $_col) {
				$oColumn = $this->_oTableSchema->$_col;
				if ($oColumn->IsPrimaryKey == true) {
					$aConditions[]                = "`{$oColumn->Name}`=:{$oColumn->Name}";
					$aQueryParams[$oColumn->Name] = $oColumn->Value;
				}
			}
		} else {
			if (is_string($mPk)) {
				$mPk = array($mPk);
			} else if (is_array($mPk)) {
				//nothing
			} else {
				Logger::Warning(sprintf('pk defined in [%s] invalid', get_called_class()));

				return false;
			}
			foreach ($mPk AS $_col) {
				$oColumn                      = $this->_oTableSchema->$_col;
				$aConditions[]                = sprintf('`%s`=:%s', $oColumn->Name, $oColumn->Name);
				$aQueryParams[$oColumn->Name] = $oColumn->Value;
			}
		}

		//getChanged Value
		foreach ($this->_aChangedColumn AS $_col) {
			$oColumn                      = $this->_oTableSchema->$_col;
			$aChanged[]                   = sprintf('`%s`=:%s', $oColumn->Name, $oColumn->Name);
			$aQueryParams[$oColumn->Name] = $oColumn->Value;
		}

		$bResult = DatabaseAdapter::Query(
			sprintf('UPDATE `%s` SET %s WHERE %s LIMIT 1', $this->getTableName(), implode(',', $aChanged), implode(' AND ', $aConditions)),
			$aQueryParams, $this->getDbConnect());

		return $bResult !== false;
	}

	/**
	 * @return bool
	 */
	protected final function _del()
	{
		if ($this->_iAction != self::ACTION_UPDATE) {
			return false;
		}

		$mPk         = $this->getPkName();
		$aConditions = array();
		$aBindValues = array();
		if ($mPk == null) {
			$aSchemaColumns = $this->_oTableSchema->getColumnsName();
			foreach ($aSchemaColumns AS $_col) {
				$oColumn = $this->_oTableSchema->$_col;
				if ($oColumn->IsPrimaryKey == true) {
					$aConditions[]               = "`{$oColumn->Name}`=:{$oColumn->Name}";
					$aBindValues[$oColumn->Name] = $oColumn->Value;
				}
			}
		} else {
			if (is_string($mPk)) {
				$mPk = array($mPk);
			} else if (is_array($mPk)) {
				//nothing
			} else {
				Logger::Warning(sprintf('pk defined in [%s] invalid', get_called_class()));

				return false;
			}
			foreach ($mPk AS $_col) {
				if ($this->_oTableSchema->hasColumn($_col)) {
					$oColumn                     = $this->_oTableSchema->$_col;
					$aConditions[]               = "`{$oColumn->Name}`=:{$oColumn->Name}";
					$aBindValues[$oColumn->Name] = $oColumn->Value;
				} else {
					Logger::Warning(sprintf('connection: %s, table: %s, model: %s has no column named [%s]', $this->getDbConnect(), $this->getTableName(), get_called_class(), $_col));

					return false;
				}
			}
		}

		if (empty($aConditions)) {
			Logger::Warning(sprintf('connection: %s, table: %s has no pk defined in [%s]', $this->getDbConnect(), $this->getTableName(), get_called_class()));

			return false;
		}

		$iAffectedRow = DatabaseAdapter::GetAffectedRowNum(
			sprintf('DELETE FROM `%s` WHERE %s LIMIT 1', $this->getTableName(), implode(' AND ', $aConditions)),
			$aBindValues, $this->getDbConnect());

		return $iAffectedRow > 0;
	}

	public static final function Any($sCondition = null, $aParam = null)
	{
		$oModel = get_called_class();
		$oModel = new $oModel();

		$iResult = DatabaseAdapter::GetVar(
			sprintf('SELECT COUNT(1) FROM `%s` %s',
				$oModel->getTableName(), !empty($sCondition) ? 'WHERE ' . $sCondition : null),
			$aParam, $oModel->getDbConnect());

		return $iResult > 0;
	}

	/**
	 * @param null|string $sCondition
	 * @param null|array $aParam
	 * @return int
	 */
	public static final function Count($sCondition = null, $aParam = null, $sDistinct = null, $sGroupBy = null)
	{
		$oModel = get_called_class();
		$oModel = new $oModel();

		$iResult = DatabaseAdapter::GetVar(
			sprintf(
				'SELECT COUNT(%s) FROM `%s` %s',
				str_nullorwhitespace($sDistinct) ? '1' : 'DISTINCT ' . $sDistinct,
				$oModel->getTableName(),
				!empty($sCondition) ? 'WHERE ' . $sCondition : null),
			$aParam, $oModel->getDbConnect());

		return intval($iResult);
	}

	public static final function CountSql($sQuery, $aParams = null, $bDistinct = false)
	{
		$oModel = get_called_class();
		$oModel = new $oModel();

		$iResult = (int)DatabaseAdapter::GetVar(
			sprintf('SELECT %s FROM %s', $bDistinct == true ? 'DISTINCT COUNT(1)' : 'COUNT(1)', $sQuery),
			$aParams, $oModel->getDbConnect());

		return intval($iResult);
	}

	public static final function CountColumn($sColumn, $sCondition = null, $aParams = null)
	{
		$oModel = get_called_class();
		$oModel = new $oModel();

		$iResult = (int)DatabaseAdapter::GetVar(
			sprintf('SELECT COUNT(DISTINCT `%s`) FROM `%s` %s',
				$sColumn,
				$oModel->getTableName(),
				!empty($sCondition) ? 'WHERE ' . $sCondition : null),
			$aParams, $oModel->getDbConnect());

		return intval($iResult);
	}

	/**
	 * Get Single Model Matched Condition
	 *
	 * @param $sCondition
	 * @param null|array $aParam
	 * @return null|BaseModule
	 */
	public static final function SingleOrNull($sCondition, $aParam = null, $aOrderBy = null)
	{
		$oTargetModel = get_called_class();
		$oTargetModel = new $oTargetModel(self::ACTION_UPDATE);

		$oResult = DatabaseAdapter::GetLine(
			sprintf('SELECT * FROM `%s` %s %s LIMIT 1',
				$oTargetModel->getTableName(),
				!empty($sCondition) ? 'WHERE ' . $sCondition : null,
				!empty($aOrderBy) ? 'ORDER BY ' . implode(',', $aOrderBy) : null),
			$aParam, $oTargetModel->getDbConnect());

		if ($oResult != false) {
			$oTargetModel->setColumnsValue($oResult);

			return $oTargetModel;
		} else {
			return null;
		}
	}

	/**
	 * @param $sCondition
	 * @param null $aParam
	 * @param null|array $aOrderBy
	 * @param int $iLimit
	 * @param int $iSkip
	 * @return array|bool
	 */
	public static final function Find($sCondition = null, $aParam = null, $sGroupBy = null, $aOrderBy = null, $iLimit = 0, $iSkip = 0)
	{
		$sModelName = get_called_class();
		$oModel     = new $sModelName(self::ACTION_UPDATE);

		$aResults = DatabaseAdapter::GetData(
			sprintf('SELECT * FROM `%s` %s %s %s %s',
				$oModel->getTableName(),
				(!empty($sCondition) ? 'WHERE ' . $sCondition : null),
				(!empty($sGroupBy) ? 'GROUP BY ' . $sGroupBy : null),
				(!empty($aOrderBy) ? 'ORDER BY ' . implode(',', $aOrderBy) : null),
				($iLimit > 0 ? ('LIMIT ' . ($iSkip >= 0 ? "{$iSkip},{$iLimit}" : $iLimit)) : null)),
			$aParam, $oModel->getDbConnect());

		if ($aResults !== false) {
			$aModelArray = array();
			foreach ($aResults AS $_item) {
				$oItem = new $sModelName(self::ACTION_UPDATE);
				$oItem->setColumnsValue($_item);
				$aModelArray[] = $oItem;
			}

			return $aModelArray;
		} else {
			return null;
		}
	}

	/**
	 * @param null $aOrderBy
	 * @param int $iLimit
	 * @param int $iSkip
	 * @return array|null
	 */
	public static final function All($aOrderBy = null, $iLimit = 0, $iSkip = 0)
	{
		$sModelName = get_called_class();
		$oModel     = new $sModelName(self::ACTION_UPDATE);

		$aResults = DatabaseAdapter::GetData(
			sprintf('SELECT * FROM `%s` %s %s',
				$oModel->getTableName(),
				(!empty($aOrderBy) ? 'ORDER BY ' . implode(',', $aOrderBy) : null),
				($iLimit > 0 ? ('LIMIT ' . ($iSkip >= 0 ? "{$iSkip},{$iLimit}" : $iLimit)) : null)),
			null, $oModel->getDbConnect());

		if ($aResults !== false) {
			$aModelArray = array();
			foreach ($aResults AS $_item) {
				$oItem = new $sModelName(self::ACTION_UPDATE);
				$oItem->setColumnsValue($_item);
				$aModelArray[] = $oItem;
			}

			return $aModelArray;
		} else {
			return null;
		}
	}

	public static final function FindSql($sQuery, $aParams = null, $sGroupBy = null, $aOrderBy = null, $iLimit = 0, $iSkip = 0)
	{
		$sModelName = get_called_class();
		$oModel     = new $sModelName(self::ACTION_UPDATE);

		$aResults = DatabaseAdapter::GetData(
			sprintf(
				'SELECT %s %s %s %s',
				$sQuery,
				(!empty($sGroupBy) ? 'GROUP BY ' . $sGroupBy : null),
				(!empty($aOrderBy) ? 'ORDER BY ' . implode(',', $aOrderBy) : null),
				($iLimit > 0 ? ('LIMIT ' . ($iSkip >= 0 ? "{$iSkip},{$iLimit}" : $iLimit)) : null)),
			$aParams, $oModel->getDbConnect());

		if ($aResults !== false) {
			$aModelArray = array();
			foreach ($aResults AS $_item) {
				$oItem = new $sModelName(self::ACTION_UPDATE);
				$oItem->setColumnsValue($_item);
				$aModelArray[] = $oItem;
			}

			return $aModelArray;
		} else {
			return null;
		}
	}

	/**
	 * Add a Item to Database
	 *
	 * @param BaseModel $oModel
	 * @return BaseModel
	 */
	public static final function Add(BaseModel $oModel)
	{
		$oModel->_insert();

		return $oModel;
	}

	/**
	 * @param null $sConditions
	 * @param null $aParam
	 * @param null $aOrderBy
	 * @param int $iLimit
	 * @param int $iSkip
	 * @param bool $bForceDel
	 * @return int
	 * @throws \Raindrop\InvalidArgumentException
	 */
	public static final function DelAny($sConditions = null, $aParam = null, $aOrderBy = null, $iLimit = 0, $iSkip = 0, $bForceDel = false)
	{
		if (str_nullorwhitespace($sConditions) AND $bForceDel !== true) {
			throw new InvalidArgumentException('Conditions');
		}
		if ($iLimit < 0) {
			throw new InvalidArgumentException('Limit');
		}
		if ($iSkip < 0) {
			throw new InvalidArgumentException('Skip');
		}

		$sModelName = get_called_class();
		$oModel     = new $sModelName(self::ACTION_INSERT);

		$sQuery = 'DELETE FROM `' . $oModel->getTableName() . '` ';
		//conditions
		if (!str_nullorwhitespace($sConditions)) {
			$sQuery .= "WHERE {$sConditions} ";
		}
		//order by
		if (!empty($aOrderBy)) {
			$sQuery .= 'ORDER BY ' . implode(',', $aOrderBy) . ' ';
		}
		$sQuery .= $iLimit > 0 ? (' LIMIT ' . ($iSkip >= 0 ? "{$iSkip},{$iLimit}" : $iLimit)) : null;

		return DatabaseAdapter::GetAffectedRowNum($sQuery, $aParam, $oModel->getDbConnect());
	}

	/**
	 * @param string $sQuery
	 * @param null|array $aParams
	 * @return PDOStatement
	 */
	public static final function Query($sQuery, $aParams = null)
	{
		$sModelName = get_called_class();
		$oModel     = new $sModelName(self::ACTION_UPDATE);

		return DatabaseAdapter::Query($sQuery, $aParams, $oModel->getDbConnect());
	}


}