<?php
/**
 * Raindrop Framework for PHP
 *
 * Message Serializer for WeChat Module
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Model;


interface IMessageSerializer
{
	public function __construct(Message $oMessage);

	public function __toString();
}