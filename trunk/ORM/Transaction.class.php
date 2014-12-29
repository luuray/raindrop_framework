<?php
/**
 * Raindrop Framework for PHP
 *
 * Transaction Manager
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

namespace Raindrop\ORM;

use Raindrop\Application;
use Raindrop\DatabaseAdapter;
use Raindrop\Debugger;
use Raindrop\Logger;

final class Transaction
{
	protected static $_aTransPool = array();

	protected $_sDataSource;
	protected $_bIsActive = false;

	/**
	 * @param string $sDataSource
	 * @return Transaction
	 */
	public static function BeginTransaction($sDataSource)
	{
		$sDataSource = strtolower($sDataSource);
		if (array_key_exists($sDataSource, self::$_aTransPool) == false) {
			self::$_aTransPool[$sDataSource] = new self($sDataSource);
		}

		return self::$_aTransPool[$sDataSource];
	}

	protected function __construct($sDataSource)
	{
		$bResult            = DatabaseAdapter::BeginTransaction($sDataSource);
		$this->_sDataSource = $sDataSource;
		$this->_bIsActive   = true;

		return $bResult;
	}

	public function __destruct()
	{
		foreach (self::$_aTransPool AS $_sDSN => $_item) {
			if ($_item->isActive() == true) {
				$_item->rollback();
				Logger::Warning('database: transaction_active(' . $_sDSN . '), rollback!');
				if (Application::IsDebugging()) {
					Debugger::Output('transaction_active(' . $_sDSN . ')', 'Transaction');
				}
			}
		}
	}

	public function isActive()
	{
		return $this->_bIsActive;
	}

	public function rollback()
	{
		if ($this->_bIsActive == true) {
			$this->_bIsActive = false;

			return DatabaseAdapter::RollbackTransaction($this->_sDataSource);
		} else {
			return false;
		}
	}

	public function commit()
	{
		if ($this->_bIsActive == true) {
			$this->_bIsActive = false;

			return DatabaseAdapter::CommitTransaction($this->_sDataSource);
		} else {
			return false;
		}
	}
}