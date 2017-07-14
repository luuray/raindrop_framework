<?php
/**
 * Raindrop Framework for PHP
 *
 * Search Provider Interface
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
use Raindrop\Model\SearchCondition;

interface ISearchProvider
{
	public function __construct($sName, Configuration $oConfig = null);

	public function search(SearchCondition $oCondition = null, $iLimit = 10, $iSkip = 0);
}