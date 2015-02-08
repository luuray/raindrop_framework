<?php
/**
 * Raindrop Framework for PHP
 *
 * Logger Interface
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
namespace Raindrop\Interfaces;

interface ILogger
{
	public function __construct($aConfig);

	public function Trace($mMsg);

	public function Debug($mMsg);

	public function Message($mMsg);

	public function Warning($mMsg);

	public function Error($mMsg);

	public function Fatal($mMsg);
}