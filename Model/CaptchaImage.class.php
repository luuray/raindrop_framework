<?php
/**
 * DTeacher
 *
 *
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: www.rainhan.net/?proj=DTeacher
 */

namespace Raindrop\Model;

class CaptchaImage
{
	protected $_sCaptcha;
	protected $_sImageStream;

	public function __construct($sCaptcha, $sImageStream)
	{
		$this->_sCaptcha = $sCaptcha;
		$this->_sImageStream= $sImageStream;
	}

	public function __toString()
	{
		return $this->_sImageStream;
	}

	public function getImage()
	{
		return $this->_sImageStream;
	}

	public function getCaptcha()
	{
		return $this->_sCaptcha;
	}
}