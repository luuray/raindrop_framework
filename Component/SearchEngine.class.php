<?php
/**
 * Raindrop Framework for PHP
 *
 * SearchEngine Wapper
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
use Raindrop\Exceptions\ComponentNotFoundException;
use Raindrop\Exceptions\ConfigurationMissingException;
use Raindrop\Exceptions\FatalErrorException;
use Raindrop\Interfaces\ISearchProvider;
use Raindrop\Model\SearchCondition;

class SearchEngine
{
	protected $_aProviders = [];

	public static function GetCondition($sProvider)
	{
		return new SearchCondition();
	}

	public static function Search($sProvider, SearchCondition $oConditions, $iLimit = 10, $iSkip = 0)
	{
		return self::_getInstance()->getProvider($sProvider)->search($oConditions, $iLimit, $iSkip);
	}

	protected static function _getInstance()
	{
		static $oInstance = null;
		if ($oInstance === null) {
			$oInstance = new self();
		}

		return $oInstance;
	}

	protected function __construct()
	{
	}

	/**
	 * @param $sName
	 *
	 * @return ISearchProvider
	 *
	 * @throws ComponentNotFoundException
	 * @throws ConfigurationMissingException
	 * @throws FatalErrorException
	 */
	public function getProvider($sName)
	{
		//$sName = strtolower($sName);
		if (array_key_exists($sName, $this->_aProviders)) {
			return $this->_aProviders[$sName];
		} else {
			$oConfig = Configuration::Get('SearchEngine\\' . $sName);
			if ($oConfig == null) {
				throw new ConfigurationMissingException('SearchEngine\\' . $sName);
			}

			$sCompName = 'Raindrop\Component\\' . $oConfig->Component;
			if (class_exists($sCompName) === false) {
				throw new ComponentNotFoundException($sCompName);
			}

			try {
				$oComponent = new $sCompName($sName, $oConfig->Params);

				if ($oComponent instanceof ISearchProvider) {
					return $oComponent;
				} else {
					throw new FatalErrorException('invalid_component_type: ' . $sCompName);
				}
			} catch (ConfigurationMissingException $ex) {
				throw new ConfigurationMissingException('SearchEngin\\' . $sName . '\Params\\' . $ex->getSection());
			}
		}
	}
}