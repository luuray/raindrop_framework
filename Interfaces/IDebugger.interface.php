<?php
/**
 * BoostCenter
 *
 *
 *
 * @author $Author$
 * @copyright
 * @date $Date$
 *
 * Copyright (c) 2010-2014,
 * Site:
 *
 * $Id$
 *
 * @version $Rev$
 */
namespace Raindrop\Interfaces;
interface IDebugger
{
	public function __construct($aConfig);

	public function output($mMsg, $sLabel = '');
}