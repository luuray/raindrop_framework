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
use Raindrop\Exceptions\InvalidArgumentException;
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
	 *
	 * @return int
	 * @throws ModelNotFoundException
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
	 *
	 * @return int
	 * @throws ModelNotFoundException
	 */
	public static function CountSql($sModel, $sQuery = null, $aParams = null, $bDistinct = false)
	{
		if (!class_exists($sModel) OR !is_subclass_of($sModel, 'Model')) {
			throw new ModelNotFoundException($sModel);
		}

		$sDbConnect = $sModel::GetDbConnect();

		return (int)DatabaseAdapter::GetVar(
			sprintf('SELECT %s FROM %s', $bDistinct == true ? 'DISTINCT COUNT(1)' : 'COUNT(1)', $sQuery),
			$aParams, $sDbConnect);
	}

	/**
	 * @param Model $oModel
	 *
	 * @return bool|Model
	 * @throws DataModelException
	 */
	public static function Save(Model $oModel)
	{
		if ($oModel->getModelState() === Model::ModelState_Create) {
			return self::GetInstance()->modelInsert($oModel);
		} else if ($oModel->getModelState() === Model::ModelState_Updated) {
			return self::GetInstance()->modelUpdate($oModel);
		} else if ($oModel->getModelState() === Model::ModelState_Normal) {
			return $oModel;
		} else {
			throw new DataModelException('undefined_model_stats');
		}
	}

	/**
	 * @param Model $oModel
	 *
	 * @return bool
	 * @throws DataModelException
	 */
	public static function Del(Model $oModel)
	{
		if ($oModel->getModelState() != Model::ModelState_Normal) {
			throw new DataModelException('invalid_model_state');
		}

		$aScheme = self::getTableScheme($oModel::getTableName(), $oModel::getDbConnect());
		$aSnapshot = $oModel->getRAWData();
		$aConditions = [];
		$aParams = [];

		if (empty($aSnapshot['Identify'])) $pIdentify = $aScheme['Columns'];
		else $pIdentify = &$aScheme['Identify'];

		foreach ($pIdentify AS $_col => $_val) {
			if (array_key_exists($_col, $aScheme['Columns']) == false) {
				throw new DataModelException('scheme_define_not_match');
			}
			$aConditions[] = sprintf('`%s`=:%s', $aScheme['Columns'][$_col], $_col);
			$aParams[$_col] = $_val;
		}

		if (DatabaseAdapter::GetAffectedRowNum(
				sprintf('DELETE FROM `%s` WHERE %s LIMIT 1',
					$oModel::getTableName(), implode(' AND ', $aConditions)),
				$aParams, $oModel::getDbConnect()) == false
		) {
			return false;
		}

		$oModel->setModelState(Model::ModelState_Deleted);

		return true;
	}

	/**
	 * @param $sModel
	 * @param null $sCondition
	 * @param null $aParams
	 * @param null $aOrderBy
	 * @param int $iLimit
	 * @param int $iSkip
	 * @param bool|false $bForceDel
	 *
	 * @return bool|int
	 * @throws InvalidArgumentException
	 * @throws ModelNotFoundException
	 */
	public static function DelAny($sModel, $sCondition = null, $aParams = null, $aOrderBy = null, $iLimit = 0, $iSkip = 0, $bForceDel = false)
	{
		if (!class_exists($sModel) OR !is_subclass_of($sModel, 'Model')) {
			throw new ModelNotFoundException($sModel);
		}

		$sQuery = sprintf('DELETE FROM `%s`', $sModel::GetTableName());

		if (str_nullorwhitespace($sCondition) AND $bForceDel !== true) {
			throw new InvalidArgumentException('condition');
		} else {
			$sQuery .= ' WHERE ' . $sCondition;
		}

		if (settype($iLimit) === false OR $iLimit < 0) {
			throw new InvalidArgumentException('limit');
		}
		if (settype($iSkip) === false OR $iSkip < 0) {
			throw new InvalidArgumentException('skip');
		}

		if (!empty($aOrderBy)) {
			$sQuery .= ' ORDER BY ' . implode(',', $aOrderBy);
		}

		$sQuery .= $iLimit > 0 ? (' LIMIT ' . ($iSkip >= 0 ? "{$iSkip},{$iLimit}" : $iLimit)) : null;

		$iResult = DatabaseAdapter::GetAffectedRowNum($sQuery, $aParams, $sModel::GetDbConnect());

		return $iResult == 0 ? false : (int)$iResult;
	}

	/**
	 * @param $sModel
	 * @param null $sCondition
	 * @param null $aParams
	 * @param null $aOrderBy
	 *
	 * @return null
	 * @throws ModelNotFoundException
	 */
	public static function SingleOrNull($sModel, $sCondition = null, $aParams = null, $aOrderBy = null)
	{
		if (!class_exists($sModel) OR !is_subclass_of($sModel, 'Model')) {
			throw new ModelNotFoundException($sModel);
		}

		$aResult = DatabaseAdapter::GetLine(
			sprintf('SELECT * FROM `%s` %s %s LIMIT 1',
				$sModel::GetTableName(),
				!empty($sCondition) ? 'WHERE ' . $sCondition : null,
				!empty($aOrderBy) ? 'ORDER BY ' . implode(',', $aOrderBy) : null),
			$aParams, $sModel::GetDbConnect());

		return $aResult == false ? null : new $sModel($aResult);
	}

	/**
	 * @param $sModel
	 * @param null $aOrderBy
	 * @param int $iLimit
	 * @param int $iSkip
	 *
	 * @return array|null
	 * @throws ModelNotFoundException
	 */
	public static function All($sModel, $aOrderBy = null, $iLimit = 0, $iSkip = 0)
	{
		if (!class_exists($sModel) OR !is_subclass_of($sModel, 'Model')) {
			throw new ModelNotFoundException($sModel);
		}

		$aResults = DatabaseAdapter::GetData(
			sprintf('SELECT * FROM `%s` %s %s',
				$sModel::GetTableName(),
				!empty($aOrderBy) ? 'ORDER BY ' . implode(',', $aOrderBy) : null,
				($iLimit > 0 ? ('LIMIT ' . ($iSkip >= 0 ? "{$iSkip},{$iLimit}" : $iLimit)) : null)),
			null, $sModel::GetDbConnect());

		if ($aResults !== false) {
			$aModelArray = [];
			foreach ($aResults AS $_item) {
				$aModelArray[] = new $sModel($_item);
			}

			return $aModelArray;
		}

		return null;
	}

	/**
	 * @param $sModel
	 * @param null $sCondition
	 * @param null $aParam
	 * @param null $sGroupBy
	 * @param null $aOrderBy
	 * @param int $iLimit
	 * @param int $iSkip
	 *
	 * @return array|null
	 * @throws ModelNotFoundException
	 */
	public static function Find($sModel, $sCondition = null, $aParam = null, $sGroupBy = null, $aOrderBy = null, $iLimit = 0, $iSkip = 0)
	{
		if (!class_exists($sModel) OR !is_subclass_of($sModel, 'Model')) {
			throw new ModelNotFoundException($sModel);
		}

		$aResults = DatabaseAdapter::GetData(
			sprintf('SELECT * FROM `%s` %s %s %s %s',
				$sModel::GetTableName(),
				(!empty($sCondition) ? 'WHERE ' . $sCondition : null),
				(!empty($sGroupBy) ? 'GROUP BY ' . $sGroupBy : null),
				(!empty($aOrderBy) ? 'ORDER BY ' . implode(',', $aOrderBy) : null),
				($iLimit > 0 ? ('LIMIT ' . ($iSkip >= 0 ? "{$iSkip},{$iLimit}" : $iLimit)) : null)),
			$aParam, $sModel::GetDbConnect());

		if ($aResults !== false) {
			$aModelArray = array();
			foreach ($aResults AS $_item) {
				$aModelArray[] = new $sModel($_item);
			}

			return $aModelArray;
		}

		return null;
	}

	/**
	 * @param $sModel
	 * @param $sQuery
	 * @param null $aParams
	 * @param null $sGroupBy
	 * @param null $aOrderBy
	 * @param int $iLimit
	 * @param int $iSkip
	 *
	 * @return array|null
	 * @throws ModelNotFoundException
	 */
	public static function FindSql($sModel, $sQuery, $aParams = null, $sGroupBy = null, $aOrderBy = null, $iLimit = 0, $iSkip = 0)
	{
		if (!class_exists($sModel) OR !is_subclass_of($sModel, 'Model')) {
			throw new ModelNotFoundException($sModel);
		}

		$aResults = DatabaseAdapter::GetData(
			sprintf(
				'SELECT %s %s %s %s',
				$sQuery,
				(!empty($sGroupBy) ? 'GROUP BY ' . $sGroupBy : null),
				(!empty($aOrderBy) ? 'ORDER BY ' . implode(',', $aOrderBy) : null),
				($iLimit > 0 ? ('LIMIT ' . ($iSkip >= 0 ? "{$iSkip},{$iLimit}" : $iLimit)) : null)),
			$aParams, $sModel::GetDbConnect());

		if ($aResults !== false) {
			$aModelArray = array();
			foreach ($aResults AS $_item) {
				$aModelArray[] = new $sModel($_item);
			}

			return $aModelArray;
		}

		return null;
	}

	/**
	 * @param $sModel
	 *
	 * @return Transaction
	 * @throws ModelNotFoundException
	 */
	public static function BeginTransaction($sModel)
	{
		if (!class_exists($sModel) OR !is_subclass_of($sModel, 'Model')) {
			throw new ModelNotFoundException($sModel);
		}

		return Transaction::BeginTransaction($sModel::GetDbConnect());
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
	 * @param $sTable
	 * @param $sDbConnect
	 *
	 * @return array|bool|mixed
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
	 *
	 * @return array
	 * @throws ModelNotFoundException
	 */
	public function getModelDefault($sModel)
	{
		if (!class_exists($sModel) OR !is_subclass_of($sModel, 'Model')) {
			throw new ModelNotFoundException($sModel);
		}
		$aScheme = $this->getTableScheme($sModel::GetTableName(), $sModel::GetDbConnect());

		$aResult = ['Default' => [], 'Identify' =>[]];
		foreach ($aScheme['Columns'] AS $_col) {
			$aResult['Default'][$_col['Name']] = $_col['Default'];
			if (in_array($_col['Name'], $aScheme['PrimaryKeys'])) $aResult['Identify'][$_col['Name']] = $_col['Default'];
		}


		return $aResult;
	}

	/**
	 * Model Insert Operation
	 *
	 * @param Model $oModel
	 *
	 * @return bool
	 * @throws DataModelException
	 * @throws \Raindrop\Exceptions\InvalidArgumentException
	 */
	public function modelInsert(Model $oModel)
	{
		$aScheme = $this->getTableScheme($oModel::getTableName(), $oModel::getDbConnect());

		$aSnapshot = $oModel->getRAWData();

		$aColumns = [];
		$aColValues = [];
		$aColParams = [];
		$sAutoId = null;
		foreach ($aScheme['Columns'] AS $_name => $_col) {
			if (array_key_exists($_name, $aSnapshot['Columns'])) {
				$aColumns[] = "`{$_col['Field']}`";
				$aColParams[] = ":{$_col['Field']}";
				$aColValues[$_col['Field']] = $aSnapshot['Columns'][$_name];
			}
			if ($_col['IsAutoIncrement'] == true) $sAutoId = $_name;
		}

		if ($sAutoId != null) {
			$iResult = DatabaseAdapter::GetLastId(
				sprintf('INSERT INTO `%s` (%s) VALUE (%s)',
					$oModel::getTableName(), implode(',', $aColumns), implode(',', $aColParams)),
				$aColValues,
				$oModel::getDbConnect());
			//success
			if (intval($iResult) != 0) {
				$oModel->setRAWData($sAutoId, (int)$iResult);
			} else {
				throw new DataModelException('save_fail');
			}
		} else {
			$iResult = DatabaseAdapter::GetAffectedRowNum(
				sprintf('INSERT INTO `%s` (%s) VALUE (%s)',
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
	 *
	 * @return bool
	 * @throws DataModelException
	 * @throws \Raindrop\Exceptions\InvalidArgumentException
	 */
	public function modelUpdate(Model $oModel)
	{
		$aScheme = $this->getTableScheme($oModel::getTableName(), $oModel::getDbConnect());
		$aSnapshot = $oModel->getRAWData();

		$aChangedIdentifies = array();
		$aIdentify = array();
		$aUpdatedField = array();
		$aQueryParams = array();
		foreach ($aScheme['Columns'] AS $_col => $_def) {
			//changed columns
			if (array_key_exists($_col, $aSnapshot['Columns']) AND $aSnapshot['Columns'][$_col] != $_def['Default']) {
				$aUpdatedField[] = sprintf('`%s`=:%s', $_def['Name'], $_col);
				$aQueryParams[$_col] = $aSnapshot[$_col];
			}
			//identify
			if (array_key_exists($_col, $aSnapshot['Identify'])) {
				$aIdentify[] = sprintf('`%s`=:IDY_%s', $_def['Name'], $_col);
				$aQueryParams['IDY_' . $_col] = $aSnapshot['Identify'][$_col];

				//identify changed
				if ($aSnapshot['Identify'][$_col] != $aSnapshot['Columns'][$_col]) $aChangedIdentifies[$_col] = $aSnapshot['Columns'][$_col];
			}
		}

		$iResult = DatabaseAdapter::GetAffectedRowNum(
			sprintf('UPDATE `%s` SET %s WHERE %s LIMIT 1',
				$oModel::getTableName(), implode(',', $aUpdatedField), implode(' AND ', $aIdentify)),
			$aQueryParams,
			$oModel::getDbConnect());
		if ($iResult <= 0) throw new DataModelException('save_fail');

		//update identify if need
		if (!empty($aChangedIdentifies)) $oModel->setRAWData($aChangedIdentifies);

		$oModel->setModelState(Model::ModelState_Normal);

		return true;
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

		$aResult = array('PrimaryKeys' => array(), 'Columns' => array());
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
				$aResult['PrimaryKeys'][] = $_item->Field;
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