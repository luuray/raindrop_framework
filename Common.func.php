<?php
/**
 * Raindrop Framework for PHP
 *
 * Common Functions
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

use Raindrop\Exceptions\InvalidArgumentException;

if (!defined('SysRoot')) {
	@header('HTTP/1.1 404 Not Found');
	die('Access Forbidden');
}

#region String Functions
/**
 * Format Digital "Size" to String
 *
 * @param int $lByteSize Digital
 *
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
 * Detect subject string is null/space/tab/newline/etc white space
 *
 * @param mixed $sSubject Detect target
 *
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

/**
 * String is begin with subjected string
 *
 * @param string $sTarget Source string
 * @param string|array $mFind String to find
 * @param bool $bMatchCase Match case
 *
 * @return bool
 */
function str_beginwith($sTarget, $mFind, $bMatchCase = false)
{
	$sTarget = $bMatchCase == true ? $bMatchCase : strtolower($sTarget);
	if (is_array($mFind)) {
		foreach ($mFind AS $_item) {
			if (settype($_item, 'string') AND strlen($_item) <= strlen($sTarget)) {
				if ($bMatchCase) {
					if (substr($sTarget, 0, strlen($_item) == $_item)) return true;
				} else {
					if (substr($sTarget, 0, strlen($_item)) == strtolower($_item)) return true;
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
 *
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
 *
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
 *
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

/**
 * Find first existed string in arguments
 *
 * @return string
 */
function str_first_exists()
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
 * Combine strings in argument with first argument as glue
 *
 * @return mixed|string
 */
function str_combine()
{
	if (func_num_args() == 1) {
		return func_get_arg(0);
	}
	$aItem = func_get_args();

	$sSplit = array_shift($aItem);
	foreach ($aItem AS $_k => $_v) {
		if (empty($_v) AND !is_int($_v)) {
			unset($aItem[$_k]);
		}
	}

	return implode($sSplit, $aItem);
}

/**
 * @param $sSource
 *
 * @return mixed|string
 */
function str_uc2underscore($sSource)
{
	if (!is_string($sSource)) {
		return strval($sSource);
	}

	$sSource = preg_replace_callback('/[A-Z]{1}/', function ($matches) {
		return '_' . strtolower($matches[0]);
	}, strtolower(substr($sSource, 0, 1)) . substr($sSource, 1));

	return $sSource;
}

/**
 * Replace Full-width char to Half-width
 *
 * @param int|string $sSubject
 *
 * @return string
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

#endregion

#region Numeric Functions
/**
 * Is unsigned int
 *
 * @param $mSubject
 *
 * @return bool
 */
function is_unsigned_int($mSubject)
{
	return settype($mSubject, 'int') AND $mSubject >= 0;
}

/**
 * Parse subject to Integer
 *
 * @param $mSubject
 *
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

/**
 * Convert String or Mixed-Array-String to Int Array with pattern split
 *
 * @param string|array $mSubject
 * @param string $sPattern
 * @param bool|false $bUnique
 *
 * @return array
 */
function to_int_array($mSubject, $sPattern = ',|', $bUnique = false)
{
	if (is_array($mSubject)) {
		$mSubject = implode(str_first($sPattern), $mSubject);
	}

	$aResult = preg_split('/[' . preg_quote($sPattern, '/') . ']/', $mSubject);
	foreach ($aResult AS $_k => &$_v) {
		if (str_nullorwhitespace($_v) OR !settype($_v, 'int')) {
			unset($aResult[$_k]);
		}
	}

	return $bUnique ? array_unique($aResult) : $aResult;
}

#endregion

#region Array Functions
define('CASE_LOWER_UNDERSCORE', 2);
/**
 * Switch array's index case
 *
 * @param mixed $aSubject Array to switch
 * @param int $iCase Key Case
 *
 * @return array Array after switch
 */
function array_key_case($aSubject, $iCase = CASE_LOWER)
{
	if (is_array($aSubject)) {
		$aResult = null;

		if ($iCase == CASE_LOWER OR $iCase == CASE_UPPER) {
			$aResult = array_change_key_case($aSubject, $iCase);
			foreach ($aSubject AS $_k => $_v) {
				if (is_array($_v)) {
					$aResult[$_k] = array_key_case($_v, $iCase);
				}
			}
		} else if ($iCase == CASE_LOWER_UNDERSCORE) {
			foreach ($aSubject AS $_k => $_v) {
				if (is_array($_v)) {
					$_v = array_key_case($_v, $iCase);
				}

				$aResult[str_uc2underscore($_k)] = $_v;
			}
		} else {
			return null;
		}

		return $aResult;
	} else {
		return $aSubject;
	}
}

/**
 * Search array with key
 *
 * @param array $aSource Target array
 * @param string|array $mKey Key to search, If array given then search by this level, Can use '\' as a level delimiter
 *
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
 * Merge arrays, replace item when array's key is same
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

#endregion

#region Object Functions
/**
 * @param $oSubject
 *
 * @return array
 * @throws InvalidArgumentException
 */
function object_get_public_properties($oSubject)
{
	if (is_object($oSubject)) {
		return get_object_vars($oSubject);
	}
	throw new InvalidArgumentException();
}

/**
 * @param bool $bFilename
 *
 * @return array|null|string
 */
function get_caller($bFilename = false)
{
	$aBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

	if (isset($aBacktrace[2])) {
		$pOffset = $aBacktrace[2];
		if ($bFilename == false) {
			return (isset($pOffset['class']) ? $pOffset['class'] : '') . (isset($pOffset['type']) ? $pOffset['type'] : '') . $pOffset['function'];
		} else {
			return [
				(isset($pOffset['class']) ? $pOffset['class'] : '') . (isset($pOffset['type']) ? $pOffset['type'] : '') . $pOffset['function'],
				((isset($pOffset['file']) ? ($pOffset['file'] . ',' . (isset($pOffset['line']) ? $pOffset['line'] : null)) : null))
			];
		}
	}

	return null;
}

#endregion

#region Datetime Functions
/**
 * Get recent time in format
 *
 * @param string $sFormat
 *
 * @return bool|string
 */
function datetime_now($sFormat = 'Y-m-d H:i:s')
{
	return date($sFormat);
}

/**
 * Parse subject to formatted date
 *
 * @param $mSubject
 * @param string $sFormat
 *
 * @return bool|string
 */
function parse_date($mSubject, $sFormat = 'Y-m-d')
{
	return parse_datetime($mSubject, $sFormat);
}

/**
 * Pares subject to formatted datetime
 *
 * @param mixed $mSubject
 * @param string $sFormat
 *
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
 *
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

#endregion

#region Hash Functions
/**
 * Calculate the md5 hash of a string in 16 char
 *
 * @param $sSubject
 *
 * @return bool|string
 */
function md5_short($sSubject)
{
	if (settype($sSubject, 'string') == false) {
		return false;
	}

	return substr(md5($sSubject), 8, 16);
}

#endregion

#region File System Functions
/**
 * Delete file and directory in recursive
 *
 * @param $sPath
 *
 * @return bool
 */
function del_recursive($sPath)
{
	if (is_dir($sPath)) {
		foreach (array_diff(scandir($sPath), ['.', '..']) AS $_item) {
			if (del_recursive($_item) == false) {
				return false;
			}
		}

		return true;
	} else if (is_file($sPath)) {
		return @unlink($sPath);
	}
}

#endregion

#serializer & deserializer
function bson2json($sBSON, $bAssoc = false)
{
	$aJson = json_decode($sBSON, true);

	array_walk_recursive($aJson, function (&$_v) {
		if (is_string($_v)) {
			$aMatch = [];
			if (preg_match('/^\/Date\(([0-9]{1,14})\)\/$/', $_v, $aMatch)) {
				$_v = intval((int)$aMatch[1] / 1000);
			}
		}
	});

	return $bAssoc == true ? $aJson : (object)$aJson;
}
#endregion