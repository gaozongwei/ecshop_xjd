<?php

/**
 * 我的红包列表
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$bonus_sn=$_GET['d'];
	$uid = isset($_REQUEST['uid'])  ? intval($_REQUEST['uid']) : 0;
	$result=array();
	$sql="SELECT ub.used_time,ub.bonus_sn, ub.user_id
		FROM  ".$ecs->table('user_bonus')." AS ub WHERE ub.bonus_sn='$bonus_sn' ;";
	//print_r($sql);
	$row= $db -> getRow($sql);
	if($row){
		if($row['user_id']==0){
			$row = $db -> query("update ".$ecs->table('user_bonus')." set user_id = '$uid' where bonus_sn = '$bonus_sn'");
			$result['code']=1;
			$result['info']="红包添加成功！";
		}else{
			$result['code']=0;
			$result['info']="红包添加失败！该红包系列已经发放过！";
		}
		
	}else{
		$result['code']=0;
		$result['info']="红包添加失败！该红包系列不存在！";
	}
	
	print_r(json_encode($result));
?>