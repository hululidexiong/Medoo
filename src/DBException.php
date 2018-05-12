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
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'DBException: ';
    }
}