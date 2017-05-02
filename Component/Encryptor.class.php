<?php
/**
 * Raindrop Framework for PHP
 *
 * Encryptor
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component;


use Raindrop\Exceptions\InvalidArgumentException;
use Raindrop\Exceptions\RuntimeException;

class Encryptor
{
	private static $_sEncryptMethod = 'aes-256-cfb';

	public static function ValidateKey($sKey)
	{
		return mb_strlen($sKey, '8bit') === 32;
	}

	/**
	 * Encrypt String
	 *
	 * @param $sKey
	 * @param $sSource
	 *
	 * @return bool|string
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public static function EncryptString($sKey, $sSource)
	{
		if (mb_strlen($sKey, '8bit') !== 32) {
			throw new InvalidArgumentException('invalid_key');
		}
		$sIV = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$_sEncryptMethod));

		$sEncrypted = openssl_encrypt($sSource, self::$_sEncryptMethod, $sKey, OPENSSL_RAW_DATA, $sIV);

		if ($sEncrypted == false) {
			throw new RuntimeException('encrypt_failed');
		}

		return base64_encode($sIV . $sEncrypted);
	}

	/**
	 * Decrypt String
	 *
	 * @param $sKey
	 * @param $sSource
	 *
	 * @return bool|string
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public static function DecryptString($sKey, $sSource)
	{
		if (mb_strlen($sKey, '8bit') !== 32) {
			throw new InvalidArgumentException('invalid_key');
		}

		$sSource = base64_decode($sSource);
		if ($sSource == false) {
			throw new InvalidArgumentException('invalid_source');
		}

		$iIVSize = openssl_cipher_iv_length(self::$_sEncryptMethod);

		$sIV     = mb_substr($sSource, 0, $iIVSize, '8bit');
		$sSource = mb_substr($sSource, $iIVSize, null, '8bit');

		$sResult = openssl_decrypt($sSource, self::$_sEncryptMethod, $sKey, OPENSSL_RAW_DATA, $sIV);

		if ($sResult === false) {
			throw new RuntimeException('encrypt_failed');
		}

		return $sResult;
	}
}