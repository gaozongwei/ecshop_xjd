<?php

/**
 * 获取会员等级信息信息
*/
	require('includes/safety_mysql.php');
	define('IN_ECS', true);
	require('includes/init.php');
	$user=$_POST['user_rank'];
	$row = $db -> getRow("SELECT * FROM ".$ecs->table('users')." WHERE `user_id`='$user'");
	$result=array();
	
	$result['code']=1;

	/*获取该会员等级信息*/
	$rank_points = $row['rank_points'];
	$sql = "SELECT rank_name,rank_id, special_rank FROM " . $ecs->table('user_rank') . " WHERE max_points > '$rank_points' ORDER BY max_points ASC LIMIT 1";
	$rank = $db->getRow($sql);
	if(!empty($rank))
	{
		$rank_name = $rank['rank_name'];
		$rank_id = $rank['rank_id'];
		
		$sql = "SELECT rank_name,min_points FROM " . $ecs->table('user_rank') . " WHERE min_points > '$rank_points' ORDER BY min_points ASC LIMIT 1";
		$rt  = $db->getRow($sql);
		
		$next_rank_name = $rt['rank_name'];
		$next_rank = $rt['min_points'] - $rank_points;
		
		$result['rank'] = array('rank_name'=>$rank_name,'rank_id'=>$rank_id,'next_rank_name'=>'积分达到'.$next_rank_name,'next_rank'=>'还差'.$next_rank);
	}else
	{
		$sql = "SELECT rank_name,rank_id, special_rank FROM " . $ecs->table('user_rank') . " ORDER BY rank_id DESC LIMIT 1";
		$rank = $db->getRow($sql);
		$rank_name = $rank['rank_name'];
		$rank_id = $rank['rank_id'];
		$result['rank'] = array('rank_name'=>$rank_name,'rank_id'=>$rank_id,'next_rank_name'=>'已经是最高等级了','next_rank'=>'没有下一级别了');
	}
	
	
	
	print_r(json_encode($result));

?>

