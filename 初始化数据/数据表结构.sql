/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : saiqi_img3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2020-12-15 16:24:35
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for img_article
-- ----------------------------
DROP TABLE IF EXISTS `img_article`;
CREATE TABLE `img_article` (
  `mId` mediumint(9) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `uId` int(11) NOT NULL COMMENT '用户id',
  `typeFile` varchar(255) DEFAULT NULL COMMENT '文章的类型：img/psd/video',
  `typeid` int(11) DEFAULT NULL COMMENT '类型id',
  `projectid` int(11) DEFAULT NULL COMMENT '项目id',
  `detailsid` int(11) DEFAULT NULL COMMENT '分类id',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `keyword` varchar(255) DEFAULT NULL COMMENT '关键字',
  `describe` mediumtext COMMENT '描述',
  `img` longtext COMMENT '图片json数据',
  `psd` longtext COMMENT 'psd文件json数据',
  `video` longtext COMMENT '视频json数据',
  `ai` longtext COMMENT 'ai文件josn数据',
  `pdf` longtext COMMENT 'pd文件josn数据',
  `word` longtext COMMENT 'word文档json数据',
  `excel` longtext COMMENT 'excel表格json数据',
  `engineering` longtext COMMENT 'engineering工程文件josn数据',
  `compress` varchar(255) DEFAULT NULL COMMENT '压缩包文件地址',
  `registerTimeImg` int(11) DEFAULT '0' COMMENT '创建时间',
  `endTimeImg` int(11) DEFAULT '0' COMMENT '最后一次修改时间',
  `retainTime` int(11) NOT NULL COMMENT '保留时间',
  `click` int(11) NOT NULL DEFAULT '0' COMMENT '点击数',
  `state` int(11) DEFAULT NULL COMMENT '状态值：1正常，2禁用',
  PRIMARY KEY (`mId`)
) ENGINE=MyISAM AUTO_INCREMENT=11369 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for img_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `img_auth_group`;
CREATE TABLE `img_auth_group` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `title` char(100) NOT NULL COMMENT '权限组名称',
  `state` int(1) NOT NULL DEFAULT '1' COMMENT '状态值：1正常，2禁用',
  `rules` longtext NOT NULL COMMENT '功能页面参数',
  `disabled` char(100) NOT NULL DEFAULT '1' COMMENT '禁止修改：1允许修改，2禁止修改',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for img_auth_rule_copy
-- ----------------------------
DROP TABLE IF EXISTS `img_auth_rule_copy`;
CREATE TABLE `img_auth_rule_copy` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `sid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '上级id',
  `name` char(255) NOT NULL COMMENT '功能页名称',
  `index` char(255) NOT NULL COMMENT '树形结构位置',
  `icon` char(255) NOT NULL COMMENT '图标类名',
  `urlKeyword` char(255) NOT NULL COMMENT '路由关键字',
  `checkAll` char(255) DEFAULT NULL COMMENT '全选状态值',
  `isIndeterminate` char(255) DEFAULT NULL COMMENT 'checkbox 的不确定状态',
  `cityOptions` longtext COMMENT '包含子功能页',
  `checkedCities` longtext COMMENT '选中的子功能页',
  `state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态值：1正常，2禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for img_browse_web_info
-- ----------------------------
DROP TABLE IF EXISTS `img_browse_web_info`;
CREATE TABLE `img_browse_web_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `uId` varchar(255) NOT NULL COMMENT '用户id',
  `sameDay` varchar(255) NOT NULL COMMENT '当天字符串日期',
  `mId` varchar(255) DEFAULT NULL COMMENT '文章id',
  `time` int(11) NOT NULL COMMENT '当前时间戳',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48133 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for img_collect
-- ----------------------------
DROP TABLE IF EXISTS `img_collect`;
CREATE TABLE `img_collect` (
  `cid` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `uid` int(11) NOT NULL COMMENT '用户id',
  `time` int(11) NOT NULL COMMENT '登录时间',
  `userip` varchar(255) NOT NULL COMMENT 'IP地址',
  `type` int(11) NOT NULL COMMENT '事件类型：0登陆，1注销',
  PRIMARY KEY (`cid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for img_details
-- ----------------------------
DROP TABLE IF EXISTS `img_details`;
CREATE TABLE `img_details` (
  `did` int(11) NOT NULL AUTO_INCREMENT COMMENT '分类id',
  `tbid` int(11) NOT NULL COMMENT '类型id',
  `dname` varchar(255) NOT NULL COMMENT '分类名称',
  `state` int(11) NOT NULL COMMENT '状态值：1正常，2禁用',
  `webShow` int(11) NOT NULL COMMENT '前台显示开关：0关，1开',
  PRIMARY KEY (`did`)
) ENGINE=InnoDB AUTO_INCREMENT=398 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for img_group_label
-- ----------------------------
DROP TABLE IF EXISTS `img_group_label`;
CREATE TABLE `img_group_label` (
  `gid` int(11) NOT NULL AUTO_INCREMENT COMMENT '标签组id',
  `lid` int(11) DEFAULT NULL COMMENT '绑定分类id',
  `name` varchar(255) DEFAULT NULL COMMENT '标签组名称',
  `state` int(11) DEFAULT NULL COMMENT '状态值：1正常，2禁用',
  `webShow` int(11) DEFAULT '1' COMMENT '前台显示开关：0关，1开',
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for img_information
-- ----------------------------
DROP TABLE IF EXISTS `img_information`;
CREATE TABLE `img_information` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `froid` int(11) NOT NULL COMMENT '下载用户id',
  `inid` int(11) NOT NULL COMMENT '被下载用户id',
  `information` varchar(255) NOT NULL COMMENT '操作内容',
  `created` int(11) NOT NULL COMMENT '操作时间',
  `state` int(11) NOT NULL COMMENT '状态值：1正常，2禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3440 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for img_label
-- ----------------------------
DROP TABLE IF EXISTS `img_label`;
CREATE TABLE `img_label` (
  `lid` int(11) NOT NULL AUTO_INCREMENT COMMENT '标签编号',
  `gid` int(11) DEFAULT NULL COMMENT '标签组编号',
  `type` varchar(255) DEFAULT NULL COMMENT '标注当前标签的所属类型',
  `name` varchar(255) DEFAULT NULL COMMENT '标签名称',
  `state` int(11) DEFAULT NULL COMMENT '状态值：1正常，2禁用',
  `webShow` int(11) DEFAULT '1' COMMENT '前台显示开关：0关，1开',
  PRIMARY KEY (`lid`)
) ENGINE=InnoDB AUTO_INCREMENT=285 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for img_operationinfo
-- ----------------------------
DROP TABLE IF EXISTS `img_operationinfo`;
CREATE TABLE `img_operationinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uId` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL COMMENT '用户操作类型数字：1.发布文章,2.修改文章,3.删除文章（回收站）,4.删除文章（回收站删除）,5.修改个人信息,6.添加新用户,7.修改用户信息,8.还原用户,9.添加用户组,10.修改用户组,11.删除用户组,12.添加项目,13.修改项目,14.删除项目,15.添加类型,16.修改类型,17.删除类型,18.添加分类,19.修改分类,20.删除分类,21.添加标签组,22.修改标签组,23.删除标签组,24.添加标签,25.修改标签,26.删除标签,27.登录后台,28.退出后台,29.下载文件,30.系统删除回收站文章（保留天数已到）',
  `time` int(11) DEFAULT NULL COMMENT '操作时间',
  `contentText` varchar(255) DEFAULT NULL COMMENT '用户操作信息显示',
  `content_groupText` varchar(255) DEFAULT NULL COMMENT '管理员版用户操作信息',
  `content_user` longtext COMMENT '操作内容：用户信息',
  `content_project` longtext COMMENT '操作内容：项目',
  `content_type` longtext COMMENT '操作内容：类型',
  `content_classification` longtext COMMENT '操作内容：分类',
  `content_group_label` longtext COMMENT '操作内容：标签组',
  `content_label` longtext COMMENT '操作内容：标签',
  `content_article_type` longtext COMMENT '操作内容：文章类型',
  `content_auth_group` longtext COMMENT '操作内容：用户组',
  `content_article` longtext COMMENT '操作内容：文章',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11052 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for img_project
-- ----------------------------
DROP TABLE IF EXISTS `img_project`;
CREATE TABLE `img_project` (
  `pid` int(10) NOT NULL AUTO_INCREMENT COMMENT '项目编号',
  `xname` varchar(255) NOT NULL COMMENT '项目名称',
  `state` int(11) NOT NULL DEFAULT '1' COMMENT '状态值：1正常，2禁用',
  `webShow` int(11) NOT NULL DEFAULT '1' COMMENT '前台显示开关：0关，1开',
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for img_type
-- ----------------------------
DROP TABLE IF EXISTS `img_type`;
CREATE TABLE `img_type` (
  `tid` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id编号',
  `lname` varchar(255) NOT NULL COMMENT '类型名称',
  `state` int(11) NOT NULL COMMENT '状态值：1正常，2禁用',
  `webShow` int(11) NOT NULL COMMENT '前台显示开关：0关，1开',
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for img_users
-- ----------------------------
DROP TABLE IF EXISTS `img_users`;
CREATE TABLE `img_users` (
  `uId` mediumint(10) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `headPortraitSrc` varchar(255) DEFAULT NULL COMMENT '头像路径',
  `nickname` varchar(255) DEFAULT NULL COMMENT '昵称',
  `sex` int(11) DEFAULT '0' COMMENT '1男，0女',
  `userName` varchar(255) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `verification` varchar(255) DEFAULT NULL COMMENT '验证码',
  `registerTime` int(11) NOT NULL COMMENT '创建用户时间',
  `endTime` int(11) DEFAULT NULL COMMENT '最后一次登录时间',
  `state` varchar(255) NOT NULL COMMENT '登录状态：0正常，1账户冻结',
  `permissions` int(11) NOT NULL COMMENT '1：设计师，2：超级管理员（默认权限）',
  `webShow` int(11) NOT NULL DEFAULT '1' COMMENT '前台显示开关：0关，1开',
  `access_token` varchar(255) DEFAULT NULL COMMENT 'access_token',
  `token_expires_in` int(13) DEFAULT '0' COMMENT 'token有效期至',
  `judgeLogin` varchar(255) DEFAULT '0' COMMENT '是否登录， 0退出，1登录',
  `shieldInfo` longtext COMMENT '屏蔽的项目和类型',
  PRIMARY KEY (`uId`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;
