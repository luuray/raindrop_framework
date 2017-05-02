<?php
/**
 * Raindrop Framework for PHP
 *
 * Configuration Manager
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop;

/**
 * Class Configuration
 * @package Raindrop
 * @method static mixed Get($sKey, $mDefaultValue=null)
 */
class Configuration implements \ArrayAccess, \Iterator
{
	protected static $_oInstance = null;

	protected $_aConfig = array();

	protected $_pPosition = null;

	public static function __callStatic($sMethod, $aArgs)
	{
		if (strtolower($sMethod) == 'get') {
			return call_user_func_array('self::GetRoot', $aArgs);
		}
	}

	/**
	 * Load Configuration File (Alias of Configuration::GetInstance)
	 */
	public static function Load()
	{
		self::GetInstance();
	}

	public static function GetInstance()
	{
		if (self::$_oInstance === null) {
			new self();
		}

		return self::$_oInstance;
	}

	public static function GetRoot($sKey = null, $mDefaultValue = null)
	{
		if ($sKey == null) return self::GetInstance();

		$aFetchTree = preg_split('#[/\\\]+#', trim($sKey));
		$oResult = self::GetInstance();
		foreach ($aFetchTree AS $_k) {
			$oResult = $oResult->$_k;
			if ($oResult == null) return $mDefaultValue;
		}

		return $oResult;
	}

	protected function __construct($aConfigSource = null)
	{
		if ($aConfigSource == null) {
			if (Application::IsDebugging()) {
				$this->_aConfig = Loader::Import('config.debug.php', AppDir);
			} else {
				$this->_aConfig = Loader::Import('config.php', AppDir);
			}

			$this->_pPosition = key($this->_aConfig);

			self::$_oInstance = $this;
		} else {
			$this->_aConfig = $aConfigSource;

			$this->_pPosition = key($this->_aConfig);

			return $this;
		}
	}

	/**
	 * Getter
	 *
	 * @param $sKey
	 * @return mixed
	 */
	public function __get($sKey)
	{
		if (array_key_exists($sKey, $this->_aConfig)) {
			$mTarget = $this->_aConfig[$sKey];

			return is_array($mTarget) ? new self($mTarget) : $mTarget;
		} else {
			return null;
		}
	}

	public function __call($sMethod, $aArgs)
	{
		if (strtolower($sMethod) == 'get') {
			return call_user_func_array([$this, 'getByKey'], $aArgs);
		}
	}

	public function getByKey($sKey, $mDefaultValue = null)
	{
		if ($sKey == null) {
			return $this;
		}

		$aFetchTree = preg_split('#[/\\\]+#', trim($sKey));
		foreach ($aFetchTree AS $_k) {
			$oResult = $this->$_k;
			if ($oResult == null) return $mDefaultValue;
		}

		return $oResult;
	}

	#region ArrayAccess Interface Methods
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
		return array_key_exists($offset, $this->_aConfig);
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
		return $this->__get($offset);
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
		return false;
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
		return false;
	}
	#endregion

	#region Iterator Interface Methods
	/**
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current()
	{
		//return $this->_aConfig[$this->_pPosition];
		return new self($this->_aConfig[$this->_pPosition]);
	}

	/**
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next()
	{
		next($this->_aConfig);
		$this->_pPosition = key($this->_aConfig);

	}

	/**
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key()
	{
		return $this->_pPosition;
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
		return array_key_exists($this->_pPosition, $this->_aConfig);
	}

	/**
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind()
	{
		reset($this->_aConfig);
		$this->_pPosition = key($this->_aConfig);
	}

	#endregion
}