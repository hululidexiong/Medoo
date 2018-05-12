<?php
/**
 * Created by PhpStorm.
 * User: Bear <hululidexiong@163.com>
 * Date: 2018/4/22
 * Time: 14:00
 */

require __DIR__ . '/vendor/autoload.php';

$config = [
    [
        // required
        'database_type' => 'mysql',
        'database_name' => 'RQXiaoS',
        'server' => '10.255.255.79',
        'username' => 'RQXiaoS',
        'password' => '123123',
        // [optional]
        'charset' => 'utf8',
        'port' => 3306,
        'prefix' => 'hhs_'
    ]
];
\MedMy\DbMy::setConfig( $config );

$sql = \MedMy\DbMy::e()->format( 'select * from %t limit 10' , ['ad'] );
echo $sql . "\n";
//DbMy::e()->fetchAll
echo json_encode( \MedMy\DbMy::e()->fetchAll( $sql ) );