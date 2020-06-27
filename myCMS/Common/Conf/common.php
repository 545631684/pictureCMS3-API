<?php
return array(
	/* 自定义常量 */
	'TIME_SECOND'			=> 1,		// 1秒
	'TIME_MINUTE'			=> 60,		// 1分钟
	'TIME_HOUR'				=> 3600,	// 1小时
	'TIME_DAY'				=> 86400,	// 1天
	'TIME_WEEK'				=> 604800,	// 1周
	'TIME_MONTH'			=> 18144000,// 1个月	
	'SRC_URL'				=> 'http://192.168.1.130/', //  前台文件调用域名
	/* 数据库表 */
	'IMG_ARTICLE'			=> 'article',// 文章表	
	'IMG_AUTH_GROUP'		=> 'auth_group',// 权限组表
	'IMG_AUTH_RULE_COPY'	=> 'auth_rule_copy',// 功能页面参数设置表	
	'IMG_COLLECT'			=> 'collect',// 用户登录退出信息表	
	'IMG_DETAILS'			=> 'details',// 分类表	
	'IMG_GROUP_LABEL'		=> 'group_label',// 标签组表	
	'IMG_LABEL'				=> 'label',// 标签表	
	'IMG_INFORMATION'		=> 'information',// 用户下载信息表	
	'IMG_PROJECT'			=> 'project',// 项目表	
	'IMG_TYPE'				=> 'type',// 类型表	
	'IMG_USERS'				=> 'users',// 用户表
	'IMG_OPERATIONINFO'		=> 'operationinfo',// 用户操作记录表
	'IMG_BROWSE_WEB_INFO'	=> 'browse_web_info',// 用户浏览记录表
	/* 接口状态码 */
	'SUCCESS'				=> 200,// 成功	
	'FAIL'					=> -4000,// 失败	
	'NOT_FOUND'				=> -4004,// 找不到	
	'TOKEN_INVALID'			=> -4001,// token失效	
	'PARAMS_INVALID'		=> -4002,// 参数错误	
);