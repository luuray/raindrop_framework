<?php
/**
 * Raindrop Framework for PHP
 *
 * SimHash Generator
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: www.rainhan.net/?proj=Raindrop
 */

namespace Raindrop\Component;


use Raindrop\Exceptions\InvalidArgumentException;

class SimHash
{
	private static $_aSearch = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
	private static $_aReplace = array('0000', '0001', '0010', '0011', '0100', '0101', '0110', '0111', '1000', '1001', '1010', '1011', '1100', '1101', '1110', '1111');


	public static function Hash($aSource, $iLength = 64)
	{
		$aHashBox = array_fill(0, $iLength, 0);

		if (!is_array($aSource)) {
			throw new InvalidArgumentException('source');
		}

		$aSource = is_int(key($aSource)) ? array_count_values($aSource) : $aSource;

		foreach ($aSource AS $_item => $_count) {
			$sHash = hash('md5', $_item);
			$sHash = str_replace(self::$_aSearch, self::$_aReplace, $sHash);
			$sHash = substr($sHash, 0, $iLength);
			$sHash = str_pad($sHash, $iLength, '0', STR_PAD_LEFT);

			for ($i = 0; $i < $iLength; $i++) {
				$aHashBox[$i] += ($sHash[$i] == '1') ? $_count : -$_count;
			}
		}

		$sSimHah = '';
		foreach ($aHashBox AS $_item) {
			$sSimHah .= $_item > 0 ? '1' : '0';
		}

		return $sSimHah;
	}
}