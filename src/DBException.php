<?php
/**
 * Created by PhpStorm.
 * User: BBear
 * Date: 2017/11/8
 * Time: 11:28
 */

namespace MedMy;


class DBException extends \PDOException
{
    function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}