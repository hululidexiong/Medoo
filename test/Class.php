<?php
/**
 * Created by PhpStorm.
 * User: Bear <hululidexiong@163.com>
 * Date: 2018/4/22
 * Time: 12:58
 */

class Base
{
    /**
     * 类实例化（单例模式）
     */
    public static function instance()
    {
        static $_instance = array();

        $classFullName = get_called_class();
        if (!isset($_instance[$classFullName])) {

            // $_instance[$classFullName] = new $classFullName();
            // 1、先前这样写的话，PhpStrom 代码提示功能失效；
            // 2、并且中间变量不能是 数组，如 不能用 return $_instance[$classFullName] 形式返回实例对象，否则 PhpStrom 代码提示功能失效；
            $instance = $_instance[$classFullName] = new self();
            return $instance;
        }

        return $_instance[$classFullName];
    }
    function a(){

    }
}
Base::instance()->a();