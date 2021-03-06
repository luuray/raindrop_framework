<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result of Json
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\ActionResult;

use Raindrop\ActionResult;
use Raindrop\Application;

class Json extends ActionResult
{
	protected $_bAllowGet = false;
	protected $_mData = null;
	protected $_iHttpCode = 200;

	/**
	 * Create a ActionResult Object
	 *
	 * @param bool $bAllowGet Allow Request by GET Method
	 * @param null|array $mData Result Data
	 * @param int $iHttpCode HTTP Response Code
	 */
	public function __construct($bAllowGet = false, $mData = null, $iHttpCode=200)
	{
		$this->_bAllowGet = $bAllowGet;
		$this->_mData     = $mData;
		$this->_iHttpCode = $iHttpCode;
	}

	/**
	 * Output Result
	 *
	 * @return mixed
	 */
	public function Output()
	{
		ob_clean();

		ob_start();

		@header('Content-type: application/json', true, $this->_iHttpCode);

		if ($this->_bAllowGet == false AND Application::GetRequest()->getMethod() != 'POST') {
			echo json_encode(array('status' => false, 'message' => 'post_method_only'));
		} else {
			echo json_encode($this->_mData);
		}

		ob_end_flush();
	}

	public function toString()
	{
		return json_encode($this->_mData);
	}
}