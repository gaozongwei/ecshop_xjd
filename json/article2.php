<?php

/**
 * 文章内容
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$article_id = 7;
	$res = $db -> getRow("SELECT `title`,`content`,`add_time`,`add_time` FROM ".$ecs->table('article')." WHERE `article_id`='$article_id'");//,`click_count`
//	$click_count=$res['click_count']+1;
//	$db -> query("update ".$ecs->table('article')." set click_count = '$click_count' where article_id = '$article_id'");
	$res = $db -> getRow("SELECT `title`,`content`,`add_time`,`add_time` FROM ".$ecs->table('article')." WHERE `article_id`='$article_id'");//,`click_count`
	$res['add_time']=date('Y-m-d',$res['add_time']);
	print_r(json_encode($res));
	
?>