<?php
/**
 * Raindrop Framework for PHP
 *
 * Identify Abstract
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


abstract class Identify
{
	protected static $_oInstance = null;

	protected $_bIdentified = false;

	/**
	 * @return Identify
	 */
	public static function GetInstance()
	{
		if (self::$_oInstance instanceof Identify) {
			//no action
		} else {
			self::$_oInstance = (new \ReflectionClass(get_called_class()))->newInstance();
		}

		return self::$_oInstance;
	}

	/**
	 * @return bool
	 */
	public final static function IsIdentified()
	{
		return self::GetInstance()->_bIdentified;
	}

	public final function __construct()
	{
		if (self::$_oInstance instanceof Identify) {
			throw new InitializedException;
		}

		$this->_initialize();

		$this->_load();
	}

	public abstract function inSession();

	/**
	 * Load Identify Status
	 * @return bool
	 */
	protected abstract function _load();

	/**
	 * Create Identify
	 *
	 * @return mixed
	 */
	protected abstract function _create();

	/**
	 * Revoke Identify
	 *
	 * @return mixed
	 */
	protected abstract function _revoke();

	protected function _initialize()
	{

	}
}