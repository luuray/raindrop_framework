<?php
/**
 * Raindrop Framework for PHP
 *
 * Query Conditions Container
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2015, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\ORM;


class Condition
{
	public function __construct(Model $oQueryModel = null)
	{
	}

	public function andWhere($sCondition, $aParams)
	{
	}

	public function orWhere($sCondition, $aParams)
	{
	}

	public function inWhere($sCondiction, $aParams)
	{
	}
}