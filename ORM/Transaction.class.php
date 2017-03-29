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
use Raindrop\Logger;

final class Transaction
{
	protected static $_aTransPool = array();

	protected $_iTransDeep = 0;
	protected $_sDataSource = '';

	public static function BeginTransaction($sDataSource)
	{
		if(Application::IsDebugging()){
			Logger::Message('beginTransaction');
		}

		$sDataSource = strtolower($sDataSource);
		if (array_key_exists($sDataSource, self::$_aTransPool)) {
			self::$_aTransPool[$sDataSource]->newTrans();
		} else {
			self::$_aTransPool[$sDataSource] = new self($sDataSource);
		}

		return self::$_aTransPool[$sDataSource];
	}

	protected function __construct($sDataSource)
	{
		$this->_sDataSource = $sDataSource;
		$this->_iTransDeep  = 1;
		DatabaseAdapter::BeginTransaction($sDataSource);
	}

	public function __destruct()
	{
		if ($this->_iTransDeep > 0) {
			DatabaseAdapter::RollbackTransaction($this->_sDataSource);

			if (Application::IsDebugging()) {
				Logger::Warning('transaction_active(' . $this->_sDataSource . ')', 'Transaction');
			}
		}
	}

	public function newTrans()
	{
		$this->_iTransDeep++;
	}

	public function commit()
	{
		if(Application::IsDebugging()){
			Logger::Message('commitTransaction');
		}

		if ($this->_iTransDeep > 0) {
			$this->_iTransDeep--;
			if ($this->_iTransDeep == 0) {
				return DatabaseAdapter::CommitTransaction($this->_sDataSource);
			}

			return true;
		} else {
			return false;
		}
	}

	public function rollback()
	{
		if(Application::IsDebugging()){
			Logger::Message('rollbackTransaction');
		}

		if ($this->_iTransDeep > 0) {
			$this->_iTransDeep--;
			if ($this->_iTransDeep == 0) {
				return DatabaseAdapter::RollbackTransaction($this->_sDataSource);
			}

			return true;
		} else {
			return false;
		}
	}
}