<?php
/**
 * Raindrop Framework for PHP
 *
 * Event Message of WeChat Component
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Message;


use Raindrop\Component\WeChat\Model\Message;

class Event extends Message
{
	//type:   subscribe, unsubscribe, subscribe      , scan    , LOCATION,
	//entkey:     *    ,     *      , qrscene_sceneId, scene_id,    *    ,
}