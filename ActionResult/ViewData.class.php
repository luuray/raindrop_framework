<?php
/**
 * Raindrop Framework for PHP
 *
 * View Data
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\ActionResult;

class ViewData implements \Iterator, \ArrayAccess
{
	protected static $_oInstance = null;

	protected $_sOffset = null;
	protected $_aViewData = array();

	public static function GetInstance()
	{
		if ((self::$_oInstance instanceof ViewData) == false) {
			new self();
		}

		return self::$_oInstance;
	}

	protected function __construct()
	{
		if (self::$_oInstance instanceof ViewData) {
			return self::$_oInstance;
		}

		$this->_sOffset = key($this->_aViewData);

		self::$_oInstance = $this;
	}

	public function __get($sKey)
	{
		$sKey = strtolower($sKey);

		return array_key_exists($sKey, $this->_aViewData) ? $this->_aViewData[$sKey] : null;
	}

	public function __set($sKey, $mValue)
	{
		$this->_aViewData[strtolower($sKey)] = $mValue;
	}

	public function __isset($sKey)
	{
		return array_key_exists(strtolower($sKey), $this->_aViewData);
	}

	public function __unset($sKey)
	{
		$sKey = strtolower($sKey);

		if (array_key_exists($sKey, $this->_aViewData)) {
			unset($this->_aViewData[$sKey]);
		}
	}

	public function mergeReplace($aData)
	{
		$aData            = array_key_case($aData, CASE_LOWER);
		$this->_aViewData = array_merge_replace($this->_aViewData, $aData);

		return $this;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current()
	{
		//return $this->_aViewData[$this->_sOffset];
		return current($this->_aViewData);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next()
	{
		if (next($this->_aViewData)) {
			$this->_sOffset = key($this->_aViewData);

			return current($this->_aViewData);
		} else {
			return false;
		}

	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key()
	{
		return key($this->_aViewData);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid()
	{
		return isset($this->_sOffset, $this->_aViewData);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind()
	{
		reset($this->_aViewData);
		$this->_sOffset = key($this->_aViewData);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset)
	{
		$offset = strtolower($offset);

		return array_key_exists($offset, $this->_aViewData);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset)
	{
		$offset = strtolower($offset);

		return array_key_exists($offset, $this->_aViewData) ? $this->_aViewData[$offset] : null;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$offset                    = strtolower($offset);
		$this->_aViewData[$offset] = $value;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		$offset = strtolower($offset);
		if (array_key_exists($offset, $this->_aViewData)) {
			unset($this->_aViewData[$offset]);
		}
	}


}