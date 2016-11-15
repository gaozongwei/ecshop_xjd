<?php

/**
 * 城市列表
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$parent_id = isset($_REQUEST['parent_id'])  ? intval($_REQUEST['parent_id']) : 0;
	$region_type=$_GET['region_type'];
	$res = $db -> getAll("SELECT region_id,region_name FROM  ".$ecs->table('region')." WHERE  region_type='$region_type' and parent_id='$parent_id'  order by region_id asc");
	
	print_r(json_encode($res));
	
?>