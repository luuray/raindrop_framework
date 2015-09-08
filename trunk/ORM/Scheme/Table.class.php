<?php
/**
 * BoostQueue
 *
 *
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2015, Rainhan System
 * Site:
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\ORM\Scheme\Table;


use Raindrop\Exceptions\ORM\ColumnNotDefinedException;

final class Table implements \Serializable
{
	protected $_aColumns = array();
	protected $_mModel;

	public function __construct($mModel, $aColumnsDefine)
	{
		$this->_mModel = $mModel;
	}

	/**
	 * @param $sColumn
	 * @return Column
	 * @throws ColumnNotDefinedException
	 */
	public function __get($sColumn)
	{
		$sColumn = strtolower($sColumn);

		if (array_key_exists($sColumn, $this->_aColumns)) return $this->_aColumns[$sColumn];
		else throw new ColumnNotDefinedException($this->_mModel, $sColumn);
	}

	public function hasColumn($sColumn)
	{
		return array_key_exists(strtolower($sColumn), $this->_aColumns);
	}


	/**
	 * String representation of object
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize()
	{
		// TODO: Implement serialize() method.
	}

	/**
	 * Constructs the object
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 * The string representation of the object.
	 * </p>
	 * @return void
	 * @since 5.1.0
	 */
	public function unserialize($serialized)
	{
		// TODO: Implement unserialize() method.
	}
}