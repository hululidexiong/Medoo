<?php
/**
 * Created by PhpStorm.
 * User: mhx
 * Date: 2017/11/8
 * Time: 11:28
 */

namespace Medoo;


class DBException extends \Exception
{
    function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}