<?php
/**
 * Raindrop Framework for PHP
 *
 * Encryptor
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2015, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\Component;


use Raindrop\Exceptions\InvalidArgumentException;

class Encryptor
{
	/**
	 * Encrypt String
	 *
	 * @param $sKey
	 * @param $sSource
	 * @return bool|string
	 * @throws InvalidArgumentException
	 */
	public static function EncryptString($sKey, $sSource)
	{
		//validate key length
		if (strlen($sKey) > mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)) throw new InvalidArgumentException('Key');

		$sIv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));

		$sEnc = mcrypt_encrypt(
			MCRYPT_RIJNDAEL_256,
			$sKey, $sSource,
			MCRYPT_MODE_CBC,
			$sIv);

		return $sEnc != false ? base64_encode(serialize([$sEnc, $sIv])) : false;
	}

	/**
	 * Decrypt String
	 *
	 * @param $sKey
	 * @param $sSource
	 * @return bool|string
	 * @throws InvalidArgumentException
	 */
	public static function DecryptString($sKey, $sSource)
	{
		//validate key length
		if (strlen($sKey) > mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)) throw new InvalidArgumentException('Key');

		//decode source
		$sSource = base64_decode($sSource);
		if ($sSource != false) {
			$sSource = @unserialize($sSource);
			if ($sSource == false OR count($sSource) != 2) throw new InvalidArgumentException('Source');
			list($sData, $sIv) = $sSource;

			$sDec = mcrypt_decrypt(
				MCRYPT_RIJNDAEL_256,
				$sKey, $sData,
				MCRYPT_MODE_CBC,
				$sIv);

			return $sDec == false ? false : rtrim($sDec, "\0");
		}

		return false;
	}
}