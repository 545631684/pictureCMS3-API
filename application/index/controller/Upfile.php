<?php
namespace app\index\controller;
use Tool\Controller_controller;
use think\Db;
use think\facade\Config;
use Org\Util\Imgcompress;

class Upfile extends Controller_controller {
    
	public function __construct() 
	{
		parent::__construct();
	}
	
	
	/**
	 * 处理文件上传
	 */
	public function upfile()
	{
		// 获取参数
		$post=$this->request->param();
		// 获取表单上传文件
		$file = $this->request->file('file');
		// 配置上传文件路径和文件加名称，对上传的同名文件不进行覆盖,上传验证文件大小在2GB以内，可上传文件后缀
		$info = $file->validate(['size'=>2147483648,'ext'=>'jpg,gif,png,jpeg,psd,psb,ai,pdf,7z,rar,zip,mp4,mp3,3gp,avi,rmvb,flv,wmv,mpeg,mov,mkv,flv,f4v,m4v,rm,dat,ts,mts,txt,docx,doc,xlsx,xls'])->move('./file/img/',true,false);
		//上传成功
		if($info) {
			// 获取文件的重命名
			$saveName = explode('\\',$info->getSaveName())[1];
			if($post['id'] == 1){ // img文件
				$res['dataImg']='file/img/'.date("Y-m-d").'/'.$saveName;
				// 创建文件夹
				$dir="file/miniImg/".date("Y-m-d");
				$houzhui=strrchr($file->getInfo('name'),'.');
				if (!file_exists($dir)) mkdir ($dir,0777,true);
				// 判断文件后缀
				if($houzhui!=".gif"){
					$res['miniImg']=$dir."/".'mini_'.$saveName;
					$image = \think\Image::open($res['dataImg']);
					// dump(filesize($res['dataImg']));
					$res['fileInfo']=[
						'name' 		=> $file->getInfo('name'),
						'width' 	=> $image->width(),
						'height' 	=> $image->height(),
						'type'	 	=> $image->type(),
						'mime'	 	=> $image->mime(),
						'size'	 	=> $this->tool->getFileSize(filesize($res['dataImg']) ? (int)filesize($res['dataImg']) : 0),
					];
					// 生成缩率图
					$image->thumb($this->tool->setImgSize($image->width(), $image->height())['width'],$this->tool->setImgSize($image->width(), $image->height())['height'],\Think\Image::THUMB_SCALING)->save($res['miniImg']);
				}else{
					$res['miniImg']="file/img/".date("Y-m-d")."/".$arr['file']['savename'];
				}
				$res['type']="1";
			} else if($post['id'] == 2){ // 头像
				$res['dataImg']='file/img/'.date("Y-m-d").'/'.$saveName;
				$res['type']="2";
			} else if($post['id'] == 3){ // psd缩率图
				$res['dataPsdImg']='file/img/'.date("Y-m-d").'/'.$saveName;
				$res['type']='3';
				$res['msg']='0';
			} else if($post['id'] == 4){ // psd文件
				// 保存psd文件路径
				$res['dataPsd']="file/img/".date("Y-m-d")."/".$saveName;
				// 文件信息
				$res['fileInfo']=[
					'name' 		=> $file->getInfo('name'),
					'type'	 	=> substr(strrchr($saveName, '.'), 1),
					'mime'	 	=> mime_content_type($res['dataPsd']),
					'size'	 	=> filesize($res['dataPsd']),
				];
				// 格式化创建文件夹内容编码
				$dir = iconv("UTF-8", "GBK", "file/psdview/".date("Y-m-d"));
				// 创建文件夹权限设置
				if (!is_dir($dir)) mkdir ($dir,0777,true);
				// 设置psd缩率图文件地址
				$pathimg="file/psdview/".date("Y-m-d")."/".$saveName.".jpg";
				// 调用插件生成psd缩率图
				$im = new \Imagick();
				$im->readImage($res['dataPsd'].'[0]');
				$im->writeImages($pathimg, false);
				
				// $im = new \Imagick($res['dataPsd'].'[0]');
				// $im->setImageIndex(0);
				// $im->setIteratorIndex(0);
				// $im->stripImage(); //去除图片信息
				// $im->setImageCompressionQuality(80); //图片质量
				// $im->writeImages($pathimg, false);
				// 判断是否生成并压缩缩率图
				if($im){
					$res['Psdview']=$pathimg;
					// psd缩略图压缩
					$image = \think\Image::open($pathimg);
					$image->thumb($this->tool->setImgSize($image->width(), $image->height())['width'],$this->tool->setImgSize($image->width(), $image->height())['height'],\Think\Image::THUMB_SCALING)->save($pathimg);
				}
				$res['type']='4';
			} else if($post['id'] == 5){ // 视频缩率图
				$res['dataVideoImg']="file/img/".date("Y-m-d")."/".$saveName;
				$res['type']='5';
				$res['msg']='0';
			} else if($post['id'] == 6){ // 视频文件
				// 保存video文件路径
				$res['dataVideo']="file/img/".date("Y-m-d")."/".$saveName;
				// 文件信息
				$res['fileInfo']=[
					'name' 		=> $file->getInfo('name'),
					'type'	 	=> substr(strrchr($saveName, '.'), 1),
					'mime'	 	=> $this->tool->mime_content_types($res['dataVideo']),
					'length'	=> $this->tool->video_info(config('FILE_URL').$res['dataVideo'])['duration'],
					'size'	 	=> filesize($res['dataVideo']),
				];
				// 设置文件夹路径
				$folder="file/videoview/".date("Y-m-d")."/";
				// 格式化创建文件夹内容编码
				$dir = iconv("UTF-8", "GBK", $folder);
				// 创建文件夹权限设置
				if (!file_exists($dir)) mkdir ($dir,0777,true);
				// 使用ffmpeg.exe生成缩率图代码设置
				$str = "ffmpeg -i " . $res['dataVideo'] . " -y -f mjpeg -ss 3 -t 1 -s 740x500 " . $folder . $saveName.".png";
				// 执行
				exec($str, $output);
				// 生成成功赋值
				if(file_exists($folder.$saveName.".png")) $res['Videoview']=$folder.$saveName.".png";
				$res['type']='6';
				$res['msg']='0';
			} else if($post['id'] == 7){ // ai文件
				$size = 210;
				$res['dataAi']="file/img/".date("Y-m-d")."/".$saveName;
				// 文件信息
				$res['fileInfo']=[
					'name' 		=> $file->getInfo('name'),
					'type'	 	=> substr(strrchr($saveName, '.'), 1),
					'mime'	 	=> mime_content_type($res['dataAi']),
					'size'	 	=> filesize($res['dataAi']),
				];
				// 格式化创建文件夹内容编码
				$dir = iconv("UTF-8", "GBK", "file/aiview/".date("Y-m-d"));
				// 创建文件夹权限设置
				if (!file_exists($dir)) mkdir ($dir,0777,true);
				// 设置ai缩率图文件地址
				$pathimg="file/aiview/".date("Y-m-d")."/".$saveName.".jpg";
				// 调用插件生成psd缩率图
				// $imageMagick = "convert" . " '". $res['dataAi'] . "' -resize '$size' '" . $pathimg . "'";
				// 执行
				// exec($imageMagick, $output);
				// $im = new \Imagick($res['dataAi']);
				// $im->readImage($res['dataAi']);
				// $im->writeImage($pathimg, false);
				// dump($imageMagick);
				// 判断是否生成并压缩缩率图
				// if($im){
					// $res['aiview']=$pathimg;
					// psd缩略图压缩
					// $image = \think\Image::open($pathimg);
					// $image->thumb($this->tool->setImgSize($image->width(), $image->height())['width'],$this->tool->setImgSize($image->width(), $image->height())['height'],\Think\Image::THUMB_SCALING)->save($pathimg);
				// }
				$res['type']='7';
			} else if($post['id'] == 8){ // 压缩包文件
				$res['file']="file/img/".date("Y-m-d")."/".$saveName;
				$res['type']='8';
				// 文件信息
				$res['fileInfo']=[
					'name' 		=> $file->getInfo('name'),
					'type'	 	=> substr(strrchr($saveName, '.'), 1),
					'mime'	 	=> mime_content_type($res['file']),
					'size'	 	=> filesize($res['file']),
				];
			}
			return json(['code'=>$this->tool->success,'data'=>$res,'msg'=>'上传成功','status'=>true,]);
		}else{
			return json(['code'=>$this->tool->fail,'data'=>$file,'msg'=>'文件超过2GB或文件格式错误','status'=>true,]);
		}
	}
	
	/**
	 * 处理文件上传
	 */
	public function upfileaaa()
	{
		$con=array(
			"maxSize"	=>0,//文件大小
			"exts"		=>array('jpg','gif','png','jpeg','psd','psb','ai','mp4','3gp','avi','rmvb','flv','wmv','mpeg','mov','mkv','flv','f4v','m4v','rm','dat','ts','mts','rar','zip','pdf','doc','docx','xlsx','xls'),//文件类型
			"autoSub"	=>false,
			"rootPath"	=>"./",//根目录   等于项目名下
			"savePath"	=>"file/img/".date("Y-m-d")."/",//路径，相对于项目名下
		);
		$upobj = new \Think\Upload($con);
		$arr = $upobj->upload();
		// 去除特殊符号导致的保存失败
		$arr['file']['savename'] = $this->tool->filterFileName($arr['file']['savename']);
		$arr['file']['name'] = $this->tool->filterFileName($arr['file']['name']);
		// 解决部分手机拍摄的照片过度旋转导致图片显示不正常的修复
		if($arr && strrchr($arr['file']['savename'],'.') == ".jpg"){
			$image = imagecreatefromstring(file_get_contents($arr['file']['savepath'].$arr['file']['savename']));
			$exif = exif_read_data($arr['file']['savepath'].$arr['file']['savename']);

			if(!empty($exif['Orientation'])) {
				switch($exif['Orientation']) {
					 case 8:
					  $image = imagerotate($image,90,0);
					  break;
					 case 3:
					  $image = imagerotate($image,180,0);
					  break;
					 case 6:
					  $image = imagerotate($image,-90,0);
					  break;
				}
			}
			imagejpeg($image,$arr['file']['savepath'].$arr['file']['savename'],100);
		}
		
		if ($arr) {	//上传成功
			$yuanwenj=$arr['file']['name'];
			$id=input('get.id');
			$image = new \Think\Image();
			if($id=="1"){//图片
				$res['dataImg']="file/img/".date("Y-m-d")."/".$arr['file']['savename'];
				$res['type']="1";
				$dir="file/miniImg/".date("Y-m-d");
				$houzhui=strrchr($arr['file']['savename'],'.');
				if (!file_exists($dir)) mkdir ($dir,0777,true);
				if($houzhui!=".gif"){
					$res['miniImg']=$dir."/".'mini_'.$arr['file']['savename'];
					$image->open($res['dataImg']);
					$res['size']=$this->tool->setImgSize($image->width(), $image->height());
					$res['size']['size']=$this->tool->getFileSize($arr['file']['size']);
					$image->thumb($res['size']['width'],$res['size']['height'],\Think\Image::IMAGE_THUMB_FILLED)->update($res['miniImg']);
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
						$image->thumb($res['size']['width'],$res['size']['height'],\Think\Image::IMAGE_THUMB_FILLED)->update($pathimg);
					}
				}
				$res['type']='4';
			} else if($id=="5"){//视频缩略图
				$res['dataVideoImg']="file/img/".date("Y-m-d")."/".$arr['file']['savename'];
				$res['type']='5';
				$res['msg']='0';
			} else if($id=="6"){//视频
				/*$houzhui=strrchr($arr['file']['savename'],'.');
				$fileSrc = "file/img/".date("Y-m-d")."/".$arr['file']['savename'];
				$folder="file/videoview/".date("Y-m-d")."/";
				$filename=$arr['file']['savename'];
				if($houzhui != '.mp4'){
					$str = "ffmpeg -i " . $fileSrc . "  -c:v libx264 -strict -2 " . $folder . stristr($filename,'.',true) . ".mp4";
					exec($str, $output);
					if(exec){
						$res['dataVideo']=$folder . stristr($filename,'.',true) . ".mp4";
						if($res['dataVideo']!=""){
							$str = "ffmpeg -i " . $res['dataVideo'] . " -y -f mjpeg -ss 3 -t 1 -s 740x500 " . $folder . $pathimg;
							exec($str, $output);
							if(exec){
								$res['Videoview']=$folder.$pathimg;
							}
						}
					}
				} else {
					$res['dataVideo']="file/img/".date("Y-m-d")."/".$arr['file']['savename'];
					$filevideo=$res['dataVideo'];
					if($filevideo!=""){
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
				}*/
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
			} else if($id=="7"){
				$res['dataAi']="file/img/".date("Y-m-d")."/".$arr['file']['savename'];
				$filepsd=$res['dataAi'];
				$filename=$arr['file']['savename'];
				if($filepsd!=""){
					$folder="file/aiview/".date("Y-m-d");
					$dir = iconv("UTF-8", "GBK", $folder);
					if (!file_exists($dir)){
						mkdir ($dir,0777,true);
					}
				}
				$res['type']='7';
			} else if($id=="8"){
				$res['file']="file/img/".date("Y-m-d")."/".$arr['file']['savename'];
				$filepsd=$res['file'];
				$filename=$arr['file']['savename'];
				if($filepsd!=""){
					$folder="file/aiview/".date("Y-m-d");
					$dir = iconv("UTF-8", "GBK", $folder);
					if (!file_exists($dir)){
						mkdir ($dir,0777,true);
					}
				}
				$res['type']='8';
			}
			return json(['code'=>$this->tool->success,'data'=>$res,'msg'=>'上传成功','status'=>$arr['file'],]);
		}else{
			return json(['code'=>$this->tool->fail,'data'=>$res,'msg'=>'上传失败','status'=>true,]);
		}
	}
}