<?php
/**
 * Raindrop Framework for PHP
 *
 * Media Service Component of WeChat Module
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Component;


use Raindrop\Application;
use Raindrop\Exceptions\NotImplementedException;
use Raindrop\Exceptions\RuntimeException;
use Raindrop\Logger;

class MediaService extends Service
{
	protected function _initialize()
	{
	}

	public function downloadTempVideo($iMediaId, $sSavePath=null)
	{
		throw new NotImplementedException();
	}

	/**
	 * @param $iMediaId
	 * @param null $sSavePath
	 *
	 * @return null|string
	 * @throws RuntimeException
	 */
	public function downloadTempAudio($iMediaId, $sSavePath=null)
	{
		if($sSavePath == null) {
			$sSavePath = tempnam(sys_get_temp_dir(), AppName);
		}
		else{
			$sPath = pathinfo($sSavePath, PATHINFO_DIRNAME);
			if(!file_exists($sPath)){
				mkdir($sPath, 0755, true);
			}
		}
		$rFile = @fopen($sSavePath, 'w');
		if($rFile == false){
			throw new RuntimeException('file_not_writable');
		}

		$sTarget = sprintf('https://api.weixin.qq.com/cgi-bin/media/get?access_token=%s&media_id=%s', $this->_oComponent->APIToken, $iMediaId);
		$rRequest = curl_init($sTarget);
		curl_setopt_array($rRequest, [
			//CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FAILONERROR    => true,
			CURLOPT_HTTPGET        => true,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_FILE           => $rFile
		]);

		$bResult = @curl_exec($rRequest);
		$sError = curl_error($rRequest);

		curl_close($rRequest);

		if(Application::IsDebugging()){
			$aDebugBacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1];

			Logger::Message(
				$aDebugBacktrace['class'] . $aDebugBacktrace['type'] . $aDebugBacktrace['function']
				. '[' . $this->_oComponent->getName() . ']:request=>' . $sTarget . ', response=>' . $sSavePath
				. ' =>length: ' . filesize($sSavePath) . ($bResult == false ? ' error=>' . $sError : null));
		}

		if ($bResult == true) {
			return $sSavePath;
		} else {
			throw new RuntimeException($sError);
		}
	}
}