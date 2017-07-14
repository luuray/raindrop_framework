<?php
/**
 * Raindrop Framework for PHP
 *
 * News Message of WeChat Module
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Message;


use Raindrop\Component\WeChat\Model\IResponsible;
use Raindrop\Component\WeChat\Model\Message;
use Raindrop\Exceptions\RuntimeException;

class News extends Message implements IResponsible
{
	protected $_aArticles;

	protected function _initialize($aArticles = null)
	{
		$this->_aArticles = $aArticles;
	}

	public function getResponseData()
	{
		return $this->_aArticles;
	}

	/**
	 * @param $sTitle
	 * @param $sDescription
	 * @param $sPicUrl
	 * @param $sUrl
	 *
	 * @throws RuntimeException
	 */
	public function addArticle($sTitle, $sDescription, $sPicUrl, $sUrl)
	{
		if(count($this->_aArticles) > 10){
			throw new RuntimeException('too_much_article');
		}

		$this->_aArticles[] = [
			'Title'=>$sTitle,
			'Description'=>$sDescription,
			'PicUrl'=>$sPicUrl,
			'Url'=>$sUrl
		];
	}
}