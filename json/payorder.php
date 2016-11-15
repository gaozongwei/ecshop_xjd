<?php

define('IN_ECS', true);

require('../includes/init.php');
include('../includes/cls_json.php');
$json   = new JSON;


$smarty->template_dir = ROOT_PATH . 'json/tpl';//app部分模板所在位置

$res    = array('error' => 0, 'result' => '', 'message' => '');
/*
 * 获取购物车中的商品
 */
if (!empty($_REQUEST['act']) && $_REQUEST['act'] == 'getpayinfo')
{
	$userid = isset($_REQUEST['userid']) ? $_REQUEST['userid']  : 0;
	$ordersn = isset($_REQUEST['ordersn']) ? $_REQUEST['ordersn']  : 0;

	if(empty($userid) || empty($ordersn)){
		$res['error'] = 1;
		$res['message'] = '非法操作';
		die($json->encode($res));
	}
	
	$sql = " select order_sn,surplus ".
			'from ' . $GLOBALS['ecs']->table('order_info') .
			" WHERE order_sn = '".$ordersn."' or parent_order_id='".$ordersn."'";
	//$res = $GLOBALS['db']->query($sql);
	$arr = $GLOBALS['db']->getAll($sql);
	
	$smarty->assign('arr',       $arr);
	$res['result'] = $smarty->fetch('payorderlist_app.lbi');

	die($json->encode($res));
}
?>
