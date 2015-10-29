<?php
/**
 * Raindrop Framework for PHP
 *
 * ORM Data Model
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

use Raindrop\Exceptions\Database\DataModelException;
use Raindrop\Exceptions\InvalidArgumentException;


/**
 * Class Model
 * @package Raindrop\ORM
 *
 * @method static bool Any(string $sCondition = null, array $aParam = null)
 * @method static int Count(string $sCondition = null, array $aParam = null, string $sDistinct = null, string $sGroupBy = null)
 * @method static int CountSql(string $sQuery, array $aParams = null, boolean $bDistinct = false)
 * @method static null|Model SingleOrNull(string $sCondition = null, array $aParam = null, array $aOrderBy = null)
 * @method static null|array All(array $aOrderBy = null, int $iLimit = 0, int $iSkip = 0)
 * @method static null|array Find(string $sCondition = null, array $aParam = null, string $sGroupBy = null, array $aOrderBy = null, int $iLimit = 0, int $iSkip = 0)
 * @method static null|array FindSql(string $sQuery = null, array $aParams = null, string $sGroupBy = null, array $aOrderBy = null, int $iLimit = 0, int $iSkip = 0)
 * @method static Transaction BeginTransaction()
 *
 * @method bool Save()
 * @method bool Del()
 * @method bool DelAny(mixed $sConditions = null, array $aParams = null, array $aOrderBy = null, int $iLimit = 0, int $iSkip = 0, int $bForceDel = false)
 */
abstract class Model implements \JsonSerializable, \Serializable
{
	#region Model Stats
	/**
	 * Deleted State
	 */
	const ModelState_Deleted = -1;
	/**
	 * Normal State
	 */
	const ModelState_Normal = 0;
	/**
	 * Updated State
	 */
	const ModelState_Updated = 1;
	/**
	 * Create State
	 */
	const ModelState_Create = 2;
	#endregion
	/**
	 * @var array
	 */
	protected $_aColumns = array();

	protected $_aChangedColumns = array();

	protected $_aExtraColumns = array();
	/**
	 * @var array
	 */
	protected $_aIdentify = array();

	/**
	 * @var int
	 */
	protected $_iState = self::ModelState_Normal;

	/**
	 * Get Table's Name
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return get_called_class();
	}

	/**
	 * Get DSN Name
	 *
	 * @return string
	 */
	public static function getDbConnect()
	{
		return 'default';
	}

	/**
	 * Is Readonly
	 *
	 * @return bool
	 */
	public static function isReadonly()
	{
		return false;
	}

	/**
	 * Get Primary Key
	 *
	 * @return null
	 */
	public static function getPkName()
	{
		return null;
	}

	/**
	 * @param \stdClass|null $oData
	 *
	 * @throws \Raindrop\Exceptions\Model\ModelNotFoundException
	 */
	public final function __construct(\stdClass $oData = null)
	{
		$aScheme = ModelAction::GetInstance()->getModelDefault(get_called_class());
		$this->_aIdentify = array_key_case($aScheme['Identify'], CASE_LOWER);

		//get default data
		if ($oData === null) {
			$this->_iState = self::ModelState_Create;
			//assign columns with default
			foreach ($aScheme['Default'] AS $_col => $_val) {
				$this->_aColumns[strtolower($_col)] = ['Name' => $_col, 'Value' => $_val];
			}
		} else {
			$aData = get_object_vars($oData);

			foreach ($aScheme['Default'] AS $_col => $_val) {
				$_lowCaseCol = strtolower($_col);
				if (!array_key_exists($_col, $aData)) continue;

				$sSourceType = gettype($_val);

				if ($sSourceType == 'NULL' OR gettype($aData[$_col]) == $sSourceType OR settype($aData[$_col], $sSourceType)) {
					$this->_aColumns[$_lowCaseCol] = ['Name' => $_col, 'Value' => $aData[$_col]];
				}
				else throw new DataModelException('invalid_column_datetype:' . $_col);

				if (array_key_exists($_lowCaseCol, $this->_aIdentify)) $this->_aIdentify[$_lowCaseCol] = $aData[$_col];
			}
		}
	}

	/**
	 * Property Getter
	 *
	 * @param string $sColumn Column's Name, if column name begin with '_' means direct access without check user-defined getter
	 *
	 * @return mixed|null
	 */
	public function __get($sColumn)
	{
		//user-defined
		if (!str_beginwith($sColumn, '_') AND method_exists($this, "get{$sColumn}")) {
			return $this->{'get' . $sColumn}();
		}

		$sColumn        = str_beginwith($sColumn, '_') ? substr($sColumn, 1) : $sColumn;
		$sLowCaseColumn = strtolower($sColumn);

		if (array_key_exists($sLowCaseColumn, $this->_aColumns)) { //scheme columns
			return $this->_aColumns[$sLowCaseColumn]['Value'];
		} else if (array_key_exists($sLowCaseColumn, $this->_aExtraColumns)) { //earlier-append property
			return $this->_aExtraColumns[$sLowCaseColumn]['Value'];
		}

		return null;
	}

	/**
	 * Property Setter
	 *
	 * @param string $sColumn Column's Name, if column name begin with '_' means direct access without check user-defined getter
	 * @param mixed $mValue
	 *
	 * @return mixed
	 *
	 * @throws DataModelException
	 */
	public function __set($sColumn, $mValue)
	{
		//direct access
		if (!str_beginwith($sColumn, '_') AND method_exists($this, "set{$sColumn}")) {
			return $this->{'set' . $sColumn}($mValue);
		}

		$sColumn        = str_beginwith($sColumn, '_') ? substr($sColumn, 1) : $sColumn;
		$sLowCaseColumn = strtolower($sColumn);

		if (array_key_exists($sLowCaseColumn, $this->_aColumns)) {
			$sSourceType = gettype($this->_aColumns[$sLowCaseColumn]['Value']);
			if ($sSourceType == 'NULL' OR gettype($mValue) == $sSourceType OR settype($mValue, $sSourceType)) {
				$this->_aColumns[$sLowCaseColumn]['Value'] = $mValue;

				//set identification
				if ($this->_iState == self::ModelState_Create AND array_key_exists($sLowCaseColumn, $this->_aIdentify)) {
					$this->_aIdentify[$sLowCaseColumn] = $mValue;
				}
				//update state
				if ($this->_iState != self::ModelState_Create) {
					$this->_iState = self::ModelState_Updated;
				}

				$this->_aChangedColumns[$sLowCaseColumn] = $mValue;

				return;
			}

			throw new DataModelException('invalid_column_type:' . $sLowCaseColumn . ', require:' . $sSourceType . ', provide:' . gettype($mValue));
		} else if (array_key_exists($sLowCaseColumn, $this->_aExtraColumns)) {
			$this->_aExtraColumns[$sLowCaseColumn]['Value'] = $mValue;

			return;
		} else {
			$this->_aExtraColumns[$sLowCaseColumn] = ['Name' => $sColumn, 'Value' => $mValue];
		}
	}

	/**
	 * @param $sAction
	 * @param $aArgs
	 * @return mixed
	 */
	public final static function __callStatic($sAction, $aArgs)
	{
		array_unshift($aArgs, get_called_class());

		return call_user_func_array("Raindrop\ORM\ModelAction::{$sAction}", $aArgs);
	}

	/**
	 * @param $sAction
	 * @param $aArgs
	 * @return mixed
	 */
	public final function __call($sAction, $aArgs)
	{
		array_unshift($aArgs, $this);

		return call_user_func_array("Raindrop\ORM\ModelAction::{$sAction}", $aArgs);
	}

	/**
	 * @return array|null
	 */
	public function getRAWData()
	{
		if (func_num_args() == 0) {
			return ['Columns' => $this->_aColumns, 'Identify' => $this->_aIdentify, 'Changed' => $this->_aChangedColumns];
		} else if (func_num_args() == 1 AND !is_array(func_get_arg(0))) {
			$sKey = strtolower(func_get_arg(0));
			return array_key_exists($sKey, $this->_aColumns) ? $this->_aColumns[$sKey] : null;
		} else {
			if (is_array(func_get_arg(0))) {
				$aKeys = func_get_arg(0);
			} else {
				$aKeys = func_get_args();
			}

			$aResult = [];
			foreach ($aKeys AS $_key) {
				if (is_string($_key) OR is_int($_key)) {
					$_key = strtolower($_key);

					if (array_key_exists($_key, $this->_aColumns)) {
						$aResult[$_key] = $this->_aColumns[$_key];
					}
				}
			}

			return $aResult;
		}
	}

	/**
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function setRAWData()
	{
		if (func_num_args() == 1 AND is_array(func_get_arg(0))) {
			$aSource = func_get_arg(0);
			foreach ($aSource AS $_k => $_v) {
				$_k = is_string($_k) ? strtolower($_k) : $_k;
				if (array_key_exists($_k, $this->_aColumns)) $this->_aColumns[$_k]['Value'] = $_v;
				if (array_key_exists($_k, $this->_aIdentify)) $this->_aIdentify[$_k] = $_v;
			}

			return true;
		} else if (func_num_args() % 2 == 0) {
			$aSource = func_get_args();
			for ($i = 0; $i < count($aSource); $i += 2) {
				$aSource[$i] = is_string($aSource[$i]) ? strtolower($aSource[$i]) : $aSource[$i];
				if (array_key_exists($aSource[$i], $this->_aColumns)) $this->_aColumns[$aSource[$i]]['Value'] = $aSource[$i + 1];
				if (array_key_exists($aSource[$i], $this->_aIdentify)) $this->_aIdentify[$aSource[$i]] = $aSource[$i + 1];
			}

			return true;
		} else {
			throw new InvalidArgumentException;
		}
	}

	/**
	 * Get Model State
	 *
	 * @return int|null
	 */
	public final function getModelState()
	{
		return $this->_iState;
	}

	/**
	 * @param $iState
	 * @return bool
	 * @throws DataModelException
	 */
	public final function setModelState($iState)
	{
		switch ($iState) {
			case self::ModelState_Normal:
				$this->_iState = self::ModelState_Normal;
				break;
			case self::ModelState_Create:
				$this->_iState = self::ModelState_Create;
				break;
			case self::ModelState_Updated:
				$this->_iState = self::ModelState_Updated;
				break;
			case self::ModelState_Deleted:
				$this->_iState = self::ModelState_Deleted;
				break;
			default:
				throw new DataModelException('undefined_model_state');
		}

		return true;
	}

	/**
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize()
	{
		return serialize([
			'columns'         => $this->_aColumns,
			'extra_columns' => $this->_aExtraColumns,
			'identify'        => $this->_aIdentify,
			'changed_columns' => $this->_aChangedColumns,
			'state'           => $this->_iState]);
	}

	/**
	 * Constructs the object
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 * The string representation of the object.
	 * </p>
	 * @return void
	 * @since 5.1.0
	 * @throws DataModelException
	 */
	public function unserialize($serialized)
	{
		$aResults = @unserialize($serialized);

		if ($aResults == false) throw new DataModelException('unserialize_fail');

		$this->_iState        = $aResults['state'];
		$this->_aColumns      = $aResults['columns'];
		$this->_aExtraColumns = $aResults['extra_columns'];
		$this->_aIdentify     = $aResults['identify'];
		$this->_aChangedColumns = $aResults['changed_columns'];
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize()
	{
		return $this->_aColumns;
	}

	/**
	 * Dump Scheme's Defined Data to Array, Extra Columns will override the value with same key to Columns
	 *
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function toArray()
	{
		$this->_prepareToArray();

		$aResult = array();

		foreach ($this->_aColumns AS $_item) {
			$aResult[$_item['Name']] = $_item['Value'];
		}

		foreach ($this->_aExtraColumns AS $_item) {
			//$aResult[$_item['Name']] = $_item['Value'];
			if($_item['Value'] instanceof Model){
				$aResult[$_item['Name']] = $_item['Value']->toArray();
			}
			else{
				$aResult[$_item['Name']] = $_item['Value'];
			}
		}

		return $aResult;
	}

	/**
	 * Prepare to Array
	 */
	protected function _prepareToArray() {}
}