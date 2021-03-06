<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result in RAW Stream
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

class RawStream extends ActionResult
{
	protected $_pStream;
	protected $_iCode;

	public function __construct($pStream=null, $iCode=200)
	{
		$this->_pStream = $pStream;
		$this->_iCode = $iCode;
	}

	public function toString()
	{
		return $this->_pStream;
	}

	public function output()
	{
		@ob_clean();
		@header_remove();

		http_response_code($this->_iCode);
		echo $this->_pStream;
	}
}