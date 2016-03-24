# proprietor
A flexible,simple PHP Framework<br/>
希望做一个灵活简单的PHP框架，满足快速开发需求<br/>
文件结构及功能说明
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
		注意：多个项目目前只支持多个域名访问，比如admin.liferam.com  liferam.com 这样才可以根据域名进行解析
		      还可以修改源码，完善，欢迎提交！
1.框架总入口类Proprietor.php（暂时这么直白称呼）<br/>

2.数据库操作类<br/>

3.工具类<br/>

4.控制器基础类<br/>

5.视图类<br/>

6.模型基础类<br/>

7.系统错误说明页面<br/>
