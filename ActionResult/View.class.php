<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result in HTML
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

namespace Raindrop\ActionResult;

use Raindrop\ActionResult;
use Raindrop\Application;
use Raindrop\Configuration;
use Raindrop\Debugger;
use Raindrop\Identify;
use Raindrop\Loader;

class View extends ActionResult
{
	protected static $_bIsValid = true;
	protected static $_sValidMessage = null;

	public static function SetValid($bIsValid = false, $sValidMessage = null)
	{
		self::$_bIsValid      = $bIsValid;
		self::$_sValidMessage = $sValidMessage;
	}

	protected $_sPage = null;
	protected $_sLayout = '/view/shared/layout.phtml';
	protected $_aViewProperties = array(
		'IsValid'      => true,
		'ValidMessage' => null);
	protected $_sRenderedBody = null;
	protected $_aSections = array();
	protected $_oViewData;

	/**
	 * Create a ActionResult Object
	 *
	 * @param string $sTplName Template to Display
	 * @param mixed $mData Output Parameter
	 */
	public function __construct($sTplName = null, $mData = null)
	{
		if (empty($sTplName)) {
			//detect by module-controller-action
			$oRequest     = Application::GetRequest();
			$sModule      = $oRequest->getModule();
			$this->_sPage = $sModule == null ? '/view/' : "/module/{$sModule}/view/";

			$this->_sPage .= sprintf('%s/%s.phtml', $oRequest->getController(), $oRequest->getAction());
		} //recent module's and controller's
		else if (strpos($sTplName, '/') === false AND strpos($sTplName, '\\') === false) {
			$oRequest     = Application::GetRequest();
			$sModule      = $oRequest->getModule();
			$sController  = $oRequest->getController();
			$this->_sPage = $sModule == null ? "/view/{$sController}/" : "/module/{$sModule}/view/{$sController}/";

			$this->_sPage .= $sTplName . (pathinfo($sTplName, PATHINFO_EXTENSION) == null ? '.phtml' : null);
		} //from root view path
		else if (str_beginwith($sTplName, '/') === false OR str_beginwith($sTplName, '\\') === false) {
			$this->_sPage = '/view/' . $sTplName . (pathinfo($sTplName, PATHINFO_EXTENSION) == null ? '.phtml' : null);
		} else {
			$this->_sPage = $sTplName . (pathinfo($sTplName, PATHINFO_EXTENSION) == null ? '.phtml' : null);
		}

		//Default Properties
		$this->SiteName   = $this->Title = Configuration::Get('System\SiteName', AppName);
		$this->BaseUrl    = Application::GetRequest()->getBaseUri();
		$this->_oViewData = ViewData::GetInstance()->MergeReplace($mData);

		//set valid state
		$this->IsValid      = self::$_bIsValid;
		$this->ValidMessage = self::$_sValidMessage;

		if (Application::IsDebugging()) {
			Debugger::Output(sprintf('Page: %s, Layout: %s', $this->_sPage, $this->_sLayout), 'View');
		}
	}

	public function __get($sName)
	{
		$sName = strtolower($sName);

		return array_key_exists($sName, $this->_aViewProperties) ? $this->_aViewProperties[$sName] : null;
	}

	public function __set($sName, $mValue)
	{
		$sName                          = strtolower($sName);
		$this->_aViewProperties[$sName] = $mValue;
	}

	public function setLayout($sLayout)
	{
		if ($sLayout === null) {
			$this->_sLayout = null;

			return true;
		}
		$sLayout = strtolower(trim($sLayout, '\\/.'));

		if (pathinfo($sLayout, PATHINFO_DIRNAME) == '.') {
			$sLayout = '/view/shared/' . $sLayout . '.phtml';
		}

		$this->_sLayout = $sLayout;
	}

	/**
	 * Render Page
	 */
	public function render()
	{
		ob_start();
		$this->_loader($this->_sPage);
		$this->_sRenderedBody = ob_get_clean();


		if ($this->_sLayout !== null) {
			ob_start();
			$this->_renderLayout();

			return ob_get_clean();
		} else {
			return $this->_sRenderedBody;
		}
	}

	protected function _renderLayout()
	{
		$this->_loader($this->_sLayout);
	}

	protected function _pathFix($sPath)
	{
		$oRequest = Application::GetRequest();

		$sModule   = $oRequest->getModule();
		$sBasePath = $sModule == null ? '/view' : "/module/{$sModule}/view";

		/*
		 * guess path
		 * if not found in recent module/controller then fail-over to recent module's shared path.
		 * and if not found in recent module's shared path, then fail-over to root shared path.
		 * after all, throw NotFoundException
		 */
		if (str_beginwith($sPath, '?')) {

		} /*
		 * absolute path
		 */
		else if (str_beginwith($sPath, '~')) {
			$sBasePath = '/';
			$sPath     = substr($sPath, 1);
		} #recent module, controller's path
		else if (str_beginwith($sPath, array('/', '\\')) == false) {
			$sFixPath = '/';
			$sFixPath .= $oRequest->getController();
			$sFixPath .= '/';
			$sPath = $sFixPath . $sPath;
		}
		#else begin from module's root folder
		if (pathinfo($sPath, PATHINFO_EXTENSION) == null) $sPath .= '.phtml';

		return strtolower(preg_replace('#\\+|/+#', DIRECTORY_SEPARATOR, $sBasePath . $sPath));
	}

	protected function _loader($sPath)
	{
		//Global View Vars
		$Title       = $this->_oViewData->Title;
		$SiteName    = $this->SiteName;
		$Keywords    = $this->Keyworks;
		$Description = $this->Decription;
		$BaseUrl     = $this->BaseUrl;
		$ViewData    = $this->_oViewData;
		$Identify    = Identify::GetInstance();

		//Make a mirror var for View
		$View = $this;

		$aPageInfo = pathinfo($sPath);

		require Loader::Import($aPageInfo['basename'], AppDir . '/' . $aPageInfo['dirname'], true, false);

	}

	/**
	 * Output Result
	 *
	 * @return mixed
	 */
	public function output()
	{
		if (Application::IsDebugging()) {
			Debugger::Output($this->_oViewData, 'ViewData');
		}
		echo $this->render();
	}

	public function toString()
	{
		return $this->render();
	}

	#region Renders
	public function renderPage($sPath)
	{
		return $this->_loader($this->_pathFix($sPath));
	}

	public function renderBody()
	{
		echo $this->_sRenderedBody;
	}
	#endregion

	#region Sections
	public function registerSection($sName, $sContent)
	{
		$sName                    = trim(strtolower($sName));
		$this->_aSections[$sName] = $sContent;
	}

	public function getSection($sName)
	{
		$sName = trim(strtolower($sName));

		return array_key_exists($sName, $this->_aSections) ? $this->_aSections[$sName] : '';
	}
	#endregion
} 