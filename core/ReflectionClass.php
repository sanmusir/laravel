<?php
# 本示例为反射类实践示例
# by sanmu
# 2019-11-25 11:17:23

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
	public function __construct(FileLog $handle) 
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

$handle = new FileLog();
$user = new User($handle);
$user->login();

###使用反射类自动实例化所依赖类###

function make($concrete)
{
	$reflector = new ReflectionClass($concrete);
	$constructor = $reflector->getConstructor();
	if (is_null($constructor)) {
		return $reflector->newInstanceArgs();
	}
	$dependencies = $constructor->getParameters();
	$instances = getDependencies($dependencies);
	return $reflector->newInstanceArgs($instances);
}

function getDependencies($paramters)
{
	$dependencies = [];
    foreach ($paramters as $paramter) {
        $dependencies[] = make($paramter->getClass()->name);
    }
    return $dependencies;
}

$user = make('User');
$user->login();

?>
