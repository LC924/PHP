<?php 
function upload_1($save_path, $custom_upload_max_filesize, $key,$type=array('jpg','jpeg','gif','png')){
    $return_data=array();
    $phpini=ini_get('upload_max_filesize');
    $phpini_multiple=get_multiple($phpini);//php标准大小
    $phpini_bytes = $phpini_multiple*substr($phpini, 0,-1);
    //规定的大小
    $custom_bytes=get_multiple($custom_upload_max_filesize)*substr($custom_upload_max_filesize, 0,-1);
    if ($custom_bytes>$phpini_bytes){
        $return_data['error']='传入的$custom_upload_max_filesize大于PHP配置文件里面的'.$phpini;
        $return_data['return']=false;
        return $return_data;
    }
    if ($_FILES[$key]['size']>$custom_bytes){
        $return_data['error']='传入的文件大于管理员设置的最大值：'.$custom_upload_max_filesize;
        $return_data['return']=false;
        return $return_data;
    }
    
    $arr_filename=pathinfo($_FILES[$key]['name']);
    if(!isset($arr_filename['extension'])||!in_array($arr_filename['extension'], $type)){
        $return_data['error']='输入文件格式不支持，只能包括'.implode(',', $type);
        $return_data['return']=false;
        return $return_data;
    }
    $arr_errors=array(
        1=>'上传的文件超过了 php.ini中 upload_max_filesize 选项限制的值',
        2=>'上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值',
        3=>'文件只有部分被上传',
        4=>'没有文件被上传',
        6=>'找不到临时文件夹',
        7=>'文件写入失败'
    );
    if(!isset($_FILES[$key]['error'])){
        $return_data['error']='由于未知原因导致，上传失败，请重试！';
        $return_data['return']=false;
        return $return_data;
    }
    if(!is_uploaded_file($_FILES[$key]['tmp_name'])){
        $return_data['error']='您上传的文件不是通过 HTTP POST方式上传的！';
        $return_data['return']=false;
        return $return_data;
    }
    if($_FILES[$key]['size']>$custom_bytes){
        $return_data['error']='上传文件的大小超过了程序作者限定的'.$custom_upload_max_filesize;
        $return_data['return']=false;
        return $return_data;
    }
    if(!file_exists($save_path)){
        if(!mkdir($save_path,0777,true)){
            $return_data['error']='上传文件保存目录创建失败，请检查权限!';
            $return_data['return']=false;
            return $return_data;
        }
    }
 
    $new_filename = str_replace('.', '', uniqid(rand(100,200),true));
    $new_filename.=".{$arr_filename['extension']}";
    if(!move_uploaded_file($_FILES[$key]['tmp_name'],$save_path.$new_filename)){
        $return_data['error']='临时文件移动失败，请检查权限!';
        $return_data['return']=false;
        return $return_data;
    }
    
    $return_data['new_filename']=$save_path.$new_filename;
    $return_data['return']=true;
    return    $return_data;
}
function get_multiple($unit){
    switch (substr(strtoupper($unit), -1)){
        case 'K':
            $multiple=1024;
            return $multiple;
        case 'M':
            $multiple=1024*1024;
            return $multiple;
        case 'G':
            $multiple=1024*1024*1024;
            return $multiple;
        default:
            return false;
    }
}
if(isset($_POST['submit'])){
    $upload=upload_1("images/", "2m", "photo");
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title>上传页面</title>
<style type="text/css">
body {
	font-size:12px;
	font-family:微软雅黑;
}
h2 {
	padding:0 0 10px 0;
	border-bottom: 1px solid #e3e3e3;
	color:#444;
}
img{
	width:200px;
	height:200px;
}
.submit {
	background-color: #3b7dc3;
	color:#fff;
	padding:5px 22px;
	border-radius:2px;
	border:0px;
	cursor:pointer;
	font-size:14px;
}
#main {
	width:80%;
	margin:0 auto;
}
</style>
</head>
<body>
	<div id="main">
		<h2>更改头像</h2>
		<div>
			<h3>原头像：</h3>
			<img src="<?php if(isset($upload) && $upload['return']){echo $upload['new_filename'];} else echo 'style/photo.jpg';?>" />
			<br />
			最佳图片尺寸：180*180 &nbsp&nbsp&nbsp&nbsp<?php if(isset($upload) && !$upload['return']){echo "<span style='color:red;'>{$upload['error']}</span>";}?>
		</div>
		<div style="margin:15px 0 0 0;">
			<form method="post" enctype="multipart/form-data">
				<input style="cursor:pointer;" width="100" type="file" name="photo" /><br /><br />
				<input class="submit" type="submit" name="submit" value="保存" />
			</form>
		</div>
	</div>
</body>
</html>