<?php
/**
 * Raindrop Framework for PHP
 *
 * Cookie Operator
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


use Raindrop\Exceptions\NotImplementedException;
use Raindrop\Exceptions\RuntimeException;

final class Cookie implements \ArrayAccess
{
	protected static $_oInstance = null;

	public static function GetInstance()
	{
		if (self::$_oInstance === null) {
			self::$_oInstance = new self();
		}

		return self::$_oInstance;
	}

	/**
	 * @param $sKey
	 * @param $mValue
	 * @param null $iLifetime
	 *
	 * @return bool
	 */
	public static function Set($sKey, $mValue, $iLifetime = null)
	{
		return self::GetInstance()->_setItem($sKey, $mValue, $iLifetime);
	}

	/**
	 * @param $sKey
	 *
	 * @return bool
	 */
	public static function Has($sKey)
	{
		return self::GetInstance()->_hasItem($sKey);
	}

	/**
	 * @param $sKey
	 *
	 * @return bool
	 */
	public static function Get($sKey)
	{
		return self::GetInstance()->_getItem($sKey);
	}

	/**
	 * @param $sKey
	 *
	 * @return bool
	 */
	public static function Del($sKey)
	{
		return self::GetInstance()->_delItem($sKey);
	}

	/**
	 * @param $sKey
	 * @param null $iLifetime
	 *
	 * @return bool
	 */
	public static function Refresh($sKey, $iLifetime = null)
	{
		return self::GetInstance()->_refresh($sKey, $iLifetime);
	}

	/**
	 * @return bool
	 */
	public function CleanUp()
	{
		return self::GetInstance()->_cleanup();
	}

	protected $_sPrefix;
	protected $_sDomain;
	protected $_iLifetime;
	protected $_bHttpOnly;
	protected $_bIsHttps;

	protected function __construct()
	{
		$this->_sPrefix   = Configuration::Get('Cookie\Prefix', AppName);
		$this->_sDomain   = Configuration::Get('Cookie\Domain', '');
		$this->_iLifetime = Configuration::Get('Cookie\Lifetime', 86400);
		$this->_bHttpOnly = Configuration::Get('Cookie\HttpOnly', true);
		$this->_bIsHttps  = (isset($_SERVER['HTTPS']) AND !in_array($_SERVER['HTTPS'], array('off', false))) ? true : false;
	}

	protected function _generateKey($sKey)
	{
		return sprintf('%s_%s', $this->_sPrefix, strtolower($sKey));
	}

	protected function _getItem($sKey)
	{
		$sKey = $this->_generateKey($sKey);
		if (array_key_exists($sKey, $_COOKIE)) {
			return $_COOKIE[$sKey];
		}

		return false;
	}

	protected function _setItem($sKey, $mValue, $iLifetime)
	{
		$sKey = $this->_generateKey($sKey);
		if (!is_string($mValue) AND !is_numeric($mValue)) {
			return false;
		}

		return setcookie(
			$sKey,
			$mValue,
			$iLifetime === null ?
				time() + $this->_iLifetime : time() + $iLifetime,
			'/',
			$this->_sDomain,
			$this->_bIsHttps,
			$this->_bHttpOnly);
	}

	protected function _hasItem($sKey)
	{
		$sKey = $this->_generateKey($sKey);

		return array_key_exists($sKey, $_COOKIE);
	}

	protected function _delItem($sKey)
	{
		$sKey = $this->_generateKey($sKey);

		return setcookie($sKey, null, time() - 1, '/', $this->_sDomain, $this->_bIsHttps, $this->_bHttpOnly);
	}

	protected function _cleanup()
	{
		foreach ($_COOKIE AS $_k => $_v) {
			if (str_beginwith($_k, $this->_sPrefix)) {
				if (setcookie($_k, null, time() - 1, '/', $this->_sDomain, $this->_bIsHttps, $this->_bHttpOnly) === false) return false;
			}
		}

		return true;
	}

	protected function _refresh($sKey, $iLifetime)
	{
		if ($this->_hasItem($sKey)) {
			$sKey = $this->_generateKey($sKey);

			if (!setcookie(
				$sKey,
				$_COOKIE[$sKey],
				$iLifetime === null ?
					time() + $this->_iLifetime : time() + $iLifetime,
				'/',
				$this->_sDomain,
				$this->_bIsHttps,
				$this->_bHttpOnly)) {
				throw new RuntimeException('cookie_set_fail');
			}
		}

		return false;
	}

	#region Access Item as Properties(Getter, Setter, etc)
	public function __get($sKey)
	{
		return $this->_getItem($sKey);
	}

	public function __set($sKey, $mValue)
	{
		$this->_setItem($sKey, $mValue, 0);
	}

	public function __isset($sKey)
	{
		return $this->_hasItem($sKey);
	}

	public function __unset($sKey)
	{
		$this->_delItem($sKey);
	}
	#endregion

	#region Access Item as Array
	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
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
	 */
	public function offsetExists($offset)
	{
		// TODO: Implement offsetExists() method.
		throw new NotImplementedException;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 *
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
	 *
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 *
	 * @return void
	 */
	public function offsetSet($sKey, $mValue)
	{
		$this->_setItem($sKey, $mValue, 0);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 *
	 * @return void
	 */
	public function offsetUnset($sKey)
	{
		$this->_delItem($sKey);
	}
	#endregion
}