<?php
/**
 * Raindrop Framework for PHP
 *
 * SendMail
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop;


use Raindrop\Component\PHPMailer;
use Raindrop\Exceptions\FatalErrorException;

class SendMail
{
	protected static $_oInstance = null;

	protected $_aHandlers = array();

	public static function GetInstance()
	{
		if (self::$_oInstance == null) {
			self::$_oInstance = new self();
		}

		return self::$_oInstance;
	}

	public static function Send($mReceiver, $sContent, $sTitle, $sHandler = 'default')
	{
		return self::GetInstance()->_getHandler($sHandler)->Send($mReceiver, $sContent, $sTitle);
	}

	protected function __construct()
	{
		$aConfig = Configuration::Get('Mail');

		if ($aConfig !== null) {
			foreach ($aConfig AS $_name => $_cfg) {
				$_name = strtolower($_name);

				$oRefComp                 = new PHPMailer($_cfg['Params'], $_name);
				$this->_aHandlers[$_name] = $oRefComp;
			}
		}
	}

	protected function _getHandler($sName)
	{
		$sName = trim(strtolower($sName));
		if (array_key_exists($sName, $this->_aHandlers)) {
			return $this->_aHandlers[$sName];
		} else {
			throw new FatalErrorException('undefined_mail_handler');
		}
	}
} 