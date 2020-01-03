<?php
if($_POST){
	ini_set('display_errors', 'On');
	ini_set('memory_limit', '64M');
	error_reporting(E_ALL);
	$t1 = $ntime = microtime(true);
	header('Content-Type: text/html; charset=utf-8');
	require_once 'phpanalysis.php';
	$str = (isset($_POST['source']) ? $_POST['source'] : '');
	$loadtime = $endtime1  = $endtime2 = $slen = 0;
	$do_fork = $do_unit = true;
	$do_multi = $do_prop = $pri_dict = false;
	$dbuser=$_POST['dbuser'];	//	root
	$dbpass=$_POST['dbpass'];	//	2577b4213d
	$conn=mysqli_connect("localhost",$dbuser,$dbpass);
	if(mysqli_connect_errno($conn)){
		echo '<span style="color:red; font-size:24px;"><b>oh 连接测试失败：</b></span>'.mysqli_connect_error();
	}else{
		echo '<span style="color:blue;font-size:24px;"><b>ok 连接测试成功</b></span>'.'<br /><br />';
		$sdata = "show databases;";
		$sqldata = mysqli_query($conn,$sdata);
	}
	$lib=isset($_POST['lib']) ? $_POST['lib'] : '';
	mysqli_select_db($conn,$lib);
	$teststr = "";
	$title=isset($_POST['title']) ? $_POST['title'] : '';
	$mId=isset($_POST['mId']) ? $_POST['mId'] : '';
	$tdb=isset($_POST['tdb']) ? $_POST['tdb'] : '';
	$keyword=isset($_POST['keyword']) ? $_POST['keyword'] : '';
	if($title && $mId && $tdb && $keyword && $lib){
		$query="select ".$title.",".$mId." from ".$tdb."";
		$result = mysqli_query($conn,$query);
	}
	mysqli_close($conn);
}
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>分词&&DB修改</title>
</head>
<body>
	<table align='center' style="font-size:24px;">
		<tr>
			<td>
				<b style="color:red;">主机名或IP地址默认为localhost ---
				<b style="color:red;font-size:18px;">连接测试报错，检查用户名密码</b></b><br />
				<form action="demo.php" method="post">
					<input type="text" name="dbuser" value="用户名" onblur="if(this.value==''){this.value='用户名'}" onfocus="if(this.value=='用户名'){this.value=''}" />
					<input type="password" name="dbpass" value="" />
					<input type="submit" name="but" value="连接测试">
				</form>
			</td>
		</tr>
		<tr>
			<td>
				<b>分词 --- 选择要分词的字段，生成update语句<b style="color:red;font-size:16px;"> --- 使用前先进行数据库备份</b></b><br />
			</td>
		</tr>
		<tr>
			<td>
				<b>Choice：</b>
				<form action="demo.php" method="post">
					<select name='lib'>
						<option name='lib' value=''>选择数据库</option>
						<?php
							if($_POST){
								foreach($sqldata as $key=>$vo){
									echo "<option name='lib' value='$vo[Database]'>$vo[Database]</option>";
								}
							}
						?>
					</select> <br />
					<input type="hidden" name="dbuser" value="<?php echo $dbuser; ?>" />
					<input type="hidden" name="dbpass" value="<?php echo $dbpass; ?>" />
					<input type="text" name="tdb" value="表名" onblur="if(this.value==''){this.value='表名'}" onfocus="if(this.value=='表名'){this.value=''}" />
					<input type="text" name="keyword" value="分词后保存的字段名" onblur="if(this.value==''){this.value='分词后保存的字段名'}" onfocus="if(this.value=='分词后保存的字段名'){this.value=''}" />
					<input type="text" name="title" value="需要分词的字段名" onblur="if(this.value==''){this.value='需要分词的字段名'}" onfocus="if(this.value=='需要分词的字段名'){this.value=''}" />
					<input type="text" name="mId" value="条件为表id" onblur="if(this.value==''){this.value='条件为表id'}" onfocus="if(this.value=='条件为表id'){this.value=''}" />&nbsp;<input type="submit" name="but" value="生成"><b style="color:red;font-size:16px;"> --- 若生成失败，检查Choice信息是否正确</b><br />
					<textarea name="source" style="width:1700px;height:740px;font-size:18px;"><?php
						if($_POST){
							$lib=isset($_POST['lib']) ? $_POST['lib'] : '';
							$title=isset($_POST['title']) ? $_POST['title'] : '';
							$mId=isset($_POST['mId']) ? $_POST['mId'] : '';
							$tdb=isset($_POST['tdb']) ? $_POST['tdb'] : '';
							$keyword=isset($_POST['keyword']) ? $_POST['keyword'] : '';
							if($title && $mId && $tdb && $keyword && $lib){
								while($row = mysqli_fetch_array($result))
								{
									$tall = microtime(true);
									//初始化类
									PhpAnalysis::$loadInit = false;
									$pa = new PhpAnalysis('utf-8', 'utf-8', $pri_dict);
									$pas = new PhpAnalysis('utf-8', 'utf-8', $pri_dict);
									//载入词典
									$pa->LoadDict();
									$pas->LoadDict();
									//执行分词
									$pa->SetSource($teststr.$row[$title]);
									$pas->SetSource($teststr.$row[$mId]);
									$pa->unitWord = $do_unit;
									$pas->unitWord = $do_unit;
									$pa->StartAnalysis($do_fork);
									$pas->StartAnalysis($do_fork);
									$okresult = $pa->GetFinallyResult(',',$do_prop);
									$okresults = $pas->GetFinallyResult(',',$do_prop);
									$pa_foundWordStr = $pa->foundWordStr;
									$pa = '';
									$pas = '';
									// $teststr = $teststr.$row['mId'].':'.$row['title']."\n";
									$teststr = "update ".$tdb." set ".$keyword." = '".ltrim($okresult, ",")."' where ".$mId." = '".ltrim($okresults, ",")."'; \n";
									echo $teststr;
									$teststr = '';
								}
							}
						}
					?></textarea>
				</form>
			</td>
		</tr>
	</table>
</body>
</html>