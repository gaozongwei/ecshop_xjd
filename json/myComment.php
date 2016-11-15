<?php

/**
 * 我的收藏
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$page=$_GET['page']*5;
	$uid = isset($_REQUEST['uid'])  ? intval($_REQUEST['uid']) : 0;
	$sql="SELECT g.add_time,g.goods_id,g.goods_name,g.shop_price,g.goods_thumb,g.is_hot,g.is_new,g.is_best,g.click_count,c.content,c.user_name,c.status FROM  ".$ecs->table('goods')."   AS g ,".$ecs->table('comment')."   AS c WHERE c.user_id='$uid' and g.goods_id= c.id_value order by c.add_time desc   LIMIT $page,5";

	//print_r($sql);
	$res = $db -> getAll($sql);
	print_r(json_encode($res));
	
?>