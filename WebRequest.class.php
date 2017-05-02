<?php
/**
 * Raindrop Framework for PHP
 *
 * Web Request
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop;

use Raindrop\Exceptions\RuntimeException;
use Raindrop\Model\UploadFile;

class WebRequest extends Request
{
	protected $_aUploadFiles = null;

	private $_aUploadErrors = [
		UPLOAD_ERR_OK         => 'success',
		UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
		UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
		UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded',
		UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
		UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
		UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
		UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload'
	];

	protected function _initialize()
	{
		//$this->_aHeader = array_key_case($_SERVER, CASE_LOWER);
		foreach ($_SERVER AS $_k => $_v) {
			if (str_beginwith($_k, 'HTTP_')) {
				$this->_aHeader[strtolower(substr($_k, 4))] = $_v;
			}
		}
		$this->_aQuery = array_key_case($_GET, CASE_LOWER);

		//empty post, try json decode raw post
		if (empty($_POST)) {
			$mResult = json_decode($this->getRawPost(), true);
			if ($mResult != false) {
				$this->_aData   = array_key_case($mResult, CASE_LOWER);
				$this->_bIsAjax = true;
			}
		} else {
			$this->_aData = array_key_case($_POST, CASE_LOWER);
		}

		if (!empty($_FILES)) {
			$this->_aUploadFiles = array_key_case($_FILES, CASE_LOWER);
		}
	}

	public function getMethod()
	{
		if ($this->_sMethod !== null) {
			return $this->_sMethod;
		}

		if (isset($_SERVER['REQUEST_METHOD'])) {
			$this->_sMethod = $_SERVER['REQUEST_METHOD'];
		} else {
			$this->_sMethod = 'UNDEFINED';
		}

		return $this->_sMethod;
	}

	public function getType()
	{
		if ($this->_bIsAjax) {
			return 'Json';
		} else {
			return $this->_sType;
		}
	}

	public function getRawPost()
	{
		return file_get_contents('php://input');
	}

	/**
	 * @param $sKey
	 *
	 * @return array|bool|UploadFile
	 * @throws RuntimeException
	 */
	public function getFile($sKey)
	{
		$sKey = strtolower($sKey);

		if ($this->_aUploadFiles == null OR !array_key_exists($sKey, $this->_aUploadFiles)) {
			return false;
		}

		if (!is_array($this->_aUploadFiles[$sKey]['tmp_name'])) {

			if ($this->_aUploadFiles[$sKey]['error'] !== UPLOAD_ERR_OK) {
				throw new RuntimeException(
					"[{$sKey}]" . $this->_aUploadErrors[$this->_aUploadFiles[$sKey]['error']],
					$this->_aUploadFiles[$sKey]['error']);
			} else if (!is_uploaded_file($this->_aUploadFiles[$sKey]['tmp_name'])) {
				throw new RuntimeException("[{$sKey}]" . 'File is not uploaded', -1);
			} else {
				return new UploadFile(
					$this->_aUploadFiles[$sKey]['name'],
					$this->_aUploadFiles[$sKey]['tmp_name'],
					$this->_aUploadFiles[$sKey]['type'],
					$this->_aUploadFiles[$sKey]['size']);
			}
		} else {
			$aResult = [];
			foreach ($this->_aUploadFiles[$sKey]['tmp_name'] AS $_k => $_v) {
				if ($this->_aUploadFiles[$sKey]['error'][$_k] != UPLOAD_ERR_OK) {
					throw new RuntimeException(
						"[{$sKey}/{$_k}]" . $this->_aUploadErrors[$this->_aUploadFiles[$sKey]['error'][$_k]],
						$this->_aUploadFiles[$sKey]['error'][$_k]);
				} else if (!is_uploaded_file($this->_aUploadFiles[$sKey]['tmp_name'][$_k])) {
					throw new RuntimeException("[{$sKey}/{$_k}]" . 'File is not uploaded', -1);
				} else {
					$aResult[] = new UploadFile(
						$this->_aUploadFiles[$sKey]['name'][$_k],
						$this->_aUploadFiles[$sKey]['tmp_name'][$_k],
						$this->_aUploadFiles[$sKey]['type'][$_k],
						$this->_aUploadFiles[$sKey]['size'][$_k]);
				}
			}

			return $aResult;
		}
	}

	public function getRemoteAddress()
	{
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			return $_SERVER['HTTP_CLIENT_IP'];
		else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if (isset($_SERVER['HTTP_X_FORWARDED']))
			return $_SERVER['HTTP_X_FORWARDED'];
		else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
			return $_SERVER['HTTP_FORWARDED_FOR'];
		else if (isset($_SERVER['HTTP_FORWARDED']))
			return $_SERVER['HTTP_FORWARDED'];
		else if (isset($_SERVER['REMOTE_ADDR']))
			return $_SERVER['REMOTE_ADDR'];
		else
			return 'UNKNOWN';
	}
}