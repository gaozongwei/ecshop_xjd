<?php

/**
 * 文章分类
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$result=array();
	$result2=array();
	$res = $db -> getAll("SELECT cat_id,cat_name FROM  ".$ecs->table('article_cat')." WHERE  parent_id='0' and cat_type='1' and  show_in_nav='0'  order by sort_order asc");
	foreach ($res as $key=>$val)
    {
		$parent_id=$val['cat_id'];
		$result2['cat_id']=$val['cat_id'];
		$result2['cat_name']=$val['cat_name'];
		$rows = $db -> getAll("SELECT cat_id,cat_name FROM  ".$ecs->table('article_cat')." WHERE  parent_id='$parent_id' and cat_type='1'  order by sort_order asc");
		$result2['list']=$rows;
		$result[]=$result2;
	}

	print_r(json_encode($result));
	
?>