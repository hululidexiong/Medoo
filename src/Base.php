<?php
/**
 * Created by PhpStorm.
 * User: mhx
 * Date: 2017/11/8
 * Time: 11:18
 */

namespace Medoo;


class Base
{
    protected $db = [];
    public $dbPoint = 0;

    private static $_instance;

    function __construct($select_db = 0)
    {

    }

    //私有克隆函数，防止外办克隆对象
    private function __clone() {
    }

    //静态方法，单例统一访问入口
    static public function getInstance() {
        if ( !self::$_instance instanceof self) {
            self::$_instance = new self ();
        }
        return self::$_instance;
    }

    function getConfig()
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

    function _init($select_db = 0)
    {
        if(!isset($this->db[$select_db])){
            $config = $this->getConfig();

            if(!isset($config[$select_db])){
                throw new DBException( $select_db .'config dose not exist!');
            }
            $this->db[$select_db] = new Medoo($config[$select_db]);
        }
        return $this;
    }

    function _init_all(){
        foreach( $this->getConfig() as $i => $config ){
            $this->_init($i);
        }
        return $this;
    }


    /*
     * 如果传入的第二个参数为空 那个调用 DB时会发生异常
     */
    public function __call($name, $arguments)
    {
        array_unshift($arguments , $name);
        return call_user_func_array( [$this,'DB'] , $arguments);
    }

    static function e( $select_db = 0 ){

        $obj = self::getInstance();
        $obj->dbPoint = $select_db;

        return $obj;
    }

    public function field($str){
        return '`'.addslashes($str).'`';
    }

    protected function columnQuote($string)
    {
        preg_match('/(^#)?([a-zA-Z0-9_]*)\.([a-zA-Z0-9_]*)(\s*\[JSON\]$)?/', $string, $column_match);

        if (isset($column_match[ 2 ], $column_match[ 3 ]))
        {
            return '"' . $this->prefix . $column_match[ 2 ] . '"."' . $column_match[ 3 ] . '"';
        }

        return '"' . $string . '"';
    }


    /**
     * mhx
     * 说明：
     * echo Base\MDb::e()->uniqueInsert('shechem_test1' , [
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

        $column = implode( ',' , array_map([$this , 'field'] , array_keys($data) ));
        $value = implode(',' , array_map([$this , 'quote'] , $data ));
        $this->query( 'insert into '.$this->field($table). ' ('.$column .') select '.$value.' from dual where not exists(select 1 from '.$this->field($table). ' where ' . $unique . ')');
        $id = $this->DB('id');
        return $id;
    }

    public function insert( $table, $data = [] ){
        $this->DB('insert' , $table, $data );
        $id = $this->DB('id');
        return $id;
    }

    public function query($sql){
        return  $this->DB('query' , $sql );
    }

//    public function fetchALL( int $fetch_style = \PDO::FETCH_ASSOC , int $cursor_orientation = \PDO::FETCH_ORI_NEXT ,int $cursor_offset = 0 ){
//        return $this->DB('fetchALL' , $fetch_style ,$cursor_orientation , $cursor_offset);
//    }

    public function last_query(){
        return $this->db[ $this->dbPoint ]->last_query();
    }

    public function DB($method , $param1 = null , $param2 = null , $param3 = null , $param4 = null , $param5 = null , $param6 = null ){
        $data = call_user_func_array([ $this->db[$this->dbPoint] ,$method], [ $param1 , $param2 , $param3 , $param4 , $param5 , $param6 ]);
        $this->sErr();
        return $data;
    }

}