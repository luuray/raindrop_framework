<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result of Http Code Result
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
use Raindrop\InvalidArgumentException;
use Raindrop\NotImplementedException;

class HttpCode extends ActionResult
{
	protected $_iCode = null;
	protected $_aParam = null;

	/**
	 * Create a ActionResult Object
	 *
	 * @param int $iHttpCode HTTP Response Code
	 * @param mixed $mParam Output Parameter
	 * @throws InvalidArgumentException
	 */
	public function __construct($iHttpCode = null, $mParam = null)
	{
		if (empty($iHttpCode)) {
			throw new InvalidArgumentException('httpcode');
		}
		$this->_iCode  = $iHttpCode;
		$this->_aParam = $mParam;
	}

	/**
	 * Output Result
	 *
	 * @return mixed
	 */
	public function Output()
	{
		// TODO: Implement Output() method.
		ob_end_clean();
		//die("Code:".$this->_iCode);
		//header('HTTP/1.1 404 Not Found');
		//header(null, true, $this->_iCode);
		http_response_code($this->_iCode);
		die();
		///die('HttpCode:' . $this->_iCode . '<br>Message:' . $this->_aParam);
	}

	public function toString()
	{
		throw new NotImplementedException();
	}
} 