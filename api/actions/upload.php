<?php
require_once './include/info.function.php';
require_once './include/work.function.php';
// if(!isset($_SESSION['admin'])){
//     Result::error('no permission~');
// }
/**
 * 返回-1为过大
 *   2为出错
 *   3为拓展名违规
 */

$file_path="upload/";
//664权限为文件属主和属组用户可读和写，其他用户只读。
if(is_dir($file_path)!=TRUE){
    mkdir($file_path,0755);
}

if(isset($_POST['data']))
{
    // var_dump($_POST['data']);
}



if(isset($_POST['work_id']))
{
    // var_dump($_POST['work_id']);
}
$user = get_person_info($GLOBALS['uid']);
$file_path= $file_path . $_POST['work_id'] . '/';
if(is_dir($file_path)!=TRUE){
    mkdir($file_path,0755);
}

if (empty($_FILES) === false) {
    //判断检查
    if($_FILES["file"]["size"] > 4096*4096){//2M
        $result['status'] = -1;
        Result::success($result);
    }
    if($_FILES["file"]["error"] > 0){
        // $result['msg'] = "文件上传发生错误：".$_FILES["file"]["error"];
        $result['status'] = 2;
        Result::success($result);
    }
    //定义允许上传的文件扩展名
    $ext_arr = get_allow_ext($_POST['work_id']);

    $temp_arr = explode(".", $_FILES["file"]["name"]);

    $file_ext = array_pop($temp_arr);
    $file_ext = trim($file_ext);
    $file_ext = strtolower($file_ext);
    $file_ext = '.'.$file_ext;
    if (in_array($file_ext, $ext_arr) === false) {
        $result['status'] = 3;
        Result::success($result);
    }


// $finfo = finfo_open(FILEINFO_MIME_TYPE);  
// $extension = finfo_file($finfo, $_FILES["file"]["tmp_name"]) ;  
// echo $extension;  
// $extension =  explode("/",$extension);
// $extension = $extension[0];
// $extension=substr(strrchr($_FILES["file"]["tmp_name"], '.'), 1);  
// finfo_close($finfo); 
    $info = pathinfo($_FILES["file"]["name"]);  
    $extension=$info['extension']; 
    // echo $extension;
// var_dump($_FILES);

    // $imageSalt = 'imageIsThere';
    // $imageName = md5($imageSalt . time() . mt_rand(0, 1e10));
    // $new_image_url = $imageName . ".jpg";
    $workid = $_POST['work_id'];
    $res = $db->get("work","download_format",[
        "id"=>$workid
    ]);
    $res = str_replace("{name}",$user['stu_name'],$res);
    $res = str_replace("{num}",$user['stu_num'],$res);
    $new_image_url = $res . '.'. $extension;

    $name = iconv('utf-8','gb2312',$file_path . $new_image_url);
    $name = $file_path . $new_image_url;
    move_uploaded_file($_FILES["file"]["tmp_name"],$name);

    $db->update('work_upload',[
            'file_name' => $new_image_url,
            'add_time' => time(),
            'has_upload' => 1
    ],[
        'upload_by_user' => $GLOBALS['uid'],
        'work_id' => $_POST['work_id'],
    ]);
    $result['image_url'] = $new_image_url;
    $result['status'] = 200;
    Result::success($result);
} else {
    $result['status'] = 404;
    Result::success($result);
}
