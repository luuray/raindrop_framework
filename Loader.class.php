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

use Raindrop\Exceptions\FileNotFoundException;

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

	/**
	 * @return bool
	 */
	public static function CheckLoadable()
	{
		$aArgs = func_get_args();
		//Directory/FileName
		if (func_num_args() == 1) {
			if (self::SecurityCheck($aArgs[0])) {
				return is_readable($aArgs[0]);
			} else {
				return false;
			}
		} else {
			$aArgs = array_reverse($aArgs);
			$sPath = implode(DIRECTORY_SEPARATOR, $aArgs);

			if (self::SecurityCheck($sPath)) {
				return is_readable($sPath);
			} else {
				return false;
			}
		}
	}

	public static function Import($sFileName, $sDir = null, $bAutoLoad = true)
	{
		$sPath = (str_nullorwhitespace($sDir) ? null : $sDir . DIRECTORY_SEPARATOR) . $sFileName;
		$sPath = preg_replace(['/[^0-9a-z_\-\.\/\\\]/i', '/[\/\\\]+/', '/^(\/\.+)+\\//'], ['_', '/', ''], $sPath);

		$aSearchPath = array();
		//if only filename then search in app root or framework root
		if (pathinfo($sPath, PATHINFO_DIRNAME) == null) {
			$aSearchPath[] = AppDir . DIRECTORY_SEPARATOR . $sPath;
			$aSearchPath[] = CorePath . DIRECTORY_SEPARATOR . $sPath;
		} else {
			$aSearchPath[] = $sPath;
		}

		$sLoadPath = null;
		foreach ($aSearchPath AS $_path) {
			if (self::SecurityCheck($_path) AND is_readable($_path)) {
				$sLoadPath = $_path;
				continue;
			}
		}
		if (empty($sLoadPath)) {
			throw new FileNotFoundException(self::ClearPath($_path));
		}

		if ($bAutoLoad == true) {
			return require_once $sLoadPath;
		} else {
			return $sLoadPath;
		}
	}

	public static function ClearPath($sSource)
	{
		return preg_replace([
			'#^'.addslashes(AppDir).'#',
			'#^'.addslashes(CorePath).'#'
		], ['%AppDir%', '%CoreDir%'], $sSource);
	}

	/**
	 * Security Check for FileName
	 *
	 * @param $sPath
	 * @return bool
	 */
	public static function SecurityCheck($sPath)
	{
		return str_beginwith(strtolower($sPath), strtolower(SysRoot));
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
			$this->_loadFramework($sTargetClass);
		} //load application
		else if (str_beginwith($sTargetClass, AppName)) {
			$this->_loadApplication($sTargetClass);
		} else {
			//jump to other spl registered loader
			return;
		}
	}

	protected function _loadFramework(&$sTargetClass)
	{
		$aNameTree = explode('\\', $sTargetClass);
		//shift root namespace out(Raindrop)
		array_shift($aNameTree);

		if (strtolower($aNameTree[0]) == 'interfaces') {
			$sTargetClass = array_pop($aNameTree) . '.interface.php';
		} else if (strtolower($aNameTree[0]) == 'exceptions') {
			//Controller, Database, Identify, Model, System, View;
			array_shift($aNameTree);

			$sTargetClass = $aNameTree[0].'.php';

			$aNameTree = ['Exceptions'];
		} else {
			$sTargetClass = array_pop($aNameTree) . '.class.php';
		}

		return self::Import($sTargetClass, CorePath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $aNameTree), true);
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

		self::Import($sTargetClass, $sPath, true);
	}
}