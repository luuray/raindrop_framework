<?php
/**
 * Raindrop Framework for PHP
 *
 * Numeric Range
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

namespace Raindrop\Model;

use Raindrop\InvalidArgumentException;
use Raindrop\NotImplementedException;


/**
 * Class Range
 * @package Raindrop\Model
 * @var int Max
 * @var int Min
 */
class Range //implements \ArrayAccess, \Iterator
{
	protected $_iMin;
	protected $_iMax;


	public function __construct($iMin = null, $iMax = null)
	{
		if ($iMin !== null) {
			if (!is_numeric($iMin)) {
				throw new InvalidArgumentException('Min');
			}
		} else {
			$iMin = null;
		}

		if ($iMax !== null) {
			if (!is_numeric($iMax)) {
				throw new InvalidArgumentException('Max');
			}
		} else {
			$iMax = null;
		}

		if ($iMax < $iMin) {
			throw new InvalidArgumentException('Max');
		}

		$this->_iMin = $iMin;
		$this->_iMax = $iMax;
	}

	public function __get($sKey)
	{
		$sKey = strtolower($sKey);
		if ($sKey == 'min') {
			return $this->_iMin;
		} else if ($sKey == 'max') {
			return $this->_iMax;
		} else {
			throw new InvalidArgumentException();
		}
	}

	public function __set($sKey, $mValue)
	{
		$sKey = strtolower($sKey);
		if (is_numeric($mValue)) {
			if ($sKey == 'min') {
				if ($this->_iMax >= $mValue) {
					$this->_iMin = $mValue;
				} else {
					throw new InvalidArgumentException();
				}
			} else if ($sKey == 'max') {
				if ($this->_iMin <= $mValue) {
					$this->_iMax = $mValue;
				} else {
					throw new InvalidArgumentException();
				}
			}
		} else {
			throw new InvalidArgumentException();
		}
	}

	public function __isset($sKey)
	{
		$sKey = strtolower($sKey);

		if ($sKey == 'min') {
			return $this->_iMin !== null;
		} else if ($sKey == 'max') {
			return $this->_iMax !== null;
		} else {
			throw new InvalidArgumentException();
		}
	}

	public function __unset($sKey)
	{
		$sKey = strtolower($sKey);

		if ($sKey == 'min') {
			$this->_iMin = null;
		} else if ($sKey == 'max') {
			$this->_iMax = null;
		} else {
			throw new InvalidArgumentException();
		}
	}

	public function __toString()
	{
		throw new NotImplementedException();

		return '';
	}

	public function __sleep()
	{
		throw new NotImplementedException();
	}

	public function __wakeUp()
	{
		throw new NotImplementedException();
	}
}