<?php
namespace Home\Controller;
use Home\Controller\ControllerController;


class WebController extends ControllerController {

	public function __construct() 
	{
		parent::__construct();
	}
	
	/**
	* 登录
	*/
	public function login(){
		//登录验证
		if (IS_POST) {
			//数据创建
			$UsersArrServe = $this->tool->img_users->create();
			$articleNum = $this->tool->img_article->where(['state' => 1])->count();
			$userArr = $this->tool->img_users->where(['userName' => $UsersArrServe['userName']])->find();
			$projectArr = $this->tool->getPublicInfo('project', $this->tool->img_project->where('1 = 1')->select());
			$typeArr = $this->tool->getPublicInfo('type', $this->tool->img_type->where('1 = 1')->select());
			$detailsArr = $this->tool->getPublicInfo('details', $this->tool->img_details->where('1 = 1')->select());
			$groupLabelArr=$this->tool->img_group_label->where('1 = 1')->select();
			$labelArr=$this->tool->img_label->where('1 = 1')->select();
			$authgroupArr = $this->tool->img_auth_group->where(['id' => $userArr['permissions']])->select();
			$userRecovery = $this->tool->img_auth_group->where('1 = 1')->select();
			$authgroupArr[0]['rules']=json_decode($authgroupArr[0]['rules']);
			//判断用户是否存在
			if (!empty($userArr['uId'])) {
				if ($userArr['state']!=1){
					if (md5($UsersArrServe['password']) == $userArr['password']) {
						//修改用户状态
						$rtn=$this->tool->img_users->where(['uId' => $userArr['uId']])->save(['judgeLogin' => 1, 'endTime' => time()]);
						// 版本转移时的临时添加，因增加字段没有赋值access_token和合法时长
						if(!$userArr['access_token']){
							$this->tool->img_users->where(['uId' => $userArr['uId']])->save(['access_token' => $this->tool->secretkey($userArr['userName']), 'token_expires_in' => time() + $this->tool->time_day]);
						}
						$jsonArr = [
							'adminInfo'			=> [
								'uId'				=> $userArr['uId'],
								'headPortraitSrc'	=> $userArr['headPortraitSrc'],
								'userName'			=> $userArr['userName'],
								'nickname'			=> $userArr['nickname'],
								'sex'				=> $userArr['sex'],
								'password'			=> I('post.password'),
								'registerTime'		=> $userArr['registerTime'],
								'endTime'			=> time(),
								'state'				=> $userArr['state'],
								'permissions'		=> $userArr['permissions'],
								'auth'				=> $authgroupArr ? $authgroupArr[0] : "{}",
								'setPasswordStyle'	=> I('post.setPasswordStyle'),
								'adminNavigation'	=> '1',
								'isCollapse'		=> false,
								'judgeLogin'		=> $userArr['judgeLogin'],
								'articlePageNum'	=> 5,
								'pageNum'			=> ceil($articleNum / 5),
								'articleAll'		=> intval($articleNum),
							],
							'publicInfo'		=> [
								'projects'			=> $projectArr ? $projectArr : "[]",
								'types'				=> $typeArr ? $typeArr : "[]",
								'details'			=> $detailsArr ? $detailsArr : "[]",
								'groupLabel'		=> $groupLabelArr ? $groupLabelArr : "[]",
								'label'				=> $labelArr ? $labelArr : "[]",
								'userRecovery'		=> $userRecovery ? $userRecovery : "[]",
								'srcUrl'			=> $this->tool->src_url,
							],
							'token'				=> [
								'access_token'		=> $userArr['access_token'],
								'token_expires_in'	=> $userArr['token_expires_in'] <= time() ? $this->tool->setTokenTimeDay($userArr['token_expires_in'],$userArr['uId']) : $userArr['token_expires_in'],
							]
						];
						$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$jsonArr,'msg'=>'登录成功','status'=>true,],'JSON');
					}else{
						$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'1','msg'=>'用户名或密码错误','status'=>true,],'JSON');
					}
				}else{
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'2','msg'=>'该用户已被冻结','status'=>true,],'JSON');
				}
			}else{
				$this->ajaxReturn(['code'=>$this->tool->not_found,'data'=>'3','msg'=>'用户名不存在','status'=>true,],'JSON');
			}
		}
    }
	
	/**
	* 注册用户
	*/
	public function user_add(){
		header("Access-Control-Allow-Origin: *"); // 允许任意域名发起的跨域请求
		if(IS_POST)
		{
			$usersArr=$this->tool->img_users->create();
			//执行添加
			if($usersArr){
				if($usersArr['userName'] =='' && $usersArr['password'] == '' && $usersArr['verification'] == ''){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'0','msg'=>'用户名、密码、验证码不能为空','status'=>true,],'JSON');
				}
				if($usersArr['verification']!=session('verification')){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'2','msg'=>'验证码错误','status'=>true,],'JSON');
				}else{
					session("verification",null);
				}
				$sql=$this->tool->img_users->where(['userName' => $usersArr['userName']])->find();
				if($sql['userName']){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'3','msg'=>'该账号已被注册','status'=>true,],'JSON');
				}else{
					$usersArr['headPortraitSrc'] = 'image/sq17.png';
					$usersArr['password'] = md5($usersArr['password']);
					$usersArr['nickname'] = $usersArr['userName'];
					$usersArr['state'] = 0;
					$usersArr['permissions'] = 1;
					$usersArr['registerTime'] = time();
					$usersArr['access_token'] = $this->tool->secretkey($usersArr['userName']);
					$usersArr['token_expires_in'] = time() + $this->tool->time_day;
					$rtn=$this->tool->img_users->add($usersArr);
					if($rtn)
					{
						$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'4','msg'=>'注册成功','status'=>true,],'JSON');
					}else{
						$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'5','msg'=>'注册失败','status'=>true,],'JSON');
					}
				}
			}else{
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'5','msg'=>'格式错误','status'=>true,],'JSON');
			}
		}
    }
	
	/**
	* 邮箱发送6位数的验证码
	*/
	public function Send()
	{
		// 生成随机六位数，不足六位两边补零
		$suiji=str_pad(mt_rand(0, 999999), 6, "0", STR_PAD_BOTH);
		if(IS_POST)
		{
			if(!empty(I('post.to'))){
				if(SendMail($_POST['to'],$subject = '验证码', $body = $suiji)){
					$jsonArr = [
						'to' 			=> $_POST['to'],
						'verification'	=> strval($suiji),
					];
					session('verification',strval($suiji));
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$jsonArr,'msg'=>'验证码发送成功','status'=>true,],'JSON');
				}
				else{
					$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'1','msg'=>'发送失败','status'=>true,],'JSON');
				}
			}else{
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'2','msg'=>'参数错误','status'=>true,],'JSON');
			}
		}
    }
	
	/**
	* 检查用户名邮箱是否重复
	*/
	public function emailrepeat(){
		// 接收输入的userName
		$userName=I('post.userName');
		if(IS_POST)
		{
			// 判断userName不为空
			if($userName!="")
			{
				// 查询数据库
				$Usersaaa=$this->tool->img_users->where(['userName' => $userName])->find();
				if($Usersaaa){
					// 有此userName 0
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'0','msg'=>'邮箱已注册','status'=>true,],'JSON');
				}else{
					// 没有userName 1
					$this->ajaxReturn(['code'=>$this->tool->success,'data'=>'1','msg'=>'success','status'=>true,],'JSON');
				}
			}
			
		}
	}
	
	/**
	* 密码找回
	*/
	public function retrievePassword(){
		header("Access-Control-Allow-Origin: *"); // 允许任意域名发起的跨域请求
		if(IS_POST)
		{
			$usersArr=$this->tool->img_users->create();
			//执行添加
			if($usersArr){
				if ($usersArr['userName'] == '' && $usersArr['password'] == '' && $usersArr['verification'] == ''){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'0','msg'=>'请填写完整数据在提交','status'=>true,],'JSON');
				}
				if ($usersArr['verification']!=session('verification')){
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'2','msg'=>'验证码错误','status'=>true,],'JSON');
				}else{
					session("verification",null);
				}
				$sql=$this->tool->img_users->where(['userName' => $usersArr['userName']])->find();
				if ($sql['userName']){
					$rtn=$this->tool->img_users->where(['uId' => $sql['uId']])->save(['password' => md5($usersArr['password'])]);
					if($rtn)
					{
						$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$usersArr,'msg'=>'密码修改成功','status'=>true,],'JSON');
					}else{
						$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>'7','msg'=>'密码修改失败','status'=>true,],'JSON');
					}
				}else{
					$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'4','msg'=>'用户名不存在','status'=>true,],'JSON');
				}
			}else{
				$this->ajaxReturn(['code'=>$this->tool->params_invalid,'data'=>'4','msg'=>'参数错误','status'=>true,],'JSON');
			}
			exit();
		}
	}
	
	/**
	* 压缩下载
	*/
	public function zipdownload(){
		if(IS_POST){
			$zip = new \ZipArchive();
			$downloadFileName = time().'-'.iconv("UTF-8", "GBK", I("post.title").'.zip');
			$downloadFileSrc = iconv("UTF-8", "GBK", './down/');
			$packDownloadFileName = $downloadFileSrc.$downloadFileName;
			$downloadFiles = [];
			$files = explode(",",I("post.zipfiles"));
			$fileNmaes = explode(",",I("post.name"));
			
			// 删除下载文件时间超过1小时的文件
			foreach(glob('./down/*.zip') as $filename)
			{
				$temp = iconv('GBK','UTF-8',substr($filename, 0, strrpos($filename, '-')));
				$temp = intval(substr($temp, strrpos($temp, '/') + 1));
				if(abs(time() - $temp) > $this->tool->time_hour) unlink($filename);
			}
			
			// 创建下载目录
			if(!is_dir($downloadFileSrc)) mkdir($downloadFileSrc,0777,true);
			
			// 打包文件
			$res = $zip->open($packDownloadFileName, \ZipArchive::CREATE);
			if ($res === TRUE) {
			 foreach ($files as $num=>$file) {
				 if(file_exists($file)){
					//这里直接用原文件的名字进行打包，也可以直接命名，需要注意如果文件名字一样会导致后面文件覆盖前面的文件，所以建议重新命名
					file_exists($file) ? $zip->addFile($file, iconv("UTF-8", "GBK", $fileNmaes[$num])) : exit('压缩到zip失败。失败文件：'.$fileNmaes[$num].'****'.$file);					 
				 }
			 }
			} else {
				exit('无法打开文件，或者文件创建失败');
			}
			$zip->close();
			
			// 文件缓存至浏览器下载
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename='.$downloadFileName);
			header('Content-Length: ' . filesize($packDownloadFileName));
			readfile($packDownloadFileName);
		} else {
			exit('下载失败');
		}
	}
}