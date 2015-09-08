<?php
/**
 * Raindrop Framework for PHP
 *
 * ORM Data Model Actions
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

namespace Raindrop\ORM;

use Raindrop\Application;
use Raindrop\Cache;
use Raindrop\DatabaseAdapter;
use Raindrop\Exceptions\Database\DataModelException;
use Raindrop\Exceptions\Model\ModelNotFoundException;
use Raindrop\Exceptions\NotImplementedException;

/**
 * Class ModelAction
 * @package Raindrop\ORM
 */
class ModelAction
{
	protected $_bCacheEnable = false;

	public static function GetInstance()
	{
		static $oInstance = null;
		if ($oInstance == null) {
			$oInstance = new self();
		}

		return $oInstance;
	}

	/**
	 * @param $sModel
	 * @param null $sCondition
	 * @param null $aParams
	 * @return bool
	 * @throws ModelNotFoundException
	 */
	public static function Any($sModel, $sCondition = null, $aParams = null)
	{
		if (!class_exists($sModel) OR !is_subclass_of($sModel, 'Model')) {
			throw new ModelNotFoundException($sModel);
		}

		$sTable = $sModel::GetTableName();
		$sDbConnect = $sModel::GetDbConnect();

		return DatabaseAdapter::GetVar(
			sprintf('SELECT COUNT(1) FROM `%s` %s',
				$sTable, !empty($sCondition) ? 'WHERE ' . $sCondition : null),
			$aParams, $sDbConnect) > 0;
	}

	/**
	 * @param $sModel
	 * @param null $sCondition
	 * @param null $aParams
	 * @param null $sDistinct
	 * @param null $sGroupBy
	 */
	public static function Count($sModel, $sCondition = null, $aParams = null, $sDistinct = null, $sGroupBy = null)
	{
		if (!class_exists($sModel) OR !is_subclass_of($sModel, 'Model')) {
			throw new ModelNotFoundException($sModel);
		}

		$sTable = $sModel::GetTableName();
		$sDbConnect = $sModel::GetDbConnect();

		return (int)DatabaseAdapter::GetVar(
			sprintf(
				'SELECT COUNT(%s) FROM `%s` %s %s',
				str_nullorwhitespace($sDistinct) ? '1' : 'DISTINCT ' . $sDistinct,
				$sTable,
				!empty($sCondition) ? 'WHERE ' . $sCondition : null,
				!empty($sGroupBy) ? 'GROUP BY' . $sGroupBy : null),
			$aParams, $sDbConnect);
	}

	/**
	 * @param $sModel
	 * @param null $sQuery
	 * @param null $aParams
	 * @param bool|false $bDistinct
	 */
	public static function CountSql($sModel, $sQuery = null, $aParams = null, $bDistinct = false)
	{
		if (!class_exists($sModel) OR !is_subclass_of($sModel, 'Model')) {
			throw new ModelNotFoundException($sModel);
		}

		$sTable = $sModel::GetTableName();
		$sDbConnect = $sModel::GetDbConnect();

		return (int)DatabaseAdapter::GetVar(
			sprintf('SELECT %s FROM %s', $bDistinct == true ? 'DISTINCT COUNT(1)' : 'COUNT(1)', $sQuery),
			$aParams, $sDbConnect);
	}

	/**
	 * @param Model $oModel
	 * @throws DataModelException
	 */
	public static function Save(Model $oModel)
	{
		if ($oModel->getModelState() === Model::ModelState_Create) {
			self::GetInstance()->modelInsert($oModel);
		} else if ($oModel->getModelState() === Model::ModelState_Updated) {
			self::GetInstance()->modelUpdate($oModel);
		} else if ($oModel->getModelState() === Model::ModelState_Normal) {
			return $oModel;
		} else {
			throw new DataModelException('undefined_model_stats');
		}
	}

	/**
	 * @param Model $oModel
	 */
	public static function Del(Model $oModel)
	{
	}

	/**
	 * @param $sModel
	 * @param null $sCondition
	 * @param null $aParams
	 * @param null $aOrderBy
	 * @param int $iLimit
	 * @param int $iSkip
	 * @param bool|false $bForceDel
	 */
	public static function DelAny($sModel, $sCondition = null, $aParams = null, $aOrderBy = null, $iLimit = 0, $iSkip = 0, $bForceDel = false)
	{
	}

	/**
	 * @param $sModel
	 * @param null $sCondition
	 * @param null $aParams
	 * @param null $aOrderBy
	 */
	public static function SingleOrNull($sModel, $sCondition = null, $aParams = null, $aOrderBy = null)
	{
	}

	/**
	 * @param $sModel
	 * @param null $aOrderBy
	 * @param int $iLimit
	 * @param int $iSkip
	 */
	public static function All($sModel, $aOrderBy = null, $iLimit = 0, $iSkip = 0)
	{
	}

	/**
	 * @param $sModel
	 * @param null $sCondition
	 * @param null $aParam
	 * @param null $sGroupBy
	 * @param null $aOrderBy
	 * @param int $iLimit
	 * @param int $iSkip
	 */
	public static function Find($sModel, $sCondition = null, $aParam = null, $sGroupBy = null, $aOrderBy = null, $iLimit = 0, $iSkip = 0)
	{
	}

	/**
	 * @param $sModel
	 * @param $sQuery
	 * @param null $aParams
	 * @param null $sGroupBy
	 * @param null $aOrderBy
	 * @param int $iLimit
	 * @param int $iSkip
	 */
	public static function FindSql($sModel, $sQuery, $aParams = null, $sGroupBy = null, $aOrderBy = null, $iLimit = 0, $iSkip = 0)
	{
	}

	/**
	 * @param $sModel
	 */
	public static function BeginTransaction($sModel)
	{
	}

	/**
	 * @param $sModel
	 */
	public static function Commit($sModel)
	{
	}

	/**
	 * @param $sModel
	 */
	public static function Rollback($sModel)
	{
	}

	/**
	 * @param $sMethod
	 * @param $aArgs
	 * @return mixed
	 * @throws NotImplementedException
	 */
	public static function __callStatic($sMethod, $aArgs)
	{
		if (method_exists(self::GetInstance(), $sMethod)) {
			return call_user_func_array([self::GetInstance(), $sMethod], $aArgs);
		} else {
			throw new NotImplementedException('ModelAction::' . $sMethod);
		}
	}

	/**
	 *
	 */
	protected function __construct()
	{
		$this->_bCacheEnable = Cache::HasHandler('ModelCache');
	}

	/**
	 *  Get Table's Scheme
	 *
	 * @param $sTable
	 * @param $sDbConnect
	 */
	public function getTableScheme($sTable, $sDbConnect)
	{
		if (Application::IsDebugging() OR $this->_bCacheEnable == false) {
			return $this->_queryTableScheme($sTable, $sDbConnect);
		} else {
			$aScheme = Cache::Get("{$sDbConnect}-{$sTable}", 'ModelCache');
			if ($aScheme == false) {
				$aScheme = $this->_queryTableScheme($sTable, $sDbConnect);
				Cache::Set("{$sDbConnect}-{$sTable}", $aScheme, 'ModelCache');
			}

			return $aScheme;
		}
	}

	/**
	 * @param $sModel
	 */
	public function getModelDefault($sModel)
	{
		if (!class_exists($sModel) OR !is_subclass_of($sModel, 'Model')) {
			throw new ModelNotFoundException($sModel);
		}
		$aScheme = $this->getTableScheme($sModel::GetTableName(), $sModel::GetDbConnect());
		$aResult = array();
		foreach ($aScheme['Columns'] AS $_col) {
			$aResult[$_col['Name']] = $_col['Default'];
		}

		return $aResult;
	}

	/**
	 * @param Model $oModel
	 * @throws DataModelException
	 * @throws \Raindrop\Exceptions\InvalidArgumentException
	 */
	public function modelInsert(Model $oModel)
	{
		$aScheme = $this->getTableScheme($oModel::getTableName(), $oModel::getDbConnect());

		$aModelData = $oModel->getRAWColumnsData();

		$aColumns = [];
		$aColValues = [];
		$sAutoId = null;
		foreach ($aScheme AS $_name => $_col) {
			if (array_key_exists($_name, $aModelData)) {
				$aColumns[] = "`{$_col['Field']}`";
				$aColParams[] = ":{$_col['Field']}";
				$aColValues[$_col['Field']] = $aModelData[$_name];
			}
			if ($_col['IsAutoIncrement'] == true) $sAutoId = $_name;
		}

		if ($sAutoId != null) {
			$iResult = DatabaseAdapter::GetLastId(
				sprint('INSERT INTO `%s` (%s) VALUE (%s)',
					$oModel::getTableName(), implode(',', $aColumns), implode(',', $aColParams)),
				$aColValues,
				$oModel::getDbConnect());
			if (intval($iResult) != 0) $oModel->setRAWData($sAutoId, (int)$iResult);
			else throw new DataModelException('save_fail');
		} else {
			$iResult = DatabaseAdapter::GetAffectedRows(
				sprint('INSERT INTO `%s` (%s) VALUE (%s)',
					$oModel::getTableName(), implode(',', $aColumns), implode(',', $aColParams)),
				$aColValues,
				$oModel::getDbConnect());
			if ($iResult <= 0) throw new DataModelException('save_fail');
		}

		$oModel->setModelState(Model::ModelState_Normal);

		return true;
	}

	/**
	 * @param Model $oModel
	 */
	public function modelUpdate(Model $oModel)
	{
		$aScheme = $this->getTableScheme($oModel::getTableName(), $oModel::getDbConnect());
		$aSnapshot = $oModel->getRAWData();

		$aUpdatedField = array();
		$aUpdatedValues = array();
		foreach ($aScheme['Columns'] AS $_col => $_def) {
			if (array_key_exists($_col, $aSnapshot) AND $aSnapshot[$_col] != $_def['Default']) {
				$aUpdatedField[] = sprintf('`%s`=:%s', $_col, $_col);
				$aUpdatedValues[$_col] = $aSnapshot[$_col];
			}
		}

		$iResult = DatabaseAdapter::GetAffectedRows();
	}

	/**
	 * @param $sTable
	 * @param $sDbConnect
	 * @return array|bool
	 */
	protected function _queryTableScheme($sTable, $sDbConnect)
	{
		$aColumns = DatabaseAdapter::GetData(sprintf('SHOW FULL COLUMNS FROM `%s`', $sTable), null, $sDbConnect);
		if ($aColumns == false || !is_array($aColumns)) {
			return false;
		}

		$aResult = array('SchemaDefinedPk' => array(), 'Columns' => array());
		foreach ($aColumns AS $_item) {
			$aResult['Columns'][strtolower($_item->Field)] = [
				'Name'            => $_item->Field,
				'Type'            => $_item->Type,
				'IsPrimary'       => $_item->Key == 'PRI',
				'Nullable'        => $_item->Null == 'YES',
				'Default'         => $this->_colDefaultDecide($_item->Type, $_item->Null == 'YES', $_item->Default),
				'IsAutoIncrement' => strpos($_item->Extra, 'auto_increment') !== false
			];

			if ($_item->Key == 'PRI') {
				$aResult['SchemaDefinedPk'][] = $_item->Field;
			}
		}

		return $aResult;
	}

	protected function _colDefaultDecide($sType, $bNullable, $sDefault)
	{
		if ($bNullable == true) {
			return $sDefault;
		} else {
			if ($sDefault !== null) return $sDefault;
			else if (str_beginwith($sType, ['int', 'tinyint', 'smallint', 'mediumint', 'bigint'])) return 0;
			else if (str_beginwith($sType, ['float', 'double', 'decimal', 'numeric'])) return 0.0;
			else if (str_beginwith($sType, ['char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext'])) return '';
			else if ($sType == 'bit(1)') return false;
		}

		return $sDefault;
	}
}