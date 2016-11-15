<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
if ($_REQUEST['step'] == 'waiting_weixin_pay'){

   	$order_id=intval($_GET['order_id']);
   file_put_contents('a.txt', $order_id.date('Y-m-d H:i:s'));
   	$sql="SELECT pay_status FROM". $GLOBALS['ecs']->table('order_info')."WHERE order_id=". $order_id;
	$pay_status=$GLOBALS['db']->getOne($sql);

	if($pay_status == 2){
	   	echo "1";
	} else{
	  	echo "2";
	}
}