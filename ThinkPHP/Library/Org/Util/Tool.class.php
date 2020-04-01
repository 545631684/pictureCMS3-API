<?php
namespace Org\Util;
class timeObj{
		public $y=0;
		public $start='';
		public $end='';
	}
class Tool {
	
	// public $tool;
	public $img_users;
	public $img_auth_group;
	public $img_auth_rule_copy;
	public $img_article;
	public $img_project;
	public $img_type;
	public $img_details;
	public $img_group_label;
	public $img_label;
	public $img_collect;
	public $img_information;
	public $img_operationinfo;
	public $src_url;
	public $time_second;
	public $time_minute;
	public $time_hour;
	public $time_day;
	public $time_week;
	public $time_month;	
	public $success;	
	public $fail;	
	public $not_found;	
	public $token_invalid;	
	public $params_invalid;
	public $headers;
	
	public function __construct() 
    {
		// 初始化数据库
		$this->img_users = M(C('IMG_USERS'));
		$this->img_auth_group = M(C('IMG_AUTH_GROUP'));
		$this->img_auth_rule_copy = M(C('IMG_AUTH_RULE_COPY'));
		$this->img_article = M(C('IMG_ARTICLE'));
		$this->img_project = M(C('IMG_PROJECT'));
		$this->img_type = M(C('IMG_TYPE'));
		$this->img_details = M(C('IMG_DETAILS'));
		$this->img_group_label = M(C('IMG_GROUP_LABEL'));
		$this->img_label = M(C('IMG_LABEL'));
		$this->img_collect = M(C('IMG_COLLECT'));
		$this->img_information = M(C('IMG_INFORMATION'));
		$this->img_operationinfo = M(C('IMG_OPERATIONINFO'));
		// 初始化时间值
		$this->time_second = C('TIME_SECOND');
		$this->time_minute = C('TIME_MINUTE');
		$this->time_hour = C('TIME_HOUR');
		$this->time_day = C('TIME_DAY');
		$this->time_week = C('TIME_WEEK');
		$this->time_month = C('TIME_MONTH');
		// 前台图片文件调用域名
		$this->src_url = C('SRC_URL');
		// 初始化错误代码
		$this->success = C('SUCCESS');
		$this->fail = C('FAIL');
		$this->not_found = C('NOT_FOUND');
		$this->token_invalid = C('TOKEN_INVALID');
		$this->params_invalid = C('PARAMS_INVALID');
		// headers信息头
		$this->headers = $this->returnJudgeToken();
		
    }

	/**
     * 获取headers信息头
     */
	public function getHeaders(){
		$headers = [];
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
	
	/**
	* 生成用户token
	*/
	public function secretkey($name){
		return sha1(md5(uniqid(date("Y-m-d h:i:s",time())).$name, TRUE));
	}
	
	/**
	* 用户token时效增加一天
	*/
	public function setTokenTimeDay($tokenTime, $uId){
		$rtn=$this->img_users->where(['uId' => $uId])->save(['token_expires_in' => $tokenTime + C("TIME_DAY")]);
		return $tokenTime + C("TIME_DAY");
	}
	
	/**
	* 用户token时效增加一周
	*/
	public function setTokenTimeWeek($tokenTime, $uId){
		$rtn=$this->img_users->where(['uId' => $uId])->save(['token_expires_in' => $tokenTime + C("TIME_WEEK")]);
		return $tokenTime + C("TIME_WEEK");
	}
	
	/**
	* 用户token时效增加一个月
	*/
	public function setTokenTimeMonth($tokenTime, $uId){
		$rtn=$this->img_users->where(['uId' => $uId])->save(['token_expires_in' => $tokenTime + C("TIME_MONTH")]);
		return $tokenTime + C("TIME_MONTH");
	}
	
	/**
	* 判断token是否失效
	*/
	public function returnJudgeToken(){
		$judgeToken = false;
		$headers = $this->getHeaders();
		$user = $this->img_users->where(['access_token' => $this->tool->headers['access_token']])->find();
		if($user['access_token']){
			if ($user['token_expires_in'] <= time()) {
				$judgeToken = false;
			}
		} else {
			$judgeToken = false;
		}
		return $judgeToken;
	}
	
	/**
	* 检查类型是否已全部屏蔽
	*/
	public function inspectTypeShield($data){
		$num = 0;
		for($i=0;i<count($data);$i++){
			$data[$i]['state'] == '1' ? $num++ : $i = $i;
		}
		return count($data) == $num ? true : false;
	}
	
	/**
	* 获取项目、类型、分类发布的文章数
	*/
	public function getPublicInfo($type, $data){
		switch ($type) {
			case 'project':
				if (count($data) != 0) {
					for($i=0;$i<count($data);$i++){
						$time = $this->img_article->where("projectid = ".$data[$i]["pid"])->count();
						$data[$i]["articlelist"] = is_string($time) == false ? "0" : $time;
					}					
				}
				break;
			case 'type':
				if (count($data) != 0) {
					for($i=0;$i<count($data);$i++){
						$time = $this->img_article->where("typeid = ".$data[$i]["tid"])->count();
						$data[$i]["articlelist"] = is_string($time) == false ? "0" : $time;
					}
				}
				break;
			case 'details':
				if (count($data) != 0) {
					for($i=0;$i<count($data);$i++){
						$time = $this->img_article->where("detailsid = ".$data[$i]["did"])->count();
						$data[$i]["articlelist"] = is_string($time) == false ? "0" : $time;
					}
				}
				break;
			case 'groupLabel':
				if (count($data) != 0) {
					for($i=0;$i<count($data);$i++){
						$time = $this->img_label->where("gid = ".$data[$i]["gid"])->count();
						$data[$i]["labelList"] = is_string($time) == false ? "0" : $time;
					}
				}
				break;
			case 'label':
				if (count($data) != 0) {
					for($i=0;$i<count($data);$i++){
						$name['keyword'] =  array("like","%{$data[$i]['name']}%");
						$time = $this->img_article->where($name)->count();
						$data[$i]["articlelist"] = is_string($time) == false ? $this->img_article->getLastSql() : $time;
					}
				}
				break;
		}
		return $data;
	}
	
	/**
	* 获取当前年份每个月开始和结束时间戳
	*/
	function getYearAll() {
		$nian = date('y');
		$temp = [];
		for ($i=0; $i<=11; $i++) {
			$obj = new timeObj();
			$obj->y= $i+1;
			$obj->start= $this->start_end_time($i+1, 'start');
			$obj->end= $this->start_end_time($i+1, 'end');
			$temp[$i] = $obj;
		}
		return $temp;
	}
	
	/**
	* 获取当前月每天的开始和结束时间戳
	*/
	function getMonthAll () {
		$days = date("t");
		$time = [];
		for ($i = 0; $i < intval($days); $i++)
		{
			# 获取当月每天
			$day[] = strtotime(date('m/d', strtotime("+" .$i. " day", strtotime(date("Y-m-01")))));
			# 获取每天开始时间
			$start = strtotime(date('Y-m-d H:i:s', strtotime("+" . $i . " day", strtotime(date("Y-m-01 00:00:00")))));
			# 获取每天结束时间
			$end = strtotime(date('Y-m-d H:i:s', strtotime("+" . $i . " day", strtotime(date("Y-m-01 23:59:59")))));
			$time[$i]['riqi'] = 1+$i.'日';
			$time[$i]['start'] = $start;
			$time[$i]['end'] = $end;
		}
		return $time;
	}
	

	/**
	* 算天数
	*/
	function is_yue_tian_num($nian, $month) {
		if (in_array($month, array(1, 3, 5, 7, 8, 01, 03, 05, 07, 08, 10, 12))) {
			$text = '31';
		}
		elseif($month == 2) {
			if ($nian % 400 == 0 || ($nian % 4 == 0 && $nian % 100 !== 0)) { //判断是否是闰年  
				$text = '29';
			} else {
				$text = '28';
			}
		} else {
			$text = '30';
		}
		return $text;
	}
	
	/**
	* $num 月  
	* $type 月开始时间戳start ，结束时间戳end
	*/
	function start_end_time($num, $type) {
		if ($num == 1) {
			if ($type == 'start') {
				return mktime(0, 0, 0, 1, 1, date('Y'));
			} else if ($type == 'end') {
				return mktime(23, 59, 59, 1, $this ->is_yue_tian_num(date('Y'), 1), date('Y'));
			}
		}

		if ($num == 2) {
			if ($type == 'start') {
				return mktime(0, 0, 0, 2, 1, date('Y'));
			} else if ($type == 'end') {
				return mktime(23, 59, 59, 2, $this ->is_yue_tian_num(date('Y'), 2), date('Y'));
			}
		}

		if ($num == 3) {
			if ($type == 'start') {
				return mktime(0, 0, 0, 3, 1, date('Y'));
			} else if ($type == 'end') {
				return mktime(23, 59, 59, 3, $this ->is_yue_tian_num(date('Y'), 1), date('Y'));
			}
		}

		if ($num == 4) {
			if ($type == 'start') {
				return mktime(0, 0, 0, 4, 1, date('Y'));
			} else if ($type == 'end') {
				return mktime(23, 59, 59, 4, $this ->is_yue_tian_num(date('Y'), 4), date('Y'));
			}
		}

		if ($num == 5) {
			if ($type == 'start') {
				return mktime(0, 0, 0, 5, 1, date('Y'));
			} else if ($type == 'end') {
				return mktime(23, 59, 59, 5, $this ->is_yue_tian_num(date('Y'), 5), date('Y'));
			}
		}

		if ($num == 6) {
			if ($type == 'start') {
				return mktime(0, 0, 0, 6, 1, date('Y'));
			} else if ($type == 'end') {
				return mktime(23, 59, 59, 6, $this ->is_yue_tian_num(date('Y'), 6), date('Y'));
			}
		}

		if ($num == 7) {
			if ($type == 'start') {
				return mktime(0, 0, 0, 7, 1, date('Y'));
			} else if ($type == 'end') {
				return mktime(23, 59, 59, 7, $this ->is_yue_tian_num(date('Y'), 7), date('Y'));
			}
		}

		if ($num == 8) {
			if ($type == 'start') {
				return mktime(0, 0, 0, 8, 1, date('Y'));
			} else if ($type == 'end') {
				return mktime(23, 59, 59, 8, $this ->is_yue_tian_num(date('Y'), 8), date('Y'));
			}
		}

		if ($num == 9) {
			if ($type == 'start') {
				return mktime(0, 0, 0, 9, 1, date('Y'));
			} else if ($type == 'end') {
				return mktime(23, 59, 59, 9, $this ->is_yue_tian_num(date('Y'), 9), date('Y'));
			}
		}

		if ($num == 10) {
			if ($type == 'start') {
				return mktime(0, 0, 0, 10, 1, date('Y'));
			} else if ($type == 'end') {
				return mktime(23, 59, 59, 10, $this ->is_yue_tian_num(date('Y'), 10), date('Y'));
			}
		}

		if ($num == 11) {
			if ($type == 'start') {
				return mktime(0, 0, 0, 11, 1, date('Y'));
			} else if ($type == 'end') {
				return mktime(23, 59, 59, 11, $this ->is_yue_tian_num(date('Y'), 11), date('Y'));
			}
		}

		if ($num == 12) {
			if ($type == 'start') {
				return mktime(0, 0, 0, 12, 1, date('Y'));
			} else if ($type == 'end') {
				return mktime(23, 59, 59, 12, $this ->is_yue_tian_num(date('Y'), 12), date('Y'));
			}
		}
	}
	
	/**
	* 计算文件大小并加入计量单位
	* $filesize 文件大小(单位:B)
	**/
	function getFileSize($filesize) {
		if($filesize >= 1073741824) {
			$info = [
				name => 'GB',
				size => round($filesize / 1073741824 * 100) / 100
			];
		} else if($filesize >= 1048576) {
			$info = [
				name => 'MB',
				size => round($filesize / 1048576 * 100) / 100
			];
		} else if($filesize >= 1024) {
			$info = [
				name => 'KB',
				size => round($filesize / 1024 * 100) / 100
			];
		} else {
			$info = [
				name => 'K',
				size => $filesize
			];
		}
		return $info;
	}
	
	/**
	* 计算图片的大小压缩比例
	* setWidth 最大宽度 （容差值100上下）
	* setHeight 最大高度 （容差值100上下）
	* multiple 缩放倍数
	*/
	function setImgSize($width, $height) {
		$multiple = [2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48,50];
		if ($width < $height) {
			$setWidth = 1280;
			$setHeight = 980;
		} else {
			$setWidth = 1920;
			$setHeight = 1080;
		}
		if ($width>$setWidth && $height>$setHeight) {
			if ($width<$height) {
				for ($i = 0; $i < count($multiple); $i++)
				{
					$widthCount = $width/$multiple[$i];
					$heightCount = $height/$multiple[$i];
					if($heightCount<$setHeight && $heightCount<=$setHeight-100 || $heightCount>$setHeight && $heightCount<=$setHeight+100) {
						return ['width' => round($widthCount,0), 'height' => round($heightCount,0)];
					}
				}
			} else if ($width>$height) {
				for ($j = 0; $j < count($multiple); $j++)
				{
					$widthCount = $width/$multiple[$j];
					$heightCount = $height/$multiple[$j];
					if($widthCount<$setWidth && $widthCount<=$setWidth-100 || $widthCount>$setWidth && $widthCount<=$setWidth+100) {
						return ['width' => round($widthCount,0), 'height' => round($heightCount,0)];
					}
				}
			} else if ($width==$height) {
				for ($e = 0; $e < count($multiple); $e++)
				{
					$widthCount = $width/$multiple[$e];
					$heightCount = $height/$multiple[$e];
					if($widthCount<$setWidth && $widthCount<=$setWidth-100 || $widthCount>$setWidth && $widthCount<=$setWidth+100) {
						return ['width' => round($widthCount,0), 'height' => round($heightCount,0)];
					}
				}
			}
		} else {
			return ['width' => $width, 'height' => $height];
		}
	}
	/**
	* 循环数组获取需要的值
	*
	* $tableName	数据库表名称
	* $key			比对键
	* $keyResult	结果键
	* $value		比对值
	*
	**/
	function getDataInfo($tableName, $key, $keyResult, $value) {
		$info = "";
		switch($tableName) {
			case 'IMG_USERS':
				$infoList = $this->img_users->select();
				break;
			case 'IMG_AUTH_GROUP':
				$infoList = $this->img_auth_group->select();
				break;
			case 'IMG_AUTH_RULE_COPY':
				$infoList = $this->img_auth_rule_copy->select();
				break;
			case 'IMG_ARTICLE':
				$infoList = $this->img_article->select();
				break;
			case 'IMG_PROJECT':
				$infoList = $this->img_project->select();
				break;
			case 'IMG_TYPE':
				$infoList = $this->img_type->select();
				break;
			case 'IMG_DETAILS':
				$infoList = $this->img_details->select();
				break;
			case 'IMG_GROUP_LABEL':
				$infoList = $this->img_group_label->select();
				break;
			case 'IMG_LABEL':
				$infoList = $this->img_label->select();
				break;
			case 'IMG_COLLECT':
				$infoList = $this->img_collect->select();
				break;
			case 'IMG_INFORMATION':
				$infoList = $this->img_information->select();
				break;
			case 'IMG_OPERATIONINFO':
				$infoList = $this->img_operationinfo->select();
				break;
		}
		foreach ($infoList as $obj) {
			if($obj[$key] == $value) $info = $obj[$keyResult];
		}
		return $info;
	}
}
