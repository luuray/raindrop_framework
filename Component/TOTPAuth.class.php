<?php
/**
 * Raindrop Framework for PHP
 *
 * TOTP Authentication
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component;


class TOTPAuth
{
	protected $_iCodeLength = 6;

	protected $_aCodeTable = array(
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
		'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
		'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
		'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
		'=');                                   // 32

	/**
	 * Get Secret
	 *
	 * @param int $iLength
	 * @return string
	 */
	public function getSecret($iLength = 16)
	{
		$sSecret    = '';
		$aCodeTable = $this->_aCodeTable;
		unset($aCodeTable[32]);
		$iCount = count($aCodeTable);


		for ($i = 0; $i < $iLength; $i++) {
			$sSecret .= $aCodeTable[mt_rand(0, $iCount)];
		}

		return $sSecret;
	}

	/**
	 * Get Code
	 *
	 * @param $sSecret
	 * @param null $iTimeSlice
	 * @return string
	 */
	public function getCode($sSecret, $iTimeSlice = null)
	{
		$iTimeSlice = $iTimeSlice === null ? floor(time() / 30) : $iTimeSlice;

		$sSecretKey = $this->_base32decode($sSecret);
		$sTime      = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $iTimeSlice);
		$sHm        = hash_hmac('SHA1', $sTime, $sSecretKey, true);
		$iOffset    = ord(substr($sHm, -1)) & 0x0F;
		$sHashPart  = substr($sHm, $iOffset, 4);

		$sValue = unpack('N', $sHashPart);
		$sValue = $sValue[1];
		$sValue = $sValue & 0x7FFFFFFF;

		$iModulo = pow(10, $this->_iCodeLength);

		return str_pad($sValue % $iModulo, $this->_iCodeLength, '0', STR_PAD_LEFT);
	}

	/**
	 * Verify Code
	 *
	 * @param $sSecret
	 * @param $sCode
	 * @param int $iDiscrepancy
	 * @param null $iCurrentTimeSlice
	 * @return bool
	 */
	public function verifyCode($sSecret, $sCode, $iDiscrepancy = 1, $iCurrentTimeSlice = null)
	{
		$iCurrentTimeSlice === null ? floor(time() / 30) : $iCurrentTimeSlice;

		for ($i = -$iDiscrepancy; $i < $iDiscrepancy; $i++) {
			return $this->getCode($sSecret, $iCurrentTimeSlice + $i) == $sCode;
		}

		return false;
	}

	/**
	 * Set Code Length
	 *
	 * @param $iLength
	 * @return $this
	 */
	public function setCodeLength($iLength)
	{
		$this->_iCodeLength=$iLength;

		return $this;
	}

	/**
	 * Base32 Encode
	 *
	 * @param $sSecret
	 * @param bool $bPadding
	 * @return string
	 */
	protected function _base32encode($sSecret, $bPadding = true)
	{
		if (empty($sSecret)) return '';

		$aSecret = str_split($sSecret);
		$sBinStr = '';

		for ($i = 0; $i < count($aSecret); $i++) {
			$sBinStr .= str_pad(base_convert(ord($aSecret[$i]), 10, 2), 8, '0', STR_PAD_LEFT);
		}

		$a5BitBin = str_split($sBinStr, 5);
		$sBase32  = '';

		for ($i = 0; $i < count($a5BitBin); $i++) {
			$sBase32 .= $this->_aCodeTable[base_convert(str_pad($a5BitBin[$i], 5, '0'), 2, 10)];
		}

		if ($bPadding && ($x = strlen($sBinStr) % 40) != 0) {
			if ($x == 8) $sBase32 .= str_repeat($this->_aCodeTable[32], 6);
			elseif ($x == 16) $sBase32 .= str_repeat($this->_aCodeTable[32], 4);
			elseif ($x == 24) $sBase32 .= str_repeat($this->_aCodeTable[32], 3);
			elseif ($x == 32) $sBase32 .= $this->_aCodeTable[32];
		}

		return $sBase32;
	}

	/**
	 *
	 *
	 * @param $sSecret
	 * @return bool|null|string
	 */
	protected function _base32decode($sSecret)
	{
		if (empty($sSecret)) {
			return null;
		}

		$aFlipped       = array_flip($this->_aCodeTable);
		$iPaddingCount  = substr_count($sSecret, $this->_aCodeTable[32]);
		$aAllowedValues = array(6, 4, 3, 1, 0);
		if (!in_array($iPaddingCount, $aAllowedValues)) return false;

		for ($i = 0; $i < 4; $i++) {
			if ($iPaddingCount == $aAllowedValues[$i] && substr($sSecret, -($aAllowedValues[$i])) != str_repeat($this->_aCodeTable[32], $aAllowedValues[$i])) return false;
		}

		$sSecret = str_replace('=', '', $sSecret);
		$sSecret = str_split($sSecret);

		$sBinStr = null;
		for ($i = 0; $i < count($sSecret); $i += 8) {
			$x = null;
			if (!in_array($sSecret[$i], $this->_aCodeTable)) return false;
			for ($j = 0; $j < 8; $j++) {
				$x .= str_pad(base_convert($aFlipped[$sSecret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
			}
			$aHex = str_split($x, 8);
			for ($k = 0; $k < count($aHex); $k++) {
				$sBinStr .= (($z = chr(base_convert($aHex[$k], 2, 10))) || ord($z) == 48) ? $z : '';
			}
		}

		return $sBinStr;
	}
}