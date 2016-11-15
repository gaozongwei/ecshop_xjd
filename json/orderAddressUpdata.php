<?php

/**
 * 我的收藏
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$order_id = isset($_REQUEST['order_id'])  ? intval($_REQUEST['order_id']) : 0;
	$user_id = isset($_REQUEST['user_id'])  ? intval($_REQUEST['user_id']) : 0;
	$consignee = isset($_REQUEST['consignee'])  ? trim($_REQUEST['consignee']) : '';
	$address = isset($_REQUEST['address'])  ? trim($_REQUEST['address']) : '';
	$zipcode = isset($_REQUEST['zipcode'])  ? intval($_REQUEST['zipcode']) : '';
	$mobile = isset($_REQUEST['mobile'])  ? intval($_REQUEST['mobile']) : '';
	$email = isset($_REQUEST['email'])  ? $_REQUEST['email'] : '';
	var_dump($mobile);
	$row = $db -> query("update ".$ecs->table('order_info')." 
	set 
	consignee = '$consignee',
	address = '$address',
	zipcode = '$zipcode',
	mobile = '$mobile',
	email = '$email' 
	where order_id = '$order_id' AND `user_id`='$user_id'");
   
	if($row){
		$result['code']=1;
		$result['info']='收货地址更新成功！';
	}else{
		$result['code']=0;
		$result['info']='收货地址更新失败！';
	}
	print_r(json_encode($result));
	
?>