<?php
//命名空间
namespace wangxuezhi007\model;
class Model{
    //建立一个私有静态属性
    //方便全局调用
    private static $config;
    //__call 实例化不存在的方法时会自动触发该方法
    public function __call($name, $arguments)
    {
        //调用当前类中的parseAction方法
        return self::parseAction($name,$arguments);
    }
    //__callStatic 该方法在调用不存在的静态方法时会自动触发该方法
    public static function __callStatic($name, $arguments)
    {
        //调用当前类中的parseAction方法
        return self::parseAction($name,$arguments);
    }
    //建立一个私有的静态属性，用来获得表名，只可以在内部调用
    private static function parseAction($name,$arguments){
        //get_called_class可以获得调用当前这个类的类名与命名空间，并赋值给$table
        //     例如：   system\model\Article
        $table = get_called_class();
        //把获得的值从右开始按照“/”截取，并去掉左边的“/”，并把这个值转为小写
        //因为类名与表名是一致的，又因为类名是大写的，但是表名是小写的，现在的$table要得到 的是表名，所以要转为小写
        $table= strtolower(ltrim(strrchr($table,'\\'),'\\'));
        //实例化Base这个类，并把值返回出去，
        return call_user_func_array([new Base(self::$config,$table),$name],$arguments);
    }
    public static function setConfig($config){
        self::$config = $config;
    }
}
