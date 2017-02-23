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
use Raindrop\Exceptions\FatalErrorException;
use Raindrop\Exceptions\FileNotFoundException;
use Raindrop\Exceptions\InvalidArgumentException;
use Raindrop\Exceptions\View\ViewNotFoundException;
use Raindrop\Loader;

class View extends ActionResult
{
	#region View Validation Status
	protected static $_bIsValid = true;
	protected static $_sValidMessage = null;
	protected static $_sTitle = null;

	public static function SetValid($bIsValid, $sValidMessage = null)
	{
		self::$_bIsValid      = $bIsValid;
		self::$_sValidMessage = $sValidMessage;
	}

	public static function SetTitle($sTitle)
	{
		self::$_sTitle = $sTitle;
	}
	#endregion

	/**
	 * @var null|ViewData
	 */
	protected $_oViewData = null;

	/**
	 * @var null|Request
	 */
	protected $_oRequest = null;
	/**
	 * @var null|string
	 */
	protected $_sLayout = null;
	/**
	 * @var null|string
	 */
	protected $_sBodyView = null;

	/**
	 * @var bool
	 */
	protected $_bIsBodyRendered = false;
	/**
	 * @var null|string
	 */
	protected $_sRenderedBody = null;

	/**
	 * @var array
	 */
	protected $_aSections = array();

	#region Implement Methods from ActionResult
	public function __construct($sTpl = null, $mData = null)
	{
		$this->SiteName   = $this->Title = Configuration::Get('System\SiteName', AppName);
		$this->BaseUrl    = Application::GetRequest()->getBaseUri();
		$this->_oViewData = ViewData::GetInstance()->mergeReplace($mData);
		$this->_oRequest  = Application::GetRequest();

		//decide bodyPage
		$sBodyView = str_nullorwhitespace($sTpl)?$this->_oRequest->getAction():$sTpl;

		$this->_sBodyView = $this->_decidePath($sBodyView);

		if ($this->_sBodyView == false) {
			throw new ViewNotFoundException('View:' . htmlentities($sBodyView, ENT_QUOTES));
		}

		//default layout
		$this->_sLayout = $this->_decidePath('layout');
	}

	public function __get($sName)
	{
		$sName = strtolower($sName);

		switch($sName){
			case 'isvalid':
				return self::$_bIsValid;
			case 'validmessage':
				return self::$_sValidMessage;
			default:
				return $this->_oViewData[$sName];
		}
	}

	public function Output()
	{
		if (Application::IsDebugging()) {
			Debugger::Output($this->_oViewData, 'ViewData');
			Debugger::Output(['BodyView'=>Loader::ClearPath($this->_sBodyView), 'Layout'=>Loader::ClearPath($this->_sLayout)], 'ViewPage');
		}

		if ($this->_bIsBodyRendered != true) {
			$this->renderBody();
		}

		echo $this->_render();

		return true;
	}

	public function toString()
	{
		if ($this->_bIsBodyRendered != true) {
			$this->renderBody();
		}

		return $this->_render();
	}
	#endregion

	/**
	 * Set Layout
	 *
	 * @param null|string $sLayout
	 */
	public function setLayout($sLayout = null)
	{
		$sLayout = strtolower(trim($sLayout));
		if (empty($sLayout)) {
			$this->_sLayout = null;
		} else {
			$this->_sLayout = $this->_decidePath($sLayout);
		}
	}

	/**
	 * Include a Partial Page
	 *
	 * @param string $sPartialPage
	 */
	public function includePartial($sPartialPage)
	{
		return $this->_render($this->_decidePath($sPartialPage));
	}

	/**
	 * Register Section
	 *
	 * @param string $sName Section Name
	 * @param string $sContent Section Content
	 * @throws InvalidArgumentException
	 */
	public function registerSection($sName, $sContent)
	{
		if (str_nullorwhitespace($sName)) {
			throw new InvalidArgumentException('SectionName');
		}

		$sName                    = strtolower($sName);
		$this->_aSections[$sName] = $sContent;
	}

	/**
	 * Get Registered Section
	 *
	 * @param string $sName
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	public function getSection($sName)
	{
		if (str_nullorwhitespace($sName)) {
			throw new InvalidArgumentException('SectionName');
		}

		$sName = strtolower($sName);

		return array_key_exists($sName, $this->_aSections) ? $this->_aSections[$sName] : null;
	}

	public function renderBody($bRefresh = false)
	{
		if ($this->_sBodyView !== null) {
			if ($this->_bIsBodyRendered == false || $bRefresh == true) {
				$this->_sRenderedBody   = $this->_render($this->_sBodyView);
				$this->_bIsBodyRendered = true;
			}

			return $this->_sRenderedBody;
		} else {
			throw new FatalErrorException('Undefined_BodyView');
		}
	}

	protected function _render($sViewName = null)
	{
		if ($sViewName == null) {
			if ($this->_sLayout === null) {
				return $this->renderBody();
			} else {
				return $this->_render($this->_sLayout);
			}
		} else {

			$func = function () use ($sViewName) {
				$Title    = self::$_sTitle;
				$SiteName = $this->SiteName;
				$BaseUrl  = $this->BaseUrl;

				$View     = $this;
				$ViewData = $this->_oViewData;
				$Identify = Application::GetIdentify();
				try {
					require_once Loader::Import($sViewName, null, false);
				}catch(FileNotFoundException $ex){
					throw new ViewNotFoundException($sViewName);
				}
			};

			ob_start();
			$func();

			return ob_get_clean();
		}
	}

	/**
	 * Decide ViewFile Path
	 *
	 * @param string $sPage
	 * @return string|false
	 */
	protected function _decidePath($sPage)
	{
		/*----------
		 * Rule:
		 *
		 * Begin with "/":
		 *  Search in path: AppDir
		 * Begin with "~":
		 *  Search in path: AppDir/[module/]view
		 * Begin with char:
		 *  Search in path: AppDir/[module/]/view/controller => AppDir/[module/]view/shared => CorePath/ActionResult/Pages
		 ----------*/

		$sPage .= pathinfo($sPage, PATHINFO_EXTENSION) == null ? '.phtml' : null;
		$sPage = preg_replace(['/\.+/', '/[\/\\\]+/'], ['.', '/'], $sPage);

		$aPaths = array();

		if (str_beginwith($sPage, '/')) {
			$aPaths[] = AppDir . $sPage;
		} else if (str_beginwith($sPage, '~')) {
			$sPage = preg_replace('/^~\//', '', $sPage);

			if ($this->_oRequest->getModule() != null) {
				$aPaths[] = AppDir . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR . $this->_oRequest->getModule() . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $sPage;
			}
			$aPaths[] = AppDir . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $sPage;

		} else {
			if (str_beginwith($sPage, './')) {
				$sPage = substr($sPage, 1);
			}

			if ($this->_oRequest->getModule() != null) {
				//controller's
				$aPaths[] = implode(DIRECTORY_SEPARATOR, [AppDir, 'module', $this->_oRequest->getModule(), 'view', $this->_oRequest->getController()]) . DIRECTORY_SEPARATOR . $sPage;
				//shared
				$aPaths[] = implode(DIRECTORY_SEPARATOR, [AppDir, 'module', $this->_oRequest->getModule(), 'view', 'shared']) . DIRECTORY_SEPARATOR . $sPage;
			} else {
				//controller's
				$aPaths[] = implode(DIRECTORY_SEPARATOR, [AppDir, 'view', $this->_oRequest->getController()]) . DIRECTORY_SEPARATOR . $sPage;
			}

			//public shared
			$aPaths[] = implode(DIRECTORY_SEPARATOR, [AppDir, 'view', 'shared']) . DIRECTORY_SEPARATOR . $sPage;
/*
			if ($this->_oRequest->getModule() != null) {
				$aPaths[] = AppDir . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR . $this->_oRequest->getModule() . DIRECTORY_SEPARATOR .
					'view' . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . $sPage;
			}

			$aPaths[] =
				AppDir . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $this->_oRequest->getController() . DIRECTORY_SEPARATOR . $sPage;

			$aPaths[] = AppDir . DIRECTORY_SEPARATOR .
				'view' . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . $sPage;
			$aPaths[] = CorePath . DIRECTORY_SEPARATOR .
				'ActionResult' . DIRECTORY_SEPARATOR . 'Pages' . DIRECTORY_SEPARATOR . $sPage;
*/
		}

		foreach ($aPaths AS $_path) {
			if (Loader::CheckLoadable($_path)) {
				return $_path;
			}
		}

		return false;
	}
}