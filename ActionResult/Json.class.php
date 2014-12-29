<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result in JSON
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

namespace Raindrop\ActionResult;

use Raindrop\ActionResult;
use Raindrop\Application;

class Json extends ActionResult
{
	protected $_bAllowGet = false;
	protected $_mData = null;

	/**
	 * Create a ActionResult Object
	 *
	 * @param bool $bAllowGet Allow Request by GET Method
	 * @param null|array $mData Result Data
	 */
	public function __construct($bAllowGet = false, $mData = null)
	{
		$this->_bAllowGet = $bAllowGet;
		$this->_mData     = $mData;
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

		@header('Content-type: application/json');

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