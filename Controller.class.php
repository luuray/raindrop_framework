<?php
/**
 * Raindrop Framework for PHP
 *
 * Controller Abstract
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop;

use Raindrop\ActionResult\ViewData;

/**
 * Class Controller
 *
 * @package Raindrop
 *
 * @property Request $Request Request
 * @property Identify $Identify Identify
 * @property ViewData $ViewData ViewData
 */
abstract class Controller
{
	const Perm_Any = '*';
	const Perm_Guest = null;

	protected $_aDependency = array();

	/**
	 * @var Application
	 */
	protected $Application;

	/**
	 * @var Request
	 */
	protected $Request;
	/**
	 * @var Identify
	 */
	protected $Identify;


	#region Identify
	/**
	 * Identify Required
	 *
	 * @return bool
	 */
	public static function identifyRequired()
	{
		return false;
	}

	/**
	 * Required Permission
	 *
	 * @return null
	 */
	public static function requiredPermission()
	{
		return '*';
	}

	#endregion

	public final function __construct()
	{
		$this->Request     = Application::GetRequest();
		$this->Identify    = Application::GetIdentify();
		$this->Application = Application::GetInstance();

		$this->_aDependency = [
			'viewdata' => function () {
				return ViewData::GetInstance();
			},
		];

		//call user-defined constructor
		$this->_initialize();
	}

	public final function __destruct()
	{
	}

	public final function __get($sPropName)
	{
		//Dependency Last
		$sDepName = strtolower($sPropName);
		if (property_exists($this, $sPropName)) {
			return $this->$sPropName;
		} else if (array_key_exists($sDepName, $this->_aDependency)) {
			$pDep = &$this->_aDependency[$sDepName];
			if (is_callable($pDep)) {
				$this->_aDependency[$sDepName] = $pDep();
			}

			return $pDep;

		} else {
			return null;
		}
	}

	public final function __set($sPropName, $mValue)
	{

	}

	public function prepare()
	{
	}

	protected function _initialize()
	{
	}
} 