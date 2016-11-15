<?php

/**
 * 订单数量
*/
	require('includes/safety_mysql.php');
	define('IN_ECS', true);
	require('includes/init.php');
	$user_id=$_POST['user_rank'];
	
	$result   = array();
	$user = '';
	/*查找代付款的数据   jx*/
	$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND pay_status = 0 AND order_status != '2'" ;
	$user['payment'] = $GLOBALS['db']->getOne($sql);
	/*查找代发货的数据   jx*/
	$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND shipping_status = 0 AND order_status != '2' AND pay_status = '2'";
	$user['deliver'] = $GLOBALS['db']->getOne($sql);
	/*查找代收货的数据   jx*/
	$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND shipping_status = 1 AND pay_status = '2' AND order_status != '2'";
	$user['receipt'] = $GLOBALS['db']->getOne($sql);
	/*查找全部订单数据   jx*/
	$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id'";
	$user['quan'] = $GLOBALS['db']->getOne($sql);
	/*查询购物车的商品数量  jx*/
	$sql = "SELECT SUM(goods_number) from ".$GLOBALS['ecs']->table('cart')." where user_id = '$user_id'";
	$user['number'] = $GLOBALS['db']->getOne($sql);
	$result['info']=$user;
	print_r(json_encode($result));
	
?>