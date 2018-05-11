### Doc

###### Entity 对象 
support type : int , tinyint , char , varchar , float , double , text , json
Factory 会识别 ： Type , Length , AUTO_INCREMENT , Default , Comment. 五个属性
###### example:
```

class Example extends \MedMy\Entity{

    //新建表后要插入的数据 （对于已存在的表不会执行此操作）
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
        'ValidateMode' => null,
        'ValidateNote' => null,
    ];
    public $sex = [
        'Type'=>'TINYINT',
        'Length'=>1,
        'Default'=>'0',
        'Comment'=> '性别 0保密 1男 2女',
        'ValidateMode' => null,
        'ValidateNote' => null,
    ];
}

```

Factory 借助 phpunit 执行
```
    
    function testEntityToTable(){
        //传入 Entity 类 Path  (省略 .php 后缀) ， 如果类名和文件名不一致 ， 可使用 as 关键字。
        \MedMy\Factory::e()->pushTable( __DIR__ .'/EntityMode as Example');
        //显示准备执行的 sql statement 用于确认。
        //run 接受一个参数，传入真时直接执行sql
        \MedMy\Factory::e()->run();
    }
    
    //执行sql
    function testRunEntityToTable(){
        \MedMy\Factory::e()->pushTable( __DIR__ .'/EntityMode as Example');
        \MedMy\Factory::e()->run(1);
    }
```

##### 不支持修改字段
    如 a to b 修改后 a b 共同存在 ， 暂没有change操作 ，相当于 add 了一个新的字段  原a列包括已有数据保留。 
##### 索引对比
    待开发