<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

Route::get('hello/:name', 'index/hello');

return [
	/******************* 后台接口begin ***********************/
	'a/manageUser_list' 		=> 	'admin/manage_user_list',
	'a/user_list' 				=> 	'admin/userList',
	'a/auth_list' 				=> 	'admin/auth_list',
	'a/auth_groupadd' 			=> 	'admin/auth_groupadd',
	'a/auth_groupedit' 			=> 	'admin/auth_groupedit',
	'a/auth_groupdel' 			=> 	'admin/auth_groupdel',
	'a/auth_grouplist' 			=> 	'admin/auth_grouplist',
	'a/auth_groupone' 			=> 	'admin/auth_groupone',
	'a/projectList' 			=> 	'admin/projectList',
	'a/projectAdd' 				=> 	'admin/projectAdd',
	'a/projectsave' 			=> 	'admin/projectsave',
	'a/projectdel' 				=> 	'admin/projectdel',
	'a/getPublicInfo' 			=> 	'admin/getPublicInfo',
	'a/typeAdd' 				=> 	'admin/typeAdd',
	'a/typesave' 				=> 	'admin/typesave',
	'a/typeDel' 				=> 	'admin/typeDel',
	'a/detailsAdd' 				=> 	'admin/detailsAdd',
	'a/detailssave' 			=> 	'admin/detailssave',
	'a/detailsDel' 				=> 	'admin/detailsDel',
	'a/labelsAdd' 				=> 	'admin/labelsAdd',
	'a/labelssave' 				=> 	'admin/labelssave',
	'a/labelsDel' 				=> 	'admin/labelsDel',
	'a/labelAdd' 				=> 	'admin/labelAdd',
	'a/labelsave' 				=> 	'admin/labelsave',
	'a/labelDel' 				=> 	'admin/labelDel',
	'a/userRecovery' 			=> 	'admin/userRecovery',
	'a/reduction' 				=> 	'admin/reduction',
	'a/user_list' 				=> 	'admin/user_list',
	'a/guanliuserSave' 			=> 	'admin/guanliuserSave',
	'a/upfile' 					=> 	'admin/upfile',
	'a/getUserToken' 			=> 	'admin/getUserToken',
	'a/userSave' 				=> 	'admin/userSave',
	'a/getUserInfo' 			=> 	'admin/getUserInfo',
	'a/getUserInfo2' 			=> 	'admin/get_user_info',
	'a/delfile' 				=> 	'admin/delfile',
	'a/delfile2' 				=> 	'admin/delfile2',
	'a/getAdminStatisticsData' 	=> 	'admin/getAdminStatisticsData',
	'a/getRecoveryArticle' 		=> 	'admin/getRecoveryArticle',
	'a/userAdd' 				=> 	'admin/userAdd',
	'a/articleAdd' 				=> 	'admin/articleAdd',
	'a/getTitleRepeat' 			=> 	'admin/getTitleRepeat',
	'a/getArticleAll' 			=> 	'admin/getArticleAll',
	'a/getArticleAll2' 			=> 	'admin/getArticleAll2',
	'a/exhibitionDel' 			=> 	'admin/exhibitionDel',
	'a/exhibitionreduction' 	=> 	'admin/exhibitionreduction',
	'a/delArticle'				=> 	'admin/delArticle',
	'a/getAdminArticle'			=> 	'admin/getAdminArticle',
	'a/articleUpdate'			=> 	'admin/articleUpdate',
	'a/getUpdateTitleRepeat'	=> 	'admin/getUpdateTitleRepeat',
	'a/getAdminIndexData'		=> 	'admin/getAdminIndexData',
	'a/exitlogin'				=> 	'admin/exitlogin',
	'a/addShieldUser'			=> 	'admin/addShieldUser',
	'a/addShieldUserType'		=> 	'admin/addShieldUserType',
	'a/setUserBrowseArticle'	=> 	'admin/setUserBrowseArticle',
	'a/getUserBrowseWebInfo'	=> 	'admin/getUserBrowseWebInfo',
	'a/userState'				=> 	'admin/userState',
	'a/getArticleSubsection'	=> 	'admin/getArticleSubsection',
	'a/getArticleUserSubsection'=> 	'admin/getArticleUserSubsection',
	'a/getArticleUserDownload'	=> 	'admin/getArticleUserDownload',
	'a/getArticleProject'		=> 	'admin/getArticleProject',
	'a/servicePrivacyTypeAdd'	=> 	'admin/servicePrivacyTypeAdd',
	'a/privacyTypeSave'			=> 	'admin/privacyTypeSave',
	'a/privacyTypeDel'			=> 	'admin/privacyTypeDel',
	'a/getArticleLabel'			=> 	'admin/getArticleLabel',
	'a/exhibitionDels'			=> 	'admin/exhibitionDels',
	'a/updateArticleQuality'	=> 	'admin/updateArticleQuality',
	'a/getUserCollectArticle'	=> 	'admin/getUserCollectArticle',
	/******************* 后台接口end *************************/
	
	/******************* 前台接口begin ***********************/
	'w/zipdownload' 			=> 	'web/zipdownload',
	'w/getWebArticleAll'		=> 	'admin/getWebArticleAll',
	'w/getWebArticle'			=> 	'admin/getWebArticle',
	'w/webUserList'				=> 	'admin/web_user_list',
	'u/login' 					=> 	'web/login',
	'u/user_add' 				=> 	'web/user_add',
	'u/emailrepeat' 			=> 	'web/emailrepeat',
	'u/send' 					=> 	'web/Send',
	'u/retrievePassword' 		=> 	'web/retrievePassword',
	'u/upfile' 					=> 	'upfile/upfile',
	'w/setOperationInfo' 		=> 	'web/setOperationInfo',
	'w/getOperationInfo' 		=> 	'web/getOperationInfo',
	'w/downloadInfo' 			=> 	'web/downloadInfo',
	'w/collectArticle' 			=> 	'web/collectArticle',
	/******************* 前台接口end *************************/
];
