<?php
/**
 * Raindrop Framework for PHP
 *
 * HTML Paging Component
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Html;


use Raindrop\Application;

class Paging extends Html implements \Iterator
{
	protected $_iTotal = null;
	protected $_iRecent = 1;
	protected $_iDisplay = null;
	protected $_sUrlTpl = null;

	protected $_iPage = 1;
	protected $_iFirst = 0;
	protected $_iLast = 0;

	/**
	 * @param $iTotal
	 * @param $iRecent
	 * @param int $iDisplay
	 * @param null $sUrlTpl
	 */
	public function __construct($iTotal, $iRecent, $iDisplay = 5, $sUrlTpl = null)
	{
		$this->_iTotal   = intval($iTotal) <= 0 ? 0 : intval($iTotal);
		$this->_iRecent  = intval($iRecent) <= 1 ? 1 : intval($iRecent);
		$this->_iDisplay = intval($iDisplay) <= 3 ? 3 : intval($iDisplay);

		$this->_sUrlTpl = $sUrlTpl;

		$this->_resetPosition();
	}

	public function hasPrev()
	{
		return $this->_iRecent > 1;
	}

	public function getPrev()
	{
		$iPage = $this->_iRecent > 1 ? $this->_iRecent - 1 : 1;

		return (object)array(
			'page'       => $iPage,
			'is_current' => false,
			'url'        => $this->_urlGenerator($iPage));
	}

	public function hasNext()
	{
		return $this->_iRecent < $this->_iTotal;
	}

	public function getNext()
	{
		$iPage = $this->_iRecent < $this->_iTotal ? $this->_iRecent + 1 : $this->_iRecent;

		return (object)array(
			'page'       => $iPage,
			'is_current' => false,
			'url'        => $this->_urlGenerator($iPage));
	}

	public function getRecentPage()
	{
		return $this->_iRecent;
	}

	public function getTotalPage()
	{
		return $this->_iTotal;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current()
	{
		return (object)array(
			'page'       => $this->_iPage,
			'is_current' => $this->_iPage == $this->_iRecent,
			'url'        => $this->_urlGenerator($this->_iPage));
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next()
	{
		$this->_iPage++;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key()
	{
		return $this->_iPage;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid()
	{
		//max display & max page
		return $this->_iPage <= $this->_iLast OR $this->_iPage == 1;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind()
	{
		$this->_resetPosition();
	}

	/**
	 * Match the default position of paging
	 *
	 * @return int Position
	 */
	protected function _resetPosition()
	{
		$iFirst = ($this->_iRecent <= $this->_iDisplay / 2 + 1 ? 1 : ($this->_iRecent - $this->_iDisplay / 2));
		$iLast  = $iFirst + $this->_iDisplay - 1 >= $this->_iTotal ? $this->_iTotal : $iFirst + $this->_iDisplay - 1;

		if ($iLast >= $this->_iTotal) {
			$iFirst = $iLast - $this->_iDisplay + 1;
		}
		$this->_iFirst = $iFirst;
		$this->_iLast  = $iLast;
	}

	protected function _urlGenerator($iPage)
	{
		if ($this->_sUrlTpl != null) {
			return str_replace('%PAGE%', $iPage, $this->_sUrlTpl);
		} else {
			$oRequest = Application::GetRequest();

			return Url::Action(
				$oRequest->getModule(),
				$oRequest->getController(),
				$oRequest->getAction(),
				array_merge($oRequest->getQuery(), array('page' => $this->_iPage)));
		}
	}
}