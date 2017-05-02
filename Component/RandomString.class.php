<?php
/**
 * Raindrop Framework for PHP
 *
 * Random String Generator
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component;

class RandomString
{
	protected static $_aAllChars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
	protected static $_aCassLess = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	protected static $_aUnconfusedChars = array('2', '3', '4', '5', '6', '7', '8', '9',
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	protected static $_aNumeric = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');

	protected function __construct()
	{
	}

	public static function GetString($iLength = 8, $bCaseSensitive = true)
	{
		if ($iLength <= 0) return null;

		$sReturn = '';
		$pTable  = null;

		$aCodeTable = $bCaseSensitive ? self::$_aAllChars : self::$_aCassLess;
		$iCount     = count($aCodeTable) - 1;

		for ($i = 0; $i < $iLength; $i++) {
			$sReturn .= $aCodeTable[mt_rand(0, $iCount)];
		}

		return $sReturn;
	}

	public static function GetUnconfused($iLength = 8)
	{
		if ($iLength <= 0) return null;

		$sReturn = '';
		$iCount  = count(self::$_aUnconfusedChars) - 1;

		for ($i = 0; $i < $iLength; $i++) {
			$sReturn .= self::$_aUnconfusedChars[mt_rand(0, $iCount)];
		}

		return $sReturn;
	}

	public static function GetNumeric($iLength=8)
	{
		if ($iLength <= 0) return null;

		$sReturn = '';
		$iCount  = count(self::$_aNumeric) - 1;

		for ($i = 0; $i < $iLength; $i++) {
			$sReturn .= self::$_aNumeric[mt_rand(0, $iCount)];
		}

		return $sReturn;
	}
}