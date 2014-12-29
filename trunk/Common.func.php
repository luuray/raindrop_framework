<?php
/**
 * Raindrop Framework for PHP
 *
 * Common Functions
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
use Raindrop\ArgumentException;
use Raindrop\InvalidArgumentException;

if (!defined('SysRoot')) {
	@header('HTTP/1.1 404 Not Found');
	die('Access Forbidden');
}
/**
 * Format Digital "Size" to String
 *
 * @param int $lByteSize Digital
 * @return string Size in String
 */
function byte2string($lByteSize)
{
	$lByteSize = intval($lByteSize);

	if ($lByteSize < 0) {
		return 'out_of_size';
	} else {
		if ($lByteSize > 1024) {//K
			if ($lByteSize > 1048576) {//M
				if ($lByteSize > 1073741824) {//G
					return sprintf('%.2F GByte', (float)$lByteSize / 1073741824);
				} else {
					return sprintf('%.2F MByte', (float)$lByteSize / 1048576);
				}
			} else {
				return sprintf('%.2F KByte', (float)$lByteSize / 1024);
			}
		} else {
			return sprintf('%d Byte', $lByteSize);
		}
	}
}

/**
 * Switch array's index case
 *
 * @param mixed $aSubject Array to switch
 * @param int $bCase Index Case
 * @return array Array after switch
 */
function array_key_case($aSubject, $bCase = CASE_LOWER)
{
	if (is_array($aSubject)) {
		$aSubject = array_change_key_case($aSubject, $bCase);
		foreach ($aSubject AS $_k => $_v) {
			if (is_array($_v)) {
				$aSubject[$_k] = array_key_case($_v, $bCase);
			}
		}

		return $aSubject;
	} else {
		return $aSubject;
	}
}

/**
 * Search array with key
 *
 * @param array $aSource Target array
 * @param string|array $mKey Key to search, If array given then search by this level, Can use '\' as a level delimiter
 * @return mixed The value to the find key, False if not found
 */
function array_find($aSource, $mKey)
{
	if (strpos('\\', $mKey) === false) {
		$mKey = explode('\\', $mKey);
	}

	if (is_array($mKey)) {
		$pArrayPoint = &$aSource;

		do {
			$_key = current($mKey);

			if (is_array($pArrayPoint)) {
				if (array_key_exists($_key, $pArrayPoint)) {
					$pArrayPoint = &$pArrayPoint[$_key];
					continue;
				}
			}

			return false;
		} while (next($mKey));

		return $pArrayPoint;
	} else {
		if (is_array($aSource)) {
			return array_key_exists($mKey, $aSource) ? $aSource[$mKey] : false;
		} else {
			return false;
		}
	}
}

/**
 * Detect subject string is null/space/tab/newline/etc white space
 *
 * @param mixed $sSubject Detect target
 * @return bool
 */
function str_nullorwhitespace($sSubject)
{
	if (empty($sSubject)) {
		return true;
	}
	if (preg_match_all('/^\s*$/', $sSubject) == strlen($sSubject)) {
		return true;
	}

	return false;
}

function is_unsigned_int($mSubject)
{
	return settype($mSubject, 'int') AND $mSubject >= 0;
}

/**
 * Replace Full-width char to Half-width
 *
 * @param int|string $sSubject
 */
function number_to_half($sSubject)
{
	$aFullWidth = array(
		'1' => '１',
		'2' => '２',
		'3' => '３',
		'4' => '４',
		'5' => '５',
		'6' => '６',
		'7' => '７',
		'8' => '８',
		'9' => '９',
		'-' => '－',
		' ' => '　',
		'.' => '.');

	return str_replace(array_values($aFullWidth), array_keys($aFullWidth), $sSubject);
}

/**
 * String is begin with subjected string
 *
 * @param string $sTarget Source string
 * @param string|array $mFind String to find
 * @param bool $bMatchCase Match case
 * @return bool
 */
function str_beginwith($sTarget, $mFind, $bMatchCase = false)
{
	$sTarget = $bMatchCase == true ? $bMatchCase : strtolower($sTarget);
	if (is_array($mFind)) {
		foreach ($mFind AS $_item) {
			if (settype($_item, 'string') AND strlen($_item) <= strlen($sTarget)) {
				if ($bMatchCase) {
					return substr($sTarget, 0, strlen($_item) == $_item);
				} else {
					return substr($sTarget, 0, strlen($_item)) == strtolower($_item);
				}
			}
		}
	} else {
		if (settype($mFind, 'string') AND strlen($mFind) <= strlen($sTarget)) {
			if ($bMatchCase) {
				return substr($sTarget, 0, strlen($mFind)) == $mFind;
			} else {
				return substr($sTarget, 0, strlen($mFind)) == strtolower($mFind);
			}
		}
	}

	return false;
}

/**
 * Detect subject string is end with needle
 *
 * @param $sSubject
 * @param $sNeedle
 * @param $bMatchCase
 * @return bool
 * @throws InvalidArgumentException
 */
function str_endwith($sSubject, $sNeedle, $bMatchCase = false)
{
	//Params validation
	if (!settype($sSubject, 'string') || !settype($sNeedle, 'string')) {
		throw new InvalidArgumentException();
	}
	if (strlen($sSubject) < $sNeedle) {
		return false;
	}

	if ($bMatchCase == true) {
		return substr($sSubject, -1 * strlen($sNeedle) == $sNeedle);
	} else {
		return strtolower(substr($sSubject, -1 * strlen($sNeedle))) == strtolower($sNeedle);
	}
}

/**
 * Get the first char of string
 *
 * @param $sSubject Subject string
 * @return bool|string First char of string, false when subject is too short
 * @throws InvalidArgumentException
 */
function str_first($sSubject)
{
	if (!settype($sSubject, 'string')) {
		throw new InvalidArgumentException();
	}

	return strlen($sSubject) > 0 ? (string)$sSubject[0] : false;
}

/**
 * Get the last char of string
 *
 * @param $sSubject Subject string
 * @return bool|string Last char of string, false when subject is too short
 * @throws InvalidArgumentException
 */
function str_last($sSubject)
{
	if (!settype($sSubject, 'string')) {
		throw new InvalidArgumentException();
	}

	return strlen($sSubject) > 0 ? (string)$sSubject[strlen($sSubject) - 1] : false;
}

function str_find_first()
{
	$aArgs = func_get_args();
	if (empty($aArgs)) {
		return false;
	}

	foreach ($aArgs AS $_arg) {
		if (str_nullorwhitespace($_arg) == false) {
			return $_arg;
		}
	}
}

/**
 * Merge arrays, replace item when array's key is same
 *
 * @throws ArgumentException
 */
function array_merge_replace()
{
	$aArgs   = func_get_args();
	$aTarget = array_shift($aArgs);

	//two and more argument
	while (!empty($aArgs)) {
		$aSource = array_shift($aArgs);
		if (!empty($aSource) AND is_array($aSource)) {
			foreach ($aSource AS $_k => $_v) {
				//integer index
				if (is_int($_k)) {
					isset($aTarget[$_k]) ? $aTarget[] = $_v : $aTarget[$_k] = $_v;
				} //recursive merge
				else if (is_array($_v) AND isset($aTarget[$_k]) AND is_array($aTarget[$_k])) {
					$aTarget[$_k] = array_merge_replace($aTarget[$_k], $_v);
				} else {
					$aTarget[$_k] = $_v;
				}
			}
		}
	}

	return $aTarget;
}

function object_get_public_properties($oSubject)
{
	if (is_object($oSubject)) {
		return get_object_vars($oSubject);
	}
	throw new InvalidArgumentException();
}

function datetime_now($sFormat = 'Y-m-d H:i:s')
{
	return date($sFormat);
}

function parse_date($mSubject, $sFormat = 'Y-m-d')
{
	return parse_datetime($mSubject, $sFormat);
}

/**
 * Pares to date in format
 *
 * @param mixed $mSubject
 * @param string $sFormat
 * @return bool|string
 */
function parse_datetime($mSubject, $sFormat = 'Y-m-d H:i:s')
{
	if (is_int($mSubject) AND $mSubject > 0) {
		return date($sFormat, $mSubject);
	} else if (@strtotime($mSubject) !== false) {
		return date($sFormat, strtotime($mSubject));
	} else {
		return false;
	}
}

/**
 * Parse to UnixTimestamp
 *
 * @param mixed $mSubject
 * @return bool|int
 */
function parse_timestamp($mSubject)
{
	if (is_numeric($mSubject) AND settype($mSubject, 'int') AND $mSubject > 0) {
		return $mSubject;
	} else if (($iUT = @strtotime($mSubject)) !== false) {
		return $iUT;
	} else {
		return false;
	}
}

/**
 * Parse to Integer
 * @param $mSubject
 * @return bool|int
 */
function parse_int($mSubject)
{
	if (is_numeric($mSubject) AND settype($mSubject, 'int')) {
		return $mSubject;
	} else {
		return false;
	}
}

function md5_short($sSubject)
{
	if (settype($sSubject, 'string') == false) {
		return false;
	}

	return substr(md5($sSubject), 8, 16);
}