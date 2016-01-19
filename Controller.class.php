<?php
/**
 * Raindrop Framework for PHP
 *
 * Basic Controller
 *
 * @author $Author$
 * @copyright
 * @date $Date$
 *
 * Copyright (c) 2014-2015, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
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
	protected $_aDependency = array();

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
		$this->Request  = Application::GetRequest();
		$this->Identify = Application::GetIdentify();

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
			if(is_callable($pDep)){
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