<?php
/**
 * Raindrop Framework for PHP
 *
 * Random String Generator
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2014, Rainhan System
 * Site: www.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */
namespace Raindrop\Component;

use Raindrop\InvalidArgumentException;

class RandomString
{
	const CHAR_ALL = 0;
	const CHAR_UNCONFUSED = 1;

	public $aAllChars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
		'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd',
		'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
	public $aUnconfusedChars = array('2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
		'J', 'K', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

	protected $_iDefaultLen = 0;
	protected $_iMode = 0;

	public function __construct($iMode = self::CHAR_ALL, $iLength = 8)
	{
		if (!in_array($iMode, array(0, 1))) {
			throw new InvalidArgumentException('string_mode');
		}
		if (!settype($iLength, 'int') OR $iLength <= 0) {
			throw new InvalidArgumentException('string_length');
		}

		$this->_iMode       = $iMode;
		$this->_iDefaultLen = $iLength;

		return $this;
	}

	public function GetString($iLength = null)
	{
		if ($iLength === null) {
			return $this->_generator($this->_iMode, $this->_iDefaultLen);
		} else if (!settype($iLength, 'int') OR $iLength <= 0) {
			throw new InvalidArgumentException('string_length');
		}

		return $this->_generator($this->_iMode, $iLength);
	}

	public function __toString()
	{
		return $this->_generator($this->_iMode, $this->_iDefaultLen);
	}

	protected function _generator($iMode, $iLength)
	{
		if ($iMode == 0) {
			$aChars = $this->aAllChars;
		} else if ($iMode == 1) {
			$aChars = $this->aUnconfusedChars;
		}

		$sResult     = '';
		$iCharsCount = count($aChars) - 1;
		while ($iLength > 0) {
			$sResult .= $aChars[mt_rand(0, $iCharsCount)];
			$iLength--;
		}

		return $sResult;
	}
}