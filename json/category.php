<?php

/**
 * 商品分类
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$result=array();
	$result2=array();
	$res = $db -> getAll("SELECT cat_id,cat_name FROM  ".$ecs->table('category')." WHERE  parent_id='0'  and  is_show='1'  order by sort_order asc");
	foreach ($res as $key=>$val)
    {
		$parent_id=$val['cat_id'];
		$result2['cat_id']=$val['cat_id'];
		$result2['cat_name']=$val['cat_name'];
		$rows = $db -> getAll("SELECT cat_id,cat_name FROM  ".$ecs->table('category')." WHERE  parent_id='$parent_id' and is_show='1'   order by sort_order asc");
		for($i=0;$i<count($rows);$i++){
			$children_id=$rows[$i]['cat_id'];
			$rows2 = $db -> getAll("SELECT cat_id,cat_name FROM  ".$ecs->table('category')." WHERE  parent_id='$children_id' and is_show='1'   order by sort_order asc");
			$rows[$i]['children']=$rows2;
		}
		$result2['list']=$rows;
		$result[]=$result2;
	}

	print_r(json_encode($result));
	
?>