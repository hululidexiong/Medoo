### Doc

function
    - setConfig
    - e
    - format
    - fetch
    - fetchAll
    - result
    - uniqueInsert
    - insert
    - close

 ###### example:
```
        use MedMy\DbMy;
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
         DbMy::setConfig( $config );
         echo json_encode( DbMy::e()->fetchAll( 'select * from %t limit 10' , ['ad'] )  );
```