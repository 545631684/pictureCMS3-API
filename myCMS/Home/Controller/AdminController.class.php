<?php
namespace Home\Controller;
use Home\Controller\ControllerController;
use Org\Util\Imgcompress;
class AdminController extends ControllerController {
    
	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	* 前置判断接口的token有效性
	*/
	public function _initialize() 
	{
		$judgeToken = true;
		$headers = [];
		$img_users = M('users');
		// 获取access_token
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		// 数据库查找
		$user = $img_users->where(['access_token' => $headers['Accesstoken']])->find();
		if($user['access_token']){
			if ($user['token_expires_in'] <= time()) {
				$judgeToken = false;
			}
		} else {
			$judgeToken = false;
		}
		if(!judgeToken){
			$this->ajaxReturn(['code'=>$this->tool->token_invalid,'data'=>'','msg'=>'success','status'=>true,],'JSON');
		}
	}
	
	/**
	* 用户列表
	*/
	public function userList(){
		$list = [1,1,1,1,1,1,1,1,1];
		$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$list,'msg'=>'success','status'=>true,],'JSON');
    }
	
	/**
	* 获取所有权限页面
	*/
	public function auth_list(){
		$authruleArr['userGroup'] = $this->tool->img_auth_rule_copy->where("sid = 0")->order("id asc")->select();
		for($i=0;$i<count($authruleArr['userGroup']);$i++){
			$authruleArr['userGroup'][$i]['cityOptions']!="[]" ? $authruleArr['userGroup'][$i]['cityOptions']=explode(",",$authruleArr['userGroup'][$i]['cityOptions']) : $authruleArr['userGroup'][$i]['cityOptions']=[]; 
			$authruleArr['userGroup'][$i]['checkAll']!="true" ? $authruleArr['userGroup'][$i]['checkAll']=false : $authruleArr['userGroup'][$i]['checkAll']=true;
			$authruleArr['userGroup'][$i]['isIndeterminate']!="true" ? $authruleArr['userGroup'][$i]['isIndeterminate']=false : $authruleArr['userGroup'][$i]['isIndeterminate']=true;
			$authruleArr['userGroup'][$i]['checkedCities']!="[]" ? $authruleArr['userGroup'][$i]['checkedCities']=explode(",",$authruleArr['userGroup'][$i]['checkedCities']) : $authruleArr['userGroup'][$i]['checkedCities']=[];
			
			$whereid[$i]['id'] = $authruleArr['userGroup'][$i]['id'];
			$authruleArr['userGroup'][$i]['rules'] = $this->tool->img_auth_rule_copy->field("id,sid,name,index,icon,urlKeyword,state")->where("sid = ".$whereid[$i]['id'])->order("id asc")->select();
		}
		$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$authruleArr,'msg'=>'success','status'=>true,],'JSON');
    }
	
	/**
	* 获取权限组列表
	*/
	public function auth_grouplist(){
		$time = [];
		$authgroupArr = $this->tool->img_auth_group->order("id asc")->select();
		for($i=0;$i<count($authgroupArr);$i++){
			$authgroupArr[$i]["rules"] = json_decode($authgroupArr[$i]["rules"]);
			$time = $this->tool->img_users->where("permissions = ".$authgroupArr[$i]["id"])->select();
			$authgroupArr[$i]["userlist"] = $time == false ? [] : $time;
		}
		if($authgroupArr){
			$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$authgroupArr,'msg'=>'success','status'=>true,],'JSON');
		}else{
			$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'权限组数据获取失败','status'=>true,],'JSON');
		}
	}
		
	/**
	* 添加权限组
	*/
	public function auth_groupadd(){
		$groupArr = [
			'title' => I('post.title'),
			'rules' => json_encode(I('post.rules')),
			'disabled' => I('post.disabled'),
		];
		if (IS_POST) {
			$rtn = $this->tool->img_auth_group->add($groupArr);
			if ($rtn) {
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
		
	/**
	* 修改权限组
	*/
	public function auth_groupedit(){
		if (IS_POST) {
			$where['id']=I("post.id");
			$groupArr = [
				'title' => I('post.title'),
				'rules' => json_encode(I('post.rules')),
				'disabled' => I('post.disabled'),
			];
			$rtn = $this->tool->img_auth_group->where($where)->save($groupArr);
			if ($rtn) {
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'修改成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'修改失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 权限组删除
	*/
	public function auth_groupdel(){
		if (IS_POST) {
			$rtn = $this->tool->img_auth_group->where(['id' => I("post.id")])->delete();
			if ($rtn) {
				// 删除权限组后修改已有此权限组的为设计师权限组（默认修改 id：1为设计师权限组）
				$usr=$this->tool->img_users->where(["permissions" => I("post.id")])->save(['permissions' => 1]);
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 获取单个权限组
	*/
	public function auth_groupone(){
		if (IS_POST) {
			$rtn = $this->tool->img_auth_group->where(["id" => I("post.id")])->find();
			$rtn['rules'] = json_decode($rtn["rules"]);
			if ($rtn) {
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$rtn,'msg'=>'获取成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'获取失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 获取项目list  （弃用）
	*/
	public function projectList(){
		$projectarr=$this->tool->getPublicInfo('project', $this->tool->img_project->where('1 = 1')->select());
		if($projectarr){
			$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$projectarr,'msg'=>'获取成功','status'=>true,],'JSON');
		}else{
			$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'获取失败','status'=>true,],'JSON');
		}
	}
	
	/**
	* 添加项目
	*/
	public function projectAdd(){
		if (IS_POST) {
			$rtn=$this->tool->img_project->add(['xname' => I('post.xname'), 'state' => I('post.state'), 'webShow' => I('post.webShow')]);
			if($rtn){
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}

	/**
	* 编辑项目
	*/
	public function projectsave(){
		if (IS_POST) {
			$temp=$this->tool->img_project->where(['xname' => I('post.xname')])->select();
			if (!$temp || count($temp)==1 && $temp[0]['pid']==I('post.pid')) {
				$projectarr=$this->tool->img_project->where(['pid' => I('post.pid')])->save(['xname' => I('post.xname'), 'state' => I('post.state'), 'webShow' => I('post.state') == '2' ? '0' : I('post.webShow')]);
				if($projectarr == true){
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑成功','status'=>true,],'JSON');
				}else if ($projectarr == false){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'编辑失败','status'=>true,],'JSON');
				}else if ($projectarr == 0){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'项目名称重复，请修改后重新提交','status'=>true,],'JSON');
				}
			} else {
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'名称重复，请修改后重新提交','status'=>true,],'JSON');
			}
		}else{
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 删除项目
	*/
	public function projectdel(){
		if (IS_POST) {
			$rtn = $this->tool->img_project->where(['pid' => I("post.pid")])->delete();
			if ($rtn) {
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 获取项目、类型、分类、标签组、标签的数据
	*/
	public function getPublicInfo(){
		$projectArr = $this->tool->getPublicInfo('project', $this->tool->img_project->where('1 = 1')->select());
		$typeArr = $this->tool->getPublicInfo('type', $this->tool->img_type->where('1 = 1')->select());
		$detailsArr = $this->tool->getPublicInfo('details', $this->tool->img_details->where('1 = 1')->select());
		$groupLabelArr=$this->tool->getPublicInfo('groupLabel', $this->tool->img_group_label->where('1 = 1')->select());
		$labelArr=$this->tool->getPublicInfo('label', $this->tool->img_label->where('1 = 1')->select());
		if($projectArr){
			$this->ajaxReturn(['code'=>$this->tool->success,'data'=>[
				'projects' 		=> $projectArr  ? $projectArr : "[]",
				'types' 		=> $typeArr  ? $typeArr : "[]",
				'details' 		=> $detailsArr  ? $detailsArr : "[]",
				'groupLabel' 	=> $groupLabelArr  ? $groupLabelArr : "[]",
				'label' 		=> $labelArr  ? $labelArr : "[]",
				'srcUrl'		=> $this->tool->src_url,
			],'msg'=>'获取成功','status'=>true,],'JSON');
		}else{
			$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'获取失败','status'=>true,],'JSON');
		}
	}
	
	/**
	* 编辑类型
	*/
	public function typesave(){
		if (IS_POST) {
			$temp=$this->tool->img_type->where(['lname' => I('post.lname')])->select();
			if (!$temp || count($temp)==1 && $temp[0]['tid']==I('post.tid')) {
				$typearr=$this->tool->img_type->where(['tid' => I('post.tid')])->save(['lname' => I('post.lname'), 'state' => I('post.state'), 'webShow' => I('post.state') == '2' ? '0' : I('post.webShow')]);
				if($typearr == true){
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑成功','status'=>true,],'JSON');
				}else if ($typearr == false){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'编辑失败','status'=>true,],'JSON');
				}else if ($typearr == 0){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请不要重复修改','status'=>true,],'JSON');
				}
			} else {
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'名称重复，请修改后重新提交','status'=>true,],'JSON');
			}
		}else{
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
		
	}
	
	/**
	* 添加类型
	*/
	public function typeAdd(){
		if (IS_POST) {
			$rtn=$this->tool->img_type->add(['lname' => I('post.lname'), 'state' => I('post.state'), 'webShow' => I('post.webShow')]);
			if($rtn){
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
		
	}
	
	/**
	* 删除类型
	*/
	public function typeDel(){
		if (IS_POST) {
			$rtn = $this->tool->img_type->where(['tid' => I("post.tid")])->delete();
			if ($rtn) {
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 添加分类
	*/
	public function detailsAdd(){
		if(IS_POST){
			$articleaaa=$this->tool->img_details->where(['tbid' => I('post.tbid'), 'dname' => I('post.dname')])->select();
			if($articleaaa){
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败,已有此分类','status'=>true,],'JSON');
			}else{
				$detailslistArr = [
					'tbid' 		=> I('post.tbid'),
					'dname' 	=> I('post.dname'),
					'state' 	=> I('post.state'),
					'webShow' 	=> I('post.webShow'),
				];
				$rtn=$this->tool->img_details->add($detailslistArr);
				if($rtn){
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,],'JSON');
				}else{
					$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败','status'=>true,],'JSON');
				}
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 编辑分类
	*/
	public function detailssave(){
		if (IS_POST) {
			$temp=$this->tool->img_details->where(['dname' => I('post.dname')])->select();
			if (!$temp || count($temp)==1 && $temp[0]['did']==I('post.did')) {
				$articleaaa=$this->tool->img_details->where(['did' => I('post.did')])->save(['tbid' => I('post.tbid'), 'dname' 	=> I('post.dname'), 'state' => I('post.state'), 'webShow' => I('post.state') == '2' ? '0' : I('post.webShow')]);
				if($articleaaa == true){
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑成功','status'=>true,],'JSON');
				}else if ($articleaaa == false){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'编辑失败','status'=>true,],'JSON');
				}else if ($articleaaa == 0){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请不要重复修改','status'=>true,],'JSON');
				}
			} else {
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'名称重复，请修改后重新提交','status'=>true,],'JSON');
			}
		}else{
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 删除分类
	*/
	public function detailsDel(){
		if (IS_POST) {
			$rtn = $this->tool->img_details->where(['did' => I("post.did")])->delete();
			if ($rtn) {
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 添加标签组
	*/
	public function labelsAdd(){
		if(IS_POST){
			$articleaaa=$this->tool->img_group_label->where(['name' => I('post.name')])->select();
			if($articleaaa){
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败,已有此标签组','status'=>true,],'JSON');
			}else{
				$rtn=$this->tool->img_group_label->add(['lid' => null, 'name' => I('post.name'), 'state' => '1', 'webShow' => '1']);
				if($rtn){
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,],'JSON');
				}else{
					$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败','status'=>true,],'JSON');
				}
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 编辑标签组
	*/
	public function labelssave(){
		if (IS_POST) {
			$temp=$this->tool->img_group_label->where(['name' => I('post.name')])->select();
			if (!$temp || count($temp)==1) {
				$articleaaa=$this->tool->img_group_label->where(['gid' => I('post.gid')])->save(['lid' => null, 'name' => I('post.name'), 'state' => I('post.state'), 'webShow' => I('post.state') == '2' ? '0' : I('post.webShow')]);
				if($articleaaa == true){
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑成功','status'=>true,],'JSON');
				}else if ($articleaaa == false){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'编辑失败','status'=>true,],'JSON');
				}else if ($articleaaa == 0){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请不要重复修改','status'=>true,],'JSON');
				}
			} else {
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'名称重复，请修改后重新提交','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 删除标签组
	*/
	public function labelsDel(){
		if (IS_POST) {
			$rtn = $this->tool->img_group_label->where(['gid' => I("post.gid")])->delete();
			if ($rtn) {
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	//label标签添加
	public function labelAdd(){
		if(IS_POST){
			$articleaaa=$this->tool->img_label->where(['name' => I('post.name')])->select();
			if($articleaaa){
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败,已有此标签','status'=>true,],'JSON');
			}else{
				$rtn=$this->tool->img_label->add(['gid' => I('post.gid'), 'name' => I('post.name'), 'state' => '1', 'webShow' => '1',]);
				if($rtn){
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,],'JSON');
				}else{
					$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'添加失败','status'=>true,],'JSON');
				}
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	//label标签修改
	public function labelsave(){
		if (IS_POST) {
			$temp=$this->tool->img_label->where(['name' => I('post.name')])->select();
			if (!$temp || count($temp)==1) {
				$articleaaa=$this->tool->img_label->where(['lid' => I('post.lid')])->save(['gid' => I('post.gid'), 'name' => I('post.name'), 'state' => I('post.state'), 'webShow' => I('post.state') == '2' ? '0' : I('post.webShow')]);
				if($articleaaa == true){
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑成功','status'=>true,],'JSON');
				}else if ($articleaaa == false){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'编辑失败','status'=>true,],'JSON');
				}else if ($articleaaa == 0){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请不要重复修改','status'=>true,],'JSON');
				}
			} else {
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'名称重复，请修改后重新提交','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 删除标签
	*/
	public function labelDel(){
		if (IS_POST) {
			$rtn = $this->tool->img_label->where(['lid' => I("post.lid")])->delete();
			if ($rtn) {
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 用户回收list
	*/
	public function userRecovery(){
		$usersArr=$this->tool->img_users->where('state = 1')->select();
		$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$usersArr == false ? [] : $usersArr, 'msg'=>'success','status'=>true,],'JSON');
	}
	
	/**
	* 用户list(状态正常的用户)
	*/
	public function user_list(){
		$usersArr=$this->tool->img_users->where('state = 0')->select();
		$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$usersArr == false ? [] : $usersArr, 'msg'=>'success','status'=>true,],'JSON');
	}
	
	/**
	* 用户list(前台显示需要的所有用户)
	*/
	public function web_user_list(){
		$usersArr=$this->tool->img_users->select();
		$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$usersArr == false ? [] : $usersArr, 'msg'=>'success','status'=>true,],'JSON');
	}
	
	/**
	* 用户还原
	*/
	public function reduction(){
		if (IS_POST) {
			$rtn=$this->tool->img_users->where(['uId' => I("post.uId")])->save(['state' => 0]);
			if($rtn){
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'还原成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'还原失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 超管修改用户信息
	*/
	public function guanliuserSave(){
		if(IS_POST){
			if(I("post.password") == '') {
				$rtn=$this->tool->img_users->where(['uId' => I("post.uId")])->save(['nickname' => I("post.nickname"), 'sex' => I("post.sex"), 'permissions' => I("post.permissions"), 'webShow' => I('post.state') == '1' ? '0' : I('post.webShow'), 'state' => I("post.state"), 'judgeLogin' => I("post.judgeLogin")]);
			} else {
				$rtn=$this->tool->img_users->where(['uId' => I("post.uId")])->save(['nickname' => I("post.nickname"), 'sex' => I("post.sex"), 'password' => I("post.password"), 'permissions' => I("post.permissions"), 'webShow' => I('post.state') == '2' ? '0' : I('post.webShow'), 'state' => I("post.state")]);
			}
			
			if($rtn){
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'编辑失败','status'=>true,],'JSON');
			}
		}
	}
	
	/**
	 * 处理文件上传
	 */
	public function upfile()
	{
		$con=array(
			"maxSize"	=>0,//文件大小
			"exts"		=>array('jpg','gif','png','jpeg','psd','psb','ai','mp4','3gp','avi','rmvb','flv','wmv','mpeg','mov','mkv','flv','f4v','m4v','rm','dat','ts','mts'),//文件类型
			"autoSub"	=>false,
			"rootPath"	=>"./",//根目录   等于项目名下
			"savePath"	=>"file/img/".date("Y-m-d")."/",//路径，相对于项目名下
		);
		$upobj = new \Think\Upload($con);
		$arr = $upobj->upload();
		$imgcompress = $this->imgcompress;
		if ($arr) {	//上传成功
			$yuanwenj=$arr['file']['name'];
			$id=I('get.id');
			$image = new \Think\Image();
			if($id=="1"){//图片
				$res['dataImg']="file/img/".date("Y-m-d")."/".$arr['file']['savename'];
				$res['type']="1";
				$dir="file/miniImg/".date("Y-m-d");
				if (!file_exists($dir)){
					mkdir ($dir,0777,true);
				}
				$houzhui=strrchr($arr['file']['savename'],'.');
				if($houzhui!=".gif"){
					$res['miniImg']=$dir."/".'mini_'.$arr['file']['savename'];
					$image->open($res['dataImg']);
					$res['size']=$this->tool->setImgSize($image->width(), $image->height());
					$res['size']['size']=$this->tool->getFileSize($arr['file']['size']);
					$image->thumb($res['size']['width'],$res['size']['height'],\Think\Image::IMAGE_THUMB_FILLED)->save($res['miniImg']);
				}else{
					$res['miniImg']="file/img/".date("Y-m-d")."/".$arr['file']['savename'];
				}
			} else if($id=="2"){
				//头像
				$res = "file/img/".date("Y-m-d")."/".$arr['file']['savename'];
			} else if($id=="3"){//psd缩略图
				$res['dataPsdImg']="file/img/".date("Y-m-d")."/".$arr['file']['savename'];
				$res['type']='3';
				$res['msg']='0';
			} else if($id=="4"){//psd文件
				$res['dataPsd']="file/img/".date("Y-m-d")."/".$arr['file']['savename'];
				$filepsd=$res['dataPsd'];
				$filename=$arr['file']['savename'];
				if($filepsd!=""){
					$folder="file/psdview/".date("Y-m-d");
					$dir = iconv("UTF-8", "GBK", $folder);
					if (!file_exists($dir)){
						mkdir ($dir,0777,true);
					}
					$pathimg=$folder."/".$filename.".jpg";
					$im = new \Imagick();
					$im->readImage($filepsd.'[0]');
					$im->writeImages($pathimg, false);
					if($im){
						$res['Psdview']=$pathimg;
						// psd缩略图压缩
						$image->open($pathimg);
						$res['size']=$this->tool->setImgSize($image->width(), $image->height());
						$res['size']['size']=$this->tool->getFileSize($arr['file']['size']);
						$image->thumb($res['size']['width'],$res['size']['height'],\Think\Image::IMAGE_THUMB_FILLED)->save($pathimg);
					}
				}
				$res['type']='4';
			} else if($id=="5"){//视频缩略图
				$res['dataVideoImg']="file/img/".date("Y-m-d")."/".$arr['file']['savename'];
				$res['type']='5';
				$res['msg']='0';
			} else if($id=="6"){//视频
				$res['dataVideo']="file/img/".date("Y-m-d")."/".$arr['file']['savename'];
				$filevideo=$res['dataVideo'];
				$filename=$arr['file']['savename'];
				if($filevideo!=""){
					$folder="file/videoview/".date("Y-m-d")."/";
					$dir = iconv("UTF-8", "GBK", $folder);
					if (!file_exists($dir)){
						mkdir ($dir,0777,true);
					}
					$pathimg=$filename.".png";
					$str = "ffmpeg -i " . $filevideo . " -y -f mjpeg -ss 3 -t 1 -s 740x500 " . $folder . $pathimg;
					exec($str, $output);
					if(exec){
						$res['Videoview']=$folder.$pathimg;
					}
				}
				$res['type']='6';
				$res['msg']='0';
			}
			$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$res,'msg'=>'上传成功','status'=>true,],'JSON');
		}else{
			$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>$res,'msg'=>'上传失败','status'=>true,],'JSON');
		}
	}
	
	/**
	 * 判断token
	 */
	public function getUserToken()
	{
		if(IS_POST){
			$user = $this->tool->img_users->where(['uId' => I('post.uId')])->select();
			if($user){
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$user[0],'msg'=>'success','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'没有此token','status'=>true,],'JSON');
			}
		}
	}
	
	/**
	 * 修改用户个人信息
	 */
	public function userSave(){
		if(IS_POST)
		{
			$usersArr=$this->tool->img_users->create();
			if($usersArr){
				if(!file_exists($usersArr['headPortraitSrc'])) $this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
				$usersArrs = [
					'headPortraitSrc' => $usersArr['headPortraitSrc'],
					'nickname' => $usersArr['nickname'],
					'sex' => $usersArr['sex'],
				];
				$rtn=$this->tool->img_users->save($usersArr);
				if($rtn || $rtn===0){
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$usersArrs,'msg'=>'更新成功','status'=>true,],'JSON');
				}else{
					$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>$res,'msg'=>'更新失败','status'=>true,],'JSON');
				}
			}else{
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
			}
		}
	}
	
	/**
	 * 获取用户个人信息
	 */
	public function getUserInfo(){
		if(IS_POST)
		{
			$userInfo=$this->tool->img_users->where(['uId' => I('post.uId')])->select();
			$authgroupArr = $this->tool->img_auth_group->where(['id' => $userInfo['permissions']])->select();
			$authgroupArr[0]['rules']=json_decode($authgroupArr[0]['rules']);
			$jsonArr = [
				'adminInfo'			=> [
					'uId'				=> $userInfo[0]['uId'],
					'headPortraitSrc'	=> $userInfo[0]['headPortraitSrc'],
					'userName'			=> $userInfo[0]['userName'],
					'nickname'			=> $userInfo[0]['nickname'],
					'sex'				=> $userInfo[0]['sex'],
					'registerTime'		=> $userInfo[0]['registerTime'],
					'endTime'			=> $userInfo[0]['endTime'],
					'state'				=> $userInfo[0]['state'],
					'permissions'		=> $userInfo[0]['permissions'],
					'auth'				=> $authgroupArr ? $authgroupArr[0] : "{}",
					'judgeLogin'		=> $userInfo[0]['judgeLogin'],
				]
			];
			$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$jsonArr,'msg'=>'success','status'=>true,],'JSON');
		}
	}
	
	/**
	 * 文件删除
	 */
	public function delfile()
	{
		if(IS_POST)
		{
			if (unlink(I("post.filesrc"))){
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>$res,'msg'=>'删除失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 获取统计页面数据All
	*/
	public function getAdminStatisticsData () {
		$Model = new \Think\Model();
		$where['uId']=I("post.uId");
		$data['temp'] = $this->tool->img_users->where($where)->select();
		// 获取当前年份每个月开始和结束时间戳
		$year = $this->tool->getYearAll();
		// 获取当前月每天的开始和结束时间戳
		$month = $this->tool->getMonthAll();
		// 获取当前月开始的时间戳
		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
		// 获取当前月结束的时间戳
		$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
		if ($data['temp'][0]['permissions'] == 2) {
			$data['m'] = $month;
			// 下载总数
			$data['activeDownloadAll'] = $this->tool->img_information->where('1=1')->count();
			// 用户总数
			$data['userAll'] = $this->tool->img_users->where('1=1')->count();
			// 文章总数
			$data['activeAll'] = $this->tool->img_article->where('1=1')->count();
			// 文章img类型总数
			$data['activeImg'] = $this->tool->img_article->where("typeFile = 'img'")->count();
			// 文章psd类型总数
			$data['activePsd'] = $this->tool->img_article->where("typeFile = 'psd'")->count();
			// 文章video类型总数
			$data['activeVideo'] = $this->tool->img_article->where("typeFile = 'video'")->count();
			// 文章type类型总数
			$data['activeType'] = $Model->query("SELECT t.lname, COUNT(a.mId) as 'count' FROM img_article a, img_type t WHERE a.typeid = t.tid GROUP BY t.lname");
			// 各项目文章总数
			$data['activeProject'] = $Model->query("SELECT g.xname, COUNT(a.mId) as 'count' FROM img_article a, img_project g WHERE a.projectid = g.pid GROUP BY g.xname");
			// 各用户文章总数
			$data['activeUsers'] = $Model->query("SELECT u.nickname, COUNT(a.mId) as 'count' FROM img_article a, img_users u WHERE a.uId = u.uId GROUP BY u.nickname");
			// 用户下载排行榜（在职用户）
			$data['articleRanking'] = $Model->query("SELECT u.nickname, COUNT(a.mId) as 'count' FROM img_article a, img_users u WHERE a.uId = u.uId and u.state != 1 GROUP BY u.nickname ORDER BY count DESC");
			// 文章type类型总数2，当前月份的
			$data['activeType2'] = $Model->query("SELECT t.lname, COUNT(a.mId) as 'count' FROM img_article a, img_type t WHERE a.typeid = t.tid and a.registerTimeImg>=".$beginThismonth." and a.registerTimeImg<=".$endThismonth." GROUP BY t.lname");
			// 文章img类型总数(当前月)
			$data['activeImgMonth'] = $this->tool->img_article->where("typeFile = 'img' and registerTimeImg>=".$beginThismonth." and registerTimeImg<=".$endThismonth)->count();
			// 文章psd类型总数(当前月)
			$data['activePsdMonth'] = $this->tool->img_article->where("typeFile = 'psd' and registerTimeImg>=".$beginThismonth." and registerTimeImg<=".$endThismonth)->count();
			// 文章video类型总数(当前月)
			$data['activeVideoMonth'] = $this->tool->img_article->where("typeFile = 'video' and registerTimeImg>=".$beginThismonth." and registerTimeImg<=".$endThismonth)->count();
			// 用户文章类型1（img/psd/video）
			$data['activeUserType1'] = $Model->query("select u.nickname, a.uId,a.typeFile,count(*) as 'count' from img_article a, img_users u WHERE a.uId = u.uId and u.userName !='admin' group by a.typeFile,a.uId");
			// 用户文章类型2（类型分类）
			$data['activeUserType2'] = $Model->query("select u.nickname, a.typeid, count(*) as 'count' from img_article a, img_type t, img_users u WHERE a.typeid = t.tid and a.uId = u.uId  and u.userName !='admin' group by a.typeid,a.uId");
			// 所有用户昵称
			$data['userNicknameAll'] = $Model->query("select nickname,uId from img_users WHERE state!=1 and userName !='admin'");
			// 所有类型
			$data['typeAll'] = $this->tool->img_type->where("1=1")->select();
			// 所有项目
			$data['projectAll'] = $this->tool->img_project->where("1=1")->select();
			// 最近发布文章
			$data['activeLately'] = $Model->query("SELECT a.mId, a.typeFile, a.title, a.img, a.psd, a.video, a.registerTimeImg, u.nickname, u.sex, a.click FROM img_article a, img_users u WHERE a.uId = u.uId GROUP BY a.title ORDER BY a.registerTimeImg DESC limit 20");
			// 全年文章类型分布
			for ($i=0; $i<=11; $i++) {
				$data['activeTypeYear'][$i][0] = $year[$i]->y;
				$data['activeTypeYear'][$i][1] = $this->tool->img_article->where("typeFile = 'img' and registerTimeImg >=".$year[$i]->start." and registerTimeImg <= ".$year[$i]->end)->count();
				$data['activeTypeYear'][$i][2] = $this->tool->img_article->where("typeFile = 'psd' and registerTimeImg >=".$year[$i]->start." and registerTimeImg <= ".$year[$i]->end)->count();
				$data['activeTypeYear'][$i][3] = $this->tool->img_article->where("typeFile = 'video' and registerTimeImg >=".$year[$i]->start." and registerTimeImg <= ".$year[$i]->end)->count();
			}
			$data['year'] = $year;
			
			// 全年用户每月下载数
			$data['temp'] = [];
			for ($j=0; $j<=11; $j++) {
				$data['activeDownloadYearAll'][$j] = $Model->query("SELECT u.nickname, count(*) as 'count' FROM img_information i, img_users u WHERE u.uId = i.froid and i.created >= ".$year[$j]->start." and i.created <= ".$year[$j]->end." group by u.nickname");
			}
			
			// 当月在职用户每日发布情况
			$data['temp'] = [];
			for ($e=0; $e<count($data['userNicknameAll']); $e++) {
				$data['activeUserReleaseMonthAll'][$e]['name'] = $data['userNicknameAll'][$e]['nickname'];
				for ($l=0; $l<=count($month); $l++) {
					$data['temp'] = $this->tool->img_article->where("uId = ".$data['userNicknameAll'][$e]['uId']." and registerTimeImg >=".$month[$l]['start']." and registerTimeImg <= ".$month[$l]['end'])->count();
					$data['activeUserReleaseMonthAll'][$e]['data'][$l] = $data['temp'] == null ? '0' : $data['temp'];
				}
			}
			
			// 每个类型下项目占比（总、月）
			$data['activeProjectMonthImg'] = $Model->query("SELECT p.pid, p.xname, count(*) as 'img' FROM (SELECT * FROM img_article WHERE typeFile = 'img' and registerTimeImg >= ".$beginThismonth." and registerTimeImg <= ".$endThismonth.") a, img_project p WHERE a.projectid = p.pid group by p.pid;");
			$data['activeProjectMonthPsd'] = $Model->query("SELECT p.pid, p.xname, count(*) as 'psd' FROM (SELECT * FROM img_article WHERE typeFile = 'psd' and registerTimeImg >= ".$beginThismonth." and registerTimeImg <= ".$endThismonth.") a, img_project p WHERE a.projectid = p.pid group by p.pid;");
			$data['activeProjectMonthVideo'] = $Model->query("SELECT p.pid, p.xname, count(*) as 'video' FROM (SELECT * FROM img_article WHERE typeFile = 'video' and registerTimeImg >= ".$beginThismonth." and registerTimeImg <= ".$endThismonth.") a, img_project p WHERE a.projectid = p.pid group by p.pid;");
			$data['activeProjectTotalImg'] = $Model->query("SELECT p.pid, p.xname, count(*) as 'img' FROM (SELECT * FROM img_article WHERE typeFile = 'img') a, img_project p WHERE a.projectid = p.pid group by p.pid;");
			$data['activeProjectTotalPsd'] = $Model->query("SELECT p.pid, p.xname, count(*) as 'psd' FROM (SELECT * FROM img_article WHERE typeFile = 'psd') a, img_project p WHERE a.projectid = p.pid group by p.pid;");
			$data['activeProjectTotalVideo'] = $Model->query("SELECT p.pid, p.xname, count(*) as 'video' FROM (SELECT * FROM img_article WHERE typeFile = 'video') a, img_project p WHERE a.projectid = p.pid group by p.pid;");
			
			// 每个类型下项目下用户占比的（总、月）
			$data['activeProjectUserTotalImg'] = $Model->query("SELECT p.pid, p.xname, u.uId, u.nickname, count(*) as 'img' FROM (SELECT * FROM img_article WHERE typeFile = 'img') a, img_project p, img_users u WHERE a.projectid = p.pid and a.uId = u.uId group by u.nickname, p.xname;");
			$data['activeProjectUserTotalPsd'] = $Model->query("SELECT p.pid, p.xname, u.uId, u.nickname, count(*) as 'psd' FROM (SELECT * FROM img_article WHERE typeFile = 'psd') a, img_project p, img_users u WHERE a.projectid = p.pid and a.uId = u.uId group by u.nickname, p.xname;");
			$data['activeProjectUserTotalVideo'] = $Model->query("SELECT p.pid, p.xname, u.uId, u.nickname, count(*) as 'video' FROM (SELECT * FROM img_article WHERE typeFile = 'video') a, img_project p, img_users u WHERE a.projectid = p.pid and a.uId = u.uId group by u.nickname, p.xname;");
			$data['activeProjectUserMonthImg'] = $Model->query("SELECT p.pid, p.xname, u.uId, u.nickname, count(*) as 'img' FROM (SELECT * FROM img_article WHERE typeFile = 'img' and registerTimeImg >= ".$beginThismonth." and registerTimeImg <= ".$endThismonth.") a, img_project p, img_users u WHERE a.projectid = p.pid and a.uId = u.uId group by u.nickname, p.xname;");
			$data['activeProjectUserMonthPsd'] = $Model->query("SELECT p.pid, p.xname, u.uId, u.nickname, count(*) as 'psd' FROM (SELECT * FROM img_article WHERE typeFile = 'psd' and registerTimeImg >= ".$beginThismonth." and registerTimeImg <= ".$endThismonth.") a, img_project p, img_users u WHERE a.projectid = p.pid and a.uId = u.uId group by u.nickname, p.xname;");
			$data['activeProjectUserMonthVideo'] = $Model->query("SELECT p.pid, p.xname, u.uId, u.nickname, count(*) as 'video' FROM (SELECT * FROM img_article WHERE typeFile = 'video' and registerTimeImg >= ".$beginThismonth." and registerTimeImg <= ".$endThismonth.") a, img_project p, img_users u WHERE a.projectid = p.pid and a.uId = u.uId group by u.nickname, p.xname;");

			$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$data,'msg'=>'success','status'=>true,],'JSON');
		}else{
			$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'111','msg'=>'获取失败','status'=>true,],'JSON');
		}
	}
	
	/**
	* 获取用户回收站文章
	*/
	public function getRecoveryArticle () {
		if(IS_POST)
		{
			$user = $this->tool->img_users->where(['uId' => I("post.uId")])->select();
			if ($user) {
				$article = $this->tool->img_article->where(['state' => 2])->select();
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
									if($dfimg!=''){
										$imgdel=unlink($dfimg);						
									}
									if($dfimgs!=''){
										$imgdel=unlink($dfimgs);
									}
								}
							}
							
							$jsonpsd=$adel['psd'];
							$dfilepsd = json_decode($jsonpsd,true);
							if(count($dfilepsd) != 0){
								for($i=0;$i<count($dfilepsd);$i++){
									$dfpsd=$dfilepsd[$i]['dataPsd']['url'];
									$dfpsds=$dfilepsd[$i]['Psdview'];
									if($dfpsd!=''){
										$psddel=unlink($dfpsd);						
									}
									if($dfpsds!=''){
										$psddel=unlink($dfpsds);
									}
								}
							}
							
							$jsonvideo=$adel['video'];
							$dfilevideo= json_decode($jsonvideo,true);
							if(count($dfilevideo) != 0){
								for($i=0;$i<count($dfilevideo);$i++){
									$dfvideoimg=$dfilevideo[$i]['dataVideo']['url'];
									$dfvideofil=$dfilevideo[$i]['Videoview'];
									if($dfvideoimg!=''){
										$videodel=unlink($dfvideoimg);
									}
									if($dfvideofil!=''){
										$videodel=unlink($dfvideofil);
									}
								}
							}
							$this->tool->img_article->where($where)->delete();
							$userInfo = $this->tool->img_users->where(['uId' => I("post.uId")])->find();
							$userAuthGroupInfo = $this->tool->img_auth_group->where(['id' => $userInfo["permissions"]])->find();
							$data = [
								"uId" 						=> I("post.uId"),
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
							$rn = $this->tool->img_operationinfo->add($data);
						}
					}
				}
				$articles = $this->tool->img_article->where(['uId' => I("post.uId"), 'state' => 2])->select();
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$articles,'msg'=>'success','status'=>true,],'JSON');
			} else {
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误111','status'=>true,],'JSON');
			}
			
		}
	}
	
	/**
	* 管理员添加用户
	*/
	public function user_add(){
		if(IS_POST)
		{
			$usersArr=$this->tool->img_users->create();
			//执行添加
			if($usersArr){
				if($usersArr['userName'] =='' && $usersArr['password'] == ''){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'0','msg'=>'用户名、密码不能为空','status'=>true,],'JSON');
				}
				$sql=$this->tool->img_users->where(['userName' => $usersArr['userName']])->find();
				if($sql['userName']){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'该账号已被注册','status'=>true,],'JSON');
				}else{
					$usersArr['headPortraitSrc'] = 'image/sq17.png';
					$usersArr['password'] = md5($usersArr['password']);
					$usersArr['nickname'] = $usersArr['nickname'];
					$usersArr['sex'] = $usersArr['sex'];
					$usersArr['state'] = $usersArr['state'];
					$usersArr['permissions'] = $usersArr['permissions'];
					$usersArr['registerTime'] = time();
					$usersArr['access_token'] = $this->tool->secretkey($usernames);
					$usersArr['token_expires_in'] = time() + $this->tool->time_day;
					$usersArr['judgeLogin'] = '0';
					$rtn=$this->tool->img_users->add($usersArr);
					if($rtn){
						$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'注册成功','status'=>true,],'JSON');
					}else{
						$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'注册失败','status'=>true,],'JSON');
					}
				}
			}else{
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'格式错误','status'=>true,],'JSON');
			}
		}
	}
	
	/**
	* 添加内容
	*/
	public function articleAdd()
	{
		if(IS_POST)
		{
			if(strlen(I('post.title')) == 0){
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请填写标题在提交','status'=>true,],'JSON');
			}
			if(strlen(I('post.keyword')) == 0){
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请填写关键词在提交','status'=>true,],'JSON');
			}
			if(strlen(I('post.describe')) == 0){
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'请填写描述在提交','status'=>true,],'JSON');
			}
			$article = [
				'uId' 				=> I('post.uId'),
				'typeFile' 			=> I('post.typeFile'),
				'typeid' 			=> I('post.typeid'),
				'projectid' 		=> I('post.projectid'),
				'detailsid' 		=> I('post.detailsid'),
				'title' 			=> I('post.title'),
				'keyword' 			=> I('post.keyword'),
				'describe' 			=> I('post.describe'),
				'img' 				=> I('post.typeFile') == 'img' || 'video' ?  json_encode(I('post.img')) : '"[]"',
				'psd' 				=> I('post.typeFile') == 'psd' ? json_encode(I('post.psd')) : '"[]"',
				'video' 			=> I('post.typeFile') == 'video' ? json_encode(I('post.video')) : '"[]"',
				'compress' 			=> null,
				'registerTimeImg' 	=> time(),
				'endTimeImg' 		=> 0,
				'click' 			=> 0,
				'state' 			=> 1,
			];
			$n = $this->tool->img_article->add($article);
			if($n){
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'添加成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'5','msg'=>'添加失败','status'=>true,],'JSON');
			}
		}else{
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 判断title是否重复
	*/
	public function getTitleRepeat(){
		if(IS_POST){
			//判断title不为空
			if(I('post.title')!="") $where['title']=I('post.title');
			//查询数据库
			$articleaaa=$this->tool->img_article->where($where)->find();
			if($articleaaa){
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'0','msg'=>'此标题重复','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'1','msg'=>'此标题可正常使用','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 判断title是否重复2
	*/
	public function getUpdateTitleRepeat(){
		if(IS_POST){
			//判断title不为空
			if(I('post.title')!="") $where['title']=I('post.title');
			//查询数据库
			$articleaaa=$this->tool->img_article->where($where)->select();
			if($articleaaa){
				if($articleaaa[0]['mId']==I('post.mId') && count($articleaaa) == 1){
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'0','msg'=>'此标题重复','status'=>true,],'JSON');
				}else{
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'1','msg'=>'此标题可正常使用','status'=>true,],'JSON');
				}
			}else{
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'1','msg'=>'此标题可正常使用','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 后台查看文章分页
	*/
	public function getArticleAll()
	{
		if(IS_POST){
			if(strlen(I("post.page")) == 0 || strlen(I("post.articlePageNum")) == 0) $this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
			$page = intval(I("post.page"));
			$articlePageNum = intval(I("post.articlePageNum"));
			$info = ['state' => 1];
			if(I("post.pid") != "") $info['projectid'] = I("post.pid");
			if(I("post.tid") != "") $info['typeid'] = I("post.tid");
			if(I("post.did") != "") $info['detailsid'] = I("post.did");
			if(I("post.uid") != "") $info['uId'] = I("post.uid");
			$articleAll=$this->tool->img_article->where($info)->count();
			$articleArrs=$this->tool->img_article->where($info)->order('mId desc')->limit(($page - 1) * $articlePageNum, $articlePageNum)->select();
			if($articleArrs){
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>['article' => $articleArrs, 'articleNum' => $articleAll, 'page' => $page ],'msg'=>'success','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>['article' => [], 'articleNum' => 0],'msg'=>'没有数据','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
		
	}
	
	/**
	* 前台查看文章分页
	*/
	public function getWebArticleAll()
	{
		if(IS_POST){
			if(strlen(I("post.page")) == 0 || strlen(I("post.articlePageNum")) == 0) $this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
			$keyword = I("post.keyword");
			$page = intval(I("post.page"));
			$articlePageNum = intval(I("post.articlePageNum"));
			$project=$this->tool->img_project->where(['webShow' => 0])->select();
			$types=$this->tool->img_type->where(['webShow' => 0])->select();
			$details=$this->tool->img_details->where(['webShow' => 0])->select();
			$users=$this->tool->img_users->where(['webShow' => 0])->select();
			if(count($project) != 0){
				$temp = [];
				for($i=0;$i<count($project);$i++){
					$temp[$i] = array('neq',$project[$i]['pid']);
				}
				$info['projectid'] = $temp;
			}
			if(count($types) != 0){
				$temp = [];
				for($j=0;$j<count($types);$j++){
					$temp[$j] = array('neq',$types[$j]['tid']);
				}
				$info['typeid'] = $temp;
			}
			if(count($details) != 0){
				$temp = [];
				for($n=0;$n<count($details);$n++){
					$temp[$n] = array('neq',$details[$n]['did']);
				}
				$info['detailsid'] = $temp;
			}
			if(count($users) != 0){
				$temp = [];
				for($o=0;$o<count($users);$o++){
					$temp[$o] = array('neq',$users[$o]['uId']);
				}
				$info['uId'] = $temp;
			}
			$info['state'] = 1;
			if(I("post.pid") != "") $info['projectid'] = I("post.pid");
			if(I("post.tid") != "") $info['typeid'] = I("post.tid");
			if(I("post.did") != "") $info['detailsid'] = I("post.did");
			if(I("post.uid") != "") $info['uId'] = I("post.uid");
			if(I("post.keyword") != "") {
				$info['title|keyword|describe']=array("like","%{$keyword}%");
			}
			$articleAll=$this->tool->img_article->where($info)->count();
			$articleArrs=$this->tool->img_article->where($info)->order('mId desc')->limit(($page - 1) * $articlePageNum, $articlePageNum)->select();
			$sql = $this->tool->img_article->getLastSql();
			if($articleArrs){
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>['article' => $articleArrs, 'articleNum' => $articleAll, 'page' => $page ],'msg'=>$sql,'status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>['article' => [], 'articleNum' => 0],'msg'=>$sql,'status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
		
	}
	
	/**
	* 删除文章（回收站）
	*/
	public function exhibitionDel()
	{
		if(IS_POST){
			$articleUserInfo = $this->tool->img_users->where(['uId' => I("post.uId")])->find();
			$article = $this->tool->img_article->where(['mId' => I("post.mId")])->find();
			if($article['uId'] == I("post.uId") || $articleUserInfo['permissions'] == '2'){
				$rtn=$this->tool->img_article->where(['mId' => I("post.mId")])->save(['state' => 2, 'retainTime' => time()]);
				if($rtn){
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,],'JSON');
				}else{
					$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'删除失败','status'=>true,],'JSON');
				}
			} else {
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'权限不足，无法操作','status'=>true,],'JSON');
			}
			
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 还原文章
	*/
	public function exhibitionreduction(){
		if(IS_POST){
			$rtn=$this->tool->img_article->where(['mId' => I("post.mId")])->save(['state' => 1, 'retainTime' => 0]);
			if($rtn){
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'还原成功','status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'','msg'=>'还原失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
		
	}
	
	/**
	* 彻底删除文章
	*/
	public function delArticle(){
		if(IS_POST){
			$where['mId']=I('post.mId');
			$adel = $this->tool->img_article->where($where)->find();
			if($adel){
				$jsonimg=$adel['img'];
				$dfileimg= json_decode($jsonimg,true);
				if(count($dfileimg) != 0){
					for($i=0;$i<count($dfileimg);$i++){
						$dfimg=$dfileimg[$i]['dataImg']['url'];
						$dfimgs=$dfileimg[$i]['miniImg'];
						if($dfimg!=''){
							$imgdel=unlink($dfimg);						
						}
						if($dfimgs!=''){
							$imgdel=unlink($dfimgs);
						}
					}
				}
				
				$jsonpsd=$adel['psd'];
				$dfilepsd = json_decode($jsonpsd,true);
				if(count($dfilepsd) != 0){
					for($i=0;$i<count($dfilepsd);$i++){
						$dfpsd=$dfilepsd[$i]['dataPsd']['url'];
						$dfpsds=$dfilepsd[$i]['Psdview'];
						if($dfpsd!=''){
							$psddel=unlink($dfpsd);						
						}
						if($dfpsds!=''){
							$psddel=unlink($dfpsds);
						}
					}
				}
				
				$jsonvideo=$adel['video'];
				$dfilevideo= json_decode($jsonvideo,true);
				if(count($dfilevideo) != 0){
					for($i=0;$i<count($dfilevideo);$i++){
						$dfvideoimg=$dfilevideo[$i]['dataVideo']['url'];
						$dfvideofil=$dfilevideo[$i]['Videoview'];
						if($dfvideoimg!=''){
							$videodel=unlink($dfvideoimg);
						}
						if($dfvideofil!=''){
							$videodel=unlink($dfvideofil);
						}
					}
				}
				$rn = $this->tool->img_article->where($where)->delete();
				if($rn){
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'删除成功','status'=>true,],'JSON');
				}else{
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'删除失败','status'=>true,],'JSON');
				}
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 获取单个文章信息(后台)
	*/
	public function getAdminArticle()
	{
		if(IS_POST){
			$articleArrs=$this->tool->img_article->where(['mId' => I('post.mId')])->select();
			$xname = $this->tool->img_project->where(['pid' => $articleArrs[0]['projectid']])->select();
			$lname = $this->tool->img_type->where(['tid' => $articleArrs[0]['typeid']])->select();
			$dname = $this->tool->img_details->where(['did' => $articleArrs[0]['detailsid']])->select();
			$articleArrs[0]['xname'] = $xname[0]['xname'];
			$articleArrs[0]['lname'] = $lname[0]['lname'];
			$articleArrs[0]['dname'] = $dname[0]['dname'];
			if($articleArrs){
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$articleArrs[0],'msg'=>$this->tool->img_article->getLastSql(),'status'=>true,],'JSON');
			}else{
				$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>$this->tool->img_article->getLastSql(),'msg'=>'获取失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 获取单个文章信息(前台)
	*/
	public function getWebArticle()
	{
		if(IS_POST){
			if(I('post.mId') != '') $ini['mId']=I('post.mId');
			if(I('post.mId') == '') $this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
			$article = $this->tool->img_article->where($ini)->find();
			$article['keyword'] =  explode(',',$article['keyword']);
			$article['img'] = json_decode($article['img']);
			$article['psd'] = json_decode($article['psd']);
			$article['video'] = json_decode($article['video']);
			$temp2 = [];
			$project=$this->tool->img_project->where(['webShow' => 0])->select();
			$types=$this->tool->img_type->where(['webShow' => 0])->select();
			$details=$this->tool->img_details->where(['webShow' => 0])->select();
			$users=$this->tool->img_users->where(['webShow' => 0])->select();
			if(count($project) != 0){
				$temp = [];
				for($i=0;$i<count($project);$i++){
					$temp[$i] = array('neq',$project[$i]['pid']);
				}
				$info['projectid'] = $temp;
			}
			if(count($types) != 0){
				$temp = [];
				for($j=0;$j<count($types);$j++){
					$temp[$j] = array('neq',$types[$j]['tid']);
				}
				$info['typeid'] = $temp;
			}
			if(count($details) != 0){
				$temp = [];
				for($n=0;$n<count($details);$n++){
					$temp[$n] = array('neq',$details[$n]['did']);
				}
				$info['detailsid'] = $temp;
			}
			if(count($users) != 0){
				$temp = [];
				for($o=0;$o<count($users);$o++){
					$temp[$o] = array('neq',$users[$o]['uId']);
				}
				$info['uId'] = $temp;
			}
			for($o=0;$o<count($article['keyword']);$o++){
				$temp2[$o]=array("like","%{$article['keyword'][$o]}%");
			}
			$info['title|keyword|describe'] = $temp2;
			$article['xiangguan'] = $this->tool->img_article->where($info)->limit(20)->select();
			$r = $this->tool->img_article->getLastSql();
			$article['user'] = $this->tool->img_users->where(['uId' => $article['uId']])->find();
			$this->tool->img_article->where(['mId' => $article['mId']])->save(['click' => intval($article['click']) + 1 ]);
			$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$article,'msg'=>'获取成功','status'=>$r,],'JSON');
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 修改文章
	*/
	public function articleUpdate()
	{
		if(IS_POST){
			$articleInfo = [
				'mId' => I('post.mId'),
				'uId' => I('post.uId'),
				'pid' => I('post.pid'),
				'tid' => I('post.tid'),
				'did' => I('post.did'),
				'title' => I('post.title'),
				'keyword' => I('post.keyword'),
				'describe' => I('post.describe'),
				'img' => json_encode(I('post.img')),
				'psd' => json_encode(I('post.psd')),
				'video' => json_encode(I('post.video')),
				'typeFile' => json_encode(I('post.typeFile')),
			];
			$articleArr = $this->tool->img_article->where(['mId' => $articleInfo['mId']])->select();
			if($articleArr)
			{
				$articleArr[0]['uId'] = $articleInfo['uId'];
				$articleArr[0]['typeid'] = $articleInfo['tid'];
				$articleArr[0]['projectid'] = $articleInfo['pid'];
				$articleArr[0]['detailsid'] = $articleInfo['did'];
				$articleArr[0]['title'] = $articleInfo['title'];
				$articleArr[0]['keyword'] = $articleInfo['keyword'];
				$articleArr[0]['describe'] = $articleInfo['describe'];
				$articleArr[0]['img']=$articleInfo['img'];
				$articleArr[0]['psd']=$articleInfo['psd'];
				$articleArr[0]['video']=$articleInfo['video'];
				$articleArr[0]['endTimeImg'] = time();
				if($articleInfo['img']!='"[]"'){
					$articleArr[0]['img']=$articleInfo['img'];
					$articleArr[0]['psd']='"[]"';
					$articleArr[0]['video']='"[]"';
					$articleArr[0]['typeFile']="img";
				}
				if($articleInfo['psd']!='"[]"'){
					$articleArr[0]['img']='"[]"';
					$articleArr[0]['psd']=$articleInfo['psd'];
					$articleArr[0]['video']='"[]"';
					$articleArr[0]['typeFile']="psd";
				}
				if($articleInfo['video']!='"[]"'){
					if($articleInfo['img']!='"[]"'){
						$articleArr[0]['img']=$articleInfo['img'];
					}else{
						$articleArr[0]['img']='"[]"';
					}
					$articleArr[0]['psd']='"[]"';
					$articleArr[0]['video']=$articleInfo['video'];
					$articleArr[0]['typeFile']="video";
				}
				$n = $this->tool->img_article->where(['mId' => $articleInfo['mId']])->save($articleArr[0]);
				if ($n){
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'修改成功','status'=>true,],'JSON');
				}else{
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'修改失败','status'=>true,],'JSON');
				}
			} else {
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'修改失败','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 后台首页数据获取
	*/
	public function getAdminIndexData()
	{
		if(IS_POST){
			if(I('post.uId') == '') $this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
			$data['article'] = $this->tool->img_article->where(['state' => 1,'uId' => I('post.uId')])->order('registerTimeImg DESC')->limit(12)->select();
			$data['downloadNum'] = $this->tool->img_information->where(['inid' => I('post.uId')])->count();
			$data['articleNum'] = $this->tool->img_article->where(['state' => 1, 'uId' => I('post.uId')])->count();
			$data['articleDeleteNum'] = $this->tool->img_article->where(['state' => 2, 'uId' => I('post.uId')])->count();
			$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$data,'msg'=>'success','status'=>true,],'JSON');
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
	
	/**
	* 注销
	*/
	public function exitlogin(){
		//退出系统
		if(IS_POST){
			if (!empty(I("post.uId"))) {
				$rtn=$this->tool->img_users->where(['uId' => I("post.uId")])->save(['state' => 0]);
				$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'','msg'=>'success','status'=>true,],'JSON');
			}
		} else {
			$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'','msg'=>'参数错误','status'=>true,],'JSON');
		}
	}
}