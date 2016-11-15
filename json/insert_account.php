<?php

/**
 * 插入会员充值记录记录
*/
	require('includes/safety_mysql.php');
	define('IN_ECS', true);
	require('../includes/init.php');
	$user_id=intval($_POST['user_id']);
	$user_note = $_POST['user_note'];
	$amount = $_POST['amount'];
	$addtime = gmtime();
	
	$data['amount'] = $amount;
	$data['user_id'] = $user_id;
	$data['user_note'] = $user_note;
	$data['add_time'] = $addtime;
	$data['payment'] = '支付宝';
	$data['process_type'] = 0;
	$data['is_paid'] = 0;
	$data['admin_note'] = '';
	$data['paid_time'] = 0;
	$data['admin_user'] = '';
	
	$db->autoExecute($ecs->table('user_account'), $data, 'INSERT');
	$new_id = $db->insert_id();
	
	if($new_id>0){
	print_r(json_encode(array('code'=>1,'info'=>'请继续支付完成充值！','id'=>$new_id,'amount'=>$amount,'add_time'=>$addtime)));
	}else{
	print_r(json_encode(array('code'=>0,'info'=>'操作失败')));
	}
	
	

?>