<?php

/**
 * 文章列表
*/
	define('IN_ECS', true);
	require('includes/init.php');

	$cat_id = isset($_REQUEST['cat_id'])  ? intval($_REQUEST['cat_id']) : 0;
	$page=$_GET['page']*10;
	
	$cat_id_list=$cat_id;
	
	if(!empty($cat_id)){
		$sql = "SELECT * FROM " .$ecs->table('article_cat'). " WHERE parent_id='$cat_id' ";
		$cat_List=$db->getAll($sql);
		for($i=0;$i<count($cat_List);$i++){
			$cat_id_list.=",".$cat_List[$i]['cat_id'];
			$cat_id1=$cat_List[$i]['cat_id'];
			$sql = "SELECT * FROM " .$ecs->table('article_cat'). " WHERE parent_id ='$cat_id1' ";
			$cat_List2=$db->getAll($sql);
			for($j=0;$j<count($cat_List2);$j++){
				$cat_id_list.=",".$cat_List2[$j]['cat_id'];
			}
		}
	}
	
	$res = $db -> getAll("SELECT article_id,title FROM  ".$ecs->table('article')." WHERE  cat_id in($cat_id_list) AND is_open=1 order by article_id asc LIMIT $page,10;");
	
	
	print_r(json_encode($res));
	
?>

