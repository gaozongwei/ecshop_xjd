<?php

/**
 * 获取登录信息
*/
	define('IN_ECS', true);
	require('../includes/init.php');
	include_once(ROOT_PATH . 'includes/lib_passport.php');

	include_once(ROOT_PATH . 'includes/modules/integrates/' . $_CFG['integrate_code'] . '.php');
    $cfg = unserialize($_CFG['integrate_config']);
    $cls = new $_CFG['integrate_code']($cfg);


	$user=$_GET['uid'];
	$oldPwd=$_GET['oldPwd'];
	$pwd=$_GET['pwd'];

	$result=array();

	$row = $db -> getRow("SELECT * FROM ".$ecs->table('users')."  WHERE  `user_id`=".$user);
	if(empty($row)){
		$result['code']=0;
		$result['info']="异常操作！";
		print_r(json_encode($result));
		exit();
	}

	

	if($cls->check_user($row['user_name'], $oldPwd)){
		if ($cls->edit_user(array('username'=>$row['user_name'], 'old_password'=>$oldPwd, 'password'=>$pwd), 0))
        {
			
			$sql="UPDATE ".$ecs->table('users'). "SET `ec_salt`='0' WHERE user_id= '".$user."'";
			$db->query($sql);
			$result['code']=1;
			$result['info']="你的密码修改成功！新密码：".$_GET['pwd'];
			print_r(json_encode($result));
			exit();

        }
        else
        {
            $result['code']=0;
			$result['info']="你修改密码失败！";
			print_r(json_encode($result));
			exit();
        }
		
	}else{
		$result['code']=0;
		$result['info']="你输入的旧密码不正确！";
		print_r(json_encode($result));
		exit();
	}

/*
	$user=$_GET['uid'];
	$oldPwd=md5($_GET['oldPwd']);
	$pwd=md5($_GET['pwd']);
	$result=array();
	$row = $db -> getRow("SELECT * FROM ".$ecs->table('users')."  WHERE  `user_id`='$user'");
	if(empty($row)){
		$result['code']=0;
		$result['info']="你输入的旧密码不正确！";
		print_r(json_encode($result));
		exit();
	}
	$row = $db -> query("update ".$ecs->table('users')." set password = '$pwd' where user_id = '$user'");
	if($row){
		$result['code']=1;
		$result['info']="你的密码修改成功！新密码：".$_GET['pwd'];
		print_r(json_encode($result));
		exit();
	}else{
		$result['code']=0;
		$result['info']="你修改密码失败！";
		print_r(json_encode($result));
		exit();
	}
	
	*/
?>