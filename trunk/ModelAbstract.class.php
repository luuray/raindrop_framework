<?php
/**
 * Raindrop Framework for PHP
 *
 * Module Abstract
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

namespace Raindrop;

abstract class ModelAbstract
{
	const ACTION_INSERT = 1;
	const ACTION_UPDATE = 2;


	protected static $_sTableName = null;
	protected static $_sConnName = 'default';
	protected static $_sPkName = null;

	public final function __construct($iAction = self::ACTION_INSERT)
	{

	}

	public final function save()
	{
	}

	public final static function SingleOrNull($aCondition)
	{
		$funcConditionMaker = function ($aSource) {
			if (!empty($aSource) && is_array($aSource)) {
				$aItems  = array();
				$aColumn = array_keys($aSource);
				foreach ($aColumn AS $_item) {
					$aItems[] = "`{$_item}`=:{$_item}";
				}

				return 'WHERE ' . implode(' AND ', $aItems);
			}
		};

		$oResult = DatabaseAdapter::GetLine(
			sprintf('SELECT * FROM `%s` %s LIMIT 1', static::$_sTableName, $funcConditionMaker($aCondition), $aCondition),
			$aCondition, static::$_sConnName, get_called_class());

		return $oResult === false ? null : $oResult;
	}

	public final static function Add(self $oSource)
	{
		$aColumns = object_get_public_properties($oSource);

		$aProperties = array();
		$sQuery      = 'INSERT INTO `%s` (%s) VALUE (%s)';

		$aColumn     = array();
		$aColumnBind = array();

		foreach ($aColumns AS $_k => $_v) {
			$aProperties[$_k] = $_v;
			$aColumn[]        = $_k;
			$aColumnBind[]    = ':' . $_k;
		}

		$sQuery = sprintf($sQuery, static::$_sTableName, implode(',', $aColumn), implode(',', $aColumnBind));

		if (static::$_sPkName != null) {
			$iId = DatabaseAdapter::GetLastId($sQuery, $aProperties, static::$_sConnName);

			if ($iId !== false) {
				$sPk           = static::$_sPkName;
				$oSource->$sPk = $iId;
			}
		} else {
			DatabaseAdapter::GetAffectedRowNum($sQuery, $aProperties, static::$_sConnName);
		}
	}
}