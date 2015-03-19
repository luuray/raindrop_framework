<?php
/**
 * Raindrop Framework for PHP
 *
 * TimeArea Model
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

namespace Raindrop\Model;
use Raindrop\Exceptions\InvalidArgumentException;


/**
 * Class TimeArea
 *
 * @package Raindrop\Model
 * @property int $BeginTime BeginTime
 * @property string $BeginDaytime BeginDaytime
 * @property int $EndTime EndTime
 * @property string $EndDaytime EndDaytime
 */
class TimeArea
{
	protected $_iBegin = null;
	protected $_iEnd = null;

	public function __construct($mBegin = null, $mEnd = null)
	{
		$mBegin != null ? $mBegin = parse_timestamp($mBegin) : null;
		$mEnd != null ? $mEnd = parse_timestamp($mEnd) : null;

		if ($mBegin != null AND $mEnd != null AND $mBegin > $mEnd) {
			throw new InvalidArgumentException('BeginTime/EndTime');
		}

		$this->_iBegin = $mBegin;
		$this->_iEnd   = $mEnd;
	}

	public function __get($sKey)
	{
		$sGetter = "_get{$sKey}";
		if (method_exists($this, $sGetter)) {
			return $this->$sGetter();
		} else {
			throw new InvalidArgumentException($sKey);
		}
	}

	public function __set($sKey, $mValue)
	{
		$sSetter = "_setP{$sKey}";
		if (method_exists($this, $sSetter)) {
			$this->$sSetter($mValue);
		} else {
			throw new InvalidArgumentException($sKey);
		}
	}

	#region Get Timestamp
	protected function _getBeginTime()
	{
		return $this->_iBegin;
	}

	protected function _getEndTime()
	{
		return $this->_iEnd;
	}
	#endregion

	#region Get Daytime
	protected function _getBeginDayTime()
	{
		return $this->_iBegin == null ? null : date('Y-m-d H:i:s', $this->_iBegin);
	}

	protected function _getEndDayTime()
	{
		return $this->_iEnd == null ? null : date('Y-m-d H:i:s', $this->_iEnd);
	}
	#endregion

	#region Setter
	public function _setBeginDaytime($mValue)
	{
		$this->_setBeginTime($mValue);
	}

	public function _setBeginTime($mValue)
	{
		$mValue = parse_timestamp($mValue);
		if ($mValue == false OR ($this->_iEnd != null AND $mValue > $this->_iEnd)) {
			throw new InvalidArgumentException('BeginTime');
		}
		$this->_iBegin = $mValue;
	}

	public function _setEndDaytime($mValue)
	{
		$this->_setEndTime($mValue);
	}

	public function _setEndTime($mValue)
	{
		$mValue = parse_timestamp($mValue);
		if ($mValue == false OR ($this->_iBegin != null AND $mValue < $this->_iBegin)) {
			throw new InvalidArgumentException('EndTime');
		}

		$this->_iEnd = $mValue;
	}
	#endregion
}