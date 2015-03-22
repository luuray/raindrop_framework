<?php
/**
 * Raindrop Framework for PHP
 *
 * Web Request
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

class WebRequest extends Request
{
	protected function _initialize()
	{
		//$this->_aHeader = array_key_case($_SERVER, CASE_LOWER);
		foreach ($_SERVER AS $_k => $_v) {
			if (str_beginwith($_k, 'HTTP_')) {
				$this->_aHeader[strtolower(substr($_k, 4))] = $_v;
			}
		}
		$this->_aQuery = array_key_case($_GET, CASE_LOWER);

		//empty post, try json decode raw post
		if (empty($_POST)) {
			$mResult = json_decode($this->getRawPost(), true);
			if ($mResult != false) {
				$this->_aData   = array_key_case($mResult, CASE_LOWER);
				$this->_bIsAjax = true;
			}
		} else {
			$this->_aData = array_key_case($_POST, CASE_LOWER);
		}
	}

	public function getMethod()
	{
		if ($this->_sMethod !== null) {
			return $this->_sMethod;
		}

		if (isset($_SERVER['REQUEST_METHOD'])) {
			$this->_sMethod = $_SERVER['REQUEST_METHOD'];
		} else {
			$this->_sMethod = 'UNDEFINED';
		}

		return $this->_sMethod;
	}

	public function getType()
	{
		if ($this->_bIsAjax) {
			return 'Json';
		} else {
			return $this->_sType;
		}
	}

	public function getRawPost()
	{
		return file_get_contents('php://input');
	}

	public function getFile($sKey)
	{
	}

	public function getRemoteAddress()
	{
		if ($_SERVER['HTTP_CLIENT_IP'])
			return $_SERVER['HTTP_CLIENT_IP'];
		else if ($_SERVER['HTTP_X_FORWARDED_FOR'])
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if ($_SERVER['HTTP_X_FORWARDED'])
			return $_SERVER['HTTP_X_FORWARDED'];
		else if ($_SERVER['HTTP_FORWARDED_FOR'])
			return $_SERVER['HTTP_FORWARDED_FOR'];
		else if ($_SERVER['HTTP_FORWARDED'])
			return $_SERVER['HTTP_FORWARDED'];
		else if ($_SERVER['REMOTE_ADDR'])
			return $_SERVER['REMOTE_ADDR'];
		else
			return 'UNKNOWN';
	}
}