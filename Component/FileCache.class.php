<?php
/**
 * Raindrop Framework for PHP
 *
 * Cache in File
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component;

use Raindrop\Configuration;
use Raindrop\Exceptions\InvalidArgumentException;
use Raindrop\Interfaces\ICache;


class FileCache implements ICache
{
	protected $_sSavePath = null;
	protected $_sName;

	/**
	 * Construct Cache Adapter
	 *
	 * @param Configuration $oConfig
	 * @param string $sName Adapter Identify Name
	 *
	 * @throws InvalidArgumentException
	 * @internal param array $oConfig Adapter Params
	 */
	public function __construct(Configuration $oConfig, $sName)
	{
		if (empty($sName)) {
			throw new InvalidArgumentException('name');
		}

		$this->_sName = preg_replace('/[^0-9a-z\-_]/i', '_', $sName);

		if (!empty($oConfig->SavePath)) {
			$sDir = $oConfig->SavePath . DIRECTORY_SEPARATOR . $sName;

			if (is_dir($sDir)) {
				if (is_readable($sDir) && is_writable($sDir)) {
					$this->_sSavePath = $sDir;

					return true;
				}
			} else {
				if (mkdir($sDir, 0755, true)) {
					@file_put_contents(
						$sDir . DIRECTORY_SEPARATOR . 'index.php',
						'<?php @header("HTTP/1.1 404 Not Found");die("Not Found"); ?>');
					$this->_sSavePath = $sDir;

					return true;
				}
			}
		}

		throw new InvalidArgumentException('config');
	}

	/**
	 * Get a Item
	 *
	 * @param string $sName Item Name
	 * @return mixed
	 */
	public function get($sName)
	{
		$sFileName = 'c_';
		$sFileName .= preg_replace('/[^a-z0-9-\_]/i', '_', $sName);
		$sFileName .= '.php';

		if (!is_readable($this->_sSavePath . DIRECTORY_SEPARATOR . $sFileName)) {
			return false;
		}

		$sFileContent = @file_get_contents($this->_sSavePath . DIRECTORY_SEPARATOR . $sFileName, false, null, 60);
		if ($sFileContent === false) {
			return false;
		} else {
			return @unserialize($sFileContent);
		}
	}

	/**
	 * Delete a Item
	 *
	 * @param string $sName Item Name
	 * @return mixed
	 */
	public function del($sName)
	{
		$sFileName = 'c_';
		$sFileName .= preg_replace('/[^a-z0-9-\_]/i', '_', $sName);
		$sFileName .= '.php';

		if (file_exists($this->_sSavePath . DIRECTORY_SEPARATOR . $sFileName)) {
			return unlink($this->_sSavePath . DIRECTORY_SEPARATOR . $sFileName);
		}

		return false;
	}

	/**
	 * Set a Value to Cache
	 *
	 * @param string $sName Item Name
	 * @param mixed $mValue Item
	 * @param int $iLifetime Lifetime
	 * @return mixed
	 */
	public function set($sName, $mValue, $iLifetime = 0)
	{
		$sName     = strtolower($sName);
		$sFileName = 'c_';
		$sFileName .= preg_replace('/[^a-z0-9-\_]/i', '_', $sName);
		$sFileName .= '.php';

		$sContent = serialize($mValue);

		return @file_put_contents(
			$this->_sSavePath . DIRECTORY_SEPARATOR . $sFileName,
			'<?php @header("HTTP/1.1 404 Not Found");die("Not Found"); ?>' . $sContent);
	}

	/**
	 * Delete All
	 *
	 * @return bool
	 */
	public function flush()
	{
		$aFiles = glob($this->_sSavePath . DIRECTORY_SEPARATOR . '*.php');
		foreach ($aFiles AS $_v) {
			if (str_beginwith(pathinfo($_v, PATHINFO_FILENAME), 'c_')) {
				@unlink($_v);
			}
		}

		return true;
	}

} 