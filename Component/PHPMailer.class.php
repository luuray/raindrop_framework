<?php
/**
 * Raindrop Framework for PHP
 *
 * Sendmail Wrapper
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

class PHPMailer implements INotification
{
	protected $_oPHPMailer;
	protected $_sHandlerName;

	public function __construct($aConfig, $sHandlerName)
	{
		// TODO: Implement __construct() method.
		require_once __DIR__ . '/phpmailer/phpmailer/class.phpmailer.php';
		require_once __DIR__ . '/phpmailer/phpmailer/class.smtp.php';

		$this->_sHandlerName = $sHandlerName;

		$this->_oPHPMailer = new \PHPMailer(true);
		$this->_oPHPMailer->isSMTP();
		$this->_oPHPMailer->isHTML();
		$this->_oPHPMailer->CharSet  = 'UTF-8';
		$this->_oPHPMailer->From     = $aConfig['Sender'];
		$this->_oPHPMailer->FromName = $aConfig['SenderName'];
		$this->_oPHPMailer->Host     = $aConfig['SMTP'];
		$this->_oPHPMailer->SMTPAuth = $aConfig['Authority'];
		$this->_oPHPMailer->Username = $aConfig['Sender'];
		$this->_oPHPMailer->Password = $aConfig['Password'];
		$this->_oPHPMailer->Port     = $aConfig['Port'];
	}

	/**
	 * @param $mReceiver
	 * @param $sContent
	 * @param string $sTitle
	 * @param null $aAttr
	 * @return bool
	 * @throws FatalErrorException
	 * @throws InvalidArgumentException
	 */
	public function send($mReceiver, $sContent, $sTitle = 'no subject', $aAttr = null)
	{
		if (is_array($mReceiver)) {
			foreach ($mReceiver AS $_v) {
				if (filter_var($_v, FILTER_VALIDATE_EMAIL) == false) {
					throw new InvalidArgumentException('receiver');
				}
			}
		} else {
			if (filter_var($mReceiver, FILTER_VALIDATE_EMAIL) == false) {
				throw new InvalidArgumentException('receiver');
			}
		}
		$this->_oPHPMailer->Body    = $sContent;
		$this->_oPHPMailer->Subject = $sTitle;
		if (is_array($mReceiver)) {
			foreach ($mReceiver AS $_rec) {
				$this->_oPHPMailer->addAddress($_rec);
			}
		} else {
			$this->_oPHPMailer->addAddress($mReceiver);
		}

		try {
			if ($this->_oPHPMailer->send() == false) {
				throw new RuntimeException($this->_oPHPMailer->ErrorInfo);
			}
			//cleanup
			$this->_oPHPMailer->clearAddresses();
			$this->_oPHPMailer->clearAttachments();

			return true;

		} catch (\phpmailerException $ex) {
			//cleanup
			$this->_oPHPMailer->clearAddresses();
			$this->_oPHPMailer->clearAttachments();

			throw new RuntimeException($ex->getMessage(), $ex->getCode(), $ex);
		}
	}
}