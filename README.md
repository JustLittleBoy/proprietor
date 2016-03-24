# proprietor
A flexible,simple PHP Framework<br/>
希望做一个灵活简单的PHP框架，满足快速开发需求<br/>
零、文件结构及功能说明
-------------------
    proprietor
      |-Controller.php//控制器基础类
      |-Pdo.php//数据库操作类
      |-Proprietor.php//框架入口类
      |-ProprietorModels.php//模型基础类
      |-ProprietorTool.php//工具类
      |-ProprietorView.php//视图类
      |-error.php//系统错误说明页面
     index.php 项目入口

0.项目入口<br/>
		
		解析路由分发
		-->根据配置加载控制器，模型 
		--> 生成渲染
		-->缓存
		define('APP_PATH', realpath('../app')); // 应用路径
		
		define('DEBUG',true);//是否显示调试信息 默认显示
		
		define('NAMESAPCE','app');//命名空间 可以不设置
		
		//define('CACHE_TIME',1);//缓存时间
		
		require_once '../proprietor/Proprietor.php'; // 引入框架总入口(路由解析文件配置，自动加载等行为)
		
		Proprietor::Init();
		
		整个配置是参考了 TP 的 配置入口风格（目前深入使用过的框架只有TP和Phalcon,所以大多数设计围绕这两种框架的优点）
		如果你需要多个项目通过不同入口进入 可以使用服务器进行路由 单独配置入口
		如果你想在单个入口完成多个项目进入，你可以在index.php中自行解析域名引入不同配置
		注意：多个项目目前只支持多个域名访问，比如admin.test.com  test.com 这样才可以根据域名进行解析
		      还可以修改源码，完善，欢迎提交！
<br/>1.框架总入口类Proprietor.php（暂时这么直白称呼）<br/>
		
		这个类是整个系统的初始化类，负责文件加载，自动路由加载控制器，以及一些基础配置
		目前支持的访问url格式:<br/>
		1.www.test.com/index/index(.html/.php)?a=1&b=2(host/Controller/Action)
		2.www.test.com/index/index/parm1/parm2(host/Controller/Action/参数1/参数2)
		
		类中自动加载目前就是引入对应目录的文件，并且不支持子目录文件引入，后期这一块会完善使用递归进行引入，提高灵活性
		
		另外还有一些错误日志处理以及错误屏蔽功能。
		
<br/>2.数据库操作类Pdo.php<br/>
		
		本类继承PDO类，并实现和封装一些自定义的方法完成一些CRUD操作，通过传入数据库配置进行连接数据库<br/>
		获取数据库连接，我们统一在ProprietorTool.php工具类中完成。<br/>
		
<br/>3.工具类ProprietorTool.php<br/>
		
		工具类，本类设计的主要目的是为了实现获取框架内对象等资源的统一，比如数据库连接，系统配置，统一之后可以避免多次实例化
		连接对象，并且实现一些运行时缓存，加快运行速度。<br/>
		另外，框架自带的错误提示功能，以及日志记录功能也在次实现。<br/>
		
<br/>4.控制器基础类Controller.php<br/>
	
		控制器基础类提供给框架使用者继承，将一些在控制器中常用的功能，例如获取数据，获取数据库连接，获取视图引擎等功能
		集中在这里使用
		
<br/>5.视图类<br/>
			
		目前视图类我做的十分简单，并不能算是一个引擎，后期有很大的优化空间，目前只支持模版页面文件加载、缓存以及简单的解析
		
<br/>6.模型基础类<br/>
		
		类中提供构造方法进行数据库选择以及表的解析，后期会引入字段校验等功能<br/>
		继承该类后可以选择要连接的数据库达到多数据库使用的目的<br/>
		
<br/>7.系统错误说明页面<br/>
		
		这个没啥好说，因为我啥都没写、、、<br/>
		
<br/>
一、使用说明
------------------------
