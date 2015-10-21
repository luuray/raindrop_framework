<?php
/**
 * Raindrop Framework for PHP
 *
 * Table Schema
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

use Raindrop\Component\FileCache;
use Raindrop\DatabaseAdapter;
use Raindrop\DatabaseException;
use Raindrop\InvalidArgumentException;

class TableSchema
{
	protected $_sTableName;
	protected $_sConnName;
	protected $_aSchemaDefinedPk = array();
	protected $_aColumns = array();
	protected $_aColumnsLowCase = array();

	protected static $_oCache = null;

	public function __sleep()
	{
		return array('_sTableName', '_sConnName', '_aSchemaDefinedPk', '_aColumns', '_aColumnsLowCase');
	}

	public function __wakeup()
	{
		self::_GetCacheHandler();
	}

	#region Schema Static Methods
	/**
	 * Get Table Schema
	 *
	 * @param $sTable
	 * @param $sConn
	 * @return TableSchema
	 * @throws \Raindrop\InvalidArgumentException
	 */
	public final static function GetSchema($sTable, $sConn)
	{
		if (empty($sTable)) {
			throw new InvalidArgumentException('table_name');
		}
		if (empty($sConn)) {
			throw new InvalidArgumentException('connection_name');
		}

		return new self($sTable, $sConn);
	}

	/**
	 * Cache The Table's Schema
	 *
	 * @param $sTable
	 * @param $sConn
	 * @return bool
	 */
	public final static function CacheSchema($sTable, $sConn)
	{
		$aColumns = DatabaseAdapter::GetData(sprintf('SHOW FULL COLUMNS FROM `%s`', $sTable), null, $sConn);
		if ($aColumns == false || !is_array($aColumns)) {
			return false;
		}

		$aResult = array('SchemaDefinedPk' => array(), 'Columns' => array());
		foreach ($aColumns AS $_item) {
			$aResult['Columns'][$_item->Field] = new ColumnSchema(
				$_item->Field, $_item->Type, $_item->Key == 'PRI', $_item->Null == 'YES',
				$_item->Default, (strpos($_item->Extra, 'auto_increment') !== false));

			if ($_item->Key == 'PRI') {
				$aResult['SchemaDefinedPk'][] = $_item->Field;
			}
		}

		//write file
		self::_GetCacheHandler()->set($sConn . '_' . $sTable, $aResult);

		return $aResult;
	}

	/**
	 * Flush Schema Cache
	 *
	 * @param null $sConn
	 * @param null $sTable
	 */
	public final static function FlushSchemaCache($sConn = null, $sTable = null)
	{
		///TODO Flush defined connection or table's cache
		self::_GetCacheHandler()->flush();
	}

	/**
	 * Get Schema Cache Handler
	 *
	 * @return FileCache
	 */
	protected static function _GetCacheHandler()
	{
		if ((self::$_oCache instanceof FileCache) == false) {
			self::$_oCache = new FileCache(array('SavePath' => SysRoot . '/cache'), 'table-schema');
		}

		return self::$_oCache;
	}
	#endregion

	/**
	 * (Protected) Get Table Schema
	 *
	 * @param $sTable
	 * @param $sConn
	 * @throws DatabaseException
	 */
	protected function __construct($sTable, $sConn)
	{
		//load schema from cache file
		$aSchema = self::_GetCacheHandler()->get($sConn . '_' . $sTable);
		if ($aSchema === false OR !is_array($aSchema)) {
			//cache not exists
			$aSchema = self::CacheSchema($sTable, $sConn);
		}
		if ($aSchema == false OR !is_array($aSchema)) {
			throw new DatabaseException(sprintf('Conn: %s, Table: %s', $sTable, $sConn));
		} else {
			foreach ($aSchema['Columns'] AS $_name => $_column) {
				$this->_aColumns[$_name]                    = $_column;
				$this->_aColumnsLowCase[strtolower($_name)] = $_name;

			}

			$this->_sTableName       = $sTable;
			$this->_sConnName        = $sConn;
			$this->_aSchemaDefinedPk = $aSchema['SchemaDefinedPk'];
		}
	}

	public function __get($sProperty)
	{
		if (array_key_exists(strtolower($sProperty), $this->_aColumnsLowCase)) {
			return $this->_aColumns[$this->_aColumnsLowCase[strtolower($sProperty)]];
		} else if (property_exists($this, $sProperty)) {
			return $this->$sProperty;
		} else {
			return false;
		}
	}

	/**
	 * Check Table Has Column
	 *
	 * @param $sName
	 * @return bool
	 */
	public function hasColumn(&$sName)
	{
		//var_dump($sName, $this->_aColumnsLowCase, array_key_exists(strtolower($sName), $this->_aColumnsLowCase));
		$sName = strtolower($sName);
		if (array_key_exists($sName, $this->_aColumnsLowCase)) {
			$sName = $this->_aColumnsLowCase[$sName];

			return true;
		}

		return false;
	}

	/**
	 * Get Column's Default Value
	 *
	 * @param $sColumn
	 * @return bool
	 */
	public function getDefault($sColumn)
	{
		$sColumn = strtolower($sColumn);
		if (array_key_exists($sColumn, $this->_aColumnsLowCase)
			&& ($this->_aColumns[$this->_aColumnsLowCase[$sColumn]] instanceof ColumnSchema)
		) {
			return $this->_aColumns[$this->_aColumnsLowCase[$sColumn]]->DefaultValue;
		} else {
			return false;
		}
	}

	/**
	 * Get Table's Column List
	 *
	 * @return array
	 */
	public function getColumnsName()
	{
		return array_values($this->_aColumnsLowCase);
	}

	/**
	 * Set Values for Column
	 *
	 * @param $sColumn
	 * @param $mValue
	 * @return bool
	 */
	public function setValue($sColumn, $mValue)
	{
		$sColumn = strtolower($sColumn);

		if (array_key_exists($sColumn, $this->_aColumnsLowCase)
			&& ($this->_aColumns[$this->_aColumnsLowCase[$sColumn]] instanceof ColumnSchema)
		) {
			return $this->_aColumns[$this->_aColumnsLowCase[$sColumn]]->setValue($mValue);
		} else {
			return false;
		}
	}

	public function getValue($sColumn)
	{
		$sColumn = strtolower($sColumn);

		if (array_key_exists($sColumn, $this->_aColumnsLowCase)
			&& ($this->_aColumns[$this->_aColumnsLowCase[$sColumn]] instanceof ColumnSchema)
		) {
			return $this->_aColumns[$this->_aColumnsLowCase[$sColumn]]->Value;
		} else {
			return false;
		}
	}
}