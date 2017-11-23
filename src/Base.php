<?php
/**
 * Created by PhpStorm.
 * User: mhx
 * Date: 2017/11/8
 * Time: 11:18
 */

namespace LPdb;


class Base extends Medoo
{
    protected static $db = [];

    function __construct( $options )
    {
        parent::__construct($options);
    }

    static protected function getConfig()
    {
        return [
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
                'prefix' => ''
            ]
        ];
    }

    static function _init($select_db = 0)
    {
        if( !isset( self::$db[$select_db] ) ){
            $config = self::getConfig();

            if(!isset($config[$select_db])){
                throw new DBException( $select_db .'config dose not exist!');
            }
            if ( !self::$db[$select_db] instanceof self) {
                self::$db[$select_db] = new self($config[$select_db]);
            }

        }
    }

    static function _init_all(){
        foreach( self::getConfig() as $i => $config ){
            self::_init($i);
        }
    }


    public function __call($name, $arguments)
    {
        parent::__call($name, $arguments);
    }

    static function e( $select_db = 0 ){
        if(empty(self::$db[$select_db])){
            self::_init($select_db);
        }
        self::$db[$select_db];
    }

    /**
     * mhx
     * 说明：
     * echo Base::e()->uniqueInsert('shechem_test1' , [
     *       'note1' => 'unique2'
     *       ] , '`note1`='.Base\MDb::e()->quote('unique2'));
     *
     * 成功返回 id 否则 返回 0
     *
     * 注： 插入的数据必须被 unique 含盖
     * @param $table
     * @param $data
     * @param string $unique
     * @return mixed
     * @throws \Exception
     */
    public function uniqueInsert($table, $data , string $unique){
        if(empty($data) || empty($unique)){
            throw new DBException(' data and unique does not empty!');
        }
        $column = implode( ',' , array_map([$this , 'columnQuote'] , array_keys($data) ));
        $value = implode(',' , array_map([$this , 'quote'] , $data ));
        $this->query( 'insert into '.$this->tableQuote($table). ' ('.$column .') select '.$value.' from dual where not exists(select 1 from '.$this->tableQuote($table). ' where ' . $unique . ')');
        $id = $this->id();
        return $id;
    }

    public function insert( $table, $data = [] ){
        parent::insert( $table, $data );
        $id = $this->id();
        return $id;
    }

}