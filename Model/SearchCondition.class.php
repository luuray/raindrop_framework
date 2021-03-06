<?php
/**
 * Raindrop Framework for PHP
 *
 * Numeric Range
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Model;


class SearchCondition implements \RecursiveIterator, \ArrayAccess, \Countable
{
	const MODE_EQUAL = 0;
	const MODE_MATCH = 1;

	protected $_aConditions;
	protected $_aOrders = null;

	protected $_sPosition;

	protected $_bIsChild = false;

	public function __construct(array $aSource = null)
	{
		$this->_aConditions = $aSource === null ? [] : $aSource;
		$this->_sPosition   = key($this->_aConditions);
		$this->_bIsChild    = $aSource !== null;
	}

	public function __get($sKey)
	{
		return $this->offsetGet($sKey);
	}

	public function setKeyword($sFields, $mValue, $iMode)
	{
		if ($this->_bIsChild == true) {
			return false;
		}

		$this->_aConditions[] = ['fields' => $sFields, 'value' => $mValue, 'mode' => $iMode];

		reset($this->_aConditions);
		$this->_sPosition = key($this->_aConditions);

		return true;
	}

	public function setOrder($sField, $bDESC = true)
	{
		$this->_aOrders[] = sprintf('`%s` %s', $sField, $bDESC ? 'DESC' : 'ASC');
	}

	public function getOrder()
	{
		return $this->_aOrders;
	}

	public function count()
	{
		return count($this->_aConditions);
	}

	#region RecursiveIterator

	public function hasChildren()
	{
		return $this->_sPosition !== null;
	}

	public function getChildren($sPosition = null)
	{
		if ($this->_sPosition !== null OR $sPosition !== null) {
			return $sPosition === null ? new self($this->_aConditions[$this->_sPosition]) : new self($this->_aConditions[$sPosition]);
		}

		throw new \OutOfBoundsException();
	}

	/**
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current()
	{
		return $this->getChildren();
	}

	/**
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next()
	{
		next($this->_aConditions);
		$this->_sPosition = key($this->_aConditions);
	}

	/**
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key()
	{
		return key($this->_aConditions);
	}

	/**
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public function valid()
	{
		return $this->_sPosition !== null;
	}

	/**
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind()
	{
		reset($this->_aConditions);
		$this->_sPosition = key($this->_aConditions);
	}
	#endregion

	#region ArrayAccess
	/**
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 *
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	public function offsetExists($offset)
	{
		return array_key_exists(strtolower($offset), $this->_aConditions);
	}

	/**
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 *
	 * @return mixed Can return all value types.
	 * @since 5.0.0
	 */
	public function offsetGet($offset)
	{
		if ($offset === null) {
			if ($this->_sPosition !== null) {
				return is_array($this->_aConditions[$this->_sPosition]) ? $this->getChildren($this->_sPosition) : $this->_aConditions[$this->_sPosition];
			}
		}		else {
			$offset =strtolower($offset);
			if (isset($this->_aConditions[$offset])) {
				return is_array($this->_aConditions[$offset]) ? $this->getChildren($offset) : $this->_aConditions[$offset];
			}
		}

		throw new \OutOfBoundsException();
	}

	/**
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetSet($offset, $value)
	{
		$offset = strtolower($offset);

		if ($this->_sPosition == null OR !in_array($offset, ['fields', 'keyword', 'mode'])) {
			return null;
		}

		$this->_aConditions[$this->_sPosition][$offset] = $value;
	}

	/**
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset($offset)
	{
		$offset = strtolower($offset);

		if ($this->_sPosition == null OR isset($this->_aConditions[$offset])) {
			return null;
		}

		unset($this->_aConditions[$this->_sPosition][$offset]);
	}
	#endregion
}