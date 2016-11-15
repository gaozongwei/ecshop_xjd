<?php

/**
 * 商品搜索
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$page=$_GET['page']*10;
	$key=$_POST['key'];
	$field=$_GET['field'];
	$sql="SELECT * FROM ".$ecs->table('goods')." WHERE is_delete = '0' AND is_on_sale = '1' AND goods_name LIKE '%$key%' OR is_delete = '0' AND is_on_sale = '1' AND goods_sn LIKE '%$key%' OR is_delete = '0' AND is_on_sale = '1' AND keywords LIKE '%$key%' ORDER BY $field DESC LIMIT $page, 10";
	//print_r($sql);
	$res = $db -> getAll($sql);
	print_r(json_encode($res));
	
?>