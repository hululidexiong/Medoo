<?php
/**
 * Created by PhpStorm.
 * User: Bear <hululidexiong@163.com>
 * Date: 2018/5/6
 * Time: 11:14
 *
 * support type : int tinyint char varchar float double text json
 */

class Example2 extends \MedMy\Entity{

    function default_data()
    {
        return [
            ['name' => 'zhangsan' , 'sex' => 1],
            ['name' => 'chenting' , 'sex' => 2],
        ];
    }

    //  AUTO_INCREMENT 默认主键（ primary key ）
    public $id = [
        'Type'=>'int',
        'Length'=>11,
        'AUTO_INCREMENT' => true
    ];

    public $name = [
        'Type'=>'varchar',
        'Length'=>255,
        'Default'=>'',
        'Comment'=> '名字',
        'ValidateMode' => null
    ];
    public $sex = [
        'Type'=>'TINYINT',
        'Length'=>1,
        'Default'=>'0',
        'Comment'=> '性别 0保密 1男 2女 3 兽',
        'ValidateMode' => null
    ];

    public $age = [
        'Type'=>'int',
        'Length'=>10,
        'Default'=>'0',
        'Comment'=> '年龄',
        'ValidateMode' => null
    ];
}