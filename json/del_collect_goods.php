<?php

/**
 * 删除收藏商品列表
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$uid = isset($_REQUEST['uid'])  ? intval($_REQUEST['uid']) : 0;
	$goods_id = isset($_REQUEST['goods_id'])  ? intval($_REQUEST['goods_id']) : 0;
	$sql="DELETE FROM ".$ecs->table('collect_goods')." WHERE user_id='$uid'  AND goods_id='$goods_id' ";
	$res=$db -> query($sql);
	$result=array();
	if($res){
		$result['code']="1";
		$result['info']="删除成功！";
	}else{
		$result['code']="0";
		$result['info']="删除失败！";
	}
	
	print_r(json_encode($result));
	
	
?>