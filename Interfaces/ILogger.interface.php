<?php
/**
 * Raindrop Framework for PHP
 *
 * Logger Interface
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Interfaces;

use Raindrop\Configuration;

interface ILogger
{
	public function __construct(Configuration $oConfig, $sRequestId=null);

	public function Trace($mMsg);

	public function Debug($mMsg);

	public function Message($mMsg);

	public function Warning($mMsg);

	public function Error($mMsg);

	public function Fatal($mMsg);
}