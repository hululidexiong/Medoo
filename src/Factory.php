<?php
/**
 * Created by PhpStorm.
 * User: BBear
 * Date: 2017/11/8
 * Time: 11:18
 */

namespace MedMy;


/**
 * Class Factory
 * @package MedMy
 *
 *
 *
 * CREATE TABLE `First` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`json_data` json NOT NULL,
`text_data` text NOT NULL,
`tinyint_data` tinyint(1) NOT NULL DEFAULT '0',
`varchar_data` varchar(255) NOT NULL,
`float_data` float(3,2) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
 *
 * CREATE TABLE `First` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`json_data` json NOT NULL,
`text_data` text NOT NULL,
`tinyint_data` tinyint(1) NOT NULL DEFAULT '0',
`varchar_data` varchar(255) DEFAULT NULL COMMENT '字符串',
`float_data` float(3,2) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `varchar_data_2` (`varchar_data`),
KEY `varchar_data` (`varchar_data`),
KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8
 *
 * ALTER TABLE `First` CHANGE `varchar_data` `varchar_data` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
 *
 * ALTER TABLE `First` ADD INDEX(`varchar_data`);
 * ALTER TABLE `First` CHANGE `varchar_data` `varchar_data` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '字符串';
 *
 * `(.*?)`\s([a-zA-Z]*?)(\([0-9,]*?\))?\s(.*)
 *
 */
class Factory extends DbMy
{

    //parse sql to object   pdo 中 只有双引号 " 和 单引号 '
    protected static $pattern  = '/"(?<column>.*?)"\s(?<type>[a-zA-Z]*?)(?<length>\([0-9,]*?\))?\s(?<other>.*)/';
    protected static $pattern_commend = '/(?:COMMENT\s)\'(?<comment>.+?)\',?/';
    //protected static $pattern_default = "/(?:DEFAULT\s)(?<default>(?'dot'')([\S]+?)(?'-dot'')|NULL)(?(dot)(?!)),?/";
    protected static $pattern_default = "/(?:DEFAULT\s)(?<default>'.+?'|NULL),?/";
    protected static $pattern_increment = 'AUTO_INCREMENT';
    //(?:DEFAULT\s)'?(?<default>[\S]*)'?,?

    protected static $pattern_not_null = 'NOT NULL';//自动生成的表结构都是Not null的


    protected $factory_entities = [];
    protected $factory_obj_entities = [];
    protected $diff = []; // 现有表结构
    protected $lineup = [];

    /**
     * note : 为了代码提示
     * @param int $select_db
     * @return Factory
     */
    static function e( $select_db = 0 ){
        return parent::e( $select_db );
    }

    public function pushTable( $entity ){
        array_push($this->factory_entities  ,  $entity );
    }

    public function run(){

        $this->create_object_for_entity();

        foreach ( $this->factory_obj_entities as $object ){
            $tableName = get_class( $object );
            //写入表属性
            $this->lineup[ $tableName ] = [];
            foreach( $object as $key => $val){
                $this->lineup[$tableName][$key] = [
                    'Type' => $val['Type'],
                    'Length' => $val['Length'],
                    'Default' => isset($val['Default']) ? $val['Default'] : null ,
                    'Commend' => isset($val['Commend']) ? $val['Commend'] : '' ,
                    'AUTO_INCREMENT' => isset($val['AUTO_INCREMENT']) ? $val['AUTO_INCREMENT'] : false ,
                ];
            }

            if( $this->existTable( $tableName ) ){
                $this->tableToObject( $tableName );
                 //对比现有表结构
            }else{
                //直接创建表
            }
        }
    }

    public function tableToObject( $tableNmae ){
        $table = $this->showTable( $tableNmae );
        //var_dump($table);
        $sql_table_name = $table['Table'];
        $this->diff[ $sql_table_name ] = [];
        $sql_statement = $table['Create Table'];
        $arr_sql_line = explode("\n",$sql_statement);
        array_shift($arr_sql_line);
        array_pop($arr_sql_line);
        var_dump( $arr_sql_line );
        foreach( $arr_sql_line as $line){
            $matches = [];
            if( preg_match (  self::$pattern ,  $line , $matches ) ){
                $this->diff[ $sql_table_name ][$matches['column']] = [
                    'Type' => $matches['type'],
                    'Length' => $matches['length']
                ];
                if(strpos( $line , self::$pattern_increment) !==false){
                    $this->diff[ $sql_table_name ][$matches['column']]['AUTO_INCREMENT'] = true;
                }

                $match_default = [];
                if( preg_match (  self::$pattern_default ,  $line , $match_default ) ){
                    $this->diff[ $sql_table_name ][$matches['column']]['Default'] = $match_default['default'] == 'NULL' ? null : trim( $match_default['default'] , '\'');
                }

                $match_comment = [];
                if( preg_match (  self::$pattern_commend ,  $line , $match_comment ) ){
                    $this->diff[ $sql_table_name ][$matches['column']]['Commend'] = $match_comment['comment'];
                }
            }
        }
        var_dump($this->diff);
    }

    protected function showTable( $table ){
        $table = $this->fetchFirst( $this->format( 'show create table %t' , [$table]) );
        return $table;
    }

    protected function showIndexOfTable( $table ){
        $table = $this->fetchFirst( $this->format( 'show index from %t' , [$table]) );
        return $table;
    }

    protected function existTable( $table ){
        return boolval(  $this->fetchFirst( $this->format( 'show tables like %s' , [ $this->tableQuote($table) ]) ) );
    }

    protected function diff_construction(){

    }

    protected function create_object_for_entity(){
        foreach( $this->factory_entities  as $entity ){
            $file = $entity . '.php';
            require $file;
            if( !class_exists ( $entity , false) ){
                throw new DBException(  $entity . ' Entity does not exist!' );
            }

            $object = new $entity();
            if(! $object instanceof \Entity){
                throw new DBException(  $entity . ' is  not instance of Entity!' );
            }
            array_push($this->factory_obj_entities  ,  $object );
        }
    }

}