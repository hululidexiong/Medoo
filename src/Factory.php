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
 * support type : int tinyint char varchar float double text json
 *
 */
class Factory extends DbMy
{

    protected static $pattern_entity_alias = '/(?<fullname>.+?)\sas\s(?<alias>[_a-zA-Z0-9]+)/';
    //parse sql to object   pdo 中 只有双引号 " 和 单引号 '
    protected static $pattern  = '/"(?<column>.*?)"\s(?<type>[a-zA-Z]*?)(?<length>\([0-9,]*?\))?\s(?<other>.*)/';
    protected static $pattern_comment = '/(?:COMMENT\s)\'(?<comment>.*?)\',?/';
    //protected static $pattern_default = "/(?:DEFAULT\s)(?<default>(?'dot'')([\S]+?)(?'-dot'')|NULL)(?(dot)(?!)),?/";
    protected static $pattern_default = "/(?:DEFAULT\s)(?<default>'.*?'|NULL),?/";
    protected static $pattern_increment = 'AUTO_INCREMENT';
    //(?:DEFAULT\s)'?(?<default>[\S]*)'?,?

    protected static $pattern_not_null = 'NOT NULL';//自动生成的表结构都是Not null的


    protected $factory_entities = [];
    protected $factory_obj_entities = [];
    protected $diff = []; // 现有表结构
    protected $lineup = []; // 最新表结果
    protected $lineup_exec = []; //待执行sql

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

    public function run( $exec = false ){

        $this->create_object_for_entity();
        foreach ( $this->factory_obj_entities as $item ){
            $entityObject = $item[0];
            $className = get_class( $entityObject );

            if( $item[1]){
                $tableName = $item[1];
            }else{
                //检查命名空间
                $tableName = $className;
                if(strpos( $className , "\\")!==false){
                    $r = new \ReflectionClass( $className );
                    $tableName = $r->getShortName();
                }
            }

            //写入表属性
            $this->lineup[ $tableName ] = [];
            foreach( $entityObject as $key => $val){

                //过滤内部属性
                if( in_array($key , ['_option'])){
                    continue;
                }

                if( empty( $val['Type'] ) ){
                    throw new DBException(' Column '. $key . ' Type does not exist!');
                }

                $this->lineup[$tableName][$key] = [
                    'Type' => $val['Type'],
                    'Length' => $val['Length'],
                    'Default' => isset($val['Default']) ? $val['Default'] : null ,
                    'Comment' => isset($val['Comment']) ? $val['Comment'] : '' ,
                    'AUTO_INCREMENT' => isset($val['AUTO_INCREMENT']) ? $val['AUTO_INCREMENT'] : false ,
                ];
            }

            if(count( $this->lineup[$tableName] ) == 0){
                //空表跳过
                continue;
            }

            if( $this->existTable( $tableName ) ){
                $this->tableToObject( $tableName );
                //对比现有表结构
//                var_dump( $this->diff[ $tableName ] );
//                var_dump( $this->lineup[$tableName] );
                foreach( $this->lineup[$tableName] as $key => $item){

                    $statement = $this->EntityToSqlStatement( $item );

                    if( isset($this->diff[ $tableName ][ $key ] ) && $this->diff[ $tableName ][ $key ]){
                        $type = $default = $auto_increment = $comment = '' ;

//                        var_dump( $this->diff[ $tableName ][ $key ]['Type'] != $statement['Type'] );
//                        var_dump( $this->diff[ $tableName ][ $key ]['Type'] );
//                        var_dump( $statement['Type'] );

//                        var_dump(  $this->diff[ $tableName ][ $key ]['Default'] != $statement['Default'] );
//                        var_dump( $this->diff[ $tableName ][ $key ]['Default'] );
//                        var_dump( $statement['Default'] );

//                        var_dump( $this->diff[ $tableName ][ $key ]['Increment'] != $statement['Increment'] );
//                        var_dump( $this->diff[ $tableName ][ $key ]['Increment'] );
//                        var_dump( $statement['Increment'] );
//
//                        var_dump( $this->diff[ $tableName ][ $key ]['Comment'] != $statement['Comment'] );
//                        var_dump( $this->diff[ $tableName ][ $key ]['Comment'] );
//                        var_dump( $statement['Comment'] );

                        if( $this->diff[ $tableName ][ $key ]['Type'] != $statement['Type']){
                            $type = $statement['Type'];
                        }
                        if( $this->diff[ $tableName ][ $key ]['Default'] != $statement['Default'] ){
                            $default = $statement['Default'] ;
                        }
                        if( $this->diff[ $tableName ][ $key ]['Increment'] != $statement['Increment'] ){
                            $auto_increment = $statement['Increment'] ;
                        }
                        if( $this->diff[ $tableName ][ $key ]['Comment'] != $statement['Comment'] ){
                            $comment = $statement['Comment'] ;
                        }
                        if( $type  || $default || $auto_increment || $comment ){
                            $type = $type?:($this->diff[ $tableName ][ $key ]['Type']??'');
                            $default = $default?:($this->diff[ $tableName ][ $key ]['Default']??'');
                            $auto_increment = $auto_increment?:($this->diff[ $tableName ][ $key ]['Increment']??'');
                            $comment = $comment?:($this->diff[ $tableName ][ $key ]['Comment']??'');

                            array_push( $this->lineup_exec , $this->format('ALTER TABLE %t MODIFY %i %i %i %i %i' , [ $tableName , $key , $type , $default , $auto_increment , $comment]));
                        }elseif( !empty($default . $auto_increment . $comment)){
                            array_push( $this->lineup_exec , $this->format('ALTER TABLE %t MODIFY %i %i %i %i %i' , [ $tableName , $key , $this->diff[ $tableName ][ $key ]['Type'] , $default , $auto_increment , $comment]));
                        }

                    }else{
                        $type = $statement['Type'];
                        $default = $statement['Default'] ;
                        $auto_increment =  $statement['Increment'] ;
                        $comment = $statement['Comment'] ;
                        array_push( $this->lineup_exec , $this->format('ALTER TABLE %t ADD %i %i %i %i %i' , [ $tableName , $key , $type , $default , $auto_increment , $comment]));
                    }
                }

            }else{
                //直接创建表
                $statement_create_sql = $this->format(' CREATE TABLE %t (' , [ $tableName ]);
                $statement_create_sql_inner = [];
                foreach( $this->lineup[$tableName] as $key => $item) {
                    $statement = $this->EntityToSqlStatement($item);
                    $type = $statement['Type'];
                    $default = $statement['Default'] ;
                    $auto_increment =  $statement['Increment'] ;
                    $comment = $statement['Comment'] ;
                    //`id` int(11) NOT NULL AUTO_INCREMENT,
                    $statement_create_sql_inner[] = $this->format(' %i %i %i %i %i ' , [ $key , $type , $default , $auto_increment , $comment]) . ",\n";
                }
                $statement_create_sql_inner_end = trim( array_pop($statement_create_sql_inner) , ",\n" );
                foreach($statement_create_sql_inner as $sql){
                    $statement_create_sql .= $sql;
                }
                $statement_create_sql .= $statement_create_sql_inner_end;
                $statement_create_sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
                $this->lineup_exec[] = $statement_create_sql;


                //插入初始化数据
                $statement_create_sql = '';
                $default_data = $entityObject->default_data();

                foreach( $default_data as$data){
                    $columns = [];
                    $values = [];
                    foreach($data as $column => $val){
                        $columns[] = $column;
                        $values[] = $val;
                    }
                    $columns_str = implode(',' , $columns);
                    $_this = $this;
                    $values_arr = array_map(function( $arg )use($_this){
                        return $_this->format(' %s ',[$arg]);
                    } ,$values );
                    $values_str = implode(',' , $values_arr);
                    $statement_create_sql .= $this->format('insert into %t(%i) values(%i)' , [ $tableName , $columns_str , $values_str]) . ";\n";
                }

                $this->lineup_exec[] = $statement_create_sql;
            }
        }

        print_r($this->lineup_exec);
        if($exec){
            $this->run_exec_sql();
        }
    }

    protected function run_exec_sql(){
        foreach( $this->lineup_exec as $sql){
            $this->query( $sql );
            var_dump( $this->error() );
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
        //var_dump( $arr_sql_line );
        foreach( $arr_sql_line as $line){
            $matches = [];
            if( preg_match (  self::$pattern ,  $line , $matches ) ){
                $this->diff[ $sql_table_name ][$matches['column']] = [
                    'Type' => $matches['type'],
                    'Length' => substr( $matches['length'],1,-1)
                ];

                if(strpos( $line , self::$pattern_increment) !==false){
                    $this->diff[ $sql_table_name ][$matches['column']]['AUTO_INCREMENT'] = true;
                }else{
                    $this->diff[ $sql_table_name ][$matches['column']]['AUTO_INCREMENT'] = false;
                }

                $match_default = [];
                if( preg_match (  self::$pattern_default ,  $line , $match_default ) ){
                    $this->diff[ $sql_table_name ][$matches['column']]['Default'] = $match_default['default'] == 'NULL' ? null : trim( $match_default['default'] , '\'');
                }else{
                    $this->diff[ $sql_table_name ][$matches['column']]['Default'] = null;
                }

                $match_comment = [];
                if( preg_match (  self::$pattern_comment ,  $line , $match_comment ) ){
                    $this->diff[ $sql_table_name ][$matches['column']]['Comment'] = $match_comment['comment'];
                }else{
                    $this->diff[ $sql_table_name ][$matches['column']]['Comment'] = '';
                }

                $statement = $this->EntityToSqlStatement( $this->diff[ $sql_table_name ][$matches['column']] );
                $this->diff[ $sql_table_name ][$matches['column']] = $statement;
            }
        }
        //var_dump($this->diff);
    }

    protected function EntityToSqlStatement( $item ){
        $item['Type'] = strtolower($item['Type']);
        switch($item['Type']){
            case 'longtext':
            case 'text':
            case 'json':
                $type =  $item['Type'] ;
                break;
            default:
                $type = $item['Type'] . '('.$item['Length'].')';
        }
        if($item['AUTO_INCREMENT']){
            $auto_increment = ' PRIMARY KEY AUTO_INCREMENT ';
            $default = '';
        }else{
            $auto_increment = '';
            $default = $item['Default'] === null ? ' DEFAULT NULL ' : $this->format(' NOT NULL DEFAULT %s' , [ $item['Default'] ]);
        }

        $comment = $item['Comment'] ? $this->format(' COMMENT %s ' , [ $item['Comment'] ]) : '';
        return [
            'Type' => $type,
            'Default' => $default,
            'Increment' => $auto_increment,
            'Comment' => $comment
        ];
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
//        echo $sql =  $this->format('show tables like %s;' , [ trim($this->tableQuote($table) , '"') ])  ;
        return boolval(  $this->fetchFirst( 'show tables like %s;' , [ trim($this->tableQuote($table) , '"') ] ) );
    }

    protected function diff_construction(){

    }

    protected function create_object_for_entity(){
        foreach( $this->factory_entities  as $entity ){

            $alias = null;
            $matches = [];
            if(preg_match(self::$pattern_entity_alias , $entity , $matches)){
                $entity = $matches['fullname'];
                $alias = $matches['alias'];
            }

            //优先检查命名空间（传入的是命名空间形式 ）走autoload ， 或者类已存在
            if( class_exists( $entity ) ){
                $entity_class = $entity;
            }else{
                $entity_full_name = $entity;
                $separator = strrpos( $entity , DIRECTORY_SEPARATOR);
                if($separator !== false){
                    $entity_class = substr( $entity , $separator + 1) ;
                }else{
                    $entity_class = $entity;
                }

                $file = $entity_full_name . '.php';
                require $file;

                if( !class_exists ( $entity_class , false) ){
                    throw new DBException(  $entity_class . ' Entity does not exist , in ' .$entity_full_name );
                }
            }


            $object = new $entity_class();
            if(! $object instanceof Entity){
                throw new DBException(  $entity_class . ' is  not instance of Entity!' );
            }
            array_push($this->factory_obj_entities  ,  [$object , $alias] );
        }
    }

}