<?php
/**
 * Raindrop Framework for PHP
 *
 * Column Schema
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


use Raindrop\InvalidArgumentException;

final class ColumnSchema
{
	const TYPE_STRING = 0;
	const TYPE_INT = 1;
	const TYPE_UINT = 2;
	const TYPE_BOOLEAN = 3;
	const TYPE_DATETIME = 4;
	const TYPE_FLOAT = 5;
	const TYPE_DOUBLE = 6;

	public $Name;
	public $Type;
	public $Size = -1;
	public $AllowNull = false;
	public $IsPrimaryKey = false;
	public $AutoIncrement = false;
	public $DefaultValue = null;

	public $Value;

	/**
	 * @param $sName
	 * @param $sType
	 * @param $bIsPk
	 * @param $bNullable
	 * @param $mDefault
	 * @param $bAutoInc
	 */
	public function __construct($sName, $sType, $bIsPk, $bNullable, $mDefault, $bAutoInc)
	{
		$this->Name          = $sName;
		$this->IsPrimaryKey  = $bIsPk;
		$this->AllowNull     = $bNullable;
		$this->AutoIncrement = $bAutoInc;
		$this->_setType($sType);
		$this->_setValue($mDefault, true);
	}

	public function setValue($mValue)
	{
		if ($mValue === null && $this->AllowNull == false) {
			return false;
		}

		return $this->_setValue($mValue);
	}

	protected function _setType($sType)
	{
		if (strpos($sType, 'char') !== false) {
			$this->Type = self::TYPE_STRING;
			$aMatch     = null;
			if (preg_match('/\((?<Len>[0-9]+)\)/', $sType, $aMatch)) {
				$this->Size = $aMatch['Len'];
			}
		} else if (strpos($sType, 'text') !== false) {
			$this->Type = self::TYPE_STRING;
			$this->Size = 0;
		} else if (strpos($sType, 'int') !== false) {
			$this->Type = strpos($sType, 'unsigned') !== false ? self::TYPE_UINT : self::TYPE_INT;
			$aMath      = null;
			if (preg_match('/\((?<Len>[0-9]+)\)/', $sType, $aMatch)) {
				$this->Size = $aMatch['Len'];
			}
		} else if (strpos($sType, 'float') !== false OR strpos($sType, 'decimal(') !== false) {
			$this->Type = self::TYPE_FLOAT;
		} else if (strpos($sType, 'double') !== false) {
			$this->Type = self::TYPE_DOUBLE;
		} else if (strpos($sType, 'bit(1)') !== false) {
			$this->Type = self::TYPE_BOOLEAN;
		} else if (strpos($sType, 'datetime') !== false) {
			$this->Type = self::TYPE_DATETIME;
		} else {
			throw new InvalidArgumentException();
		}
	}

	protected function _setValue($mValue, $bSetDefault = false)
	{
		if ($mValue === null) {
			if ($this->AllowNull != true) {
				return false;
			} else {
				$this->Value = null;

				return true;
			}
		}


		if ($this->Type == self::TYPE_STRING) {
			if ($this->Size > 0 && mb_strlen($mValue) > $this->Size) {
				return false;
			}
			$mValue = (string)$mValue;
		} else if ($this->Type == self::TYPE_INT && settype($mValue, 'int')) {
			$mValue = $mValue;
		} else if ($this->Type == self::TYPE_UINT && settype($mValue, 'int')) {
			if ($mValue < 0) {
				return false;
			}
			$mValue = $mValue;
		} else if (($this->Type == self::TYPE_FLOAT || $this->Type == self::TYPE_DOUBLE) && settype($mValue, 'float')) {
			$mValue = $mValue;
		} else if ($this->Type == self::TYPE_BOOLEAN) {
			if (str_beginwith($mValue, 'b')) {
				$mValue = $mValue == 'b\'0\'' ? false : true;
			} else if (settype($mValue, 'bool')) {
				$mValue = $mValue;
			} else {
				return false;
			}
		} else if ($this->Type == self::TYPE_DATETIME) {
			if (is_int($mValue) || (is_string($mValue) && ($mValue = @strtotime($mValue)) !== false)) {
				$mValue = date('Y-m-d H:i:s', $mValue);
			} else {
				return false;
			}
		} else {
			return false;
		}

		if ($bSetDefault == true) {
			$this->DefaultValue = $mValue;
		}
		$this->Value = $mValue;

		return true;
	}
}