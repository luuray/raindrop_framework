<?php
/**
 * Raindrop Framework for PHP
 *
 * View of ErrorPage
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\ActionResult;

use Raindrop\Exceptions\FatalErrorException;
use Raindrop\Exceptions\NotImplementedException;
use Raindrop\Loader;

///TODO Output Error Page
class ErrorPage extends View
{
	public function __construct($iStatusCode = 404, $sMessage = null, $sCallStack=null)
	{
		if (settype($iCode, 'int') === false OR !in_array($iStatusCode, [301, 400, 403, 404, 500])) {
			throw new FatalErrorException;
		}

		http_response_code($iStatusCode);
		$this->_oViewData = ViewData::GetInstance();
		$this->_oViewData->Message = $sMessage;
		$this->_oViewData->CallStack = $sCallStack;

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