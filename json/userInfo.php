<?php

/**
 * 获取登录信息
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$user=$_GET['uid'];
	$act=$_GET['act'];
	$result=array();
	if($act=="get"){
	$row = $db -> getRow("SELECT * FROM ".$ecs->table('users')."  WHERE  `user_id`='$user'");
	$result['code']="get";
	$result['info']=$row;
	print_r(json_encode($result));
	exit();
	}
	if($act=="update"){
	$user_name=$_POST['user_name'];
	$mobile_phone=$_GET['mobile_phone'];
	$email=$_GET['email'];
	$sex=$_GET['sex'];
	$row = $db -> query("update ".$ecs->table('users')." set 
							
							mobile_phone = '$mobile_phone',
							email = '$email',
							sex = '$sex'
						where user_id = '$user'");
	$result['code']="update";
	if($row){	
		$result['info']="1";
	}else{
		$result['info']="0";

	}
	print_r(json_encode($result));
	exit();
	}
	
	
	
	
?>