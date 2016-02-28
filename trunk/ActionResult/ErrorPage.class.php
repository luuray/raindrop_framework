<?php
/**
 * Raindrop Framework for PHP
 *
 * Error Page View
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2014-2015, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\ActionResult;

use Raindrop\ActionResult;
use Raindrop\Exceptions\FatalErrorException;
use Raindrop\Exceptions\NotImplementedException;
use Raindrop\Loader;

///TODO Output Error Page
class ErrorPage extends View
{
	public function __construct($iStatusCode = 404, $mData = null)
	{
		if (settype($iCode, 'int') === false OR !in_array($iStatusCode, [301, 400, 403, 404, 500])) {
			throw new FatalErrorException;
		}

		http_response_code($iStatusCode);
		$this->_oViewData = ViewData::GetInstance()->mergeReplace($mData);

		$sPage = AppDir . "/view/shared/{$iStatusCode}.phtml";

		if (Loader::CheckLoadable($sPage)) {
			$this->_sBodyView = $sPage;
			$this->_sLayout   = AppDir . '/view/shared/layout.phtml';
		} else {
			$this->_sBodyView = CorePath . "/ActionResult/Pages/{$iStatusCode}.phtml";
			$this->_sLayout   = null;
		}
	}

	public function toString()
	{
		throw new NotImplementedException;
	}
}