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

namespace Raindrop\Exceptions\ORM;

use Raindrop\Exceptions\Database\DataModelException;
use Raindrop\ORM\Model;

class TableNotDefinedException extends DataModelException
{
	public function __construct($sDbConn, $sTable)
	{
		parent::__construct("DbConn:{$sDbConn}, Table:{$sTable}");
	}
}

class ColumnNotDefinedException extends DataModelException
{
	public function __construct($mModel, $sColumn)
	{
		if ($mModel instanceof Model) {
			parent::__construct("DbConn:{$mModel::getDbConnect()}, Table:{$mModel::getTableName()}, Column:{$sColumn}");
		} else {
			throw new DataModelException('model_not_found:' . is_object($mModel) ? get_class($mModel) : $mModel);
		}
	}
}