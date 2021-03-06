<?php
/**
 * Raindrop Framework for PHP
 *
 * MessageBase of WeChat Component
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Model;


use Raindrop\Exceptions\NotImplementedException;

/**
 * Class Message
 * @package Raindrop\Component\WeChat\Model
 *
 * @property string $FromUserName
 * @property string $ToUserName
 * @property string $Type
 * @property int $CreateTime
 * @property string MessageId
 */
abstract class Message
{
	protected $_aMeta = [];

	/**
	 * Message constructor.
	 *
	 * @param string $sFromUser
	 * @param string $sToUser
	 * @param int $iCreateTime
	 * @param int $iMsgId
	 * @param null|array $aData
	 */
	public final function __construct($sFromUser, $sToUser, $iCreateTime = null, $iMsgId = null, $aData = null)
	{
		$this->_aMeta = [
			'FromUserName' => $sFromUser,
			'ToUserName'   => $sToUser,
			'CreateTime'   => $iCreateTime,
			'MsgId'        => $iMsgId,
			'MsgType'      => isset($aData['MsgType']) ? strtolower($aData['MsgType']) : 'undefined'
		];

		$this->_initialize($aData);
	}

	protected abstract function _initialize($aData = null);

	public final function getType()
	{
		$sCls = explode('\\', get_class($this));

		return array_pop($sCls);
	}

	public final function getMeta()
	{
		return $this->_aMeta;
	}

	public final function __get($sKey)
	{
		if (method_exists($this, 'get' . $sKey)) {
			$sMethod = 'get' . $sKey;

			return $this->$sMethod();
		} else if (array_key_exists($sKey, $this->_aMeta)) {
			return $this->_aMeta[$sKey];
		} else {
			return null;
		}
	}

	public final function __set($sKey, $mValue)
	{
		throw new NotImplementedException();
	}
}