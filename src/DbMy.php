<?php
/**
 * Created by PhpStorm.
 * User: BBear
 * Date: 2017/11/8
 * Time: 11:18
 */

namespace MedMy;


class DbMy extends Medoo
{
    protected static $db = [];
    protected static $config = [];
    protected static $currentDb;

    function __construct( $options )
    {
        parent::__construct($options);
    }

    public static function setConfig( $config ){
        self::$config = $config;
    }
    static protected function getConfig()
    {
        return self::$config ? self::$config : [
//            [
//                // required
//                'database_type' => 'mysql',
//                'database_name' => 'RQXiaoS',
//                'server' => '10.255.255.79',
//                'username' => 'RQXiaoS',
//                'password' => '123123',
//                // [optional]
//                'charset' => 'utf8',
//                'port' => 3306,
//                'prefix' => ''
//            ]
        ];
    }



//    static function _init_all(){
//        foreach( self::getConfig() as $i => $config ){
//            self::_init($i);
//        }
//    }


    public function __call($name, $arguments)
    {
        return parent::__call($name, $arguments);
    }


    static function _init($select_db = 0)
    {
        if( !isset( self::$db[$select_db] ) ){
            $config = self::getConfig();

            if(!isset($config[$select_db])){
                throw new DBException( $select_db .' config dose not exist!');
            }
            if ( !isset(self::$db[$select_db]) ||  !self::$db[$select_db] instanceof self) {
                self::$currentDb = self::$db[$select_db] = new static($config[$select_db]);
            }
        }else{
            self::$currentDb = self::$db[$select_db];
        }
        return self::$currentDb;
        //self::$currentDb = self::$db[$select_db];
    }

    /**
     * note :
     * @param int $select_db
     * @return DbMy
     */
    static function e( $select_db = 0 ){


        if(empty(self::$db[$select_db])){
            self::_init($select_db);
        }
        self::$currentDb = self::$db[$select_db];
        return self::$db[$select_db];
    }

    /**
     *
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
        $e = parent::insert( $table, $data );
        $id = $this->id();
        return $id;
    }

    public function format( $sql, array $arg) {
        $count = substr_count($sql, '%');
        if (!$count) {
            return $sql;
        } elseif ($count > count($arg)) {
            throw new DBException('SQL string format error! This SQL need "' . $count . '" vars to replace into.', 0, $sql);
        }
        $len = strlen($sql);
        $i = $find = 0;
        $ret = '';
        while ($i <= $len && $find < $count) {
            if ($sql{$i} == '%') {
                $next = $sql{$i + 1};
                if ($next == 't') {
                    $ret .= $this->tableQuote($arg[$find]);
                } elseif ($next == 's') {
                    $ret .= $this->quote(is_array($arg[$find]) ? serialize($arg[$find]) : (string) $arg[$find]);
                } elseif ($next == 'f') {
                    $ret .= sprintf('%F', $arg[$find]);
                } elseif ($next == 'd') {
                    $ret .= intval($arg[$find]);
                } elseif ($next == 'i') {
                    $ret .= $arg[$find];
                } elseif ($next == 'n') {
                    if (!empty($arg[$find])) {
                        $ret .= is_array($arg[$find]) ? implode(',', $this->quote($arg[$find])) : $this->quote($arg[$find]);
                    } else {
                        $ret .= '0';
                    }
                } else {
                    $ret .= $this->quote($arg[$find]);
                }
                $i++;
                $find++;
            } else {
                $ret .= $sql{$i};
            }
            $i++;
        }
        if ($i < $len) {
            $ret .= substr($sql, $i);
        }
        return $ret;
    }


    /**
     * note :this->query()->fetch
     * @param $sql
     * @param array $args
     * @param int $fetch_style
     * @return array
     */
    public function fetchFirst( $sql , $args = [] , $fetch_style = \PDO::FETCH_ASSOC ){
        // FETCH_COLUMN
        return $this->query( $this->format($sql , $args ) )->fetch( $fetch_style );
    }

    /**
     * note :
     * @param $sql
     * @param array $args
     * @param int $fetch_style
     * @return array
     */
    public function fetchAll($sql , $args = [] , $fetch_style = \PDO::FETCH_ASSOC ){
        return $this->query( $this->format($sql , $args ) )->fetchAll( $fetch_style );
    }

    public function resultFirst( $sql , $args = []){
        return $this->fetch( $sql , $args , \PDO::FETCH_COLUMN);
    }

    public function close(){

    }
}