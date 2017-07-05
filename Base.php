<?php
//命名空间
namespace wangxuezhi007\model;
use \PDOException;
use \PDO;
//建立一个base类，用来连接数据库，获取数据库内容等
class Base{
    //建立一个私有静态属性$pdo,并赋值为null
    //方便全局调用
    private static $pdo = NULL;
    //建立一个私有的属性$table
    //方便全局调用
    private $table;
    //建立一个私有属性$where,并赋值空字符串
    //方便全局调用
    private $where= '';
    //建立一个构造方法
    //在外部实例化这个类时，会自动触发这个构造方法
    public function __construct($config,$table)
    {
        //调用当前类中的connect方法
        //在外部实例化这个类时，会自动触发这个构造方法，就会自动调用这个方法
        $this->connent($config);
        //调用当前类中的$table 属性，并赋值$table
        $this->table = $table;
    }

    //建立一个连接数据库的方法
    public function connent($config){
        //做if判断
        //如果属性$pdo已经连接数据库了，就不需要连接数据库了
        if (!is_null(self::$pdo)) return;
        //建立一个try  catch
        //为了捕获pdo的异常错误
        try{
            //定义一个变量$dsn
            //里面写入数据库的类型，服务器的地址，和访问的数据库名称
            $dsn = "mysql:host=" . $config['db_host'] . ";dbname=" . $config['db_name'];
            //定义一个变量$user
            //里面写入数据库的用户名
            $user = $config['db_user'];
            //定义一个变量$password
            //里面写入数据库的密码
            $password = $config['db_password'];
            //实例化PDO这个类，里面传入上面定义好的三个变量，用$pdo接收
            //因为如果要使用pdo连接数据库，就要建立一个对象
            $pdo = new PDO($dsn,$user,$password);
            //设置错误
            //因为catch只可以捕捉到pdo的异常类型，所以把以下代码的错误类型都变为pdo的异常错误类型，就可以被catch捕捉到了
            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            //设置字符集
            //为了不导致乱码的现象
            $pdo->query("SET NAMES " .$config['db_charset']);
            //把pdo对象存入私有的静态属性中
            //方便全局调用
            self::$pdo = $pdo;
        }catch(PDOException $e){
            //输出错误并结束代码运行
            exit($e->getMessage());
        }
    }
    //建立一个获取其中一条的方法
    public function where($where){
        //调用当前类中的私有属性，并赋值
        $this->where = "WHERE {$where} ";
        //把当前的对象返回出去
        return $this;
    }
    //建立获取全部数据的方法
    public function get(){
        //建立一条sql语句
        //因为要从mysql数据库中调取数据，就要用sql语句
        $sql = "SELECT * FROM {$this->table} {$this->where}";
        //调用当前类中的q方法，并把$sql作为参数传入q方法中
        return $this->q($sql);
    }
    //获得表的主键
    public function getPri(){
        //如果要获得表的主键，首先要查看表结构
        //所以调用当前类中的q方法，里面传入sql语句，可以获得表结构
        $desc = $this->q("DESC {$this->table}");
//        p($desc);
        //定义一个变量$priFieled，默认为空
        //为了存放主键字段，例如是cid还是aid
        $priFieled = '';
        //循环获得的表结构
        //为了获得主键字段
        foreach ($desc as $v){
            //做if判断
            //判断当$v['Key'] =='PRI'时，说明这是一个主键
            if ($v['Key'] =='PRI'){
                //$v['Field']代表主键字段，就把$v['Field']赋给我们上面定义好的$priFieled变量
                $priFieled = $v['Field'];
                //如果找到，终止本次循环
                break;
            }
        }
        //把获得的主键字段返回给当前类中的find方法
        return  $priFieled;
    }
    //建立一个寻找数据中一条数据的方法
    public function find($pri){
        //调用当前类中的getPri方法
        //获得主键的字段，用$priFieled接收
        //如果要寻找其中的一条数据，要获得主键的字段，例如是cid还是aid
        $priFieled = $this->getPri();
        //调用当前类中的where方法
        //里面传入的参数是 主键字段=用户传进来的id
        $this->where("{$priFieled}={$pri}");
        //组合一条sql语句
        //因为当前的这个方法，是要连接sql数据库使用的，要用到sql语句
        $sql = "SELECT * FROM {$this->table} {$this->where}";
        //调用当前类中的q方法，并把组合好的sql语句作为参数传入q方法中，并赋值给$data
        //因为q方法执行的是有结果集的操作，当前方法是一个有结果集的操作，所以调用q方法
        $data = $this->q($sql);
//        p($data);
        //把获得的数组转为一维数组
        //因为获得的是一个二维数组，二维数组的值是重复的，为了方便使用，转为一维数组
        $data = current($data);
//        p($data);
        //把获得的数组赋给当前类中的私有属性$data
        //为了方便在findArray方法中调用
        $this->data = $data;
        //返回当前的对象给findArray方法
        return $this;
    }
    //建立一个寻找数据中的某一条数据的方法
   public function findArray($pri){
        //调用当前类中的find方法
       //从这里接收find方法赋给$obj，因为find方法返回的是一个对象，所以在这里接收的也是一个对象
        $obj = $this->find($pri);
        //调用$obj对象中的data数据
       //因为我们接收到的是一个对象，但是返回时不能返回对象，要返回对象中的数组，
       //又因为find方法在返回对象时已经获得了数组，赋值给属性，也就是说在返回的这个对象中已经存在数组，所以直接调用对象中的数据，
       //返回给app\home\controller\Entry类中的arc方法
        return $obj->data;
   }
    //建立一个有结果集操作的方法
    //接收到get方法中传来的q方法
    public function q($sql){
        //建立一个try  catch
        //用来捕获pdo发生的异常错误
        try{
            //调用pdo中的qurey方法，把值返回给$result
            $result = self::$pdo->query($sql);
            //result得到的是一个数组，获取result中的关联数组
            $data = $result->fetchAll(PDO::FETCH_ASSOC);
            //把数组返回出去
            return $data;

        }catch(PDOException $e){
            //输出错误，并且终止代码运行
            exit($e->getMessage());
        }
    }
    //执行无结果集的操作方法，例如增删改
    public function e($sql){
        //建立try  catch
        //为了捕获pdo的异常错误
        try{
            //调用pdo对象中的exec方法（exec用来执行无结果集操作）
         return   self::$pdo->exec($sql);
        }catch(PDOException $e){
            //如果捕获到pdo的异常错误，就输出错误，并终止代码运行
            exit($e->getMessage());
        }
    }
}