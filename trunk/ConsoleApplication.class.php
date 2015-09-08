<?php
/**
 * Raindrop Framework for PHP
 *
 * Console Application
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

use Raindrop\Component\ConsoleDaemon;
use Raindrop\Exceptions\FatalErrorException;
use Raindrop\Exceptions\FileNotFoundException;

require_once 'Application.class.php';

final class ConsoleApplication extends Application
{
	protected $_sAccount = null;
	protected $_sPassword = null;

	protected function _initialize()
	{
		//Not CLI Request
		if (str_beginwith(php_sapi_name(), 'cli') == false) {
			@ob_clean();
			@header("HTTP/1.1 404 Not Found");
			exit(0);
		}
	}

	protected function _initializeIdentify()
	{
		try {
			if (Loader::Import('console.identify.php', AppDir)) {
				$sIdentifyName = AppName . '\ConsoleIdentify';

				$this->_oIdentify = $sIdentifyName::GetInstance();

				if ($this->_oIdentify instanceof Identify) {
				} else {
					throw new FatalErrorException('identify_not_defined');
				}
			} else {
				throw new FatalErrorException('identify_not_defined');
			}
		} catch (FileNotFoundException $ex) {
			throw new FatalErrorException('identifier_not_found');
		}
	}

	protected function _getRequest()
	{
		/**
		 * u/user => Identify Username
		 * p/password => Identify Password, Username Required
		 * t/target => Request Target(Model/Controller/Action?QueryString)
		 * a/argument => Request Argument(Like POST in WebRequest)
		 * h/help => Output Console Argument Info
		 */
		$aOpt = getopt('du::p::t::a::h', array('daemon', 'user::', 'password::', 'target::', 'argument::', 'help'));

		//output help message
		if (empty($aOpt) OR array_key_exists('h', $aOpt) OR array_key_exists('help', $aOpt)) {
			echo <<<HELP
-=-=-=-=- Raindrop Framework Console Access -=-=-=-=-
!!! Arguments !!!
-d/--daemon\t Run as Daemon Mode
-u/--user\t Identify username
-u/--password\t Identify password, Username required when this argument is signed
-t/--target\t Request target (Module/Controller/Action?QueryString)
-a/--argument\t Request arguments, format in "Key=Value", Multiple argument can be assign by multiple "argument" parameters
-h/--help\t Show this Message

!!! Notice !!!
FOR "Target":
\t With "ShortCut Argument" and "Long Argument" signed same time The FIRST signed "Long Argument" will take effect!
FOR "Argument":
\t All "ShortCut Argument" and "Long Argument" will be combine together;
\t Same "Keys" in "ShortCur Argument" will be replace by "Long Argument";
\t The last group with same "Key" in "Key=Value" argument will take effect;
\t "Value" should by url encoded

HELP;
			//Finish
			exit(0);
		}

		//Work Mode
		if (array_key_exists('daemon', $aOpt) OR array_key_exists('d', $aOpt)) {
			define('DAEMON_MODE', true);
		} else {
			define('DAEMON_MODE', false);
		}
		//Target Decide
		$sTarget = null;
		if (array_key_exists('target', $aOpt)) {
			$mTarget = $aOpt['target'];
			$sTarget = is_string($mTarget) ? $mTarget : array_shift($mTarget);
		} else if (array_key_exists('t', $aOpt)) {
			$mTarget = $aOpt['t'];
			$sTarget = is_string($mTarget) ? $mTarget : array_shift($mTarget);
		}
		//QueryString with Target
		$aQuery = array();
		if (strpos($sTarget, '?') !== false) {
			$sQueryString = null;
			list($sTarget, $sQueryString) = explode('?', $sTarget);
			$aQuery = parse_str($sQueryString, $aQuery);
		}

		//Argument
		$aData          = array();
		$funcDataDecode = function (&$aData, $sSource) {
			if (strpos($sSource, '=') == false)
				$this->_halt('Argument format error(no_key_value_delimiter_or_no_key_name):' . $sSource);

			list($sDKey, $sDValue) = explode('=', $sSource);

			if (str_nullorwhitespace($sDKey))
				$this->_halt('Argument format error(key_name_is_null_or_whitespace');

			$aData[strtolower(trim($sDKey))] = urldecode($sDValue);
		};

		if (array_key_exists('a', $aOpt)) {
			if (is_array($aOpt['a'])) {
				foreach ($aOpt['a'] AS $_item) {
					$funcDataDecode($aData, $_item);
				}
			} else if ($aOpt['a'] != false) {
				$funcDataDecode($aData, $aOpt['a']);
			}
		}
		if (array_key_exists('argument', $aOpt)) {
			if (is_array($aOpt['argument'])) {
				foreach ($aOpt['argument'] AS $_item) {
					$funcDataDecode($aData, $_item);
				}
			} else if ($aOpt['argument'] != false) {
				$funcDataDecode($aData, $_item);
			}
		}

		//Identify
		$oRequest = new ConsoleRequest($sTarget, null);
		$oRequest->setQuery($aQuery);
		$oRequest->setData($aData);

		return $oRequest;
	}

	protected function _run()
	{
		if (defined('DAEMON_MODE') AND DAEMON_MODE == true) {
			new ConsoleDaemon(Configuration::Get('System\Listen'));
		} else {

		}
	}

	protected function _finish()
	{
	}
}