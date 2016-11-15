<?php

/**
 * 店铺搜索
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$key=$_POST['key'];
	$sql="SELECT * FROM ".$ecs->table('supplier_street')." WHERE (status='1' and is_show=1 AND supplier_name LIKE '%$key%') OR (status='1' AND  supplier_title LIKE '%$key%')";
	
	$sql = "SELECT * FROM `xjdapp`.`ecs_supplier_street` where status=1 and is_show=1 and supplier_id in(SELECT DISTINCT supplier_id FROM ". $ecs->table('supplier_shop_config') . " AS ssc WHERE ( code = 'shop_name' AND value LIKE '%$key%' ) OR ( code = 'shop_keywords' AND value LIKE '%$key%' ))";
	
	
	//file_put_contents('./asd.txt',$sql,true);
	$res = $db -> getAll($sql);
	//print_r($res);
	print_r(json_encode($res));
	
?>