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
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop;

use Raindrop\Interfaces\IController;

abstract class ApiController
{
	/**
	 * @var null|Request
	 */
	protected $_oRequest = null;
	/**
	 * @var null|Dispatcher
	 */
	protected $_oDispatcher = null;

	/**
	 * @var APIIdentify
	 */
	protected $_oIdentify = null;

	protected $_bIsVerified = false;

	/**
	 * Identify Required
	 *
	 * @return bool
	 */
	public final function identifyRequired()
	{
		return false;
	}

	public final function __construct()
	{
		$this->_oRequest    = Application::GetRequest();
		$this->_oDispatcher = Dispatcher::GetInstance();

		$sIdentifyObjName = AppName . '\APIIdentify';
		$this->_oIdentify = $sIdentifyObjName::GetInstance(
			$this->_oRequest->getQuery('session')
		);
		$sToken           = $this->_oRequest->getQuery('token');
		if ($sToken != null) {
			$this->_bIsVerified = $this->_oIdentify->verifyToken($sToken);
		}

		return $this->_initialize();
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