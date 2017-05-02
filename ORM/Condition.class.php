<?php
/**
 * Raindrop Framework for PHP
 *
 * Query Condition Builder
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
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