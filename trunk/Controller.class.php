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
 * Copyright (c) 2010-2014,
 * Site:
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop;

use Raindrop\ActionResult\ViewData;
use Raindrop\Interfaces\IController;

abstract class Controller implements IController
{
	/**
	 * @var null|Request
	 */
	protected $_oRequest = null;
	/**
	 * @var null|Dispatcher
	 */
	protected $_oDispatcher = null;

	protected $_oIdentify = null;

	protected $_oViewData = null;

	#region Identify
	/**
	 * Identify Required
	 *
	 * @return bool
	 */
	public function identifyRequired()
	{
		return false;
	}

	/**
	 * Required Permission
	 *
	 * @return null
	 */
	public function requiredPermission()
	{
		return null;
	}

	#endregion

	public final function __construct()
	{
		$this->_oRequest    = Application::GetRequest();
		$this->_oDispatcher = Dispatcher::GetInstance();
		$this->_oIdentify   = Application::GetIdentify();
		$this->_oViewData   = ViewData::GetInstance();

		$this->_initialize();
	}

	public final function __destruct()
	{
	}

	public function prepare()
	{
	}

	protected function _initialize()
	{
	}
} 