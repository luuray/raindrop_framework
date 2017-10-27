<?php
/**
 * Raindrop Framework for PHP
 *
 * ArrayList Data Strut
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Model;

///TODO ArrayList Strut
use RecursiveIterator;

class ArrayList implements \ArrayAccess, \Countable, \RecursiveIterator, \Serializable, \JsonSerializable
{
	protected $_aData = [];
	protected $_aDataKeyMap = [];
	protected $_aMeta = [];

	protected $_sOffset = null;

	public function __construct(array $aData = null, $aMeta = null)
	{
		if ($aData !== null) {
			$this->_aData = $aData;
			foreach (array_keys($aData) AS $_k) {
				$this->_aDataKeyMap[strtolower($_k)] = $_k;
			}

			$this->_sOffset = key($this->_aData);

		}
		$this->_aMeta = is_array($aMeta) ? array_key_case($aMeta, CASE_LOWER) : null;
	}

	public function __call($sTarget, $aArgs = null)
	{
		$sTarget = strtolower($sTarget);
		if (str_beginwith($sTarget, 'get')) {
			$sMetaName = substr($sTarget, 3);

			return $this->_aMeta[$sMetaName];
		} else {
			return null;
		}
	}

	public function __get($sKey)
	{
		$sKey = strtolower($sKey);

		if (array_key_exists($sKey, $this->_aDataKeyMap)) {
			return $this->_aData[$this->_aDataKeyMap[$sKey]];
		} else {
			return null;
		}
	}

	public function __set($sKey, $mValue)
	{
		if (array_key_exists(strtolower($sKey), $this->_aDataKeyMap)) {
			$this->_aData[$this->_aDataKeyMap[strtolower($sKey)]] = $mValue;
		} else {
			$this->_aDataKeyMap[strtolower($sKey)] = $sKey;
			$this->_aData[$sKey]                   = $mValue;
		}
	}

	public function __unset($sKey)
	{
		if (array_key_exists(strtolower($sKey), $this->_aDataKeyMap)) {
			unset($this->_aData[strtolower($sKey)]);
			unset($this->_aDataKeyMap[strtolower($sKey)]);
		}
	}

	public function __isset($sKey)
	{
		return array_key_exists(strtolower($sKey), $this->_aDataKeyMap);
	}

	#region RecursiveIterator

	/**
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current()
	{
		return $this->offsetGet($this->_sOffset);
	}

	/**
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next()
	{
		next($this->_aData);
		$this->_sOffset = key($this->_aData);
	}

	/**
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key()
	{
		return $this->_sOffset;
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
		return $this->_sOffset !== null;
	}

	/**
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind()
	{
		reset($this->_aData);
		$this->_sOffset = key($this->_aData);
	}

	/**
	 * Returns if an iterator can be created for the current entry.
	 * @link http://php.net/manual/en/recursiveiterator.haschildren.php
	 * @return bool true if the current entry can be iterated over, otherwise returns false.
	 * @since 5.1.0
	 */
	public function hasChildren()
	{
		return $this->_sOffset !== null;
	}

	/**
	 * Returns an iterator for the current entry.
	 * @link http://php.net/manual/en/recursiveiterator.getchildren.php
	 *
	 * @param null $sOffset
	 *
	 * @return RecursiveIterator An iterator for the current entry.
	 * @since 5.1.0
	 */
	public function getChildren($sOffset = null)
	{
		if ($this->_sOffset !== null OR $sOffset !== null) {
			return $sOffset === null ? new self($this->_aData[$this->_sOffset]) : new self($this->_aData[$sOffset]);
		}

		throw new \OutOfBoundsException();
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
		return array_key_exists(strtolower($offset), $this->_aDataKeyMap);
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
			if ($this->_sOffset !== null) {
				return is_array($this->_aData[$this->_sOffset]) ? $this->getChildren($this->_sOffset) : $this->_aData[$this->_sOffset];
			}
		} else {
			$offset = strtolower($offset);
			if (isset($this->_aDataKeyMap[$offset])) {
				return is_array($this->_aData[$this->_aDataKeyMap[$offset]]) ? $this->getChildren($offset) : $this->_aData[$this->_aDataKeyMap[$offset]];
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
		$this->_aDataKeyMap[strtolower($offset)] = $offset;
		$this->_aData[$offset]                   = $value;

		$this->rewind();
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
		if ($this->offsetExists($offset)) {
			$offset = strtolower($offset);
			unset($this->_aData[$this->_aDataKeyMap[$offset]]);
			unset($this->_aDataKeyMap[$offset]);
		}
	}
	#endregion

	#region Serializable
	/**
	 * String representation of object
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize()
	{
		return serialize(['Data' => $this->_aData, 'Meta' => $this->_aMeta, 'KeyMap' => $this->_aDataKeyMap]);
	}

	/**
	 * Constructs the object
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 *
	 * @param string $serialized <p>
	 * The string representation of the object.
	 * </p>
	 *
	 * @return void
	 * @since 5.1.0
	 */
	public function unserialize($serialized)
	{
		$aResult = unserialize($serialized, false);
		if (is_array($aResult) AND array_key_exists('Data') AND array_key_exists('Meta') AND array_key_exists('KeyMap')) {
			$this->_aData       = $aResult['Data'];
			$this->_aMeta       = $aResult['Meta'];
			$this->_aDataKeyMap = $aResult['KeyMap'];

			$this->_sOffset = key($this->_aData);
		}
	}
	#endregion

	#region Countable
	/**
	 * Count elements of an object
	 * @link http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * </p>
	 * <p>
	 * The return value is cast to an integer.
	 * @since 5.1.0
	 */
	public function count()
	{
		return count($this->_aData);
	}
	#endregion

	#region JSONSetializable
	/**
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize()
	{
		return serialize(array_key_case(['data' => $this->_aData, 'meta' => $this->_aMeta], CASE_LOWER_UNDERSCORE));
	}

	#endregion

	public function toArray()
	{
		return $this->_aData;
	}
}