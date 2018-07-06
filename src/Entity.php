<?php
/**
 * Created by PhpStorm.
 * User: Bear <hululidexiong@163.com>
 * Date: 2018/5/4
 * Time: 15:22
 */

namespace MedMy;

class Entity{



    public $_option = [
        'is_filter_null' => true,
        'get_attr_strip' => [],
        'get_field' => []
    ];

    function __construct()
    {
        $this->_option['get_attr_strip'] = $this->get_attr_strip();
    }

    //首次创建表时插入的数据
    function default_data(){
        return [];
    }

    //getAttribute 不会获取的数据
    function get_attr_strip(){
        return ['id' , 'inputtime'];
    }
}