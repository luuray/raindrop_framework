<?php
/**
 * Raindrop Framework for PHP
 *
 * Upload File
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Model;


use Raindrop\Exceptions\RuntimeException;
use Raindrop\Logger;

class UploadFile
{
	protected $_sName;
	protected $_sTempName;
	protected $_sMimeType;
	protected $_iSize;

	public function __construct($sName, $sTempName, $sMimeType, $iSize)
	{
		$this->_sName     = $sName;
		$this->_sTempName = $sTempName;
		$this->_sMimeType = $sMimeType;
		$this->_iSize     = $iSize;

	}

	public function getExtension()
	{
		return strtolower(pathinfo($this->_sName, PATHINFO_EXTENSION));
	}

	public function move($sTarget)
	{
		$sPath = pathinfo($sTarget, PATHINFO_DIRNAME);
		if (!file_exists($sPath)) {
			mkdir($sPath, 0755, true);
		}

		Logger::Debug('move_uploaded_file:'.$sTarget);

		$bResult = @move_uploaded_file($this->_sTempName, $sTarget);

		if($bResult == false){
			$aErr = error_get_last();
			throw new RuntimeException($aErr['message']);
		}

		return $bResult;
	}
}