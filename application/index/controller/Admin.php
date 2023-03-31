<?php
namespace app\index\controller;
use Tool\Controller_controller;
use Tool\Tool;
use think\Db;
use think\facade\Config;
use Org\Util\Imgcompress;

class Admin extends Controller_controller {
    
	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	* 前置判断接口的token有效性
	*/
	public function initialize() 
	{
		$judgeToken = true;
		$headers = [];
		// 获取access_token
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}

		// 检查http头部没有携带Accesstoken信息直接拒绝
		if(array_key_exists("Accesstoken", $headers)){
			// 数据库查找
			$user = db('users')->where(['access_token' => $headers['Accesstoken']])->find();
			if($user['access_token']){
				if($user['token_expires_in'] <= time()) {
					$judgeToken = false;
				}
			} else {
				$judgeToken = false;
			}
			if(!$judgeToken){
				die('TOKEN过期,请重新登录');
			}
		} else {
			die('TOKEN过期,请重新登录');
		}
		
		
	}
	
	/**
	* 获取用户信息
	*/
	public function get_user_info(){
		//登录验证
		if ($this->request->isPost()) {
			//数据创建
			$UsersArrServe = $this->request->param();
			// dump($UsersArrServe);
			$articleNum = db('article')->where(['state' => 1])->count();
			$userArr = db('users')->where(['uId' => $UsersArrServe['uId']])->find();
			$projectArr = $this->tool->getPublicInfo('project', db('project')->where('1 = 1')->select());
			$typeArr = $this->tool->getPublicInfo('type', db('type')->where('1 = 1')->select());
			$detailsArr = $this->tool->getPublicInfo('details', db('details')->where('1 = 1')->select());
			$groupLabelArr=db('group_label')->where('1 = 1')->select();
			$labelArr=db('label')->where('1 = 1')->select();
			$privacyTypes=db('privacy_type')->where('1 = 1')->select();
			$authgroupArr = db('auth_group')->where(['id' => $userArr['permissions']])->select();
			$userRecovery = db('auth_group')->where('1 = 1')->select();
			$authgroupArr[0]['rules']=json_decode($authgroupArr[0]['rules']);
			//判断用户是否存在
			if (!empty($userArr['uId'])) {
				$jsonArr = [
					'adminInfo'			=> [
						'uId'				=> $userArr['uId'],
						'headPortraitSrc'	=> $userArr['headPortraitSrc'],
						'userName'			=> $userArr['userName'],
						'nickname'			=> $userArr['nickname'],
						'sex'				=> $userArr['sex'],
						'password'			=> '',
						'registerTime'		=> $userArr['registerTime'],
						'endTime'			=> $userArr['endTime'],
						'state'				=> $userArr['state'],
						'permissions'		=> $userArr['permissions'],
						'auth'				=> $authgroupArr ? $authgroupArr[0] : "{}",
						'setPasswordStyle'	=> '',
						'isCollapse'		=> false,
						'judgeLogin'		=> $userArr['judgeLogin'],
						'shieldInfo'		=> $userArr['shieldInfo'] != null ? json_decode($userArr['shieldInfo']) : "{}",
					],
					'publicInfo'		=> [
						'projects'			=> $projectArr ? $projectArr : "[]",
						'types'				=> $typeArr ? $typeArr : "[]",
						'details'			=> $detailsArr ? $detailsArr : "[]",
						'groupLabel'		=> $groupLabelArr ? $groupLabelArr : "[]",
						'label'				=> $labelArr ? $labelArr : "[]",
						'privacyTypes'		=> $privacyTypes ? $privacyTypes : "[]",
						'userRecovery'		=> $userRecovery ? $userRecovery : "[]",
						'srcUrl'			=> $this->tool->src_url,
					],
					'token'				=> [
						'access_token'		=> $userArr['access_token'],
						'token_expires_in'	=> $userArr['token_expires_in'],
					]
				];
				return json(['code'=>$this->tool->success,'data'=>$jsonArr,'msg'=>'登录成功','status'=>true]);
			}else{
				return json(['code'=>$this->tool->not_found,'data'=>'3','msg'=>'用户名不存在','status'=>true]);
			}
		}
    }
	
	/**
	* 用户列表
	*/
	public function userList(){
		$list = [1,1,1,1,1,1,1,1,1];
		return json(['code'=>$this->tool->success,'data'=>$list,'msg'=>'success','status'=>true,]);
    }
	
	/**
	* 获取所有权限页面
	*/
	public function auth_list(){
		$authruleArr['userGroup'] = db('auth_rule_copy')->where("sid = 0")->order("id asc")->select();
		for($i=0;$i<count($authruleArr['userGroup']);$i++){
			$authruleArr['userGroup'][$i]['cityOptions']!="[]" ? $authruleArr['userGroup'][$i]['cityOptions']=explode(",",$authruleArr['userGroup'][$i]['cityOptions']) : $authruleArr['userGroup'][$i]['cityOptions']=[]; 
			$authruleArr['userGroup'][$i]['checkAll']!="true" ? $authruleArr['userGroup'][$i]['checkAll']=false : $authruleArr['userGroup'][$i]['checkAll']=true;
			$authruleArr['userGroup'][$i]['isIndeterminate']!="true" ? $authruleArr['userGroup'][$i]['isIndeterminate']=false : $authruleArr['userGroup'][$i]['isIndeterminate']=true;
			$authruleArr['userGroup'][$i]['checkedCities']!="[]" ? $authruleArr['userGroup'][$i]['checkedCities']=explode(",",$authruleArr['userGroup'][$i]['checkedCities']) : $authruleArr['userGroup'][$i]['checkedCities']=[];
			
			$whereid[$i]['id'] = $authruleArr['userGroup'][$i]['id'];
			$authruleArr['userGroup'][$i]['rules'] = db('auth_rule_copy')->field("id,sid,name,index,icon,urlKeyword,state")->where("sid = ".$whereid[$i]['id'])->order("id asc")->select();
		}
		return json(['code'=>$this->tool->success,'data'=>$authruleArr,'msg'=>'success','status'=>true,]);
    }
	
	/**
	* 获取权限组列表
	*/
	public function auth_grouplist(){
		$time = [];
		$authgroupArr = db('auth_group')->order("id asc")->select();
		for($i=0;$i<count($authgroupArr);$i++){
			$authgroupArr[$i]["rules"] = json_decode($authgroupArr[$i]["rules"]);
			$time = db('users')->where("permissions = ".$authgroupArr[$i]["id"])->select();
			$authgroupArr[$i]["userlist"] = $time == false ? [] : $time;
		}
		if($authgroupArr){
			return json(['code'=>$this->tool->success,'data'=>$authgroupArr,'msg'=>'success','status'=>true,]);
		}else{
			return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'权限组数据获取失败','status'=>true,]);
		}
	}
		
	/**
	* 添加权限组
	*/
	public function auth_groupAdd(){
		$groupArr = [
			'title' => input('post.title'),
			'rules' => json_encode(input('post.rules')),
			'disabled' => input('post.disabled'),
		];
		if ($this->request->isPost()) {
			$rtn = db('auth_group')->insert($groupArr);
			if ($rtn) {
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
		
	/**
	* 修改权限组
	*/
	public function auth_groupedit(){
		if ($this->request->isPost()) {
			$where['id']=input("post.id");
			$groupArr = [
				'title' => input('post.title'),
				'rules' => json_encode(input('post.rules')),
				'disabled' => input('post.disabled'),
			];
			$rtn = db('auth_group')->where($where)->update($groupArr);
			if ($rtn) {
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'修改成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'修改失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 权限组删除
	*/
	public function auth_groupdel(){
		if ($this->request->isPost()) {
			$rtn = db('auth_group')->where(['id' => input("post.id")])->delete();
			if ($rtn) {
				// 删除权限组后修改已有此权限组的为设计师权限组（默认修改 id：1为设计师权限组）
				$usr=db('users')->where(["permissions" => input("post.id")])->update(['permissions' => 1]);
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 获取单个权限组
	*/
	public function auth_groupone(){
		if ($this->request->isPost()) {
			$rtn = db('auth_group')->where(["id" => input("post.id")])->find();
			$rtn['rules'] = json_decode($rtn["rules"]);
			if ($rtn) {
				return json(['code'=>$this->tool->success,'data'=>$rtn,'msg'=>'获取成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'获取失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 获取项目list  （弃用）
	*/
	public function projectList(){
		$projectarr=$this->tool->getPublicInfo('project', db('project')->where('1 = 1')->select());
		if($projectarr){
			return json(['code'=>$this->tool->success,'data'=>$projectarr,'msg'=>'获取成功','status'=>true,]);
		}else{
			return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'获取失败','status'=>true,]);
		}
	}
	
	/**
	* 获取项目、类型、分类、标签组、标签的数据
	*/
	public function getPublicInfo(){
		if ($this->request->isPost()) {
			if(stripos(input('post.types'),'user')){
				$users = db('users')->select();
			}
			if(stripos(input('post.types'),'projects')){
				$projects = $this->tool->getPublicInfo('project', db('project')->where('1 = 1')->select());
			}
			if(stripos(input('post.types'),'types')){
				$types = $this->tool->getPublicInfo('type', db('type')->where('1 = 1')->select());
			}
			if(stripos(input('post.types'),'details')){
				$details = $this->tool->getPublicInfo('details', db('details')->where('1 = 1')->select());
			}
			if(stripos(input('post.types'),'groupLabel')){
				$groupLabel = $this->tool->getPublicInfo('groupLabel', db('group_label')->where('1 = 1')->select());
			}
			if(stripos(input('post.types'),'label')){
				$label = $this->tool->getPublicInfo('label', db('label')->where('1 = 1')->select());
			}
			if(stripos(input('post.types'),'privacyType')){
				$privacyType = $this->tool->getPublicInfo('privacyType', db('privacy_type')->where('1 = 1')->select());
				if(count($privacyType) != 0){
					for($i=0;$i<count($privacyType);$i++){
						if($privacyType[$i]['authGroup'] != null){
							$privacyType[$i]['authGroup'] = explode(",",$privacyType[$i]['authGroup']);
							for($j=0;$j<count($privacyType[$i]['authGroup']);$j++){
								$privacyType[$i]['authGroup'][$j] = (int)$privacyType[$i]['authGroup'][$j];
							}
						}
						if($privacyType[$i]['users'] != null){
							$privacyType[$i]['users'] = explode(",",$privacyType[$i]['users']);
							for($g=0;$g<count($privacyType[$i]['users']);$g++){
								$privacyType[$i]['users'][$g] = (int)$privacyType[$i]['users'][$g];
							}
						}
					}					
				}
			}
			if(stripos(input('post.types'),'authGroup')){
				$authGroup = db('auth_group')->field('id,title,state')->where('1 = 1')->select();
			}
			$srcUrl = $this->tool->src_url;
			$data = [
				'users' 		=> isset($users) ? $users : [],
				'projects' 		=> isset($projects) ? $projects : [],
				'types' 		=> isset($types) ? $types : [],
				'details' 		=> isset($details) ? $details : [],
				'groupLabel' 	=> isset($groupLabel) ? $groupLabel : [],
				'label' 		=> isset($label) ? $label : [],
				'privacyType' 	=> isset($privacyType) ? $privacyType : [],
				'authGroup' 	=> isset($authGroup) ? $authGroup : [],
				'srcUrl' 		=> $srcUrl,
			];
			
			return json(['code'=>$this->tool->success,'data'=>$data,'msg'=>'获取成功','status'=>true,]);
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 添加隐私分类
	*/
	public function servicePrivacyTypeAdd(){
		if ($this->request->isPost()) {
			if(input('post.tid') != '' && input('post.state') != '' && input('post.uid') != '' || input('post.qroupid') != ''){
				if(input('post.uid') == ''){
					$rtn=db('privacy_type')->insert(['tid' => input('post.tid'), 'authGroup' => input('post.qroupid'), 'state' => input('post.state')]);
				} else if(input('post.qroupid') == ''){
					$rtn=db('privacy_type')->insert(['tid' => input('post.tid'), 'users' => input('post.uid'), 'state' => input('post.state')]);
				} else if(input('post.qroupid') != '' && input('post.uid') != ''){
					$rtn=db('privacy_type')->insert(['tid' => input('post.tid'), 'users' => input('post.uid'), 'authGroup' => input('post.qroupid'), 'state' => input('post.state')]);
				}
				
				if($rtn){
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,]);
				}else{
					return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败','status'=>true,]);
				}
			} else {
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
			}
			
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 编辑隐私分类
	*/
	public function privacyTypeSave(){
		if ($this->request->isPost()) {
			$temp=db('privacy_type')->where(['id' => input('post.id')])->select();
			if (!$temp || count($temp)==1 && $temp[0]['id']==input('post.id')) {
				if(input('post.uid') == ''){
					$rtn=db('privacy_type')->where(['id' => input('post.id')])->update(['tid' => input('post.tid'), 'users' => null, 'authGroup' => input('post.qroupid'), 'state' => input('post.state')]);
				} else if(input('post.qroupid') == ''){
					$rtn=db('privacy_type')->where(['id' => input('post.id')])->update(['tid' => input('post.tid'), 'users' => input('post.uid'), 'authGroup' => null, 'state' => input('post.state')]);
				} else if(input('post.qroupid') != '' && input('post.uid') != ''){
					$rtn=db('privacy_type')->where(['id' => input('post.id')])->update(['tid' => input('post.tid'), 'users' => input('post.uid'), 'authGroup' => input('post.qroupid'), 'state' => input('post.state')]);
				}
				
				if($rtn == true){
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑成功','status'=>true,]);
				}else if ($rtn == false){
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'编辑失败','status'=>db('privacy_type')->getLastSql(),]);
				}
			}
		}else{
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}

	/**
	* 删除隐私分类
	*/
	public function privacyTypeDel(){
		if ($this->request->isPost()) {
			$rtn = db('privacy_type')->where(['id' => input("post.id")])->delete();
			if ($rtn) {
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 添加项目
	*/
	public function projectAdd(){
		if ($this->request->isPost()) {
			$rtn=db('project')->insert(['xname' => input('post.xname'), 'state' => input('post.state'), 'webShow' => input('post.webShow')]);
			if($rtn){
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	
	/**
	* 编辑项目
	*/
	public function projectsave(){
		if ($this->request->isPost()) {
			$temp=db('project')->where(['xname' => input('post.xname')])->select();
			if (!$temp || count($temp)==1 && $temp[0]['pid']==input('post.pid')) {
				$projectarr=db('project')->where(['pid' => input('post.pid')])->update(['xname' => input('post.xname'), 'state' => input('post.state'), 'webShow' => input('post.state') == '2' ? '0' : input('post.webShow')]);
				if($projectarr == true){
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑成功','status'=>true,]);
				}else if ($projectarr == false){
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'编辑失败','status'=>true,]);
				}else if ($projectarr == 0){
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'项目名称重复，请修改后重新提交','status'=>true,]);
				}
			} else {
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'名称重复，请修改后重新提交','status'=>true,]);
			}
		}else{
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 删除项目
	*/
	public function projectdel(){
		if ($this->request->isPost()) {
			$rtn = db('project')->where(['pid' => input("post.pid")])->delete();
			if ($rtn) {
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 添加项目屏蔽人
	*/
	public function addShieldUser(){
		if ($this->request->isPost()) {
			$detaArr = [
				'uId' 			=> input('post.uId'),
				'shieldInfo' 	=> input('post.shieldInfo'),
			];
			$userInfo = db('users')->where(['uId' => $detaArr['uId']])->find();
			$userShieldInfo = json_decode($userInfo['shieldInfo'],true);
			// var_dump($userShieldInfo);
			if($userShieldInfo == null){
				$rtn = db('users')->where(['uId' => $detaArr['uId']])->update(['shieldInfo' => json_encode($detaArr['shieldInfo'])]);
				if ($rtn) {
					return json(['code'=>$this->tool->success,'data'=>'1','msg'=>'添加成功','status'=>true,]);
				}else{
					return json(['code'=>$this->tool->fail,'data'=>'1','msg'=>'添加失败','status'=>true,]);
				}
			} else {
				$panduan = false; 
				$xiugai = false;
				for($i=0;$i<count($userShieldInfo);$i++){
					if($userShieldInfo[$i]['pid'] == $detaArr['shieldInfo'][0]['pid']&&$userShieldInfo[$i]['state'] == '1'){
						$panduan = true;
					} else if($userShieldInfo[$i]['pid'] == $detaArr['shieldInfo'][0]['pid']&&$userShieldInfo[$i]['state'] == '0'){
						$xiugai = true;
						$userShieldInfo[$i]['state'] = '1';
						for($j=0;$j<count($userShieldInfo[$i]['type']);$j++){
							$userShieldInfo[$i]['type'][$j]['state'] = '1';
						}
					}
				}
				if($panduan){
					return json(['code'=>$this->tool->fail,'data'=>'2','msg'=>'已添加','status'=>true,]);
				} else {
					$xiugai == false ? array_push($userShieldInfo,$detaArr['shieldInfo'][0]) : $xiugai = $xiugai;
					$rtn = db('users')->where(['uId' => $detaArr['uId']])->update(['shieldInfo' => json_encode($userShieldInfo)]);
					// echo db('users')->getLastSql();
					if ($rtn) {
						return json(['code'=>$this->tool->success,'data'=>'3','msg'=>'添加成功','status'=>true,]);
					}else{
						return json(['code'=>$this->tool->fail,'data'=>'3','msg'=>'添加失败','status'=>true,]);
					}
				}
			}
			
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 添加类型屏蔽人
	*/
	public function addShieldUserType(){
		if ($this->request->isPost()) {
			$detaArr = [
				'uId' 			=> input('post.uId'),
				'shieldInfo' 	=> input('post.shieldInfo')
			];
			$acceptData = $detaArr['shieldInfo'];
			$userInfo = db('users')->where(['uId' => $detaArr['uId']])->find();
			$userShieldInfo = json_decode($userInfo['shieldInfo'],true);
			if($userShieldInfo == null){
				$rtn = db('users')->where(['uId' => $detaArr['uId']])->update(['shieldInfo' => json_encode($acceptData)]);
				if ($rtn) {
					return json(['code'=>$this->tool->success,'data'=>'1','msg'=>'添加成功','status'=>true,]);
				}else{
					return json(['code'=>$this->tool->fail,'data'=>'1','msg'=>'添加失败','status'=>true,]);
				}
			} else {
				$panduan = false;
				$num = 0;
				for($i=0;$i<count($userShieldInfo);$i++){
					if($userShieldInfo[$i]['pid'] == $detaArr['shieldInfo'][0]['pid'] && $userShieldInfo[$i]['state'] == '1'){
						$panduan = true;
					} else if($userShieldInfo[$i]['pid'] == $detaArr['shieldInfo'][0]['pid'] && $userShieldInfo[$i]['state'] == '0'){
						for($e=0;$e<count($detaArr['shieldInfo'][0]['type']);$e++){
							if($detaArr['shieldInfo'][0]['type'][$e]['state'] == '1'){
								for($j=0;$j<count($userShieldInfo[$i]['type']);$j++){
									if($userShieldInfo[$i]['type'][$j]['tid'] == $detaArr['shieldInfo'][0]['type'][$e]['tid'] && $userShieldInfo[$i]['type'][$j]['state'] == '0'){
										$userShieldInfo[$i]['type'][$j]['state'] = '1';
										// $this->tool->inspectTypeShield($userShieldInfo[$i]['type']) == true ? $userShieldInfo[$i]['state'] = '1' : $userShieldInfo[$i]['state'] = '0';
									}else if($userShieldInfo[$i]['type'][$j]['tid'] == $detaArr['shieldInfo'][0]['type'][$e]['tid'] && $userShieldInfo[$i]['type'][$j]['state'] == '1'){
										$panduan = true;
									}
								}
							}
						}
					} else {
						$num++;
					}
				}
				count($userShieldInfo) == $num ? array_push($userShieldInfo,$detaArr['shieldInfo'][0]):$num = $num;
				if($panduan){
					// var_dump($panduan);
					return json(['code'=>$this->tool->fail,'data'=>'2','msg'=>'已添加','status'=>true,]);
				} else {
					// var_dump($userShieldInfo);
					$rtn = db('users')->where(['uId' => $detaArr['uId']])->update(['shieldInfo' => json_encode($userShieldInfo)]);
					if ($rtn) {
						return json(['code'=>$this->tool->success,'data'=>'2','msg'=>'添加成功','status'=>true,]);
					}else{
						return json(['code'=>$this->tool->fail,'data'=>'2','msg'=>'添加失败','status'=>true,]);
					}
				}
			}
		}
	}
	
	/**
	* 编辑类型
	*/
	public function typeupdate(){
		if ($this->request->isPost()) {
			$temp=db('type')->where(['lname' => input('post.lname')])->select();
			if (!$temp || count($temp)==1 && $temp[0]['tid']==input('post.tid')) {
				$typearr=db('type')->where(['tid' => input('post.tid')])->update(['lname' => input('post.lname'), 'state' => input('post.state'), 'webShow' => input('post.state') == '2' ? '0' : input('post.webShow')]);
				if($typearr == true){
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑成功','status'=>true,]);
				}else if ($typearr == false){
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'编辑失败','status'=>true,]);
				}else if ($typearr == 0){
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请不要重复修改','status'=>true,]);
				}
			} else {
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'名称重复，请修改后重新提交','status'=>true,]);
			}
		}else{
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
		
	}
	
	/**
	* 添加类型
	*/
	public function typeAdd(){
		if ($this->request->isPost()) {
			$rtn=db('type')->insert(['lname' => input('post.lname'), 'state' => input('post.state'), 'webShow' => input('post.webShow')]);
			if($rtn){
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
		
	}
	
	/**
	* 删除类型
	*/
	public function typeDel(){
		if ($this->request->isPost()) {
			$rtn = db('type')->where(['tid' => input("post.tid")])->delete();
			if ($rtn) {
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 添加分类
	*/
	public function detailsAdd(){
		if($this->request->isPost()){
			$articleaaa=db('details')->where(['tbid' => input('post.tbid'), 'dname' => input('post.dname')])->select();
			if($articleaaa){
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败,已有此分类','status'=>true,]);
			}else{
				$detailslistArr = [
					'tbid' 		=> input('post.tbid'),
					'dname' 	=> input('post.dname'),
					'state' 	=> input('post.state'),
					'webShow' 	=> input('post.webShow'),
				];
				$rtn=db('details')->insert($detailslistArr);
				if($rtn){
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,]);
				}else{
					return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败','status'=>true,]);
				}
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 编辑分类
	*/
	public function detailssave(){
		if ($this->request->isPost()) {
			$temp=db('details')->where(['dname' => input('post.dname')])->select();
			if (!$temp || count($temp)==1 && $temp[0]['did']==input('post.did')) {
				$articleaaa=db('details')->where(['did' => input('post.did')])->update(['tbid' => input('post.tbid'), 'dname' 	=> input('post.dname'), 'state' => input('post.state'), 'webShow' => input('post.state') == '2' ? '0' : input('post.webShow')]);
				if($articleaaa == true){
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑成功','status'=>true,]);
				}else if ($articleaaa == false){
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'编辑失败','status'=>true,]);
				}else if ($articleaaa == 0){
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请不要重复修改','status'=>true,]);
				}
			} else {
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'名称重复，请修改后重新提交','status'=>true,]);
			}
		}else{
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 删除分类
	*/
	public function detailsDel(){
		if ($this->request->isPost()) {
			
			$rtn = db('privacy_type')->where(['tid' => input("post.did")])->select();
			if(!$rtn){	
				$rtn = db('details')->where(['did' => input("post.did")])->delete();
				if ($rtn) {
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,]);
				}else{
					return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,]);
				}
			} else {
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败,请先删除绑定的隐私分类和文章。','status'=>true,]);
			}
			
			
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 添加标签组
	*/
	public function labelsAdd(){
		if($this->request->isPost()){
			$articleaaa=db('group_label')->where(['name' => input('post.name')])->select();
			if($articleaaa){
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败,已有此标签组','status'=>true,]);
			}else{
				$rtn=db('group_label')->insert(['lid' => null, 'name' => input('post.name'), 'state' => '1', 'webShow' => '1']);
				if($rtn){
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,]);
				}else{
					return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败','status'=>true,]);
				}
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 编辑标签组
	*/
	public function labelssave(){
		if ($this->request->isPost()) {
			$temp=db('group_label')->where(['name' => input('post.name')])->select();
			if (!$temp || count($temp)==1) {
				$articleaaa=db('group_label')->where(['gid' => input('post.gid')])->update(['lid' => null, 'name' => input('post.name'), 'state' => input('post.state'), 'webShow' => input('post.state') == '2' ? '0' : input('post.webShow')]);
				if($articleaaa == true){
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑成功','status'=>true,]);
				}else if ($articleaaa == false){
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'编辑失败','status'=>true,]);
				}else if ($articleaaa == 0){
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请不要重复修改','status'=>true,]);
				}
			} else {
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'名称重复，请修改后重新提交','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 删除标签组
	*/
	public function labelsDel(){
		if ($this->request->isPost()) {
			$rtn = db('group_label')->where(['gid' => input("post.gid")])->delete();
			if ($rtn) {
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	//label标签添加
	public function labelAdd(){
		if($this->request->isPost()){
			$articleaaa=db('label')->where(['name' => input('post.name')])->select();
			if($articleaaa){
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败,已有此标签','status'=>true,]);
			}else{
				$rtn=db('label')->insert(['gid' => input('post.gid'), 'name' => input('post.name'), 'state' => '1', 'webShow' => '1',]);
				if($rtn){
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,]);
				}else{
					return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败','status'=>true,]);
				}
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	//label标签修改
	public function labelsave(){
		if ($this->request->isPost()) {
			$temp=db('label')->where(['name' => input('post.name')])->select();
			if (!$temp || count($temp)==1) {
				$articleaaa=db('label')->where(['lid' => input('post.lid')])->update(['gid' => input('post.gid'), 'name' => input('post.name'), 'state' => input('post.state'), 'webShow' => input('post.state') == '2' ? '0' : input('post.webShow')]);
				if($articleaaa == true){
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑成功','status'=>true,]);
				}else if ($articleaaa == false){
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'编辑失败','status'=>true,]);
				}else if ($articleaaa == 0){
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请不要重复修改','status'=>true,]);
				}
			} else {
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'名称重复，请修改后重新提交','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 删除标签
	*/
	public function labelDel(){
		if ($this->request->isPost()) {
			$rtn = db('label')->where(['lid' => input("post.lid")])->delete();
			if ($rtn) {
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 用户回收list
	*/
	public function userRecovery(){
		$usersArr=db('users')->where('state = 1')->select();
		for($i=0;$i<count($usersArr);$i++){
			$temp = Db::query("SELECT COUNT(*) as articleNum FROM img_article WHERE uId = ".$usersArr[$i]['uId']);
			$usersArr[$i]['articleNum'] = intval($temp[0]['articleNum']);
			$usersArr[$i]['shieldInfo'] = json_decode($usersArr[$i]['shieldInfo'],true);
		}
		return json(['code'=>$this->tool->success,'data'=>$usersArr == false ? [] : $usersArr, 'msg'=>'success','status'=>true,]);
	}
	
	/**
	* 用户list(后台所有的用户)
	*/
	public function user_list(){
		$usersArr=db('users')->select();
		for($i=0;$i<count($usersArr);$i++){
			$temp = Db::query("SELECT COUNT(*) as articleNum FROM img_article WHERE uId = ".$usersArr[$i]['uId']);
			$usersArr[$i]['articleNum'] = intval($temp[0]['articleNum']);
			$usersArr[$i]['shieldInfo'] = json_decode($usersArr[$i]['shieldInfo'],true);
		}
		return json(['code'=>$this->tool->success,'data'=>$usersArr == false ? [] : $usersArr, 'msg'=>'success','status'=>true,]);
	}
	
	/**
	* 用户list(后台管理用户获取)
	*/
	public function manage_user_list(){
		$usersArr=db('users')->where('state = 0')->select();
		for($i=0;$i<count($usersArr);$i++){
			$temp = Db::query("SELECT COUNT(*) as articleNum FROM img_article WHERE uId = ".$usersArr[$i]['uId']);
			$usersArr[$i]['articleNum'] = intval($temp[0]['articleNum']);
			$usersArr[$i]['shieldInfo'] = json_decode($usersArr[$i]['shieldInfo'],true);
		}
		return json(['code'=>$this->tool->success,'data'=>$usersArr == false ? [] : $usersArr, 'msg'=>'success','status'=>true,]);
	}
	
	
	
	/**
	* 用户list(前台显示需要的所有用户)
	*/
	public function web_user_list(){
		$usersArr = db('users')->select();
		for($i=0;$i<count($usersArr);$i++){
			$temp = Db::query("SELECT COUNT(*) as articleNum FROM img_article WHERE uId = ".$usersArr[$i]['uId']);
			$usersArr[$i]['articleNum'] = intval($temp[0]['articleNum']);
			$usersArr[$i]['shieldInfo'] = json_decode($usersArr[$i]['shieldInfo'],true);
		}
		return json(['code'=>$this->tool->success,'data'=>$usersArr == false ? [] : $usersArr, 'msg'=>'success','status'=>true,]);
	}
	
	/**
	* 用户还原
	*/
	public function reduction(){
		if ($this->request->isPost()) {
			$rtn=db('users')->where(['uId' => input("post.uId")])->update(['state' => 0]);
			if($rtn){
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'还原成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'还原失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 超管修改用户信息
	*/
	public function guanliuserSave(){
		if($this->request->isPost()){
			if(input("post.password") == '') {
				$rtn=db('users')->where(['uId' => input("post.uId")])->update(['nickname' => input("post.nickname"), 'sex' => input("post.sex"), 'permissions' => input("post.permissions"), 'webShow' => input('post.state') == '1' ? '0' : input('post.webShow'), 'state' => input("post.state"), 'judgeLogin' => input("post.judgeLogin"), 'shieldInfo' => input("post.shieldInfo") == null ? null : json_encode(input("post.shieldInfo"))]);
			} else {
				$rtn=db('users')->where(['uId' => input("post.uId")])->update(['nickname' => input("post.nickname"), 'sex' => input("post.sex"), 'password' => md5(input("post.password")), 'permissions' => input("post.permissions"), 'webShow' => input('post.state') == '2' ? '0' : input('post.webShow'), 'state' => input("post.state"), 'shieldInfo' => input("post.shieldInfo") == null ? null : json_encode(input("post.shieldInfo"))]);
			}
			
			if($rtn){
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'编辑失败','status'=>db('users')->getLastSql(),]);
			}
		}
	}
	
	/**
	 * 判断token
	 */
	public function getUserToken()
	{
		if($this->request->isPost()){
			$user = db('users')->where(['uId' => input('post.uId')])->select();
			if($user){
				return json(['code'=>$this->tool->success,'data'=>$user[0],'msg'=>'success','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'没有此token','status'=>true,]);
			}
		}
	}
	
	/**
	 * 修改用户个人信息
	 */
	public function userSave(){
		if($this->request->isPost())
		{
			$usersArr=$this->request->param();
			if($usersArr){
				if(!file_exists($usersArr['headPortraitSrc'])) return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
				$usersArrs = [
					'headPortraitSrc' => $usersArr['headPortraitSrc'],
					'nickname' => $usersArr['nickname'],
					'sex' => $usersArr['sex'],
				];
				$rtn=db('users')->update($usersArr);
				if($rtn || $rtn===0){
					return json(['code'=>$this->tool->success,'data'=>$usersArrs,'msg'=>'更新成功','status'=>true,]);
				}else{
					return json(['code'=>$this->tool->fail,'data'=>$res,'msg'=>'更新失败','status'=>true,]);
				}
			}else{
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
			}
		}
	}
	
	/**
	 * 获取用户个人信息
	 */
	public function getUserInfo(){
		if($this->request->isPost())
		{
			$userInfo=db('users')->where(['uId' => input('post.uId')])->find();
			$authgroupArr = db('auth_group')->where(['id' => $userInfo['permissions']])->find();
			$authgroupArr['rules']=json_decode($authgroupArr['rules']);
			$jsonArr = [
				'adminInfo'			=> [
					'uId'				=> $userInfo['uId'],
					'headPortraitSrc'	=> $userInfo['headPortraitSrc'],
					'userName'			=> $userInfo['userName'],
					'nickname'			=> $userInfo['nickname'],
					'sex'				=> $userInfo['sex'],
					'registerTime'		=> $userInfo['registerTime'],
					'endTime'			=> $userInfo['endTime'],
					'state'				=> $userInfo['state'],
					'permissions'		=> $userInfo['permissions'],
					'auth'				=> $authgroupArr ? $authgroupArr : "{}",
					'judgeLogin'		=> $userInfo['judgeLogin'],
				]
			];
			return json(['code'=>$this->tool->success,'data'=>$jsonArr,'msg'=>'success','status'=>true,]);
		}
	}
	
	/**
	 * 文件删除
	 */
	public function delfile()
	{
		if($this->request->isPost())
		{
			if(file_exists(input("post.filesrc"))){
				unlink(input("post.filesrc"));
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,]);
			} else {
				return json(['code'=>$this->tool->fail,'data'=>$res,'msg'=>'删除失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	 * 文件删除2
	 */
	public function delfile2()
	{
		if($this->request->isPost())
		{
			$filesrc = explode(',',input("post.filesrc"));
			foreach($filesrc as $value){
			 if(file_exists(input("post.filesrc"))) unlink($value);
			}
			return json(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,]);
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 获取统计页面数据All
	* 原数据统计逻辑代码，以便后期查看
	*/
	public function getAdminStatisticsData2 () {
		$where['uId']=input("post.uId");
		$data['temp'] = db('users')->where($where)->select();
		// 获取当前年份每个月开始和结束时间戳
		$year = $this->tool->getYearAll();
		// 获取当前月每天的开始和结束时间戳
		$month = $this->tool->getMonthAll();
		// 获取当前月开始的时间戳
		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
		// 获取当前月结束的时间戳
		$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
		if ($data['temp'][0]['permissions'] == 2 || 5) {
			$data['m'] = $month;
			// 下载总数
			$data['activeDownloadAll'] = db('information')->where('1=1')->count();
			// 用户总数
			$data['userAll'] = db('users')->where('1=1')->count();
			// 文章总数
			$data['activeAll'] = db('article')->where('1=1')->count();
			// 文章img类型总数
			$data['activeImg'] = db('article')->where("typeFile = 'img'")->count();
			// 文章psd类型总数
			$data['activePsd'] = db('article')->where("typeFile = 'psd'")->count();
			// 文章video类型总数
			$data['activeVideo'] = db('article')->where("typeFile = 'video'")->count();
			// 文章type类型总数
			$data['activeType'] = Db::query("SELECT t.lname, COUNT(a.mId) as 'count' FROM img_article a, img_type t WHERE a.typeid = t.tid GROUP BY t.lname");
			// 各项目文章总数
			$data['activeProject'] = Db::query("SELECT g.xname, COUNT(a.mId) as 'count' FROM img_article a, img_project g WHERE a.projectid = g.pid GROUP BY g.xname");
			// 各用户文章总数
			$data['activeUsers'] = Db::query("SELECT u.nickname, COUNT(a.mId) as 'count' FROM img_article a, img_users u WHERE a.uId = u.uId GROUP BY u.nickname");
			// 用户下载排行榜（在职用户）
			$data['articleRanking'] = Db::query("SELECT u.nickname, COUNT(a.mId) as 'count' FROM img_article a, img_users u WHERE a.uId = u.uId and u.state != 1 GROUP BY u.nickname ORDER BY count DESC");
			// 文章type类型总数2，当前月份的
			$data['activeType2'] = Db::query("SELECT t.lname, COUNT(a.mId) as 'count' FROM img_article a, img_type t WHERE a.typeid = t.tid and a.registerTimeImg>=".$beginThismonth." and a.registerTimeImg<=".$endThismonth." GROUP BY t.lname");
			// 文章img类型总数(当前月)
			$data['activeImgMonth'] = db('article')->where("typeFile = 'img' and registerTimeImg>=".$beginThismonth." and registerTimeImg<=".$endThismonth)->count();
			// 文章psd类型总数(当前月)
			$data['activePsdMonth'] = db('article')->where("typeFile = 'psd' and registerTimeImg>=".$beginThismonth." and registerTimeImg<=".$endThismonth)->count();
			// 文章video类型总数(当前月)
			$data['activeVideoMonth'] = db('article')->where("typeFile = 'video' and registerTimeImg>=".$beginThismonth." and registerTimeImg<=".$endThismonth)->count();
			// 用户文章类型1（img/psd/video）
			$data['activeUserType1'] = Db::query("select u.nickname, a.uId,a.typeFile,count(*) as 'count' from img_article a, img_users u WHERE a.uId = u.uId and u.userName !='admin' group by a.typeFile,a.uId");
			// 用户文章类型2（类型分类）
			$data['activeUserType2'] = Db::query("select u.nickname, a.typeid, count(*) as 'count' from img_article a, img_type t, img_users u WHERE a.typeid = t.tid and a.uId = u.uId  and u.userName !='admin' group by a.typeid,a.uId");
			// 所有用户昵称
			$data['userNicknameAll'] = Db::query("select nickname,uId from img_users WHERE state!=1 and userName !='admin'");
			// 所有类型
			$data['typeAll'] = db('type')->where("1=1")->select();
			// 所有项目
			$data['projectAll'] = db('project')->where("1=1")->select();
			
			// 最近发布文章
			$data['activeLately'] = Db::query("SELECT a.mId, a.typeFile, a.title, a.img, a.psd, a.video, a.ai, a.pdf, a.word, a.excel, a.engineering, a.registerTimeImg, u.nickname, u.sex, a.click FROM img_article a, img_users u WHERE a.uId = u.uId GROUP BY a.title ORDER BY a.registerTimeImg DESC limit 20");
			// json字符串转数组
			if(count($data['activeLately']) != 0){
				for($a=0;$a<count($data['activeLately']);$a++){
					$data['activeLately'][$a]['img'] = json_decode($data['activeLately'][$a]['img']);
					$data['activeLately'][$a]['psd'] = json_decode($data['activeLately'][$a]['psd']);
					$data['activeLately'][$a]['video'] = json_decode($data['activeLately'][$a]['video']);
					$data['activeLately'][$a]['ai'] = json_decode($data['activeLately'][$a]['ai']);
					$data['activeLately'][$a]['pdf'] = json_decode($data['activeLately'][$a]['pdf']);
					$data['activeLately'][$a]['word'] = json_decode($data['activeLately'][$a]['word']);
					$data['activeLately'][$a]['excel'] = json_decode($data['activeLately'][$a]['excel']);
					$data['activeLately'][$a]['engineering'] = json_decode($data['activeLately'][$a]['engineering']);
				}
			}
			
			// 全年文章类型分布
			for ($i=0; $i<=11; $i++) {
				$data['activeTypeYear'][$i][0] = $year[$i]->y;
				$data['activeTypeYear'][$i][1] = db('article')->where("typeFile = 'img' and registerTimeImg >=".$year[$i]->start." and registerTimeImg <= ".$year[$i]->end)->count();
				$data['activeTypeYear'][$i][2] = db('article')->where("typeFile = 'psd' and registerTimeImg >=".$year[$i]->start." and registerTimeImg <= ".$year[$i]->end)->count();
				$data['activeTypeYear'][$i][3] = db('article')->where("typeFile = 'video' and registerTimeImg >=".$year[$i]->start." and registerTimeImg <= ".$year[$i]->end)->count();
			}
			$data['year'] = $year;
			
			// 全年用户每月下载数
			$data['temp'] = [];
			for ($j=0; $j<=11; $j++) {
				$data['activeDownloadYearAll'][$j] = Db::query("SELECT u.nickname, count(*) as 'count' FROM img_information i, img_users u WHERE u.uId = i.froid and i.created >= ".$year[$j]->start." and i.created <= ".$year[$j]->end." group by u.nickname");
			}
			
			// 当月在职用户每日发布情况
			$data['temp'] = [];
			for ($e=0; $e<count($data['userNicknameAll']); $e++) {
				$data['activeUserReleaseMonthAll'][$e]['name'] = $data['userNicknameAll'][$e]['nickname'];
				for ($l=0; $l<=count($month); $l++) {
					$data['temp'] = db('article')->where("uId = ".$data['userNicknameAll'][$e]['uId']." and registerTimeImg >=".$month[$l]['start']." and registerTimeImg <= ".$month[$l]['end'])->count();
					$data['activeUserReleaseMonthAll'][$e]['data'][$l] = $data['temp'] == null ? '0' : $data['temp'];
				}
			}
			
			// 每个类型下项目占比（总、月）
			$data['activeProjectMonthImg'] = Db::query("SELECT p.pid, p.xname, count(*) as 'img' FROM (SELECT * FROM img_article WHERE typeFile = 'img' and registerTimeImg >= ".$beginThismonth." and registerTimeImg <= ".$endThismonth.") a, img_project p WHERE a.projectid = p.pid group by p.pid;");
			$data['activeProjectMonthPsd'] = Db::query("SELECT p.pid, p.xname, count(*) as 'psd' FROM (SELECT * FROM img_article WHERE typeFile = 'psd' and registerTimeImg >= ".$beginThismonth." and registerTimeImg <= ".$endThismonth.") a, img_project p WHERE a.projectid = p.pid group by p.pid;");
			$data['activeProjectMonthVideo'] = Db::query("SELECT p.pid, p.xname, count(*) as 'video' FROM (SELECT * FROM img_article WHERE typeFile = 'video' and registerTimeImg >= ".$beginThismonth." and registerTimeImg <= ".$endThismonth.") a, img_project p WHERE a.projectid = p.pid group by p.pid;");
			$data['activeProjectTotalImg'] = Db::query("SELECT p.pid, p.xname, count(*) as 'img' FROM (SELECT * FROM img_article WHERE typeFile = 'img') a, img_project p WHERE a.projectid = p.pid group by p.pid;");
			$data['activeProjectTotalPsd'] = Db::query("SELECT p.pid, p.xname, count(*) as 'psd' FROM (SELECT * FROM img_article WHERE typeFile = 'psd') a, img_project p WHERE a.projectid = p.pid group by p.pid;");
			$data['activeProjectTotalVideo'] = Db::query("SELECT p.pid, p.xname, count(*) as 'video' FROM (SELECT * FROM img_article WHERE typeFile = 'video') a, img_project p WHERE a.projectid = p.pid group by p.pid;");
			
			// 每个类型下项目下用户占比的（总、月）
			$data['activeProjectUserTotalImg'] = Db::query("SELECT p.pid, p.xname, u.uId, u.nickname, count(*) as 'img' FROM (SELECT * FROM img_article WHERE typeFile = 'img') a, img_project p, img_users u WHERE a.projectid = p.pid and a.uId = u.uId group by u.nickname, p.xname;");
			$data['activeProjectUserTotalPsd'] = Db::query("SELECT p.pid, p.xname, u.uId, u.nickname, count(*) as 'psd' FROM (SELECT * FROM img_article WHERE typeFile = 'psd') a, img_project p, img_users u WHERE a.projectid = p.pid and a.uId = u.uId group by u.nickname, p.xname;");
			$data['activeProjectUserTotalVideo'] = Db::query("SELECT p.pid, p.xname, u.uId, u.nickname, count(*) as 'video' FROM (SELECT * FROM img_article WHERE typeFile = 'video') a, img_project p, img_users u WHERE a.projectid = p.pid and a.uId = u.uId group by u.nickname, p.xname;");
			$data['activeProjectUserMonthImg'] = Db::query("SELECT p.pid, p.xname, u.uId, u.nickname, count(*) as 'img' FROM (SELECT * FROM img_article WHERE typeFile = 'img' and registerTimeImg >= ".$beginThismonth." and registerTimeImg <= ".$endThismonth.") a, img_project p, img_users u WHERE a.projectid = p.pid and a.uId = u.uId group by u.nickname, p.xname;");
			$data['activeProjectUserMonthPsd'] = Db::query("SELECT p.pid, p.xname, u.uId, u.nickname, count(*) as 'psd' FROM (SELECT * FROM img_article WHERE typeFile = 'psd' and registerTimeImg >= ".$beginThismonth." and registerTimeImg <= ".$endThismonth.") a, img_project p, img_users u WHERE a.projectid = p.pid and a.uId = u.uId group by u.nickname, p.xname;");
			$data['activeProjectUserMonthVideo'] = Db::query("SELECT p.pid, p.xname, u.uId, u.nickname, count(*) as 'video' FROM (SELECT * FROM img_article WHERE typeFile = 'video' and registerTimeImg >= ".$beginThismonth." and registerTimeImg <= ".$endThismonth.") a, img_project p, img_users u WHERE a.projectid = p.pid and a.uId = u.uId group by u.nickname, p.xname;");
			
			// 每日网站浏览统计
			$data['userBrowseWebInfo'] = Db::query("SELECT u.nickname,count(*) as count,d.uId,d.sameDay FROM img_browse_web_info d, img_users u WHERE d.sameDay = '".date('Y-m-d')."' and d.uId = u.uid group by u.nickname,d.uId");

			return json(['code'=>$this->tool->success,'data'=>$data,'msg'=>'success','status'=>true,]);
		}else{
			return json(['code'=>$this->tool->fail,'data'=>'111','msg'=>'获取失败','status'=>true,]);
		}
	}
	
	/**
	* 获取统计页面数据All
	*/
	public function getAdminStatisticsData () {
		$data['temp'] = db('users')->where("uId = ".input("post.uId"))->select();
		if ($data['temp'][0]['permissions'] == 2 || 5) {
			$data['startDate'] = '2018-7-01';
			
			$startDt = getdate(strtotime($data['startDate']));
			$endDt = getdate(strtotime(date("Y-m-d", time())));
			$sUTime = mktime(12, 0, 0, $startDt['mon'], $startDt['mday'], $startDt['year']);
			$eUTime = mktime(12, 0, 0, $endDt['mon'], $endDt['mday'], $endDt['year']);
			$data['runningDays'] = round(abs($sUTime - $eUTime) / 86400);
			
			// 下载总数
			$data['activeDownloadAll'] = db('information')->where('1=1')->count();
			
			// 用户总数
			$data['userAll'] = db('users')->where('1=1')->count();
			
			// 文章总数
			$data['activeAll'] = db('article')->where('state != 2')->count();
			
			// 文章img类型总数
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where("state != 2 and typeFile LIKE '%img%' ")->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['img']));
			}
			$data['fileType']['img'] = [
				'fileNum' 		=> $temp2,
				'articleNum' 	=> count($temp1),
				'ratio' 		=> round(count($temp1) / ($data['activeAll'] / 100), 2),
			];
			
			// 文章psd类型总数
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where("state != 2 and typeFile LIKE '%psd%' ")->select();
			// dump($temp1);
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['psd']));
			}
			$data['fileType']['psd'] = [
				'fileNum' 		=> $temp2,
				'articleNum' 	=> count($temp1),
				'ratio' 		=> round(count($temp1) / ($data['activeAll'] / 100), 2),
			];
			
			// 文章video类型总数
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where("state != 2 and typeFile LIKE '%video%' ")->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['video']));
			}
			$data['fileType']['video'] = [
				'fileNum' 		=> $temp2,
				'articleNum' 	=> count($temp1),
				'ratio' 		=> round(count($temp1) / ($data['activeAll'] / 100), 2),
			];
			
			// 文章ai类型总数
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where("state != 2 and typeFile LIKE '%ai%' ")->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['ai']));
			}
			$data['fileType']['ai'] = [
				'fileNum' 		=> $temp2,
				'articleNum' 	=> count($temp1),
				'ratio' 		=> round(count($temp1) / ($data['activeAll'] / 100), 2),
			];
			
			// 文章pdf类型总数
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where("state != 2 and typeFile LIKE '%pdf%' ")->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['pdf']));
			}
			$data['fileType']['pdf'] = [
				'fileNum' 		=> $temp2,
				'articleNum' 	=> count($temp1),
				'ratio' 		=> round(count($temp1) / ($data['activeAll'] / 100), 2),
			];
			
			// 文章engineering类型总数
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where("state != 2 and typeFile LIKE '%engineering%' ")->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['engineering']));
			}
			$data['fileType']['zip'] = [
				'fileNum' 		=> $temp2,
				'articleNum' 	=> count($temp1),
				'ratio' 		=> round(count($temp1) / ($data['activeAll'] / 100), 2),
			];
			
			// 文章word类型总数
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where("state != 2 and typeFile LIKE '%word%' ")->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['word']));
			}
			$data['fileType']['word'] = [
				'fileNum' 		=> $temp2,
				'articleNum' 	=> count($temp1),
				'ratio' 		=> round(count($temp1) / ($data['activeAll'] / 100), 2),
			];
			
			// 文章excel类型总数
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where("state != 2 and typeFile LIKE '%excel%' ")->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['excel']));
			}
			$data['fileType']['excel'] = [
				'fileNum' 		=> $temp2,
				'articleNum' 	=> count($temp1),
				'ratio' 		=> round(count($temp1) / ($data['activeAll'] / 100), 2),
			];
			
			// 用户发布排行榜（在职用户）
			$data['articleRanking'] = Db::query("SELECT u.nickname, COUNT(a.mId) as 'count' FROM img_article a, img_users u WHERE a.uId = u.uId and u.state != 1 GROUP BY u.nickname ORDER BY count DESC");
			
			// 最近发布文章
			$data['activeLately'] = Db::query("SELECT a.mId, a.typeFile, a.title, a.img, a.psd, a.video, a.ai, a.pdf, a.word, a.excel, a.engineering, a.registerTimeImg, u.nickname, u.sex, a.click FROM img_article a, img_users u WHERE a.uId = u.uId GROUP BY a.title ORDER BY a.registerTimeImg DESC limit 20");
			
			// 用户收藏排行榜前十 倒序
			$data['userCollect'] = Db::query("SELECT u.nickname,c.collectUid,COUNT(*) as 'count' FROM img_collect_article c, img_users u WHERE c.collectUid = u.uId GROUP BY c.collectUid ORDER BY count DESC");
			
			// 收藏用户最多的十篇文章 倒序
			$data['activeCollect'] = Db::query("SELECT a.title,c.collectMid,COUNT(*) as 'count' FROM img_collect_article c, img_article a WHERE c.collectMid = a.mId GROUP BY c.collectMid ORDER BY count DESC");
			
			// json字符串转数组
			if(count($data['activeLately']) != 0){
				for($a=0;$a<count($data['activeLately']);$a++){
					$data['activeLately'][$a]['img'] = json_decode($data['activeLately'][$a]['img']);
					$data['activeLately'][$a]['psd'] = json_decode($data['activeLately'][$a]['psd']);
					$data['activeLately'][$a]['video'] = json_decode($data['activeLately'][$a]['video']);
					$data['activeLately'][$a]['ai'] = json_decode($data['activeLately'][$a]['ai']);
					$data['activeLately'][$a]['pdf'] = json_decode($data['activeLately'][$a]['pdf']);
					$data['activeLately'][$a]['word'] = json_decode($data['activeLately'][$a]['word']);
					$data['activeLately'][$a]['excel'] = json_decode($data['activeLately'][$a]['excel']);
					$data['activeLately'][$a]['engineering'] = json_decode($data['activeLately'][$a]['engineering']);
				}
			}
			
			return json(['code'=>$this->tool->success,'data'=>$data,'msg'=>'success','status'=>true,]);
		}else{
			return json(['code'=>$this->tool->fail,'data'=>'111','msg'=>'获取失败','status'=>true,]);
		}
	}
	
	/**
	* 查询用户浏览数据
	*/
	public function getUserBrowseWebInfo () {
		if($this->request->isPost())
		{
			
			if(input("post.startDate") == input("post.endDate")){
				$data['riqi'] = [input("post.startDate")];
			} else {
				$data['riqi']= $this->tool->getDateFromRange(strtotime(date(input("post.startDate").'00:00:00')), strtotime(date(input("post.endDate").'00:00:00')));
			}
			$data['users'] = Db::query("select nickname,uId from img_users WHERE state=0");
			
			// 用户名称
			if(count($data['users']) != 0){
				for ($i=0; $i<count($data['users']); $i++) {
					$data['name'][$i] = $data['users'][$i]['nickname'];
				}
			}


			// 循环获取在职用户浏览数据
			for ($e=0; $e<count($data['users']); $e++) {
				$data['userBrowseWebInfo'][$e]['name'] = $data['users'][$e]['nickname'];
				for ($l=0; $l<count($data['riqi']); $l++) {
					$data['temp'] =  Db::query("SELECT count(*) as num from img_browse_web_info WHERE sameDay = '".$data['riqi'][$l]."' and uId = ".$data['users'][$e]['uId']);
					$data['temp3'] =  Db::query("SELECT count(*) as num from img_browse_web_info WHERE sameDay = '".$data['riqi'][$l]."'");
					$data['temp2'][$l] =  "SELECT count(*) as num from img_browse_web_info WHERE sameDay = ".$data['riqi'][$l]." and uId = ".$data['users'][$e]['uId'];
					$data['userBrowseWebInfo'][$e]['data'][$l] = $data['temp'] == null ? '0' : $data['temp'][0]['num'];
					$data['num'][$l] = $data['temp3'] == null ? '0' : $data['temp3'][0]['num'];
					$data['temp'] = $data['temp3'] = [];
				}
			}

			return json(['code'=>$this->tool->success,'data'=>['names'=>$data['name'], 'riqi'=>$data['riqi'], 'info'=>$data['userBrowseWebInfo'], 'num'=>$data['num']],'msg'=>'success','status'=>true,]);
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 统计-文章类型
	* 1）支持单月查询：查询指定月份每天的数据
	* 2）支持最多跨度一年内每月数据
	* 3）支持项目查询
	*/
	public function getArticleSubsection () {
		if($this->request->isPost())
		{
			// 判断指定的时间是单个月还是多个月份
			if(input("post.beginTime") == input("post.endTime") && input("post.beginTime") != '' && input("post.endTime") != ''){
				$shijian = $this->tool->getMonthAll(input("post.beginTime"));
				$data['type'] = '1';
			} else if(input("post.beginTime") != input("post.endTime") && input("post.beginTime") != '' && input("post.endTime") != ''){
				$beginTimeY = explode("-",input("post.beginTime"))[0];
				$beginTimeM = explode("-",input("post.beginTime"))[1];
				$endTimeY = explode("-",input("post.endTime"))[0];
				$endTimeM = explode("-",input("post.endTime"))[1];
				$shijian = $this->tool->getDiyYearAll($beginTimeY,$beginTimeM,$endTimeY,$endTimeM);
				// 数据内容的多少的区别设置
				$data['type'] = count($shijian) <= 6 ? '2':'3';
			}
			
			// 所有类型
			$data['articleTypeName'] = db('type')->where("state=1")->select();
			// 所有项目
			$projects = db('project')->select();
			$data['projects'][0] = [ 'label'=>'运维中', 'options'=>[] ];
			$data['projects'][1] = [ 'label'=>'已结束', 'options'=>[] ];
			for ($s=0; $s<count($projects); $s++) {
				if($projects[$s]['webShow'] == '1'){
					$data['projects'][0]['options'][count($data['projects'][0]['options'])] = ['value'=>$projects[$s]['pid'], 'label'=>$projects[$s]['xname']];
				}
				if($projects[$s]['webShow'] == '0'){
					$data['projects'][1]['options'][count($data['projects'][1]['options'])] = ['value'=>$projects[$s]['pid'], 'label'=>$projects[$s]['xname']];
				}
			}
			// 判断是否有传入项目参数
			if(input("post.project") != ''){
				$projectSql = "projectid = ".input("post.project")." and ";
			} else {
				$projectSql = "";
			}
			
			// 循环查询时间内容数据
			for ($e=0; $e<count($shijian); $e++) {
				$data['riqi'][$e]['time'] = $shijian[$e]['riqi'];
				
				// 文章类型循环查询文章个数
				for ($w=0; $w<count($data['articleTypeName']); $w++) {
					$data['riqi'][$e]['articleType'][$w] = [
						'typeName' => $data['articleTypeName'][$w]['lname'],
						'num' => db('article')->where($projectSql."typeid = ".$data['articleTypeName'][$w]['tid']." and registerTimeImg>=".$shijian[$e]['start']." and registerTimeImg<=".$shijian[$e]['end'])->count(),
					];
				}
			}
			
			// 文章img类型总数(自定义时间)
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where($projectSql."typeFile LIKE '%img%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['img']));
			}
			$data['fileType'][0] = [
				'typeName' 	=> 'img',
				'num' 	=> $temp2
			];
			// 文章psd类型总数(自定义时间)
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where($projectSql."typeFile LIKE '%psd%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['psd']));
			}
			$data['fileType'][1] = [
				'typeName' 	=> 'psd',
				'num' 	=> $temp2,
			];
			// 文章video类型总数(自定义时间)
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where($projectSql."typeFile LIKE '%video%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['video']));
			}
			$data['fileType'][2] = [
				'typeName' 	=> 'video',
				'num' 	=> $temp2,
			];
			// 文章ai类型总数(自定义时间)
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where($projectSql."typeFile LIKE '%ai%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['ai']));
			}
			$data['fileType'][3] = [
				'typeName' 	=> 'ai',
				'num' 	=> $temp2,
			];
			// 文章pdf类型总数(自定义时间)
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where($projectSql."typeFile LIKE '%pdf%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['pdf']));
			}
			$data['fileType'][4] = [
				'typeName' 	=> 'pdf',
				'num' 	=> $temp2,
			];
			// 文章engineering类型总数(自定义时间)
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where($projectSql."typeFile LIKE '%engineering%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['engineering']));
			}
			$data['fileType'][5] = [
				'typeName' 	=> 'zip',
				'num' 	=> $temp2,
			];
			// 文章word类型总数(自定义时间)
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where($projectSql."typeFile LIKE '%word%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['word']));
			}
			$data['fileType'][6] = [
				'typeName' 	=> 'word',
				'num' 	=> $temp2,
			];
			// 文章excel类型总数(自定义时间)
			$temp1 = []; $temp2 = 0;
			$temp1 = db('article')->where($projectSql."typeFile LIKE '%excel%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
			for ($q=0; $q<count($temp1); $q++) {
				$temp2 += count((array)json_decode($temp1[$q]['excel']));
			}
			$data['fileType'][7] = [
				'typeName' 	=> 'excel',
				'num' 	=> $temp2,
			];
			
			return json(['code'=>$this->tool->success,'data'=>$data,'msg'=>'success','status'=>$shijian,]);

		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 统计-用户发布
	* 1）支持单月查询：查询指定月份每天的数据
	* 2）支持最多跨度一年内每月数据
	* 3）支持单用户（单日总数）/多用户（每月总数）数据显示
	*/
	public function getArticleUserSubsection(){
		if($this->request->isPost())
		{
			// 判断指定的时间是单个月还是多个月份
			if(input("post.beginTime") == input("post.endTime") && input("post.beginTime") != '' && input("post.endTime") != ''){
				$shijian = $this->tool->getMonthAll(input("post.beginTime"));
				$echartsType = '1';
			} else if(input("post.beginTime") != input("post.endTime") && input("post.beginTime") != '' && input("post.endTime") != ''){
				$beginTimeY = explode("-",input("post.beginTime"))[0];
				$beginTimeM = explode("-",input("post.beginTime"))[1];
				$endTimeY = explode("-",input("post.endTime"))[0];
				$endTimeM = explode("-",input("post.endTime"))[1];
				$shijian = $this->tool->getDiyYearAll($beginTimeY,$beginTimeM,$endTimeY,$endTimeM);
			}
			
			// 数据模型的类别设置
			$data['type'] = input("post.uid") == "" ? '2':'1';
			
			// 所有类型
			$data['articleTypeName'] = db('type')->where("state=1")->select();
			$data['users'] = db('users')->field('uId,nickname,state')->where("state=".input("post.state"))->select();
			$data['userALL'] = db('users')->field('uId,nickname,state')->select();
			
			// 所有项目
			$projects = db('project')->select();
			$data['projects'][0] = [ 'label'=>'运维中', 'options'=>[] ];
			$data['projects'][1] = [ 'label'=>'已结束', 'options'=>[] ];
			for ($s=0; $s<count($projects); $s++) {
				if($projects[$s]['webShow'] == '1'){
					$data['projects'][0]['options'][count($data['projects'][0]['options'])] = ['value'=>$projects[$s]['pid'], 'label'=>$projects[$s]['xname']];
				}
				if($projects[$s]['webShow'] == '0'){
					$data['projects'][1]['options'][count($data['projects'][1]['options'])] = ['value'=>$projects[$s]['pid'], 'label'=>$projects[$s]['xname']];
				}
			}
			// 判断是否有传入项目参数
			if(input("post.project") != ''){
				$projectSql = "projectid = ".input("post.project")." and ";
			} else {
				$projectSql = "";
			}
			
			if(input("post.uid") == ""){
				// 文章img类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."typeFile LIKE '%img%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['img']));
				}
				$data['fileType'][0] = [
					'typeName' 	=> 'img',
					'num' 	=> $temp2
				];
				// 文章psd类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."typeFile LIKE '%psd%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['psd']));
				}
				$data['fileType'][1] = [
					'typeName' 	=> 'psd',
					'num' 	=> $temp2,
				];
				// 文章video类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."typeFile LIKE '%video%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['video']));
				}
				$data['fileType'][2] = [
					'typeName' 	=> 'video',
					'num' 	=> $temp2,
				];
				// 文章ai类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."typeFile LIKE '%ai%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['ai']));
				}
				$data['fileType'][3] = [
					'typeName' 	=> 'ai',
					'num' 	=> $temp2,
				];
				// 文章pdf类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."typeFile LIKE '%pdf%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['pdf']));
				}
				$data['fileType'][4] = [
					'typeName' 	=> 'pdf',
					'num' 	=> $temp2,
				];
				// 文章engineering类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."typeFile LIKE '%engineering%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['engineering']));
				}
				$data['fileType'][5] = [
					'typeName' 	=> 'zip',
					'num' 	=> $temp2,
				];
				// 文章word类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."typeFile LIKE '%word%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['word']));
				}
				$data['fileType'][6] = [
					'typeName' 	=> 'word',
					'num' 	=> $temp2,
				];
				// 文章excel类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."typeFile LIKE '%excel%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['excel']));
				}
				$data['fileType'][7] = [
					'typeName' 	=> 'excel',
					'num' 	=> $temp2,
				];
				
				for ($q=0; $q<count($data['users']); $q++) {
					$data['userStatistics'][$q]['name'] = $data['users'][$q]['nickname'];
					// 文章类型循环查询文章个数
					for ($w=0; $w<count($data['articleTypeName']); $w++) {
						$data['userStatistics'][$q]['articleType'][$w] = [
							'typeName' => $data['articleTypeName'][$w]['lname'],
							'num' => db('article')->where($projectSql."uId = ".$data['users'][$q]['uId']." and typeid = ".$data['articleTypeName'][$w]['tid']." and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->count(),
						];
					}
				}
			} else {
				// 用户名称
				for ($q=0; $q<count($data['users']); $q++) {
					if($data['users'][$q]['uId'] == input("post.uid")){
						$data['userStatistics'][0]['name'] = $data['users'][$q]['nickname'];
					}
				}
				
				// 文章img类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."uId = ".input("post.uid")." and typeFile LIKE '%img%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['img']));
				}
				$data['userStatistics'][0]['fileType'][0] = [
					'typeName' 	=> 'img',
					'num' 	=> $temp2,
				];
				// 文章psd类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."uId = ".input("post.uid")." and typeFile LIKE '%psd%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['psd']));
				}
				$data['userStatistics'][0]['fileType'][1] = [
					'typeName' 	=> 'psd',
					'num' 	=> $temp2,
				];
				// 文章video类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."uId = ".input("post.uid")." and typeFile LIKE '%video%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['video']));
				}
				$data['userStatistics'][0]['fileType'][2] = [
					'typeName' 	=> 'video',
					'num' 	=> $temp2,
				];
				// 文章ai类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."uId = ".input("post.uid")." and typeFile LIKE '%ai%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['ai']));
				}
				$data['userStatistics'][0]['fileType'][3] = [
					'typeName' 	=> 'ai',
					'num' 	=> $temp2,
				];
				// 文章pdf类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."uId = ".input("post.uid")." and typeFile LIKE '%pdf%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['pdf']));
				}
				$data['userStatistics'][0]['fileType'][4] = [
					'typeName' 	=> 'pdf',
					'num' 	=> $temp2,
				];
				// 文章engineering类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."uId = ".input("post.uid")." and typeFile LIKE '%engineering%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['engineering']));
				}
				$data['userStatistics'][0]['fileType'][5] = [
					'typeName' 	=> 'zip',
					'num' 	=> $temp2,
				];
				// 文章word类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."uId = ".input("post.uid")." and typeFile LIKE '%word%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['word']));
				}
				$data['userStatistics'][0]['fileType'][6] = [
					'typeName' 	=> 'word',
					'num' 	=> $temp2,
				];
				// 文章excel类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where($projectSql."uId = ".input("post.uid")." and typeFile LIKE '%excel%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['excel']));
				}
				$data['userStatistics'][0]['fileType'][7] = [
					'typeName' 	=> 'excel',
					'num' 	=> $temp2,
				];
				
				// 循环查询时间内容数据
				for ($e=0; $e<count($shijian); $e++) {
					$data['userStatistics'][0]['riqi'][$e]['time'] = $shijian[$e]['riqi'];
					// 文章类型循环查询文章个数
					for ($w=0; $w<count($data['articleTypeName']); $w++) {
						$data['userStatistics'][0]['riqi'][$e]['articleType'][$w] = [
							'typeName' => $data['articleTypeName'][$w]['lname'],
							'num' => db('article')->where($projectSql."uId = ".input("post.uid")." and typeid = ".$data['articleTypeName'][$w]['tid']." and registerTimeImg>=".$shijian[$e]['start']." and registerTimeImg<=".$shijian[$e]['end'])->count(),
						];
					}
				}
			}
			
			
			return json(['code'=>$this->tool->success,'data'=>$data,'msg'=>'success','status'=>true,]);
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 统计-用户下载
	* 1）支持单月查询：查询指定月份每天的数据
	* 2）支持最多跨度一年内每月数据
	* 3）支持单用户（单日总数）/多用户（每月总数）数据显示
	*/
	public function getArticleUserDownload(){
		if($this->request->isPost())
		{
			
			
			// 判断指定的时间是单个月还是多个月份
			if(input("post.beginTime") == input("post.endTime") && input("post.beginTime") != '' && input("post.endTime") != ''){
				$shijian = $this->tool->getMonthAll(input("post.beginTime"));
				$echartsType = '1';
			} else if(input("post.beginTime") != input("post.endTime") && input("post.beginTime") != '' && input("post.endTime") != ''){
				$beginTimeY = explode("-",input("post.beginTime"))[0];
				$beginTimeM = explode("-",input("post.beginTime"))[1];
				$endTimeY = explode("-",input("post.endTime"))[0];
				$endTimeM = explode("-",input("post.endTime"))[1];
				$shijian = $this->tool->getDiyYearAll($beginTimeY,$beginTimeM,$endTimeY,$endTimeM);
			}
			
			// 数据模型的类别设置 1：单人   2：多人
			$data['type'] = input("post.uid") == "" ? '2':'1';
			
			// 指定状态用户和所有用户
			$data['users'] = db('users')->field('uId,nickname,state')->where("state=".input("post.state"))->select();
			$data['userALL'] = db('users')->field('uId,nickname,state')->select();
			
			if(input("post.uid") == ""){
				// 查询多人指定时间、用户状态的数据
				for ($e=0; $e<count($data['users']); $e++) {
					$temp = Db::query("SELECT u.nickname as name, count(*) as 'count' FROM img_information i, img_users u WHERE u.uId = i.froid and u.uId = ".$data['users'][$e]['uId']." and i.created >= ".$shijian[0]['start']." and i.created <= ".$shijian[count($shijian)-1]['end']." group by u.nickname");
					$data['articleUserDownload'][$e] = $temp == [] ? ['name'=>$data['users'][$e]['nickname'],'count'=>'0'] : $temp[0];
				}
			} else {
				for ($q=0; $q<count($data['users']); $q++) {
					if($data['users'][$q]['uId']==input("post.uid")) $onUser = $data['users'][$q];
				}
				// 查询单人指定时间、用户状态的数据
				for ($e=0; $e<count($shijian); $e++) {
					$data['articleUserDownload'][$e]['riqi'] = $shijian[$e]['riqi'];
					$temp = Db::query("SELECT u.nickname as name, count(*) as 'count' FROM img_information i, img_users u WHERE u.uId = i.froid and u.uId = ".input("post.uid")." and i.created >= ".$shijian[$e]['start']." and i.created <= ".$shijian[$e]['end']." group by u.nickname");
					$data['articleUserDownload'][$e]['shuju'] = $temp == [] ? ['name'=>$onUser['nickname'],'count'=>'0'] : $temp[0];
				}
			}
			
			return json(['code'=>$this->tool->success,'data'=>$data,'msg'=>'success','status'=>true,]);
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 统计-项目文章占比
	* 1）支持单月查询总数据
	* 2）支持最多跨度一年内总数据
	* 3）支持项目查询
	*/
	public function getArticleProject(){
		if($this->request->isPost())
		{
			// 判断指定的时间是单个月还是多个月份
			if(input("post.beginTime") == input("post.endTime") && input("post.beginTime") != '' && input("post.endTime") != ''){
				$shijian = $this->tool->getMonthAll(input("post.beginTime"));
				$data['type'] = '1';
			} else if(input("post.beginTime") != input("post.endTime") && input("post.beginTime") != '' && input("post.endTime") != ''){
				$beginTimeY = explode("-",input("post.beginTime"))[0];
				$beginTimeM = explode("-",input("post.beginTime"))[1];
				$endTimeY = explode("-",input("post.endTime"))[0];
				$endTimeM = explode("-",input("post.endTime"))[1];
				$shijian = $this->tool->getDiyYearAll($beginTimeY,$beginTimeM,$endTimeY,$endTimeM);
				// 数据内容的多少的区别设置
				$data['type'] = count($shijian) <= 6 ? '2':'3';
			} else if(input("post.beginTime") == '' && input("post.endTime") == ''){
				$shijian = [];
			}
			
			// 所有类型
			$data['articleTypeName'] = db('type')->where("state=1")->select();
			// 所有项目
			$projects = db('project')->select();
			$data['projects'][0] = [ 'label'=>'运维中', 'options'=>[] ];
			$data['projects'][1] = [ 'label'=>'已结束', 'options'=>[] ];
			for ($s=0; $s<count($projects); $s++) {
				if($projects[$s]['webShow'] == '1'){
					$data['projects'][0]['options'][count($data['projects'][0]['options'])] = ['value'=>$projects[$s]['pid'], 'label'=>$projects[$s]['xname']];
				}
				if($projects[$s]['webShow'] == '0'){
					$data['projects'][1]['options'][count($data['projects'][1]['options'])] = ['value'=>$projects[$s]['pid'], 'label'=>$projects[$s]['xname']];
				}
				if(input("post.project") != '' && input("post.project") == $projects[$s]['pid']){
					$projectNam = $projects[$s]['xname'];
				}
			}
			
			if(count($shijian) == 0 && input("post.project") == ''){
				
				// 项目文章循环查询文章个数
				for ($e=0; $e<count($projects); $e++) {
					$data['articleProject']['project'][$e] = [
						'projectName' => $projects[$e]['xname'],
						'num' => intval(db('article')->where("state != 2 and projectid = ".$projects[$e]['pid'])->count()),
					];
				}
				
				// 文章类型循环查询文章个数
				for ($w=0; $w<count($data['articleTypeName']); $w++) {
					$data['articleProject']['articleType'][$w] = [
						'typeName' => $data['articleTypeName'][$w]['lname'],
						'num' => intval(db('article')->where("state != 2 and typeid = ".$data['articleTypeName'][$w]['tid'])->count()),
					];
				}
				
				// 文章img类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%img%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['img']));
				}
				$data['articleProject']['fileType'][0] = [
					'typeName' 	=> 'img',
					'num' 	=> $temp2
				];
				// 文章psd类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%psd%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['psd']));
				}
				$data['articleProject']['fileType'][1] = [
					'typeName' 	=> 'psd',
					'num' 	=> $temp2,
				];
				// 文章video类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%video%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['video']));
				}
				$data['articleProject']['fileType'][2] = [
					'typeName' 	=> 'video',
					'num' 	=> $temp2,
				];
				// 文章ai类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%ai%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['ai']));
				}
				$data['articleProject']['fileType'][3] = [
					'typeName' 	=> 'ai',
					'num' 	=> $temp2,
				];
				// 文章pdf类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%pdf%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['pdf']));
				}
				$data['articleProject']['fileType'][4] = [
					'typeName' 	=> 'pdf',
					'num' 	=> $temp2,
				];
				// 文章engineering类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%engineering%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['engineering']));
				}
				$data['articleProject']['fileType'][5] = [
					'typeName' 	=> 'zip',
					'num' 	=> $temp2,
				];
				// 文章word类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%word%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['word']));
				}
				$data['articleProject']['fileType'][6] = [
					'typeName' 	=> 'word',
					'num' 	=> $temp2,
				];
				// 文章excel类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%excel%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['excel']));
				}
				$data['articleProject']['fileType'][7] = [
					'typeName' 	=> 'excel',
					'num' 	=> $temp2,
				];
			} else if(count($shijian) != 0 && input("post.project") != '') {
				
				// 项目文章查询个数
				$data['articleProject']['project'][0] = [
					'projectName' => $projectNam,
					'num' => intval(db('article')->where("state != 2 and projectid = ".input("post.project")." and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->count()),
				];
				
				// 文章类型循环查询文章个数
				for ($w=0; $w<count($data['articleTypeName']); $w++) {
					$data['articleProject']['articleType'][$w] = [
						'typeName' => $data['articleTypeName'][$w]['lname'],
						'num' => intval(db('article')->where("state != 2 and projectid = ".input("post.project")." and typeid = ".$data['articleTypeName'][$w]['tid']." and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->count()),
					];
				}
				
				// 文章img类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%img%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['img']));
				}
				$data['articleProject']['fileType'][0] = [
					'typeName' 	=> 'img',
					'num' 	=> $temp2
				];
				// 文章psd类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%psd%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['psd']));
				}
				$data['articleProject']['fileType'][1] = [
					'typeName' 	=> 'psd',
					'num' 	=> $temp2,
				];
				// 文章video类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%video%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['video']));
				}
				$data['articleProject']['fileType'][2] = [
					'typeName' 	=> 'video',
					'num' 	=> $temp2,
				];
				// 文章ai类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%ai%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['ai']));
				}
				$data['articleProject']['fileType'][3] = [
					'typeName' 	=> 'ai',
					'num' 	=> $temp2,
				];
				// 文章pdf类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%pdf%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['pdf']));
				}
				$data['articleProject']['fileType'][4] = [
					'typeName' 	=> 'pdf',
					'num' 	=> $temp2,
				];
				// 文章engineering类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%engineering%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['engineering']));
				}
				$data['articleProject']['fileType'][5] = [
					'typeName' 	=> 'zip',
					'num' 	=> $temp2,
				];
				// 文章word类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%word%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['word']));
				}
				$data['articleProject']['fileType'][6] = [
					'typeName' 	=> 'word',
					'num' 	=> $temp2,
				];
				// 文章excel类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%excel%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['excel']));
				}
				$data['articleProject']['fileType'][7] = [
					'typeName' 	=> 'excel',
					'num' 	=> $temp2,
				];
				
			} else if(count($shijian) == 0 && input("post.project") != ''){
				// 项目文章查询个数
				$data['articleProject']['project'][0] = [
					'projectName' => $projectNam,
					'num' => intval(db('article')->where("state != 2 and projectid = ".input("post.project"))->count()),
				];
				
				// 文章类型循环查询文章个数
				for ($w=0; $w<count($data['articleTypeName']); $w++) {
					$data['articleProject']['articleType'][$w] = [
						'typeName' => $data['articleTypeName'][$w]['lname'],
						'num' => intval(db('article')->where("state != 2 and projectid = ".input("post.project")." and typeid = ".$data['articleTypeName'][$w]['tid'])->count()),
					];
				}
				
				// 文章img类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%img%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['img']));
				}
				$data['articleProject']['fileType'][0] = [
					'typeName' 	=> 'img',
					'num' 	=> $temp2
				];
				// 文章psd类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%psd%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['psd']));
				}
				$data['articleProject']['fileType'][1] = [
					'typeName' 	=> 'psd',
					'num' 	=> $temp2,
				];
				// 文章video类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%video%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['video']));
				}
				$data['articleProject']['fileType'][2] = [
					'typeName' 	=> 'video',
					'num' 	=> $temp2,
				];
				// 文章ai类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%ai%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['ai']));
				}
				$data['articleProject']['fileType'][3] = [
					'typeName' 	=> 'ai',
					'num' 	=> $temp2,
				];
				// 文章pdf类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%pdf%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['pdf']));
				}
				$data['articleProject']['fileType'][4] = [
					'typeName' 	=> 'pdf',
					'num' 	=> $temp2,
				];
				// 文章engineering类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%engineering%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['engineering']));
				}
				$data['articleProject']['fileType'][5] = [
					'typeName' 	=> 'zip',
					'num' 	=> $temp2,
				];
				// 文章word类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%word%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['word']));
				}
				$data['articleProject']['fileType'][6] = [
					'typeName' 	=> 'word',
					'num' 	=> $temp2,
				];
				// 文章excel类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and projectid = ".input("post.project")." and typeFile LIKE '%excel%' ")->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['excel']));
				}
				$data['articleProject']['fileType'][7] = [
					'typeName' 	=> 'excel',
					'num' 	=> $temp2,
				];
			} else if(count($shijian) != 0 && input("post.project") == ''){
				
				// 项目文章循环查询文章个数
				for ($e=0; $e<count($projects); $e++) {
					$data['articleProject']['project'][$e] = [
						'projectName' => $projects[$e]['xname'],
						'num' => intval(db('article')->where("state != 2 and projectid = ".$projects[$e]['pid']." and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->count()),
					];
				}
				
				// 文章类型循环查询文章个数
				for ($w=0; $w<count($data['articleTypeName']); $w++) {
					$data['articleProject']['articleType'][$w] = [
						'typeName' => $data['articleTypeName'][$w]['lname'],
						'num' => intval(db('article')->where("state != 2 and typeid = ".$data['articleTypeName'][$w]['tid']." and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->count()),
					];
				}
				
				// 文章img类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%img%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['img']));
				}
				$data['articleProject']['fileType'][0] = [
					'typeName' 	=> 'img',
					'num' 	=> $temp2
				];
				// 文章psd类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%psd%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['psd']));
				}
				$data['articleProject']['fileType'][1] = [
					'typeName' 	=> 'psd',
					'num' 	=> $temp2,
				];
				// 文章video类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%video%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['video']));
				}
				$data['articleProject']['fileType'][2] = [
					'typeName' 	=> 'video',
					'num' 	=> $temp2,
				];
				// 文章ai类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%ai%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['ai']));
				}
				$data['articleProject']['fileType'][3] = [
					'typeName' 	=> 'ai',
					'num' 	=> $temp2,
				];
				// 文章pdf类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%pdf%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['pdf']));
				}
				$data['articleProject']['fileType'][4] = [
					'typeName' 	=> 'pdf',
					'num' 	=> $temp2,
				];
				// 文章engineering类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%engineering%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['engineering']));
				}
				$data['articleProject']['fileType'][5] = [
					'typeName' 	=> 'zip',
					'num' 	=> $temp2,
				];
				// 文章word类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%word%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['word']));
				}
				$data['articleProject']['fileType'][6] = [
					'typeName' 	=> 'word',
					'num' 	=> $temp2,
				];
				// 文章excel类型总数(自定义时间)
				$temp1 = []; $temp2 = 0;
				$temp1 = db('article')->where("state != 2 and typeFile LIKE '%excel%' and registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'])->select();
				for ($q=0; $q<count($temp1); $q++) {
					$temp2 += count((array)json_decode($temp1[$q]['excel']));
				}
				$data['articleProject']['fileType'][7] = [
					'typeName' 	=> 'excel',
					'num' 	=> $temp2,
				];
			}
			
			return json(['code'=>$this->tool->success,'data'=>$data,'msg'=>'success','status'=>true,]);

		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 统计-标签文章
	* 1）支持用户查询
	* 2）支持最多跨度一年内时间查询
	* 3）支持项目查询
	*/
	public function getArticleLabel () {
		if($this->request->isPost())
		{
			// 判断指定的时间是单个月还是多个月份
			if(input("post.beginTime") == input("post.endTime") && input("post.beginTime") != '' && input("post.endTime") != ''){
				$shijian = $this->tool->getMonthAll(input("post.beginTime"));
				$sql[0]= "registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'];
			} else if(input("post.beginTime") != input("post.endTime") && input("post.beginTime") != '' && input("post.endTime") != ''){
				$beginTimeY = explode("-",input("post.beginTime"))[0];
				$beginTimeM = explode("-",input("post.beginTime"))[1];
				$endTimeY = explode("-",input("post.endTime"))[0];
				$endTimeM = explode("-",input("post.endTime"))[1];
				$shijian = $this->tool->getDiyYearAll($beginTimeY,$beginTimeM,$endTimeY,$endTimeM);
				$sql[0]= "registerTimeImg>=".$shijian[0]['start']." and registerTimeImg<=".$shijian[count($shijian)-1]['end'];
			} else if(input("post.beginTime") == '' && input("post.endTime") == ''){
				$shijian = [];
			}
			
			// 所有用户
			$data['users'] = db('users')->field('uId,nickname,state')->select();

			// 所有类型
			$data['type'] = db('type')->select();
			
			// 表头参数
			$data['thead'] = [];
			foreach($data['type'] as $key => $value){
				if($key == 0){
					$data['thead'][count($data['thead'])] = [
						'label' => '名称',
						'prop' => 'name'
					];
					$data['thead'][count($data['thead'])] = [
						'label' => $value['lname'],
						'prop' => $value['nameEnglish']
					];
				} else {
					$data['thead'][count($data['thead'])] = [
						'label' => $value['lname'],
						'prop' => $value['nameEnglish']
					];
				}
			}
			$data['thead'][count($data['thead'])] = [ 'label' => 'img', 'prop' => 'img' ];
			$data['thead'][count($data['thead'])] = [ 'label' => 'psd', 'prop' => 'psd' ];
			$data['thead'][count($data['thead'])] = [ 'label' => 'video', 'prop' => 'video' ];
			$data['thead'][count($data['thead'])] = [ 'label' => 'ai', 'prop' => 'ai' ];
			$data['thead'][count($data['thead'])] = [ 'label' => 'pdf', 'prop' => 'pdf' ];
			$data['thead'][count($data['thead'])] = [ 'label' => 'word', 'prop' => 'word' ];
			$data['thead'][count($data['thead'])] = [ 'label' => 'excel', 'prop' => 'excel' ];
			$data['thead'][count($data['thead'])] = [ 'label' => 'zip', 'prop' => 'zip' ];
			

			// 所有项目
			$projects = db('project')->select();
			$data['projects'][0] = [ 'label'=>'运维中', 'options'=>[] ];
			$data['projects'][1] = [ 'label'=>'已结束', 'options'=>[] ];
			for ($s=0; $s<count($projects); $s++) {
				if($projects[$s]['webShow'] == '1'){
					$data['projects'][0]['options'][count($data['projects'][0]['options'])] = ['value'=>$projects[$s]['pid'], 'label'=>$projects[$s]['xname']];
				}
				if($projects[$s]['webShow'] == '0'){
					$data['projects'][1]['options'][count($data['projects'][1]['options'])] = ['value'=>$projects[$s]['pid'], 'label'=>$projects[$s]['xname']];
				}
				if(input("post.project") != '' && input("post.project") == $projects[$s]['pid']){
					$projectNam = $projects[$s]['xname'];
				}
			}
			
			$temp = []; $label = [];
			
			if(input("post.state")){
				if(input("post.pid") != ''){
					$sql[count($sql)]= "projectid = ".input("post.pid");
				}
				if(input("post.uid") != ''){
					$sql[count($sql)]= "uId = ".input("post.uid");
				}
				if(input("post.key") != ''){
					$temp = [];
					if(stripos(input("post.key"),',')){
						$temp = explode(",",input("post.key"));
						foreach($temp as $value){
							$label[count($label)]= [
								'name' => $value,
								'sql' => "( `title` LIKE '%".$value."%' OR `keyword` LIKE '%".$value."%' OR `describe` LIKE '%".$value."%' )",
							];
						}
					} else {
						$label[count($label)]= [
								'name' => input("post.key"),
								'sql' => "( `title` LIKE '%".input("post.key")."%' OR `keyword` LIKE '%".input("post.key")."%' OR `describe` LIKE '%".input("post.key")."%' )",
							];
					}
				} else {
					// 标签组 + 标签不为空 || 标签组等于空 + 标签不为空
					if(input("post.gid") != '' && input("post.lid") != '' || input("post.gid") == '' && input("post.lid") != ''){
						$temp = [];
						$temp = db('label')->where(['lid' => input("post.lid")])->find();
						$label[count($label)]= [
							'name' => $temp['name'],
							'sql' => "( `title` LIKE '%".$temp['name']."%' OR `keyword` LIKE '%".$temp['name']."%' OR `describe` LIKE '%".$temp['name']."%' )",
						];
					// 标签不为空组 + 标签等于空
					} else if(input("post.gid") != '' && input("post.lid") == '') {
						$temp = [];
						$temp = db('label')->where(['gid' => input("post.gid")])->select();
						foreach($temp as $value){
							$label[count($label)]= [
								'name' => $value['name'],
								'sql' => "( `title` LIKE '%".$value['name']."%' OR `keyword` LIKE '%".$value['name']."%' OR `describe` LIKE '%".$value['name']."%' )",
							];
						}

					}
				}
				// 遍历所有的单个条件拼接sql
				foreach($sql as $key => $value){
					if($key != count($sql)-1){
						$sqlExecute = $sqlExecute.$value." and ";
					} else {
						$sqlExecute = $sqlExecute.$value;
					}
				}
				// 对多个标签的循环查询
				foreach($label as $key => $value){
					$data['article'][$key]['name'] = $value['name'];
					foreach($data['type'] as $typeValue){
						if($sqlExecute != null){
							$articleData = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeid = ".$typeValue['tid']." and ".$sqlExecute." and ".$value['sql'])->count();
						} else {
							$articleData = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeid = ".$typeValue['tid']." and ".$value['sql'])->count();
						}
						$data['article'][$key][$typeValue['nameEnglish']] = $articleData == null ? 0 : $articleData;
						$data['article'][$key]['sql'] = db('article')->getLastSql();
						$articleData = 0;
					}

					if($sqlExecute != null){
						$data['article'][$key]['img'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%img%' and ".$sqlExecute." and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%img%' and ".$sqlExecute." and ".$value['sql'])->count();
						$data['article'][$key]['psd'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%psd%' and ".$sqlExecute." and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%psd%' and ".$sqlExecute." and ".$value['sql'])->count();
						$data['article'][$key]['video'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%video%' and ".$sqlExecute." and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%video%' and ".$sqlExecute." and ".$value['sql'])->count();
						$data['article'][$key]['ai'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%ai%' and ".$sqlExecute." and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%ai%' and ".$sqlExecute." and ".$value['sql'])->count();
						$data['article'][$key]['pdf'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%pdf%' and ".$sqlExecute." and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%pdf%' and ".$sqlExecute." and ".$value['sql'])->count();
						$data['article'][$key]['word'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%word%' and ".$sqlExecute." and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%word%' and ".$sqlExecute." and ".$value['sql'])->count();
						$data['article'][$key]['excel'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%excel%' and ".$sqlExecute." and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%excel%' and ".$sqlExecute." and ".$value['sql'])->count();
						$data['article'][$key]['zip'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%engineering%' and ".$sqlExecute." and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%engineering%' and ".$sqlExecute." and ".$value['sql'])->count();
					} else {
						$data['article'][$key]['img'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%img%' and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%img%' and ".$value['sql'])->count();
						$data['article'][$key]['psd'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%psd%' and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%psd%' and ".$value['sql'])->count();
						$data['article'][$key]['video'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%video%' and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%video%' and ".$value['sql'])->count();
						$data['article'][$key]['ai'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%ai%' and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%ai%' and ".$value['sql'])->count();
						$data['article'][$key]['pdf'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%pdf%' and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%pdf%' and ".$value['sql'])->count();
						$data['article'][$key]['word'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%word%' and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%word%' and ".$value['sql'])->count();
						$data['article'][$key]['excel'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%excel%' and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%excel%' and ".$value['sql'])->count();
						$data['article'][$key]['zip'] = db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%engineering%' and ".$value['sql'])->count() == null ? 0 : db('article')->field('mId,uId,typeFile,typeid,projectid,detailsid,title,click')->where("typeFile LIKE '%engineering%' and ".$value['sql'])->count();
					}
				}
			}	
				
			// {pid:_this.projectValue, uid:_this.userValue, key:keyValue, lid:_this.labelValue, gid:_this.groupLabelValue, beginTime:_this.timeValue[0], endTime:_this.timeValue[1], state: state}
			
			return json(['code'=>$this->tool->success,'data'=>$data,'msg'=>'success','status'=>true,]);
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 获取用户回收站文章
	*/
	public function getRecoveryArticle () {
		if($this->request->isPost())
		{
			$user = db('users')->where(['uId' => input("post.uId")])->select();
			if ($user) {
				// 计算当前用户回收站文章保留天数到期删除文章
				$article = db('article')->where(['state' => 2])->select();
				for ($e=0; $e<count($article); $e++) {
					$deleteTime = $article[$e]['retainTime'];
					$deleteTime = $deleteTime + ($this->tool->time_day * 30);
					$day = ($deleteTime - time()) / $this->tool->time_day;
					if($day < 1){
						$where['mId'] = $article[$e]['mId'];
						$adel = $article[$e];
						if($adel){
							$jsonimg=$adel['img'];
							$dfileimg= json_decode($jsonimg,true);
							if(count($dfileimg) != 0){
								for($i=0;$i<count($dfileimg);$i++){
									$dfimg=$dfileimg[$i]['dataImg']['url'];
									$dfimgs=$dfileimg[$i]['miniImg'];
									if($dfimg!='' && file_exists($dfimg)){
										unlink($dfimg);						
									}
									if($dfimgs!='' && file_exists($dfimgs)){
										unlink($dfimgs);
									}
								}
							}
							
							$jsonpsd=$adel['psd'];
							$dfilepsd = json_decode($jsonpsd,true);
							if(count($dfilepsd) != 0){
								for($i=0;$i<count($dfilepsd);$i++){
									$dfpsd=$dfilepsd[$i]['dataPsd']['url'];
									$dfpsds=$dfilepsd[$i]['Psdview'];
									if($dfpsd!='' && file_exists($dfpsd)){
										unlink($dfpsd);						
									}
									if($dfpsds!='' && file_exists($dfpsds)){
										unlink($dfpsds);
									}
								}
							}
							
							$jsonvideo=$adel['video'];
							$dfilevideo= json_decode($jsonvideo,true);
							if(count($dfilevideo) != 0){
								for($i=0;$i<count($dfilevideo);$i++){
									$dfvideoimg=$dfilevideo[$i]['dataVideo']['url'];
									$dfvideofil=$dfilevideo[$i]['Videoview'];
									if($dfvideoimg!='' && file_exists($dfvideoimg)){
										unlink($dfvideoimg);
									}
									if($dfvideofil!='' && file_exists($dfvideofil)){
										unlink($dfvideofil);
									}
								}
							}
							db('article')->where($where)->delete();
							$userInfo = db('users')->where(['uId' => input("post.uId")])->find();
							$userAuthGroupInfo = db('auth_group')->where(['id' => $userInfo["permissions"]])->find();
							$data = [
								"uId" 						=> input("post.uId"),
								"type" 						=> 29,
								"time" 						=> time(),
								"contentText"				=> "回收站文章【".$adel['title']."】保留天数已到自动删除",
								"content_groupText"			=> $userInfo['nickname']."[".$userAuthGroupInfo['title']."]回收站文章【".$adel['title']."】保留天数已到自动删除",
								"content_user"				=> "{}",
								"content_auth_group"		=> "{}",
								"content_project"			=> "{}",
								"content_type"				=> "{}",
								"content_classification"	=> "{}",
								"content_group_label"		=> "{}",
								"content_label"				=> "{}",
								"content_article_type"		=> "{}"
							];
							$rn = db('operationinfo')->insert($data);
						}
					}
				}
				/*$allwz = db('article')->select();
				$typesss = [];
				// json字符串转数组
				for($a=0;$a<count($allwz);$a++){
					$allwz[$a]['img'] == '[]' ? [] : $typesss[count($typesss)] = 'img';
					$allwz[$a]['psd'] == '[]' ? [] : $typesss[count($typesss)] = 'psd';
					$allwz[$a]['video'] == '[]' ? [] : $typesss[count($typesss)] = 'video';
					$allwz[$a]['ai'] == '[]' ? [] : $typesss[count($typesss)] = 'ai';
					$allwz[$a]['pdf'] == '[]' ? [] : $typesss[count($typesss)] = 'pdf';
					$allwz[$a]['word'] == '[]' ? [] : $typesss[count($typesss)] = 'word';
					$allwz[$a]['excel'] == '[]' ? [] : $typesss[count($typesss)] = 'excel';
					$allwz[$a]['engineering'] == '[]' ? [] : $typesss[count($typesss)] = '压缩包';
					db('article')->where(['mId' => $allwz[$a]['mId']])->update(['typeFile' => join(",", $typesss)]);
					$typesss = [];
				}*/
				
				// 查询当前用户回收站文章
				if($user[0]['permissions'] == '2'){
					$articleArrs = db('article')->where(['state' => 2])->select();
				} else {
					$articleArrs = db('article')->where(['uId' => input("post.uId"), 'state' => 2])->select();
				}
				
				// json字符串转数组
				for($a=0;$a<count($articleArrs);$a++){
					$nickname = db('users')->field('nickname')->where(['uId' => $articleArrs[$a]['uId']])->find();
					$articleArrs[$a]['nickname'] = $nickname['nickname'];
					$articleArrs[$a]['img'] = $articleArrs[$a]['img'] == '[]' ? [] : json_decode($articleArrs[$a]['img']);
					$articleArrs[$a]['psd'] = $articleArrs[$a]['psd'] == '[]' ? [] : json_decode($articleArrs[$a]['psd']);
					$articleArrs[$a]['video'] = $articleArrs[$a]['video'] == '[]' ? [] : json_decode($articleArrs[$a]['video']);
					$articleArrs[$a]['ai'] = $articleArrs[$a]['ai'] == '[]' ? [] : json_decode($articleArrs[$a]['ai']);
					$articleArrs[$a]['pdf'] = $articleArrs[$a]['pdf'] == '[]' ? [] : json_decode($articleArrs[$a]['pdf']);
					$articleArrs[$a]['word'] = $articleArrs[$a]['word'] == '[]' ? [] : json_decode($articleArrs[$a]['word']);
					$articleArrs[$a]['excel'] = $articleArrs[$a]['excel'] == '[]' ? [] : json_decode($articleArrs[$a]['excel']);
					$articleArrs[$a]['engineering'] = $articleArrs[$a]['engineering'] == '[]' ? [] : json_decode($articleArrs[$a]['engineering']);
				}
				return json(['code'=>$this->tool->success,'data'=>$articleArrs,'msg'=>'success','status'=>true,]);
			} else {
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误111','status'=>true,]);
			}
			
		}
	}
	
	/**
	* 管理员添加用户
	*/
	public function userAdd(){
		if($this->request->isPost())
		{
			$usersArr=$this->request->param();
			//执行添加
			if($usersArr){
				if($usersArr['userName'] =='' && $usersArr['password'] == ''){
					return json(['code'=>$this->tool->params_invalid,'data'=>'0','msg'=>'用户名、密码不能为空','status'=>true,]);
				}
				$sql=db('users')->where(['userName' => $usersArr['userName']])->find();
				if($sql){
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'该账号已被注册','status'=>true,]);
				}else{
					$usersArr['headPortraitSrc'] = 'image/sq17.png';
					$usersArr['password'] = md5($usersArr['password']);
					$usersArr['nickname'] = $usersArr['nickname'];
					$usersArr['sex'] = $usersArr['sex'];
					$usersArr['state'] = $usersArr['state'];
					$usersArr['permissions'] = $usersArr['permissions'];
					$usersArr['registerTime'] = time();
					$usersArr['access_token'] = $this->tool->secretkey($usersArr['nickname']);
					$usersArr['token_expires_in'] = time() + $this->tool->time_day;
					$usersArr['judgeLogin'] = '0';
					$rtn=db('users')->insert($usersArr);
					if($rtn){
						return json(['code'=>$this->tool->success,'data'=>'','msg'=>'注册成功','status'=>true,]);
					}else{
						return json(['code'=>$this->tool->success,'data'=>'','msg'=>'注册失败','status'=>true,]);
					}
				}
			}else{
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'格式错误','status'=>true,]);
			}
		}
	}
	
	/**
	* 添加内容
	*/
	public function articleAdd()
	{
		if($this->request->isPost())
		{
			if(strlen(input('post.title')) == 0){
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请填写标题在提交','status'=>true,]);
			}
			if(strlen(input('post.keyword')) == 0){
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请填写关键词在提交','status'=>true,]);
			}
			if(strlen(input('post.describe')) == 0){
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请填写描述在提交','status'=>true,]);
			}
			$article = [
				'uId' 				=> input('post.uId'),
				'typeFile' 			=> input('post.typeFile'),
				'typeid' 			=> input('post.typeid'),
				'projectid' 		=> input('post.projectid'),
				'detailsid' 		=> input('post.detailsid'),
				'title' 			=> input('post.title'),
				'keyword' 			=> input('post.keyword'),
				'describe' 			=> html_entity_decode(input('post.describe')),
				'img' 				=> input('post.img') != '' ? json_encode(input('post.img')) : '[]',
				'psd' 				=> input('post.psd') != '' ? json_encode(input('post.psd')) : '[]',
				'video' 			=> input('post.video') != '' ? json_encode(input('post.video')) : '[]',
				'ai' 				=> input('post.ai') != '' ? json_encode(input('post.ai')) : '[]',
				'pdf' 				=> input('post.pdf') != '' ? json_encode(input('post.pdf')) : '[]',
				'word' 				=> input('post.word') != '' ? json_encode(input('post.word')) : '[]',
				'excel' 			=> input('post.excel') != '' ? json_encode(input('post.excel')) : '[]',
				'engineering' 		=> input('post.engineering') != '' ? json_encode(input('post.engineering')) : '[]',
				'compress' 			=> null,
				'registerTimeImg' 	=> time(),
				'endTimeImg' 		=> 0,
				'click' 			=> 0,
				'state' 			=> 1,
				'quality' 			=> input('post.quality')
			];
			$n = db('article')->insert($article);
			if($n){
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'5','msg'=>'添加失败','status'=>true,]);
			}
		}else{
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 判断title是否重复
	*/
	public function getTitleRepeat(){
		if($this->request->isPost()){
			//判断title不为空
			if(input('post.title')!="") $where['title']=input('post.title');
			//查询数据库
			$articleaaa=db('article')->where($where)->find();
			if($articleaaa){
				return json(['code'=>$this->tool->success,'data'=>'0','msg'=>'此标题重复','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->success,'data'=>'1','msg'=>'此标题可正常使用','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 判断title是否重复2
	*/
	public function getUpdateTitleRepeat(){
		if($this->request->isPost()){
			//判断title不为空
			if(input('post.title')!="") $where['title']=input('post.title');
			//查询数据库
			$articleaaa=db('article')->where($where)->select();
			if($articleaaa){
				if($articleaaa[0]['mId']==input('post.mId') && count($articleaaa) == 1){
					return json(['code'=>$this->tool->success,'data'=>'0','msg'=>'此标题重复','status'=>true,]);
				}else{
					return json(['code'=>$this->tool->success,'data'=>'1','msg'=>'此标题可正常使用','status'=>true,]);
				}
			}else{
				return json(['code'=>$this->tool->success,'data'=>'1','msg'=>'此标题可正常使用','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 后台查看文章分页
	*/
	public function getArticleAll()
	{
		if($this->request->isPost()){
			if(strlen(input("post.page")) == 0 || strlen(input("post.articlePageNum")) == 0) return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
			$page = intval(input("post.page"));
			$articlePageNum = intval(input("post.articlePageNum")) < 5 ? 5 : intval(input("post.articlePageNum"));
			$project=db('project')->where(['state' => 2])->select();
			$types=db('type')->where(['state' => 2])->select();
			$details=db('details')->where(['state' => 2])->select();
			$userInfo = db('users')->where(['uId' => input("post.userId")])->find();
			$sqlNum = "SELECT count(*) num FROM img_article where state = 1";
			$sql = "SELECT * FROM img_article where state = 1";
			if(input("post.pid") != "") $info['projectid'] = input("post.pid");
			if(input("post.tid") != "") $info['typeid'] = input("post.tid");
			if(input("post.did") != "") $info['detailsid'] = input("post.did");
			if(input("post.uid") != "") $info['uId'] = input("post.uid");
			if($userInfo['permissions'] != 2){
				if(count($project) != 0){
					for($i=0;$i<count($project);$i++){
						$info['shieldProjectid'][$i] = $project[$i]['pid'];
					}
				}
				if(count($types) != 0){
					for($j=0;$j<count($types);$j++){
						$info['shieldTypeid'][$j] = $types[$j]['tid'];
					}
				}
				if(count($details) != 0){
					for($n=0;$n<count($details);$n++){
						$info['shieldDetailsid'][$n] = $details[$n]['did'];
					}
				}
				if($userInfo['shieldInfo'] != null){
					$userInfo['shieldInfo'] = json_decode($userInfo['shieldInfo'], true);
					for($s=0;$s<count($userInfo['shieldInfo']);$s++){
						if($userInfo['shieldInfo'][$s]['state'] == '1'){
							if($info['shieldPid'] == null) $info['shieldPid'] = [];
							$info['shieldPid'][count($info['shieldPid'])] = $userInfo['shieldInfo'][$s]['pid'];
						} else if($userInfo['shieldInfo'][$s]['state'] == '0'){
							$info['shieldTid'][0] = $userInfo['shieldInfo'][$s]['pid'];
							for($b=0;$b<count($userInfo['shieldInfo'][$s]['type']);$b++){
								if($userInfo['shieldInfo'][$s]['type'][$b]['state'] == '1'){
									$info['shieldTid'][1] =$userInfo['shieldInfo'][$s]['type'][$b]['tid'];
								}
							}
						}
					}
				}
			}
			
			foreach ($info as $key=>$value)
			{
				if($key=='projectid'){
					$sql = $sql.' and '.'projectid = '.$value;
					$sqlNum = $sqlNum.' and '.'projectid = '.$value;
				} else if($key=='typeid'){
					$sql = $sql.' and '.'typeid = '.$value;
					$sqlNum = $sqlNum.' and '.'typeid = '.$value;
				} else if($key=='detailsid'){
					$sql = $sql.' and '.'detailsid = '.$value;
					$sqlNum = $sqlNum.' and '.'detailsid = '.$value;
				} else if($key=='uId'){
					$sql = $sql.' and '.'uId = '.$value;
					$sqlNum = $sqlNum.' and '.'uId = '.$value;
				} else if($key=='shieldPid'){
					$temp = "";
					for($i=0;$i<count($value);$i++){
						$temp = $temp.'projectid <> '.$value[$i].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='shieldTid'){
					$temp = 'projectid <> '.$value[0].' and ';
					for($j=0;$j<count($value[1]);$j++){
						$temp = $temp.'typeid <> '.$value[1][$j].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='shieldProjectid'){
					$temp = "";
					for($w=0;$w<count($value);$w++){
						$temp = $temp.'projectid <> '.$value[$w].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='shieldTypeid'){
					$temp = "";
					for($e=0;$e<count($value);$e++){
						$temp = $temp.'typeid <> '.$value[$e].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='shieldDetailsid'){
					$temp = "";
					for($r=0;$r<count($value);$r++){
						$temp = $temp.'detailsid <> '.$value[$r].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				}
			}
			$articleAll=Db::query($sqlNum);
			$articleArrs=Db::query($sql.' ORDER BY mId desc LIMIT '.($page - 1) * $articlePageNum.','.$articlePageNum);
			
			// json字符串转数组
			for($a=0;$a<count($articleArrs);$a++){
				$articleArrs[$a]['img'] = $articleArrs[$a]['img'] == '[]' ? [] : json_decode($articleArrs[$a]['img']);
				$articleArrs[$a]['psd'] = $articleArrs[$a]['psd'] == '[]' ? [] : json_decode($articleArrs[$a]['psd']);
				$articleArrs[$a]['video'] = $articleArrs[$a]['video'] == '[]' ? [] : json_decode($articleArrs[$a]['video']);
				$articleArrs[$a]['ai'] = $articleArrs[$a]['ai'] == '[]' ? [] : json_decode($articleArrs[$a]['ai']);
				$articleArrs[$a]['pdf'] = $articleArrs[$a]['pdf'] == '[]' ? [] : json_decode($articleArrs[$a]['pdf']);
				$articleArrs[$a]['word'] = $articleArrs[$a]['word'] == '[]' ? [] : json_decode($articleArrs[$a]['word']);
				$articleArrs[$a]['excel'] = $articleArrs[$a]['excel'] == '[]' ? [] : json_decode($articleArrs[$a]['excel']);
				$articleArrs[$a]['engineering'] = $articleArrs[$a]['engineering'] == '[]' ? [] : json_decode($articleArrs[$a]['engineering']);
			}
			
			if($articleArrs){
				return json(['code'=>$this->tool->success,'data'=>['article' => $articleArrs, 'articleNum' => intval($articleAll[0]['num']), 'page' => $page ],'msg'=>$info,'status'=>db('article')->getLastSql(),]);
			}else{
				return json(['code'=>$this->tool->success,'data'=>['article' => [], 'articleNum' => 0],'msg'=>$sqlNum,'status'=>db('article')->getLastSql(),]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 后台查看文章分页2
	*/
	public function getArticleAll2()
	{
		if($this->request->isPost()){
			if(strlen(input("post.page")) == 0 || strlen(input("post.articlePageNum")) == 0) return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
			$page = intval(input("post.page"));
			$articlePageNum = intval(input("post.articlePageNum")) < 6 ? 6 : intval(input("post.articlePageNum"));
			$project=db('project')->where(['state' => 2])->select();
			$types=db('type')->where(['state' => 2])->select();
			$details=db('details')->where(['state' => 2])->select();
			$userInfo = db('users')->where(['uId' => input("post.userId")])->find();
			$sqlNum = "SELECT count(*) num FROM img_article where state = 1";
			$sql = "SELECT * FROM img_article where state = 1";
			$info = [];
			if(input("post.pid") != "") $info['projectid'] = input("post.pid");
			if(input("post.tid") != "") $info['typeid'] = input("post.tid");
			if(input("post.did") != "") $info['detailsid'] = input("post.did");
			if(input("post.uid") != "") $info['uId'] = input("post.uid");
			if(input("post.keyword") != "") $info['keyword']=input("post.keyword");
			if(input("post.type") != "") $info['type']=input("post.type");
			if(input("post.quality") != "") $info['quality']=input("post.quality");
			if($userInfo['permissions'] != 2){
				if(count($project) != 0){
					for($i=0;$i<count($project);$i++){
						$info['shieldProjectid'][$i] = $project[$i]['pid'];
					}
				}
				if(count($types) != 0){
					for($j=0;$j<count($types);$j++){
						$info['shieldTypeid'][$j] = $types[$j]['tid'];
					}
				}
				if(count($details) != 0){
					for($n=0;$n<count($details);$n++){
						$info['shieldDetailsid'][$n] = $details[$n]['did'];
					}
				}
				if($userInfo['shieldInfo'] != null){
					$userInfo['shieldInfo'] = json_decode($userInfo['shieldInfo'], true);
					for($s=0;$s<count($userInfo['shieldInfo']);$s++){
						if($userInfo['shieldInfo'][$s]['state'] == '1'){
							if($info['shieldPid'] == null) $info['shieldPid'] = [];
							$info['shieldPid'][count($info['shieldPid'])] = $userInfo['shieldInfo'][$s]['pid'];
						} else if($userInfo['shieldInfo'][$s]['state'] == '0'){
							$info['shieldTid'][0] = $userInfo['shieldInfo'][$s]['pid'];
							for($b=0;$b<count($userInfo['shieldInfo'][$s]['type']);$b++){
								if($userInfo['shieldInfo'][$s]['type'][$b]['state'] == '1'){
									$info['shieldTid'][1] =$userInfo['shieldInfo'][$s]['type'][$b]['tid'];
								}
							}
						}
					}
				}
			}
			
			foreach ($info as $key=>$value)
			{
				if($key=='projectid'){
					$sql = $sql.' and '.'projectid = '.$value;
					$sqlNum = $sqlNum.' and '.'projectid = '.$value;
				} else if($key=='typeid'){
					$sql = $sql.' and '.'typeid = '.$value;
					$sqlNum = $sqlNum.' and '.'typeid = '.$value;
				} else if($key=='detailsid'){
					$sql = $sql.' and '.'detailsid = '.$value;
					$sqlNum = $sqlNum.' and '.'detailsid = '.$value;
				} else if($key=='uId'){
					$sql = $sql.' and '.'uId = '.$value;
					$sqlNum = $sqlNum.' and '.'uId = '.$value;
				} else if($key=='shieldPid'){
					$temp = "";
					for($i=0;$i<count($value);$i++){
						$temp = $temp.'projectid <> '.$value[$i].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='shieldTid'){
					$temp = 'projectid <> '.$value[0].' and ';
					for($j=0;$j<count($value[1]);$j++){
						$temp = $temp.'typeid <> '.$value[1][$j].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='shieldProjectid'){
					$temp = "";
					for($w=0;$w<count($value);$w++){
						$temp = $temp.'projectid <> '.$value[$w].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='shieldTypeid'){
					$temp = "";
					for($e=0;$e<count($value);$e++){
						$temp = $temp.'typeid <> '.$value[$e].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='shieldDetailsid'){
					$temp = "";
					for($r=0;$r<count($value);$r++){
						$temp = $temp.'detailsid <> '.$value[$r].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='keyword'){
					$sql = $sql." and ( `title` LIKE '%".$value."%' OR `keyword` LIKE '%".$value."%' OR `describe` LIKE '%".$value."%' )";
					$sqlNum = $sqlNum." and ( `title` LIKE '%".$value."%' OR `keyword` LIKE '%".$value."%' OR `describe` LIKE '%".$value."%' )";
				} else if($key=='type'){
					$sql = $sql." and ( `typeFile` LIKE '%".$value."%' )";
					$sqlNum = $sqlNum." and ( `typeFile` LIKE '%".$value."%' )";
				} else if($key=='quality'){
					$sql = $sql." and ( `quality` = ".(int)$value." )";
					$sqlNum = $sqlNum." and ( `quality` = ".(int)$value." )";
				}
			}
			$articleAll=Db::query($sqlNum);
			$articleArrs=Db::query($sql.' ORDER BY mId desc LIMIT '.($page - 1) * $articlePageNum.','.$articlePageNum);
			
			// json字符串转数组
			for($a=0;$a<count($articleArrs);$a++){
				$articleArrs[$a]['img'] = $articleArrs[$a]['img'] == '[]' ? [] : json_decode($articleArrs[$a]['img']);
				$articleArrs[$a]['psd'] = $articleArrs[$a]['psd'] == '[]' ? [] : json_decode($articleArrs[$a]['psd']);
				$articleArrs[$a]['video'] = $articleArrs[$a]['video'] == '[]' ? [] : json_decode($articleArrs[$a]['video']);
				$articleArrs[$a]['ai'] = $articleArrs[$a]['ai'] == '[]' ? [] : json_decode($articleArrs[$a]['ai']);
				$articleArrs[$a]['pdf'] = $articleArrs[$a]['pdf'] == '[]' ? [] : json_decode($articleArrs[$a]['pdf']);
				$articleArrs[$a]['word'] = $articleArrs[$a]['word'] == '[]' ? [] : json_decode($articleArrs[$a]['word']);
				$articleArrs[$a]['excel'] = $articleArrs[$a]['excel'] == '[]' ? [] : json_decode($articleArrs[$a]['excel']);
				$articleArrs[$a]['engineering'] = $articleArrs[$a]['engineering'] == '[]' ? [] : json_decode($articleArrs[$a]['engineering']);
				
				// 增加当前用户收藏状态的显示值
				$select = db('collect_article')->where(['collectMid' => $articleArrs[$a]['mId'], 'collectUid' => $userInfo['uId']])->select();
				$select ? $articleArrs[$a]['isUserCollect'] = true : $articleArrs[$a]['isUserCollect'] = false;

				// 检查当前用户是否有可收藏当前文章的权限
				$purviewUser = db('privacy_type')->where(['tid' => $articleArrs[$a]['detailsid']])->select();
				if($articleArrs[$a]['uId'] == $userInfo['uId']){ 
					// 1.检查是否为文章发布人
					$articleArrs[$a]['disabled'] = false;
				} else if($purviewUser){ 
					// 2.当前文章是否为隐私分类文章,检查是否有权限收藏
					if(in_array($userInfo['uId'], explode(",",$purviewUser[0]['users'])) || $userInfo['permissions'] == 2) {
						$articleArrs[$a]['disabled'] = false;
					} else {
						$articleArrs[$a]['disabled'] = true;
					}
				} else { 
					// 不是发布人，不是隐私类文章默认可收藏
					$articleArrs[$a]['disabled'] = false;
				}
				
			}
			
			if($articleArrs){
				return json(['code'=>$this->tool->success,'data'=>['article' => $articleArrs, 'articleNum' => intval($articleAll[0]['num']), 'page' => $page ],'msg'=>$info,'status'=>db('article')->getLastSql(),]);
			}else{
				return json(['code'=>$this->tool->success,'data'=>['article' => [], 'articleNum' => 0],'msg'=>$sqlNum,'status'=>db('article')->getLastSql(),]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 前台查看文章分页
	*/
	public function getWebArticleAll()
	{
		if($this->request->isPost()){
			if(strlen(input("post.page")) == 0 || strlen(input("post.articlePageNum")) == 0) return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
			$page = intval(input("post.page"));
			$articlePageNum = intval(input("post.articlePageNum")) < 8 ? 8 : intval(input("post.articlePageNum"));
			$sqlNum = "SELECT count(*) num FROM img_article where state = 1";
			$sql = "SELECT * FROM img_article where state = 1";
			$project=db('project')->where(['state' => 2])->select();
			$types=db('type')->where(['state' => 2])->select();
			$details=db('details')->where(['state' => 2])->select();
			$userInfo = db('users')->where(['uId' => input("post.userId")])->find();
			$info = [];
			if(input("post.pid") != "") $info['projectid'] = input("post.pid");
			if(input("post.tid") != "") $info['typeid'] = input("post.tid");
			if(input("post.did") != "") $info['detailsid'] = input("post.did");
			if(input("post.uid") != "") $info['uId'] = input("post.uid");
			if(input("post.keyword") != "") $info['keyword']=input("post.keyword");
			if(input("post.type") != "") $info['type']=input("post.type");
			if(input("post.time") != "") $info['time']=input("post.time");
			if(input("post.quality") != "") $info['quality']=input("post.quality");
			if($userInfo['permissions'] != 2){
				if(count($project) != 0){
					for($i=0;$i<count($project);$i++){
						$info['shieldProjectid'][$i] = $project[$i]['pid'];
					}
				}
				if(count($types) != 0){
					for($j=0;$j<count($types);$j++){
						$info['shieldTypeid'][$j] = $types[$j]['tid'];
					}
				}
				if(count($details) != 0){
					for($n=0;$n<count($details);$n++){
						$info['shieldDetailsid'][$n] = $details[$n]['did'];
					}
				}
				if($userInfo['shieldInfo'] != null){
					$userInfo['shieldInfo'] = json_decode($userInfo['shieldInfo'], true);
					for($s=0;$s<count($userInfo['shieldInfo']);$s++){
						if($userInfo['shieldInfo'][$s]['state'] == '1'){
							if($info['shieldPid'] == null) $info['shieldPid'] = [];
							$info['shieldPid'][count($info['shieldPid'])] = $userInfo['shieldInfo'][$s]['pid'];
						} else if($userInfo['shieldInfo'][$s]['state'] == '0'){
							$info['shieldTid'][0] = $userInfo['shieldInfo'][$s]['pid'];
							for($b=0;$b<count($userInfo['shieldInfo'][$s]['type']);$b++){
								if($userInfo['shieldInfo'][$s]['type'][$b]['state'] == '1'){
									$info['shieldTid'][1] =$userInfo['shieldInfo'][$s]['type'][$b]['tid'];
								}
							}
						}
					}
				}
			}
			
			
			foreach ($info as $key=>$value)
			{
				if($key=='projectid'){
					$sql = $sql.' and '.'projectid = '.$value;
					$sqlNum = $sqlNum.' and '.'projectid = '.$value;
				} else if($key=='typeid'){
					$sql = $sql.' and '.'typeid = '.$value;
					$sqlNum = $sqlNum.' and '.'typeid = '.$value;
				} else if($key=='detailsid'){
					$sql = $sql.' and '.'detailsid = '.$value;
					$sqlNum = $sqlNum.' and '.'detailsid = '.$value;
				} else if($key=='uId'){
					$sql = $sql.' and '.'uId = '.$value;
					$sqlNum = $sqlNum.' and '.'uId = '.$value;
				} else if($key=='keyword'){
					$sql = $sql." and ( `title` LIKE '%".$value."%' OR `keyword` LIKE '%".$value."%' OR `describe` LIKE '%".$value."%' )";
					$sqlNum = $sqlNum." and ( `title` LIKE '%".$value."%' OR `keyword` LIKE '%".$value."%' OR `describe` LIKE '%".$value."%' )";
				} else if($key=='shieldPid'){
					$temp = "";
					for($i=0;$i<count($value);$i++){
						$temp = $temp.'projectid <> '.$value[$i].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='shieldTid'){
					$temp = "";
					$temp = 'projectid <> '.$value[0].' and ';
					for($j=0;$j<count($value[1]);$j++){
						$temp = $temp.'typeid <> '.$value[1][$j].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='shieldProjectid'){
					$temp = "";
					for($w=0;$w<count($value);$w++){
						$temp = $temp.'projectid <> '.$value[$w].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='shieldTypeid'){
					$temp = "";
					for($e=0;$e<count($value);$e++){
						$temp = $temp.'typeid <> '.$value[$e].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='shieldDetailsid'){
					$temp = "";
					for($r=0;$r<count($value);$r++){
						$temp = $temp.'detailsid <> '.$value[$r].' and ';
					}
					$sql = $sql.' and ( '.substr($temp,0,strlen($temp)-5).' )';
					$sqlNum = $sqlNum.' and ( '.substr($temp,0,strlen($temp)-5).' )';
				} else if($key=='type'){
					$sql = $sql." and ( `typeFile` LIKE '%".$value."%' )";
					$sqlNum = $sqlNum." and ( `typeFile` LIKE '%".$value."%' )";
				} else if($key=='time'){
					$sql = $sql." and ( `registerTimeImg`>=".strtotime(explode(",",$value)[0])." and  `registerTimeImg`<=".strtotime(explode(",",$value)[1]." 23:59:59")." )";
					$sqlNum = $sqlNum." and ( `registerTimeImg`>=".strtotime(explode(",",$value)[0])." and  `registerTimeImg`<=".strtotime(explode(",",$value)[1]." 23:59:59")." )";
				} else if($key=='quality'){
					$sql = $sql." and ( `quality` = ".(int)$value." )";
					$sqlNum = $sqlNum." and ( `quality` = ".(int)$value." )";
				}
			}
			if($userInfo['permissions'] == 4){
				$time = strtotime("-0 year -3 month -0 day");
				$sql = $sql.' and '.'registerTimeImg < '.$time;
				$sqlNum = $sqlNum.' and '.'registerTimeImg < '.$time;
			}
			
			// 增加关键词对文件名称的搜索
			// if(input("post.keyword") != ''){
				// $keyword = json_encode(input("post.keyword"),true);
				// $keyword = preg_replace("/\"/",'',$keyword);
				// $keywordn = preg_replace("/\\\/","",$keyword);
				// $sql = $sql." or ( `img` LIKE '%".$keyword."%' or `img` LIKE '%".$keywordn."%' or `psd` LIKE '%".$keyword."%' or `video` LIKE '%".$keyword."%' or `ai` LIKE '%".$keyword."%' or `pdf` LIKE '%".$keyword."%' or `word` LIKE '%".$keyword."%' or `excel` LIKE '%".$keyword."%' or `engineering` LIKE '%".$keyword."%' )";
				// $sqlNum = $sqlNum." or ( `img` LIKE '%".$keyword."%' or `img` LIKE '%".$keywordn."%' or `psd` LIKE '%".$keyword."%' or `video` LIKE '%".$keyword."%' or `ai` LIKE '%".$keyword."%' or `pdf` LIKE '%".$keyword."%' or `word` LIKE '%".$keyword."%' or `excel` LIKE '%".$keyword."%' or `engineering` LIKE '%".$keyword."%' )";
			// }
			
			$articleAll=Db::query($sqlNum);
			// 如果添加时间选择则正序显示反之倒序
			if(stripos($sql,'registerTimeImg') && $userInfo['permissions'] != 4){
				$articleArrs=Db::query($sql.' ORDER BY mId asc LIMIT '.($page - 1) * $articlePageNum.','.$articlePageNum);
			} else {
				$articleArrs=Db::query($sql.' ORDER BY mId desc LIMIT '.($page - 1) * $articlePageNum.','.$articlePageNum);
			}
			
			// json字符串转数组
			for($a=0;$a<count($articleArrs);$a++){
				$articleArrs[$a]['img'] = json_decode($articleArrs[$a]['img']);
				$articleArrs[$a]['psd'] = json_decode($articleArrs[$a]['psd']);
				$articleArrs[$a]['video'] = json_decode($articleArrs[$a]['video']);
				$articleArrs[$a]['ai'] = json_decode($articleArrs[$a]['ai']);
				$articleArrs[$a]['pdf'] = json_decode($articleArrs[$a]['pdf']);
				$articleArrs[$a]['word'] = json_decode($articleArrs[$a]['word']);
				$articleArrs[$a]['excel'] = json_decode($articleArrs[$a]['excel']);
				$articleArrs[$a]['engineering'] = json_decode($articleArrs[$a]['engineering']);
				
				// 增加当前用户收藏状态的显示值
				$select = db('collect_article')->where(['collectMid' => $articleArrs[$a]['mId'], 'collectUid' => $userInfo['uId']])->select();
				$select ? $articleArrs[$a]['isUserCollect'] = true : $articleArrs[$a]['isUserCollect'] = false;

				// 检查当前用户是否有可收藏当前文章的权限
				$purviewUser = db('privacy_type')->where(['tid' => $articleArrs[$a]['detailsid']])->select();
				if($articleArrs[$a]['uId'] == $userInfo['uId']){ 
					// 1.检查是否为文章发布人
					$articleArrs[$a]['disabled'] = false;
				} else if($purviewUser){ 
					// 2.当前文章是否为隐私分类文章,检查是否有权限收藏
					if(in_array($userInfo['uId'], explode(",",$purviewUser[0]['users'])) || $userInfo['permissions'] == 2) {
						$articleArrs[$a]['disabled'] = false;
					} else {
						$articleArrs[$a]['disabled'] = true;
					}
				} else { 
					// 不是发布人，不是隐私类文章默认可收藏
					$articleArrs[$a]['disabled'] = false;
				}
				
			}
			
			// $sql = db('article')->getLastSql();
			if($articleArrs){
				return json(['code'=>$this->tool->success,'data'=>['article' => $articleArrs, 'articleNum' => intval($articleAll[0]['num']), 'page' => $page ],'msg'=>$sql,'status'=>true,]);
			}else{
				return json(['code'=>$this->tool->success,'data'=>['article' => [], 'articleNum' => 0],'msg'=>$sql,'status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
		
	}
	
	/**
	* 删除文章（回收站）
	*/
	public function exhibitionDel()
	{
		if($this->request->isPost()){
			$articleUserInfo = db('users')->where(['uId' => input("post.uId")])->find();
			$article = db('article')->where(['mId' => input("post.mId")])->find();
			if($article['uId'] == input("post.uId") || $articleUserInfo['permissions'] == '2'){
				$rtn=db('article')->where(['mId' => input("post.mId")])->update(['state' => 2, 'retainTime' => time()]);
				if($rtn){
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,]);
				}else{
					return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,]);
				}
			} else {
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'权限不足，无法操作','status'=>true,]);
			}
			
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 批量删除文章（回收站）
	*/
	public function exhibitionDels()
	{
		if($this->request->isPost()){
			$rtn=db('article')->where(['mId' => input("post.id")])->update(['state' => 2, 'retainTime' => time()]);
			if($rtn){
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 还原文章
	*/
	public function exhibitionreduction(){
		if($this->request->isPost()){
			$rtn=db('article')->where(['mId' => input("post.mId")])->update(['state' => 1, 'retainTime' => 0]);
			if($rtn){
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'还原成功','status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>'','msg'=>'还原失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
		
	}
	
	/**
	* 彻底删除文章
	*/
	public function delArticle(){
		if($this->request->isPost()){
			$where['mId']=input('post.mId');
			$adel = db('article')->where($where)->find();
			if($adel){
				$jsonimg=$adel['img'];
				$dfileimg= json_decode($jsonimg,true);
				if(count($dfileimg) != 0){
					for($i=0;$i<count($dfileimg);$i++){
						$dfimg=$dfileimg[$i]['dataImg']['url'];
						$dfimgs=$dfileimg[$i]['miniImg'];
						if($dfimg!='' && file_exists($dfimg)){
							unlink($dfimg);						
						}
						if($dfimgs!='' && file_exists($dfimgs)){
							unlink($dfimgs);
						}
					}
				}
				
				$jsonpsd=$adel['psd'];
				$dfilepsd = json_decode($jsonpsd,true);
				if(count($dfilepsd) != 0){
					for($i=0;$i<count($dfilepsd);$i++){
						$dfpsd=$dfilepsd[$i]['dataPsd']['url'];
						$dfpsds=$dfilepsd[$i]['Psdview'];
						if($dfpsd!='' && file_exists($dfpsd)){
							unlink($dfpsd);						
						}
						if($dfpsds!='' && file_exists($dfpsds)){
							unlink($dfpsds);
						}
					}
				}
				
				$jsonvideo=$adel['video'];
				$dfilevideo= json_decode($jsonvideo,true);
				if(count($dfilevideo) != 0){
					for($i=0;$i<count($dfilevideo);$i++){
						$dfvideoimg=$dfilevideo[$i]['dataVideo']['url'];
						$dfvideofil=$dfilevideo[$i]['Videoview'];
						if($dfvideoimg!='' && file_exists($dfvideoimg)){
							unlink($dfvideoimg);
						}
						if($dfvideofil!='' && file_exists($dfvideofil)){
							unlink($dfvideofil);
						}
					}
				}
				
				$jsonvideo=$adel['ai'];
				$dfilevideo= json_decode($jsonvideo,true);
				if(count($dfilevideo) != 0){
					for($i=0;$i<count($dfilevideo);$i++){
						$dfvideoimg=$dfilevideo[$i]['dataAi']['url'];
						if($dfvideoimg!='' && file_exists($dfvideoimg)){
							unlink($dfvideoimg);
						}
					}
				}
				
				$jsonvideo=$adel['pdf'];
				$dfilevideo= json_decode($jsonvideo,true);
				if(count($dfilevideo) != 0){
					for($i=0;$i<count($dfilevideo);$i++){
						$dfvideoimg=$dfilevideo[$i]['file']['url'];
						if($dfvideoimg!='' && file_exists($dfvideoimg)){
							unlink($dfvideoimg);
						}
					}
				}
				
				$jsonvideo=$adel['engineering'];
				$dfilevideo= json_decode($jsonvideo,true);
				if(count($dfilevideo) != 0){
					for($i=0;$i<count($dfilevideo);$i++){
						$dfvideoimg=$dfilevideo[$i]['file']['url'];
						if($dfvideoimg!='' && file_exists($dfvideoimg)){
							unlink($dfvideoimg);
						}
					}
				}
				
				$jsonvideo=$adel['word'];
				$dfilevideo= json_decode($jsonvideo,true);
				if(count($dfilevideo) != 0){
					for($i=0;$i<count($dfilevideo);$i++){
						$dfvideoimg=$dfilevideo[$i]['file']['url'];
						if($dfvideoimg!='' && file_exists($dfvideoimg)){
							unlink($dfvideoimg);
						}
					}
				}
				
				$jsonvideo=$adel['excel'];
				$dfilevideo= json_decode($jsonvideo,true);
				if(count($dfilevideo) != 0){
					for($i=0;$i<count($dfilevideo);$i++){
						$dfvideoimg=$dfilevideo[$i]['file']['url'];
						if($dfvideoimg!='' && file_exists($dfvideoimg)){
							unlink($dfvideoimg);
						}
					}
				}
				
				$rn = db('article')->where($where)->delete();
				if($rn){
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,]);
				}else{
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'删除失败','status'=>true,]);
				}
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 记录人员当然浏览次数
	*/
	public function setUserBrowseArticle(){
		$info = [
			'uId' => input('post.uId'),
			'sameDay' => date('Y-m-d'),
			'mId' => null,
			'time' => time(),
		];
		$articleArrs=db('browse_web_info')->where(['uId' => $info['uId'], 'sameDay' => $info['sameDay'], 'mId' => array('exp','is null')])->find();
		if($articleArrs){
			return json(['code'=>$this->tool->success,'data'=>'','msg'=>'记录成功','status'=>true,]);
		} else {
			$n = db('browse_web_info')->insert($info);
			if($n){
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'记录成功','status'=>true,]);
			} else {
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'记录失败','status'=>true,]);
			}
		}
	}
	
	/**
	* 获取单个文章信息(后台)
	*/
	public function getAdminArticle()
	{
		if($this->request->isPost()){
			$articleArrs=db('article')->where(['mId' => input('post.mId')])->select();
			$xname = db('project')->where(['pid' => $articleArrs[0]['projectid']])->select();
			$lname = db('type')->where(['tid' => $articleArrs[0]['typeid']])->select();
			$dname = db('details')->where(['did' => $articleArrs[0]['detailsid']])->select();
			$articleArrs[0]['xname'] = $xname[0]['xname'];
			$articleArrs[0]['lname'] = $lname[0]['lname'];
			$articleArrs[0]['dname'] = $dname[0]['dname'];
			$articleArrs[0]['quality'] = strval($articleArrs[0]['quality']);
			// json字符串转数组
			for($a=0;$a<count($articleArrs);$a++){
				$articleArrs[$a]['img'] = $articleArrs[$a]['img'] == '[]' ? [] : json_decode($articleArrs[$a]['img']);
				$articleArrs[$a]['psd'] = $articleArrs[$a]['psd'] == '[]' ? [] : json_decode($articleArrs[$a]['psd']);
				$articleArrs[$a]['video'] = $articleArrs[$a]['video'] == '[]' ? [] : json_decode($articleArrs[$a]['video']);
				$articleArrs[$a]['ai'] = $articleArrs[$a]['ai'] == '[]' ? [] : json_decode($articleArrs[$a]['ai']);
				$articleArrs[$a]['pdf'] = $articleArrs[$a]['pdf'] == '[]' ? [] : json_decode($articleArrs[$a]['pdf']);
				$articleArrs[$a]['word'] = $articleArrs[$a]['word'] == '[]' ? [] : json_decode($articleArrs[$a]['word']);
				$articleArrs[$a]['excel'] = $articleArrs[$a]['excel'] == '[]' ? [] : json_decode($articleArrs[$a]['excel']);
				$articleArrs[$a]['engineering'] = $articleArrs[$a]['engineering'] == '[]' ? [] : json_decode($articleArrs[$a]['engineering']);
			}
			if($articleArrs){
				return json(['code'=>$this->tool->success,'data'=>$articleArrs[0],'msg'=>db('article')->getLastSql(),'status'=>true,]);
			}else{
				return json(['code'=>$this->tool->fail,'data'=>db('article')->getLastSql(),'msg'=>'获取失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 获取单个文章信息(前台)
	*/
	public function getWebArticle()
	{
		if($this->request->isPost()){
			if(input('post.mId') != '') $ini['mId']=input('post.mId');
			if(input('post.mId') == '') return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
			$article = db('article')->where($ini)->find();
			$article['user'] = db('users')->where(['uId' => $article['uId']])->find();
			$seeUser = db('users')->where(['uId' => input('post.uId')])->find();
			$privacyType=db('privacy_type')->where(['tid' => $article['detailsid']])->find();
			
			// 用户收藏参数
			$collect_article = db('collect_article')->where(['collectMid' => input('post.mId'), 'collectUid' => input('post.uId')])->find();
			$collect_article ? $article['isUserCollect'] = true : $article['isUserCollect'] = false;
			
			// 判断有无隐私分类权限
			if($privacyType){
				isset($privacyType['users']) ? $privacyType['users'] = explode(",",$privacyType['users']) : $privacyType['users'] = [];
				isset($privacyType['authGroup']) ? $privacyType['authGroup'] = explode(",",$privacyType['authGroup']) : $privacyType['authGroup'] = [];
				
				// 有隐私分类的进行细致判断是否拥有可阅读权限
				if(($privacyType && $privacyType['state'] == '1' && array_search($seeUser['uId'],$privacyType['users']) || array_search($seeUser['permissions'],$privacyType['authGroup'])) || $article['user']['uId'] == $seeUser['uId'] || $seeUser['permissions'] == '2' || !$privacyType || ($privacyType && $privacyType['state'] == '2' && $privacyType['users'] == '' && $privacyType['authGroup'] == '')){
					$json = $this->getArticle($article,$seeUser);
					return json($json);
				} else {
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'无权查看当前文章！','status'=>true,]);
				}
			}
			// 在不涉及隐私分类时直接查询输出
			$json = $this->getArticle($article,$seeUser);
			return json($json);
			
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	public function getArticle($article,$seeUser) {
		$article['keyword'] =  explode(',',$article['keyword']);
		$article['img'] = json_decode($article['img']);
		$article['psd'] = json_decode($article['psd']);
		$article['video'] = json_decode($article['video']);
		$article['ai'] = json_decode($article['ai']);
		$article['pdf'] = json_decode($article['pdf']);
		$article['word'] = json_decode($article['word']);
		$article['excel'] = json_decode($article['excel']);
		$article['engineering'] = json_decode($article['engineering']);
		$info = [];
		$project=db('project')->where(['webShow' => 0])->select();
		$types=db('type')->where(['webShow' => 0])->select();
		$details=db('details')->where(['webShow' => 0])->select();
		$users=db('users')->where(['webShow' => 0])->select();
		if(count($project) != 0){
			for($i=0;$i<count($project);$i++){
				$info[count($info)] = ['projectid','<>',$project[$i]['pid']];
			}
		}
		if(count($types) != 0){
			for($j=0;$j<count($types);$j++){
				$info[count($info)] = ['typeid','<>',$types[$j]['tid']];
			}
		}
		if(count($details) != 0){
			for($n=0;$n<count($details);$n++){
				$info[count($info)] = ['detailsid','<>',$details[$n]['did']];
			}
		}
		if(count($users) != 0){
			for($o=0;$o<count($users);$o++){
				$info[count($info)] = ['uId','<>',$users[$o]['uId']];
			}
		}
		for($o=0;$o<count($article['keyword']);$o++){
			$info[count($info)]=['title','like',"%{$article['keyword'][$o]}%"];
			$info[count($info)]=['keyword','like',"%{$article['keyword'][$o]}%"];
			$info[count($info)]=['describe','like',"%{$article['keyword'][$o]}%"];
		}
		$article['xiangguan'] = db('article')->where($info)->limit(20)->select();
		$r = db('article')->getLastSql();
		
		db('article')->where(['mId' => $article['mId']])->update(['click' => intval($article['click']) + 1 ]);
		db('browse_web_info')->insert(['uId' => input('post.uId'), 'sameDay' => date('Y-m-d'), 'mId' => $article['mId'], 'time' => time()]);
		return ['code'=>$this->tool->success,'data'=>$article,'msg'=>'获取成功','status'=>true];
	}
	
	/**
	* 修改文章
	*/
	public function articleUpdate()
	{
		if($this->request->isPost()){
			$articleInfo = [
				'mId' 		=> input('post.mId'),
				'uId' 		=> input('post.uId'),
				'pid' 		=> input('post.pid'),
				'tid' 		=> input('post.tid'),
				'did' 		=> input('post.did'),
				'title' 	=> input('post.title'),
				'keyword' 	=> input('post.keyword'),
				'describe' 	=> html_entity_decode(input('post.describe')),
				'img' 		=> input('post.img') != '' ? json_encode(input('post.img')) : '[]',
				'psd' 		=> input('post.psd') != '' ? json_encode(input('post.psd')) : '[]',
				'video' 	=> input('post.video') != '' ? json_encode(input('post.video')) : '[]',
				'ai' 		=> input('post.ai') != '' ? json_encode(input('post.ai')) : '[]',
				'pdf' 		=> input('post.pdf') != '' ? json_encode(input('post.pdf')) : '[]',
				'word' 		=> input('post.word') != '' ? json_encode(input('post.word')) : '[]',
				'excel' 	=> input('post.excel') != '' ? json_encode(input('post.excel')) : '[]',
				'engineering' => input('post.engineering') != '' ? json_encode(input('post.engineering')) : '[]',
				'typeFile' 	=> input('post.typeFile'),
				'quality' 	=> (int)input('post.quality')
			];
			$articleArr = db('article')->where(['mId' => $articleInfo['mId']])->select();
			if($articleArr)
			{
				$articleArr[0]['uId'] = $articleInfo['uId'];
				$articleArr[0]['typeid'] = $articleInfo['tid'];
				$articleArr[0]['projectid'] = $articleInfo['pid'];
				$articleArr[0]['detailsid'] = $articleInfo['did'];
				$articleArr[0]['title'] = $articleInfo['title'];
				$articleArr[0]['typeFile'] = $articleInfo['typeFile'];
				$articleArr[0]['keyword'] = $articleInfo['keyword'];
				$articleArr[0]['describe'] = $articleInfo['describe'];
				$articleArr[0]['img'] = $articleInfo['img'];
				$articleArr[0]['psd'] = $articleInfo['psd'];
				$articleArr[0]['video'] = $articleInfo['video'];
				$articleArr[0]['ai'] = $articleInfo['ai'];
				$articleArr[0]['pdf'] = $articleInfo['pdf'];
				$articleArr[0]['word'] = $articleInfo['word'];
				$articleArr[0]['excel'] = $articleInfo['excel'];
				$articleArr[0]['engineering'] = $articleInfo['engineering'];
				$articleArr[0]['endTimeImg'] = time();
				$articleArr[0]['quality'] = $articleInfo['quality'];
				
				$n = db('article')->where(['mId' => $articleInfo['mId']])->update($articleArr[0]);
				
				if ($n){
					return json(['code'=>$this->tool->success,'data'=>'','msg'=>'修改成功','status'=>db('article')->getLastSql(),]);
				}else{
					return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'修改失败','status'=>true,]);
				}
			} else {
				return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'修改失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 后台首页数据获取
	*/
	public function getAdminIndexData()
	{
		if($this->request->isPost()){
			if(input('post.uId') == '') return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
			$data['article'] = db('article')->where(['state' => 1,'uId' => input('post.uId')])->order('registerTimeImg DESC')->limit(50)->select();
			$data['downloadNum'] = db('information')->where(['inid' => input('post.uId')])->count();
			$data['articleNum'] = db('article')->where(['state' => 1, 'uId' => input('post.uId')])->count();
			$data['articleDeleteNum'] = db('article')->where(['state' => 2, 'uId' => input('post.uId')])->count();
			return json(['code'=>$this->tool->success,'data'=>$data,'msg'=>'success','status'=>true,]);
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 注销
	*/
	public function exitlogin(){
		//退出系统
		if($this->request->isPost()){
			if (!empty(input("post.uId"))) {
				$rtn=db('users')->where(['uId' => input("post.uId")])->update(['judgeLogin' => '0']);
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'success','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 检查用户状态
	*/
	public function userState(){
		if($this->request->isPost()){
			$rtn=db('users')->where(['uId' => input("post.uId")])->find();
			return json(['code'=>$this->tool->success,'data'=>$rtn,'msg'=>'success','status'=>true,]);
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 批量修改文章质量
	*/
	public function updateArticleQuality()
	{
		if($this->request->isPost()){
			$post=$this->request->param();
			$rtn=db('article')->where(['mId' => input("post.id")])->update(['quality' => (int)input("post.quality")]);
			if($rtn){
				return json(['code'=>$this->tool->success,'data'=>'','msg'=>'修改成功','status'=>true,]);
			} else {
				return json(['code'=>$this->tool->params_invalid,'data'=>['error' => $temp],'msg'=>'修改失败','status'=>true,]);
			}
		} else {
			return json(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,]);
		}
	}
	
	/**
	* 获取用户个人收藏文章
	*/
	public function getUserCollectArticle () {
		if($this->request->isPost())
		{
			$articleArrs = Db::query("SELECT a.mId,a.title,a.typeFile,a.img,a.psd,a.video,a.ai,a.pdf,a.word,a.excel,a.engineering,c.collectTime FROM img_article a, img_collect_article c WHERE a.mId = c.collectMid and c.collectUid = ".input("post.uId")." ORDER BY c.collectTime DESC");
			if ($articleArrs) {
				// json字符串转数组
				for($a=0;$a<count($articleArrs);$a++){
					$nickname = db('users')->field('nickname')->where(['uId' => input("post.uId")])->find();
					$articleArrs[$a]['nickname'] = $nickname['nickname'];
					$articleArrs[$a]['img'] = $articleArrs[$a]['img'] == '[]' ? [] : json_decode($articleArrs[$a]['img']);
					$articleArrs[$a]['psd'] = $articleArrs[$a]['psd'] == '[]' ? [] : json_decode($articleArrs[$a]['psd']);
					$articleArrs[$a]['video'] = $articleArrs[$a]['video'] == '[]' ? [] : json_decode($articleArrs[$a]['video']);
					$articleArrs[$a]['ai'] = $articleArrs[$a]['ai'] == '[]' ? [] : json_decode($articleArrs[$a]['ai']);
					$articleArrs[$a]['pdf'] = $articleArrs[$a]['pdf'] == '[]' ? [] : json_decode($articleArrs[$a]['pdf']);
					$articleArrs[$a]['word'] = $articleArrs[$a]['word'] == '[]' ? [] : json_decode($articleArrs[$a]['word']);
					$articleArrs[$a]['excel'] = $articleArrs[$a]['excel'] == '[]' ? [] : json_decode($articleArrs[$a]['excel']);
					$articleArrs[$a]['engineering'] = $articleArrs[$a]['engineering'] == '[]' ? [] : json_decode($articleArrs[$a]['engineering']);
				}
				return json(['code'=>$this->tool->success,'data'=>$articleArrs,'msg'=>'success','status'=>true,]);
			} else {
				return json(['code'=>$this->tool->success,'data'=>[],'msg'=>'success','status'=>true,]);
			}
			
		}
	}
	
}
