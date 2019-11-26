<?php
# 本示例为Facade示例
# by sanmu
# 2019-11-26 00:28:24

//定义一个日志接口
interface Log
{
	public function write();
}

//文件记录实现接口
class FileLog implements Log
{
	public function write() 
	{
		echo 'write log with file .';
	}
}

//定义用户类
class User
{
	protected $logHandle;

	//控制反转，所依赖的类由外部传入
	public function __construct(Log $handle) 
	{
		$this->logHandle = $handle;
	}

	public function login()
	{
		//登陆操作
		//记录日志
		$this->logHandle->write();
	}
}

###Ioc###

class Ioc 
{
	//绑定数组
	public $binding = [];

	//绑定
	public function bind($abstract, $concrete)
	{
        $this->binding[$abstract] = function ($ioc) use ($concrete) {
            return $ioc->build($concrete);
        };
	}

	//解析
	public function make($abstract)
	{
        $concrete = $this->binding[$abstract];
        return $concrete($this);
	}

	//实例化对象
	public function build($concrete) 
	{
		$reflector = new ReflectionClass($concrete);
		$constructor = $reflector->getConstructor();
		if (is_null($constructor)) {
			return $reflector->newInstanceArgs();
		}
		$dependencies = $constructor->getParameters();
		$instances = $this->getDependencies($dependencies);
		return $reflector->newInstanceArgs($instances);	 
	}

	public function getDependencies($paramters)
	{
		$dependencies = [];
    	foreach ($paramters as $paramter) {
       		$dependencies[] = $this->make($paramter->getClass()->name);
       	}
    	return $dependencies;
	}

}

class UserFacade
{
	//Ioc容器
	protected static $ioc;

	public static function setIoc($ioc)
	{
		self::$ioc = $ioc;
	}

    //返回user在容器中绑定的key
	public static function getFacadeAccessor()
	{
		return 'user';
	}

	//静态方法的调用
	public static function __callStatic($method, $args)
	{
		$instance = self::$ioc->make(self::getFacadeAccessor());
		return  $instance->$method(...$args);
	}
}

$ioc = new Ioc();
$ioc->bind('Log','FileLog');
$ioc->bind('user','User');

UserFacade::setIoc($ioc);

UserFacade::login();

?>
