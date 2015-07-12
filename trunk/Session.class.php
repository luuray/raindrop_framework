<?php
/**
 * Raindrop Framework for PHP
 *
 * Session Model
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

namespace Raindrop;


use Raindrop\Exceptions\InitializedException;

final class Session implements \Iterator, \ArrayAccess
{
	/**
	 * @var null|Session
	 */
	protected static $_oInstance = null;

	protected $_mOffset = null;
	protected $_pSession = null;
	protected $_sSessionId = null;
	protected $_sPrefix = null;

	/**
	 * @return null|Session
	 */
	public static function GetInstance()
	{
		//return self::$_oInstance === null ? new self() : self::$_oInstance;
		if (self::$_oInstance === null) self::$_oInstance = new self();

		return self::$_oInstance;
	}

	/**
	 * @throws InitializedException
	 */
	public static function Start()
	{
		if (self::$_oInstance === null) {
			self::GetInstance()->_initialize();
		} else {
			throw new InitializedException();
		}
	}

	public static function Restart()
	{
		if (self::$_oInstance === null) {
			self::GetInstance()->_initialize();
		} else {
			self::GetInstance()->destroy();

			self::GetInstance()->_initialize(true);
		}
	}

	/**
	 * @return string
	 */
	public static final function GetSessionId()
	{
		return self::$_oInstance == null ? null : self::$_oInstance->_sSessionId;
	}

	/**
	 * @param $sKey
	 * @return mixed
	 */
	public static function Get($sKey)
	{
		return self::$_oInstance->_getItem($sKey);
	}

	/**
	 * @param $sKey
	 * @param $mValue
	 */
	public static function Set($sKey, $mValue)
	{
		self::$_oInstance->_setItem($sKey, $mValue);
	}

	/**
	 * @param $sKey
	 * @return mixed
	 */
	public static function Has($sKey)
	{
		return self::$_oInstance->_hasItem($sKey);
	}

	/**
	 * @param $sKey
	 */
	public static function Del($sKey)
	{
		self::$_oInstance->_delItem($sKey);
	}

	/**
	 *
	 */
	protected function __construct()
	{
		$this->_initialize();
	}

	protected function _initialize($bRestart=false)
	{
		//todo session to memCache

		if ($bRestart) {
			session_regenerate_id(true);
		} else {
			if (@session_start() == false) throw new FatalErrorException('session_start_fail');
		}

		$this->_pSession = &$_SESSION;
		$this->_mOffset    = key($this->_pSession);
		$this->_sSessionId = session_id();
		$this->_sPrefix    = Configuration::Get('Cookie\Prefix', AppName);
	}

	/**
	 * @param $sKey
	 * @return string
	 */
	protected function _generateKey($sKey)
	{
		return sprintf('%s_%s', $this->_sPrefix, strtolower(trim($sKey)));
	}

	/**
	 * @param $sKey
	 * @return bool
	 */
	protected function _getItem($sKey)
	{
		$sKey = $this->_generateKey($sKey);

		return array_key_exists($sKey, $this->_pSession) ? $this->_pSession[$sKey] : false;
	}

	/**
	 * @param $sKey
	 * @param $mValue
	 */
	protected function _setItem($sKey, $mValue)
	{
		$sKey = $this->_generateKey($sKey);

		$this->_pSession[$sKey] = $mValue;
	}

	/**
	 * @param $sKey
	 * @return bool
	 */
	protected function _hasItem($sKey)
	{
		$sKey = $this->_generateKey($sKey);

		return array_key_exists($sKey, $this->_pSession);
	}

	/**
	 * @param $sKey
	 */
	protected function _delItem($sKey)
	{
		$sKey = $this->_generateKey($sKey);

		if (array_key_exists($sKey, $this->_pSession)) unset($this->_pSession[$sKey]);
	}

	/**
	 * @param $sKey
	 * @return bool
	 */
	public function __get($sKey)
	{
		return $this->_getItem($sKey);
	}

	/**
	 * @param $sKey
	 * @param $mValue
	 */
	public function __set($sKey, $mValue)
	{
		$this->_setItem($sKey, $mValue);
	}

	/**
	 * @param $sKey
	 * @return bool
	 */
	public function __isset($sKey)
	{
		return $this->_hasItem($sKey);
	}

	/**
	 * @param $sKey
	 */
	public function __unset($sKey)
	{
		$this->_delItem($sKey);
	}

	/**
	 *
	 */
	public function destroy()
	{
		return array_walk($this->_pSession,
			function ($_v, $_k) {
				if (str_beginwith($_k, $this->_sPrefix)) unset($this->_pSession[$_k]);
			});
	}
#region Iterator
	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current()
	{
		return $this->_pSession[$this->_mOffset];
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next()
	{
		do {
			if (next($this->_pSession) == false) return false;
		} while (str_beginwith(key($this->_pSession), $this->_sPrefix));

		$this->_mOffset = key($this->_pSession);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key()
	{
		return $this->_mOffset;
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
		return isset($this->_pSession[$this->_mOffset]);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind()
	{
		reset($this->_pSession);
		$this->_mOffset = key($this->_pSession);
	}
#endregion

#region ArrayAccess
	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $sKey <p>
	 * An offset to check for.
	 * </p>
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($sKey)
	{
		return $this->_hasItem($sKey);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $sKey <p>
	 * The offset to retrieve.
	 * </p>
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($sKey)
	{
		return $this->_getItem($sKey);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $sKey <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $mValue <p>
	 * The value to set.
	 * </p>
	 * @return void
	 */
	public function offsetSet($sKey, $mValue)
	{
		$this->_setItem($sKey, $mValue);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $sKey <p>
	 * The offset to unset.
	 * </p>
	 * @return void
	 */
	public function offsetUnset($sKey)
	{
		$this->_delItem($sKey);
	}
#endregion
}