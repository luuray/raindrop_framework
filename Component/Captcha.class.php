<?php
/**
 * Raindrop Framework for PHP
 *
 * Captcha Image Generator
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component;

use Raindrop\Model\CaptchaImage;

class Captcha
{
	const TYPE_PNG = 0;
	const TYPE_JPEG = 1;
	const TYPE_GIF = 2;

	public static function GetImage($iLen = 4, $iWidth = 120, $iHeight = 40, $iType = Captcha::TYPE_PNG)
	{
		$sCaptcha = RandomString::GetUnconfused($iLen);
		$iFountSize = ceil($iHeight/1.5);
		$oImg     = new \Imagick();
		$oImg->newImage($iWidth, $iHeight, new \ImagickPixel('#cccccc'));

		//draw
		$oImgDrawer = new \ImagickDraw();
		$oImgDrawer->setFont(CorePath . '/Misc/Font/SourceSansPro-Regular.otf');
		$oImgDrawer->setFontSize($iFountSize);


		//draw char
		for ($i = 0; $i < $iLen; $i++) {
			$_char = substr($sCaptcha, $i, 1);
			$oImg->annotateImage($oImgDrawer, ($iFountSize* $i) + 10, ceil($iHeight / 2) + 5, mt_rand(-60, 60), $_char);
		}

		//make some confusing
		$oImg->swirlImage(mt_rand(0, 20));

		for ($i = 0; $i < rand(2, 10); $i++) {
			$oImgDrawer->line(mt_rand(0, $iWidth), mt_rand(0, $iHeight), mt_rand(0, $iWidth), mt_rand(0, $iHeight));
		}

		$oImg->drawImage($oImgDrawer);

		switch ($iType) {
			case Captcha::TYPE_PNG:
				$oImg->setImageFormat('png');
				break;
			case Captcha::TYPE_JPEG:
				$oImg->setImageFormat('jpeg');
				break;
			case Captcha::TYPE_GIF:
				$oImg->setImageFormat('gif');
				break;
		}

		$oImg->setImageType(\Imagick::IMGTYPE_OPTIMIZE);

		return new CaptchaImage($sCaptcha, $oImg->getImageBlob());
	}
}