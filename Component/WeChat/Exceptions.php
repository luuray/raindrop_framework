<?php
/**
 * Raindrop Framework for PHP
 *
 * WeChat Exceptions
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component\WeChat\Exceptions;

use Raindrop\Exceptions\RuntimeException;

class InvalidAccessTokenException extends RuntimeException
{
}

abstract class APIErrorException extends RuntimeException
{
}

class APIRequestException extends APIErrorException
{
}

class APIResponseException extends APIErrorException
{
}

class MessageDecodingException extends RuntimeException
{
}