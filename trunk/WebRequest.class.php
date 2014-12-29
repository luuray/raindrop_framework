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

	public function getRawPost()
	{
		return file_get_contents('php://input');
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