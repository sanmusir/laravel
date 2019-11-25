<?php
# 本示例为IOC容器示例
# by sanmu
# 2019-11-25 20:03:55

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

//实例化容器
$ioc = new Ioc();
//绑定接口的实现
$ioc->bind('Log','FileLog');
//绑定User 到容器
$ioc->bind('user','User');

$user = $ioc->make('user');
$user->login();

?>
