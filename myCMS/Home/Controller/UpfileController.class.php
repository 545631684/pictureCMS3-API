<?php
namespace Home\Controller;
use Home\Controller\ControllerController;
use Org\Util\Imgcompress;
class UpfileController extends ControllerController {
    
	public function __construct() 
	{
		parent::__construct();
	}
	
	
	
	
	/**
	 * 处理文件上传
	 */
	public function upfile()
	{
		$con=array(
			"maxSize"	=>0,//文件大小
			"exts"		=>array('jpg','gif','png','jpeg','psd','psb','ai','mp4','3gp','avi','rmvb','flv','wmv','mpeg','mov','mkv','flv','f4v','m4v','rm','dat','ts','mts','rar','zip','pdf'),//文件类型
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
			$id=I('get.id');
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
			$this->ajaxReturn(['code'=>$this->tool->success,'data'=>$res,'msg'=>'上传成功','status'=>$arr['file'],],'JSON');
		}else{
			$this->ajaxReturn(['code'=>$this->tool->fail,'data'=>$res,'msg'=>'上传失败','status'=>true,],'JSON');
		}
	}
}