<?php
return array(
	/* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     	// 数据库类型
    'DB_HOST'               =>  'localhost', 	// 服务器地址
    'DB_NAME'               =>  'saiqi_img3',   // 数据库名
    'DB_USER'               =>  'root',      	// 用户名
    'DB_PWD'                =>  '2577b4213d',   // 密码
    'DB_PORT'               =>  '3306',        	// 端口
    'DB_PREFIX'             =>  'img_',    		// 数据库表前缀
    'DB_FIELDTYPE_CHECK'    =>  false,       	// 是否进行字段类型检查
    'DB_FIELDS_CACHE'       =>  false,        	// 启用字段缓存
    'DB_CHARSET'            =>  'utf8',      	// 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        =>  0, 				// 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        =>  false,       	// 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         =>  1, 				// 读写分离后 主服务器数量
    'DB_SLAVE_NO'           =>  '', 			// 指定从服务器序号
    'DB_SQL_BUILD_CACHE'    =>  false, 			// 数据库查询的SQL创建缓存
    'DB_SQL_BUILD_QUEUE'    =>  'file',   		// SQL缓存队列的缓存方式 支持 file xcache和apc
    'DB_SQL_BUILD_LENGTH'   =>  20, 			// SQL缓存的队列长度
    'DB_SQL_LOG'            =>  false, 			// SQL执行日志记录
    'DB_BIND_PARAM'         =>  false, 			// 数据库写入数据自动参数绑定
	/* URL设置 */
    'URL_CASE_INSENSITIVE'  =>  true,   		// 默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'             =>  2,       		// URL访问模式,可选参数0、1、2、3,代表以下四种模式：
	'URL_ROUTER_ON'         =>  true,   		// 是否开启URL路由
	
    /* 默认路由规则 针对模块 */
	'MODULE_ALLOW_LIST'		=> array('Home'),
	'URL_ROUTE_RULES'       => array(
		/******************* 后台接口begin ***********************/
		'a/manageUser_list' 				=> 	'home/admin/manage_user_list',
		'a/user_list' 				=> 	'home/admin/userList',
		'a/auth_list' 				=> 	'home/admin/auth_list',
		'a/auth_groupadd' 			=> 	'home/admin/auth_groupadd',
		'a/auth_groupedit' 			=> 	'home/admin/auth_groupedit',
		'a/auth_groupdel' 			=> 	'home/admin/auth_groupdel',
		'a/auth_grouplist' 			=> 	'home/admin/auth_grouplist',
		'a/auth_groupone' 			=> 	'home/admin/auth_groupone',
		'a/projectList' 			=> 	'home/admin/projectList',
		'a/projectAdd' 				=> 	'home/admin/projectAdd',
		'a/projectsave' 			=> 	'home/admin/projectsave',
		'a/projectdel' 				=> 	'home/admin/projectdel',
		'a/getPublicInfo' 			=> 	'home/admin/getPublicInfo',
		'a/typeAdd' 				=> 	'home/admin/typeAdd',
		'a/typesave' 				=> 	'home/admin/typesave',
		'a/typeDel' 				=> 	'home/admin/typeDel',
		'a/detailsAdd' 				=> 	'home/admin/detailsAdd',
		'a/detailssave' 			=> 	'home/admin/detailssave',
		'a/detailsDel' 				=> 	'home/admin/detailsDel',
		'a/labelsAdd' 				=> 	'home/admin/labelsAdd',
		'a/labelssave' 				=> 	'home/admin/labelssave',
		'a/labelsDel' 				=> 	'home/admin/labelsDel',
		'a/labelAdd' 				=> 	'home/admin/labelAdd',
		'a/labelsave' 				=> 	'home/admin/labelsave',
		'a/labelDel' 				=> 	'home/admin/labelDel',
		'a/userRecovery' 			=> 	'home/admin/userRecovery',
		'a/reduction' 				=> 	'home/admin/reduction',
		'a/user_list' 				=> 	'home/admin/user_list',
		'a/guanliuserSave' 			=> 	'home/admin/guanliuserSave',
		'a/upfile' 					=> 	'home/admin/upfile',
		'a/getUserToken' 			=> 	'home/admin/getUserToken',
		'a/userSave' 				=> 	'home/admin/userSave',
		'a/getUserInfo' 			=> 	'home/admin/getUserInfo',
		'a/delfile' 				=> 	'home/admin/delfile',
		'a/getAdminStatisticsData' 	=> 	'home/admin/getAdminStatisticsData',
		'a/getRecoveryArticle' 		=> 	'home/admin/getRecoveryArticle',
		'a/user_add' 				=> 	'home/admin/user_add',
		'a/articleAdd' 				=> 	'home/admin/articleAdd',
		'a/getTitleRepeat' 			=> 	'home/admin/getTitleRepeat',
		'a/getArticleAll' 			=> 	'home/admin/getArticleAll',
		'a/exhibitionDel' 			=> 	'home/admin/exhibitionDel',
		'a/exhibitionreduction' 	=> 	'home/admin/exhibitionreduction',
		'a/delArticle'				=> 	'home/admin/delArticle',
		'a/getAdminArticle'			=> 	'home/admin/getAdminArticle',
		'a/articleUpdate'			=> 	'home/admin/articleUpdate',
		'a/getUpdateTitleRepeat'	=> 	'home/admin/getUpdateTitleRepeat',
		'a/getAdminIndexData'		=> 	'home/admin/getAdminIndexData',
		'a/exitlogin'				=> 	'home/admin/exitlogin',
		'a/addShieldUser'			=> 	'home/admin/addShieldUser',
		'a/addShieldUserType'		=> 	'home/admin/addShieldUserType',
		/******************* 后台接口end *************************/
		
		/******************* 前台接口begin ***********************/
		'w/zipdownload' 			=> 	'home/web/zipdownload',
		'w/getWebArticleAll'		=> 	'home/admin/getWebArticleAll',
		'w/getWebArticle'			=> 	'home/admin/getWebArticle',
		'w/webUserList'				=> 	'home/admin/web_user_list',
		'u/login' 					=> 	'home/web/login',
		'u/user_add' 				=> 	'home/web/user_add',
		'u/emailrepeat' 			=> 	'home/web/emailrepeat',
		'u/send' 					=> 	'home/web/Send',
		'u/retrievePassword' 		=> 	'home/web/retrievePassword',
		'w/setOperationInfo' 		=> 	'home/web/setOperationInfo',
		'w/getOperationInfo' 		=> 	'home/web/getOperationInfo',
		/******************* 前台接口end *************************/
	), 
	
	'URL_HTML_SUFFIX'       =>  'html',  // URL伪静态后缀设置
    'URL_DENY_SUFFIX'       =>  'ico|png|gif|jpg', // URL禁止访问的后缀设置
	'LOAD_EXT_CONFIG'		=>	'common',	// 调用自定常量
);