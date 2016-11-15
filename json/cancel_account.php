<?php

/**
 * 删除会员充值记录记录
*/
	require('includes/safety_mysql.php');
	define('IN_ECS', true);
	require('../includes/init.php');
	$user_id=intval($_POST['user_id']);
	$id=intval(substr($_POST['id'],10));
	// appjx   修改
	$sql = "SELECT user_money FROM " .$GLOBALS['ecs']->table('users').
           " WHERE user_id = '$user_id'";

    $surplus_amount = $GLOBALS['db']->getOne($sql);
	
	$sql="DELETE FROM ".$ecs->table('user_account')." WHERE id='$id' AND user_id = '$user_id' AND  is_paid = '0' ";
	$db -> query($sql);
	
	$sql = 'SELECT * FROM ' .$GLOBALS['ecs']->table('user_account').
           " WHERE user_id = '$user_id'" .
           " ORDER BY add_time DESC";
	$res = $db -> getAll($sql);
	$process_type = array('充值','退款');
	$is_paid = array('未确认','已完成');
	for($i=0;$i<count($res);$i++){
		$res[$i]['id'] = $res[$i]['add_time'].$res[$i]['id'];
		$res[$i]['add_time'] = local_date($GLOBALS['_CFG']['date_format'], $res[$i]['add_time']);
		$res[$i]['process_type'] = $process_type[$res[$i]['process_type']];
		$res[$i]['is_paid'] = $is_paid[$res[$i]['is_paid']];
	}
	//appjx   修改
	print_r(json_encode(array('list'=>$res,'surplus_amount'=>$surplus_amount)));
	
	

?>