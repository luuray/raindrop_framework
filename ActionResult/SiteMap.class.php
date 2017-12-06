<?php
/**
 * Raindrop Framework for PHP
 *
 * Action Result of SiteMap
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */


namespace Raindrop\ActionResult;


use Raindrop\ActionResult;

class SiteMap extends ActionResult
{
	protected $_sContent = null;

	public function __construct($mData = null, $bIsIndex = false)
	{
		$oSiteMap = new \XMLWriter();
		$oSiteMap->openMemory();

		$oSiteMap->setIndent(true);

		$mData = is_array($mData) ? array_key_case($mData, CASE_LOWER) : [];

		$oSiteMap->startDocument('1.0', 'UTF-8');
		if ($bIsIndex) {
			$oSiteMap->startElementNS(null, 'sitemapindex', 'http://www.sitemaps.org/schemas/sitemap/0.9');
			foreach ($mData AS $_item) {
				if (array_key_exists('loc', $_item)) {
					$oSiteMap->startElement('sitemap');
					$oSiteMap->writeElement('loc', htmlentities($_item['loc'], ENT_QUOTES, 'UTF-8'));

					if (array_key_exists('lastmod', $_item)) {
						$iTime = is_int($_item['lastmod']) ? $_item['lastmod'] : strtotime($_item['lastmod']);
						if ($iTime !== false) {
							$oSiteMap->writeElement('lastmod', date('c', $iTime));
						}
					}
					$oSiteMap->endElement();
				}
			}
			$oSiteMap->endElement();
		} else {
			$oSiteMap->startElementNS(null, 'urlset', 'http://www.sitemaps.org/schemas/sitemap/0.9');
			foreach ($mData AS $_item) {
				if (array_key_exists('loc', $_item)) {
					$oSiteMap->startElement('url');
					//loc
					$oSiteMap->writeElement('loc', htmlentities($_item['loc'], ENT_QUOTES, 'UTF-8'));
					//lastMod
					if (array_key_exists('lastmod', $_item)) {
						$iTime = is_int($_item['lastmod']) ? $_item['lastmod'] : strtotime($_item['lastmod']);
						if ($iTime !== false) {
							$oSiteMap->writeElement('lastmod', date('c', $iTime));
						}
					}
					//changeFreq
					if (array_key_exists('changefreq', $_item) AND in_array($_item['changefreq'], ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])) {
						$oSiteMap->writeElement('changefreq', $_item['changefreq']);
					}
					//Priority
					if (array_key_exists('priority', $_item) AND $_item['priority'] >= 0 and $_item['priority'] <= 1) {
						$oSiteMap->writeElement('priority', $_item['priority']);
					}

					$oSiteMap->endElement();
				}
			}
			$oSiteMap->endElement();
		}

		$oSiteMap->endElement();

		$this->_sContent = $oSiteMap->outputMemory();
	}

	public function Output()
	{
		ob_clean();
		ob_start();

		@header('Content-type: application/xml', true, 200);

		echo $this->_sContent;
		ob_end_flush();
	}

	public function toString()
	{
		return $this->_sContent;
	}
}