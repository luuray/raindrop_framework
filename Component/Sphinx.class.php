<?php
/**
 * Raindrop Framework for PHP
 *
 * Sphinx Search Provider
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component;


use Raindrop\Configuration;
use Raindrop\Exceptions\ConfigurationMissingException;
use Raindrop\Exceptions\RuntimeException;
use Raindrop\Interfaces\ISearchProvider;
use Raindrop\Logger;
use Raindrop\Model\ArrayList;
use Raindrop\Model\SearchCondition;

class Sphinx implements ISearchProvider
{
	protected $_oConnect = null;
	protected $_sIndex = null;

	protected $_sName;

	public function __construct($sName, Configuration $oConfig = null)
	{
		$this->_sName = $sName;

		try {
			$this->_oConnect = new \PDO(
				sprintf('mysql:dbname=;host=%s;port=%d;charset=utf8mb4', $oConfig->Server, $oConfig->Port),
				null, null,
				[
					\PDO::ATTR_EMULATE_PREPARES => true,
					\PDO::ATTR_ERRMODE          => \PDO::ERRMODE_EXCEPTION
				]);
			$this->_sIndex   = $oConfig->Index;

			if (empty($this->_sIndex)) {
				throw new ConfigurationMissingException("Index");
			}
		} catch (\PDOException $ex) {
			throw new RuntimeException($ex->getMessage());
		}
	}

	public function search(SearchCondition $oCondition = null, $iLimit = 10, $iSkip = 0)
	{
		try {
			$aConditions = [];
			$aCondParams = [];
			$iCount      = 0;

			if ($oCondition != null) {
				foreach ($oCondition AS $_item) {
					if ($_item->Mode == SearchCondition::MODE_MATCH) {
						$aConditions[]                          = sprintf('MATCH(:%s)', md5_short($_item->Fields));
						$aCondParams[md5_short($_item->Fields)] = sprintf('@(%s) %s', $this->_escapeStr($_item->Fields), $this->_escapeStr($_item->Value));
					} else if ($_item->Mode == SearchCondition::MODE_EQUAL) {
						$sFields                          = $_item->Fields;
						$aConditions[]                    = sprintf('`%s`=:%s', $sFields, md5_short($sFields));
						$aCondParams[md5_short($sFields)] = $this->_escapeStr($_item->Value);
					}
				}
			}

			$oStmt = $this->_oConnect->prepare(sprintf(
				'SELECT COUNT(*) FROM `%s` %s',
				$this->_sIndex, (empty($aConditions) ? '' : 'WHERE ' . implode(' AND ', $aConditions))));
			foreach ($aCondParams AS $_k => $_v) {
				if (is_int($_v)) {
					$oStmt->bindValue(':' . $_k, intval($_v), \PDO::PARAM_INT);
				} else {
					$oStmt->bindValue(':' . $_k, $_v, \PDO::PARAM_STR);
				}
			}

			if ($oStmt->execute() == false) {
				Logger::Debug('SearchProvider:' . $this->_sName
					. ', Conditions:' . print_r($aConditions, true) . ', Params:' . print_r($aCondParams, true) . ', Error:' . $oStmt->errorInfo()[2]);

				throw new RuntimeException('search_engine_failed:' . $oStmt->errorInfo()[2]);
			} else {
				$iCount = $oStmt->fetchColumn(0);

				Logger::Debug('SearchProvider:' . $this->_sName
					. ', Conditions:' . print_r($aConditions, true) . ', Params:' . print_r($aCondParams, true) . ', Record:' . $iCount);
			}

			if ($iCount <= $iSkip) {
				return new ArrayList(null, ['Skip' => $iSkip, 'Limit' => $iLimit, 'Count' => $iCount]);
			}

			$oStmt = $this->_oConnect->prepare(sprintf(
				'SELECT *,WEIGHT() FROM %s %s %s %s',
				$this->_sIndex, //idx
				(empty($aConditions) ? '' : 'WHERE ' . implode(' AND ', $aConditions)),//where
				($oCondition->getOrder() == null ? '' : 'ORDER BY ' . implode(', ', $this->getOrder())),//order
				sprintf(' LIMIT %d, %d', $iSkip, $iLimit)//limit
			));
			foreach ($aCondParams AS $_k => $_v) {
				if (is_int($_v)) {
					$oStmt->bindValue(':' . $_k, intval($_v), \PDO::PARAM_INT);
				} else {
					$oStmt->bindValue(':' . $_k, $_v, \PDO::PARAM_STR);
				}
			}

			if ($oStmt->execute() == false) {
				throw new RuntimeException('search_engine_failed:' . $oStmt->errorInfo()[2]);
			}

			$aResult = [];
			while ($row = $oStmt->fetch(\PDO::FETCH_ASSOC)) {
				$aResult[] = $row;
			}

			return new ArrayList($aResult, ['Skip' => $iSkip, 'Limit' => $iLimit, 'Count' => $iCount]);
		} catch (\PDOException $ex) {
			throw new RuntimeException($ex->getMessage(), $ex->getCode(), $ex);
		}
	}

	protected function _escapeStr($sSource)
	{
		return str_replace(
			['\\', '(', ')', '|', '-', '!', '@', '~', '"', '&', '/', '^', '$', '='],
			['\\\\', '\(', '\)', '\|', '\-', '\!', '\@', '\~', '\"', '\&', '\/', '\^', '\$', '\='],
			$sSource);
	}
}