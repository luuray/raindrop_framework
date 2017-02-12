<?php
/**
 * Raindrop Framework for PHP
 *
 * Request
 *
 * @author $Author$
 * @copyright
 * @date $Date$
 *
 * Copyright (c) 2014-2015, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop;


use Raindrop\Model\UploadFile;

abstract class Request implements \ArrayAccess
{
	#region Method Constants
	const METHOD_OPTIONS = 'OPTIONS';
	const METHOD_GET = 'GET';
	const METHOD_HEAD = 'HEAD';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_DELETE = 'DELETE';
	const METHOD_TRACE = 'TRACE';
	const METHOD_CONNECT = 'CONNECT';
	const METHOD_CLI = 'CLI';
	#endregion

	protected $_sMethod = null;
	protected $_sBaseUri = null;
	protected $_sRequestUri = null;
	protected $_iRequestTime = null;

	protected $_sModule = null;
	protected $_sController = 'default';
	protected $_sAction = 'index';
	protected $_sType = 'View';

	protected $_bIsAjax = null;

	protected $_aHeader = array();
	protected $_aQuery = array();
	protected $_aData = array();

	/**
	 * Get QueryString(aka GET Array)
	 *
	 * @param null|string $sKey
	 * @param null $mDefault
	 * @return mixed
	 */
	public function getQuery($sKey = null, $mDefault = null)
	{
		if ($sKey !== null) {
			$sKey = strtolower($sKey);

			return array_key_exists($sKey, $this->_aQuery) ? $this->_aQuery[$sKey] : $mDefault;
		} else {
			return $this->_aQuery;
		}
	}

	/**
	 * Get Form Data(aka POST Array)
	 *
	 * @param null|string $sKey
	 * @param null $mDefault
	 * @return mixed
	 */
	public function getData($sKey = null, $mDefault = null)
	{
		if ($sKey !== null) {
			$sKey = strtolower($sKey);

			return array_key_exists($sKey, $this->_aData) ? $this->_aData[$sKey] : $mDefault;
		} else {
			return $this->_aData;
		}
	}

	/**
	 * Get Request Header
	 *
	 * @param string $sKey
	 * @param null $mDefault
	 * @return null|string
	 */
	public function getHeader($sKey, $mDefault = null)
	{
		$sKey = strtolower($sKey);

		return array_key_exists($sKey, $this->_aHeader) ? $this->_aHeader[$sKey] : $mDefault;
	}

	/**
	 * Get Raw Post Data
	 *
	 * @return string
	 */
	public abstract function getRawPost();

	/**
	 * Get Uploaded File
	 *
	 * @param $sKey
	 * @return UploadFile
	 */
	public abstract function getFile($sKey);

	/**
	 * @param null $sRequestUri
	 * @param null $sBaseUri
	 */
	public function __construct($sRequestUri = null, $sBaseUri = null)
	{
		//Request Time
		$this->_iRequestTime = empty($_SERVER['REQUEST_TIME']) ? time() : $_SERVER['REQUEST_TIME'];
		//Base Uri
		$this->_sBaseUri = $sBaseUri === null ?
			$this->getBaseUri() : $sBaseUri;
		//Request Uri
		$this->_sRequestUri = $sRequestUri === null ?
			$this->getRequestUri() : $sRequestUri;

		$this->_sMethod = $this->getMethod();

		$this->_initialize();
	}

	protected function _initialize()
	{
	}

	public function __get($sKey)
	{
		$sKey = strtolower($sKey);
		
		//GET->POST
		if(array_key_exists($sKey, $this->_aQuery)){
			return $this->_aQuery[$sKey];
		}
		else if(array_key_exists($sKey, $this->_aData)){
			return $this->_aData[$sKey];
		}
		else{
			return null;
		}
	}

	public function __isset($sKey)
	{
		$sKey = strtolower($sKey);

		return array_key_exists($sKey, $this->_aQuery) OR array_key_exists($sKey, $this->_aData);
	}

	public function getRequestTime()
	{
		return $this->_iRequestTime;
	}

	public function getScheme()
	{
		return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
	}

	/**
	 * Get Request Method
	 */
	public abstract function getMethod();

	/**
	 * @return string
	 */
	public function getHttpHost()
	{
		$sHost = $_SERVER['HTTP_HOST'];
		if (!empty($sHost)) {
			return $sHost;
		}

		$sScheme = $this->getScheme();
		$sName   = $_SERVER['SERVER_NAME'];
		$iPort   = $_SERVER['SERVER_PORT'];

		if ($sName === null) {
			return '';
		} else if (($sScheme == 'http' && $iPort == 80) OR ($sScheme == 'https' && $iPort == 443)) {
			return $sName;
		} else {
			return $sName . ':' . $iPort;
		}
	}

	/**
	 * Get Base Uri
	 *
	 * @return null|string
	 */
	public function getBaseUri()
	{
		if ($this->_sBaseUri !== null) {
			return $this->_sBaseUri;
		}

		///TODO Better BaseUri Detector
		$this->_sBaseUri = $this->getScheme() . '://' . $this->getHttpHost();

		return $this->_sBaseUri;
	}

	/**
	 * Get Request Uri
	 *
	 * @return null|string
	 */
	public function getRequestUri()
	{
		if ($this->_sRequestUri !== null) {
			return $this->_sRequestUri;
		}

		$sBase = array_key_exists('PATH_INFO', $_SERVER) ? $_SERVER['PATH_INFO'] : '/';
		!empty($_SERVER['QUERY_STRING']) ? ($sBase .= '?' . $_SERVER['QUERY_STRING']) : null;

		$this->_sRequestUri = $sBase;

		return $this->_sRequestUri;
	}

	public function getRemoteAddress()
	{
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
	}

	public function setQuery($aQuery)
	{
		if (is_array($aQuery)) $this->_aQuery = $aQuery;
	}

	public function setData($aData)
	{
		if (is_array($aData)) $this->_aData = $aData;
	}
	#region Route Result Setter
	/**
	 * @param $sModule
	 * @return bool
	 * @throws ModuleNotFoundException
	 */
	public function setModule($sModule)
	{
		//Default Module
		if (str_nullorwhitespace($sModule)) {
			$this->_sModule = null;

			return true;
		}
		///TODO ModuleName Security Check
		$sModule        = strtolower(trim($sModule, '\\/:*?!"<>'));
		$this->_sModule = $sModule;
	}

	/**
	 * @param $sController
	 */
	public function setController($sController)
	{
		///TODO ControllerName Security Check
		$this->_sController = $sController;
	}

	/**
	 * @param $sAction
	 */
	public function setAction($sAction)
	{
		///TODO ActionName Security Check
		$this->_sAction = $sAction;
	}

	public function setType($sType)
	{
		//$sType = strtolower(trim($sType, '\\/'));
		try {
			if (class_exists('Raindrop\ActionResult\\' . $sType)) {
				$this->_sType = $sType;

				return true;
			}
		} catch (FatalErrorException $ex) {
		} catch (FileNotFoundException $ex) {
		}

		return false;
	}

	/**
	 * Is Ajax Request(Request by XmlHttpRequest)
	 *
	 * @return bool
	 */
	public function isAjax()
	{
		if ($this->_bIsAjax === null) {
			$this->_bIsAjax =
				$this->getType()=='Json' ||
				(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
				(!empty($_SERVER['HTTP_ACCEPT']) && str_beginwith($_SERVER['HTTP_ACCEPT'], 'application/json'));
		}

		return $this->_bIsAjax;
	}
	#endregion

	#region Route Result Getter
	/**
	 *
	 */
	public function getModule()
	{
		return $this->_sModule;
	}

	/**
	 *
	 */
	public function getController()
	{
		return $this->_sController;
	}

	/**
	 *
	 */
	public function getAction()
	{
		return $this->_sAction;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->_sType;
	}
	#endregion

	#region ArrayAccess
	public function offsetExists($mOffset)
	{
		$mOffset = strtolower($mOffset);

		return array_key_exists($mOffset, $this->_aQuery) OR array_key_exists($mOffset, $this->_aData);

	}
	public function offsetGet($mOffset)
	{
		$mOffset = strtolower($mOffset);

		if(array_key_exists($mOffset, $this->_aQuery)){
			return $this->_aQuery[$mOffset];
		}
		else if(array_key_exists($mOffset, $this->_aData)){
			return $this->_aData[$mOffset];
		}
		else{
			return null;
		}
	}
	public function offsetSet($mOffset, $mValue)
	{
		return false;
	}
	public function offsetUnset($mOffset)
	{
		return false;
	}
	#endregion
}