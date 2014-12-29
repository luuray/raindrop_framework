<?php
/**
 * Raindrop Framework for PHP
 *
 * Loader
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

class Loader
{
	protected static $_oInstance = null;

	/**
	 * Alias of Loader::GetInstance
	 * @return Loader
	 */
	public static function Initialize()
	{
		self::GetInstance();
	}

	/**
	 * @return Loader
	 */
	public static function GetInstance()
	{
		if (self::$_oInstance === null) {
			self::$_oInstance = new self();

			//register loader to spl
			spl_autoload_register(array(self::$_oInstance, 'autoload'));
		}

		return self::$_oInstance;
	}

	public static function Import($sFileName, $sDir, $bCheckExists = true, $bAutoLoad = true)
	{
		$oLoader = self::GetInstance();

		if (self::SecurityCheck($sFileName) == false) {
			throw new FatalErrorException('loader_unsecured_filename: ' . $sFileName);
		}

		if ($bCheckExists == true && is_readable($sDir . DIRECTORY_SEPARATOR . $sFileName) == false) {
			//debug
			Logger::Warning(sprintf('[FileNotFound]Path: %s, FileName: %s', $sDir, $sFileName));

			throw new FileNotFoundException($sDir . DIRECTORY_SEPARATOR . $sFileName);
		}

		if ($bAutoLoad == true) {
			//return @include_once $sDir . DIRECTORY_SEPARATOR . $sFileName;
			return require_once $sDir . DIRECTORY_SEPARATOR . $sFileName;
		} else {
			return $sDir . DIRECTORY_SEPARATOR . $sFileName;
		}
	}

	/**
	 * Security Check for FileName
	 *
	 * @param $sFileName
	 * @return bool
	 */
	public static function SecurityCheck($sFileName)
	{
		return !(bool)preg_match('/[^a-z0-9\\/\\\\_.:-]/i', $sFileName);
	}

	public function __construct()
	{

	}

	public function autoload($sTargetClass)
	{
		//declared check
		if (class_exists($sTargetClass, false) || interface_exists($sTargetClass, false)) return true;

		//load framework
		if (str_beginwith($sTargetClass, 'Raindrop')) {
			$sPath = $this->_loadFramework($sTargetClass);
		} //load application
		else if (str_beginwith($sTargetClass, AppName)) {
			$sPath = $this->_loadApplication($sTargetClass);
		} else {
			//throw new FatalErrorException('loader_invalid_target: ' . $sTargetClass);
			//jump to other spl registered loader
			return;
		}

		if (self::SecurityCheck($sTargetClass)) {
			self::Import($sTargetClass, $sPath);
		} else {
			throw new FatalErrorException('loader_unsecured_filename: ' . $sTargetClass);
		}
	}

	protected function _loadFramework(&$sTargetClass)
	{
		$aNameTree = explode('\\', $sTargetClass);
		//shift root namespace out(Raindrop)
		array_shift($aNameTree);

		if ($aNameTree[0] == 'Interfaces') {
			$sTargetClass = array_pop($aNameTree) . '.interface.php';
		} else {
			$sTargetClass = array_pop($aNameTree) . '.class.php';
		}

		return CorePath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $aNameTree);
	}

	protected function _loadApplication(&$sTargetClass)
	{
		$aNameTree = explode('\\', $sTargetClass);
		array_shift($aNameTree);//remove app namespace

		$sClassName  = array_pop($aNameTree);
		$sLayOut     = array_pop($aNameTree);
		$aNameTree[] = $sLayOut;
		$sLayOut     = strtolower($sLayOut);

		$sPath        = AppDir . DIRECTORY_SEPARATOR . strtolower(implode(DIRECTORY_SEPARATOR, $aNameTree));
		$sTargetClass = $sLayOut === 'controller' ?
			substr($sClassName, 0, -10) . '.controller.php' :
			$sClassName . '.class.php';

		return $sPath;
	}
}