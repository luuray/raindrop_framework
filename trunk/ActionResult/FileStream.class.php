<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result for File Download Stream
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
namespace Raindrop\ActionResult;

use Raindrop\ActionResult;
use Raindrop\Exceptions\FileNotFoundException;
use Raindrop\Exceptions\InvalidArgumentException;
use Raindrop\Exceptions\NotImplementedException;

class FileStream extends ActionResult
{
	protected $_sFileName = null;

	/**
	 * Create a ActionResult Object
	 *
	 * @param string $sFileName File name to download
	 * @throws InvalidArgumentException
	 */
	public function __construct($sFileName = null)
	{
		if (empty($sFileName)) {
			throw new InvalidArgumentException('filename');
		}
		if(!is_readable(SysRoot . '/' . $sFileName) AND !is_file(SysRoot.'/'.$sFileName)){
			throw new FileNotFoundException($sFileName);
		}

		$this->_sFileName = SysRoot . '/' . $sFileName;
	}

	/**
	 * Output Result
	 *
	 * @return mixed
	 */
	public function Output()
	{
		ob_end_clean();

		header('Content-Description: File Transfer');
		header('Content-Type: ' . (function_exists('finfo_file')?finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->_sFileName) : 'application/octet-stream'));
		header('Content-Disposition: attachment; filename='.basename($this->_sFileName));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($this->_sFileName));
		readfile($this->_sFileName);

		exit;
	}

	public function toString()
	{
		throw new NotImplementedException();
	}
}