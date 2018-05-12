<?php
/**
 * Created by PhpStorm.
 * User: Bear <hululidexiong@163.com>
 * Date: 2018/4/22
 * Time: 12:33
 */


declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use MedMy\DbMy;

final class BaseTest extends TestCase
{
    function init(){
        $config = [
                [
                // required
                'database_type' => 'mysql',
                'database_name' => 'MyTest',
                //'server' => '192.168.68.129',
                    'server' => 'localhost',
                'username' => 'root',
                'password' => '123123',
                // [optional]
                'charset' => 'utf8',
                'port' => 3306,
                'prefix' => ''
            ]
        ];
        DbMy::setConfig( $config );
    }
    function __construct()
    {
        parent::__construct();
        $this->init();
    }

    function testFetchAll(){
//        $sql = MedMy\DbMy::e()->format( 'select * from %t limit 10' , ['ad'] );
//        echo $sql . "\n";
        echo json_encode( DbMy::e()->fetchAll( 'select * from %t limit 10' , ['ad'] )  );
        echo "\n";
    }

    function testFetch(){
//        $sql = MedMy\DbMy::e()->format( 'select * from %t limit 10' , ['ad'] );
//        echo $sql . "\n";
        echo json_encode( DbMy::e()->fetch( 'select * from %t limit 10' , ['ad'] )  );
        echo "\n";
        echo json_encode( DbMy::e()->fetch( 'select * from %t limit 10' , ['ad']  , \PDO::FETCH_COLUMN )  );
        echo "\n";
    }

    function testShowTable(){

        $data = \MedMy\Factory::e()->fetchFirst('select * from First ');
        var_dump($data);

        $data = \MedMy\Factory::e()->fetchFirst( \MedMy\Factory::e()->format('show tables like %s' , ['Fir'] ) );
        var_dump($data);

        $data = \MedMy\Factory::e()->fetchFirst( \MedMy\Factory::e()->format('show tables like %s' , [ 'First']) );
        var_dump($data);

        echo \MedMy\Factory::e()->format('show tables like %s' , [ 'First']);
    }

    function testTableToObject(){
        $data = \MedMy\Factory::e()->tableToObject('First');
    }

    function testEntityToTable(){
        \MedMy\Factory::e()->pushTable( __DIR__ .'/EntityMode as Example');
        \MedMy\Factory::e()->pushTable( __DIR__ .'/EntityMode2 as Example2');
        \MedMy\Factory::e()->run();
    }
    function testRunEntityToTable(){
        \MedMy\Factory::e()->pushTable( __DIR__ .'/EntityMode as Example');
        \MedMy\Factory::e()->run(1);
    }
}
//phpunit --bootstrap test/bootstrap.php  test/BaseTest.php --disallow-test-output
//phpunit --bootstrap test/bootstrap.php  test/BaseTest.php --disallow-test-output --filter testEntityToTable
//phpunit --bootstrap test/bootstrap.php  test/BaseTest.php --disallow-test-output --filter testRunEntityToTable