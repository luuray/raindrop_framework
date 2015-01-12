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
use Raindrop\FatalErrorException;
use Raindrop\Identify;
use Raindrop\InvalidArgumentException;
use Raindrop\Loader;
use Raindrop\ViewNotFound;

class View extends ActionResult
{
	#region View Validation Status
	protected static $_bIsValid = true;
	protected static $_sValidMessage = null;

	public static function SetValid($bIsValid = false, $sValidMessage = null)
	{
		self::$_bIsValid      = $bIsValid;
		self::$_sValidMessage = $sValidMessage;
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
	 * @var array
	 */
	protected $_aSections = array();

	#region Implement Methods from ActionResult
	public function __construct($sTpl = null, $mData = null)
	{
		$this->_oViewData = ViewData::GetInstance()->mergeReplace($mData);
		$this->_oRequest  = Application::GetRequest();

		//decide bodyPage
		if (str_nullorwhitespace($sTpl) !== true) {
			$sTpl             = strtolower(trim($sTpl));
			$this->_sBodyView = $this->_decidePath($sTpl);
		} else {
			$this->_sBodyView = $this->_decidePath($this->_oRequest->getAction());
		}

		if ($this->_sBodyView == false) {
			throw new ViewNotFound('View:' . htmlentities($sTpl, ENT_QUOTES));
		}

		//default layout
		$this->_sLayout = $this->_decidePath('layout');
	}

	public function Output()
	{
		if (Application::IsDebugging()) {
			Debugger::Output($this->_oViewData, 'ViewData');
		}

		echo $this->_render();
		@ob_end_flush();
	}

	public function toString()
	{
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
			$this->_sLayout = $sLayout;
		}
	}

	/**
	 * Include a Partial Page
	 *
	 * @param string $sPartialPage
	 */
	public function includePartial($sPartialPage)
	{
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

	public function renderBody()
	{
		if ($this->_sBodyView !== null) {
			return $this->_render($this->_sBodyView);
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
				$View     = $this;
				$ViewData = $this->_oViewData;

				require Loader::Import($sViewName, null, false);
			};

			return $func();
		}
	}

	/**
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

		if (str_beginwith($sPage, '/')) {
			$sPath = AppDir . $sPage;

			return Loader::CheckLoadable($sPath) ? $sPath : false;
		} else if (str_beginwith($sPage, '~')) {
			$sPage = preg_replace('/^~\/', '', $sPage);
			$sPath = AppDir . DIRECTORY_SEPARATOR . ($this->_oRequest->getModule() == null ? '' : $this->_oRequest->getModule()
					. DIRECTORY_SEPARATOR) . 'view' . DIRECTORY_SEPARATOR . $sPage;

			return Loader::CheckLoadable($sPath) ? $sPath : false;
		} else {
			if (str_beginwith('./', $sPage)) {
				$sPage = substr($sPage, 1);
			}
			$aPaths = [
				AppDir . DIRECTORY_SEPARATOR .
				($this->_oRequest->getModule() == null ? '' : $this->_oRequest->getModule() . DIRECTORY_SEPARATOR) .
				'view' . DIRECTORY_SEPARATOR . $this->_oRequest->getController() . DIRECTORY_SEPARATOR . $sPage,
				AppDir . DIRECTORY_SEPARATOR .
				($this->_oRequest->getModule() == null ? '' : $this->_oRequest->getModule() . DIRECTORY_SEPARATOR) .
				'view' . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . $sPage,
				AppDir . DIRECTORY_SEPARATOR .
				'view' . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . $sPage,
				CorePath . DIRECTORY_SEPARATOR .
				'ActionResult' . DIRECTORY_SEPARATOR . 'Pages' . DIRECTORY_SEPARATOR . $sPage
			];
			foreach ($aPaths AS $_path) {
				if (Loader::CheckLoadable($_path)) {
					return $_path;
				}
			}

			return false;
		}
	}
}