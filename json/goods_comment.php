<?php

/**
 * 商品内容
*/
	define('IN_ECS', true);
	require('includes/init.php');
	//require('../includes/lib_goods.php');
	$goods_id = isset($_REQUEST['goods_id'])  ? intval($_REQUEST['goods_id']) : 0;
	
	/*获取商品的评论*/
	$sql="SELECT user_name,content,add_time,comment_rank FROM ".$ecs->table('comment')." WHERE id_value='$goods_id' and status=1 ORDER BY add_time DESC";
	$comment = $db -> getAll($sql);
	for($i=0;$i<count($comment);$i++){
		$comment[$i]['add_time'] = date("Y-m-d h:m:s",$comment[$i]['add_time']);
	}
	
	print_r(json_encode($comment));
	
?>