<?php
/**
 * Raindrop Framework for PHP
 *
 * Api Request
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


class ApiRequest extends Request
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
}