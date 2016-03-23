<?php
// header("Content-type: text/html; charset=utf-8");

/**
 * 基本配置
 *
 * 系统路径
 *
 * 应用路径
 *
 * 配置路径
 */

// 解析路由分发
// 根据配置加载控制器，模型
// 方法加载解析视图
// 生成渲染
// 缓存
define('START', memory_get_usage(false));//开始内存计算
// echo memory_get_usage(true);
define('APP_PATH', realpath('../app')); // 应用路径

define('DEBUG',true);//是否显示调试信息

define('NAMESAPCE','app');//命名空间

//define('CACHE_TIME',1);//缓存时间

require_once '../proprietor/Proprietor.php'; // 引入路由解析

Proprietor::Init();




