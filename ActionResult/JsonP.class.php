<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result in JSONCallback
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2016, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */
namespace Raindrop\ActionResult;


use Raindrop\ActionResult;

class JsonP extends ActionResult
{
	protected $_bAllowGet = false;
	protected $_sCallback = null;
	protected $_mData = null;
	protected $_iHttpCode = 200;

	public function __construct($bAllowGet=false, $sCallback=null, $mData=null,$iHttpCode=200)
	{
		$this->_bAllowGet = $bAllowGet;
		$this->_sCallback = $sCallback;
		$this->_mData     = $mData;
		$this->_iHttpCode = $iHttpCode;

	}

	public function toString()
	{
		return sprintf('try{%s(%s);}catch(e){}', $this->_sCallback, json_encode($this->_mData));
	}

	public function output()
	{
		ob_clean();
		ob_start();

		http_response_header($this->_iHttpCode);
		@header('Content-Type: application/json;charset=UTF-8');

		//callback header
		printf('try{%s(', $this->_sCallback);

		if ($this->_bAllowGet == false AND Application::GetRequest()->getMethod() != 'POST') {
			echo json_encode(array('status' => false, 'message' => 'post_method_only'));
		} else {
			echo json_encode($this->_mData);
		}

		echo ');}catch(e){}';

		ob_end_flush();
	}
}