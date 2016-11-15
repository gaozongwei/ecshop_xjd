<?php

/**
 * 我的收藏
*/
	require('includes/safety_mysql.php');
	define('IN_ECS', true);
	require('includes/init.php');
	$id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
	$uid = isset($_REQUEST['uid'])  ? intval($_REQUEST['uid']) : 0;
	$add_time=time();
	$result=array();
	$sql="SELECT * 
	FROM  ".$ecs->table('collect_goods')." WHERE user_id='$uid' and goods_id='$id'";
	
	$isCollect=$db ->getRow($sql);
	
	if(!empty($isCollect)){
		$sql="DELETE FROM ".$ecs->table('collect_goods')." WHERE user_id='$uid'  AND goods_id='$id' ";
		$res=$db -> query($sql);
		if($res)
		{
			$result['code']=1;
			$result['info']="取消收藏成功！";
		}else
		{
			$result['code']=1;
			$result['info']="取消收藏失败！";
		}
		print_r(json_encode($result));
		exit();
	}
	
	$sql="INSERT INTO  ".$ecs->table('collect_goods')." (
	`rec_id` ,
	`user_id` ,
	`goods_id` ,
	`add_time` ,
	`is_attention`
	)
	VALUES (
	NULL ,  '$uid',  '$id',  '$add_time',  '0'
	);";
	$res = $db -> query($sql);
	if($res){
		$result['code']=1;
		$result['info']="收藏成功！";
		print_r(json_encode($result));
	}else{
		$result['code']=0;
		$result['info']="收藏失败！";
		print_r(json_encode($result));
	}
	
?>