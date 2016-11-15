<?php

/**
 * 我的订单
*/
ob_start();

define('IN_ECS', true);

require('../includes/init.php');
include('../includes/cls_json.php');
$json   = new JSON;


$smarty->template_dir = ROOT_PATH . 'json/tpl';//app部分模板所在位置
	$page=$_GET['page']*10;
	$uid=isset($_GET['uid']) ? intval($_GET['uid']) : 0;
	$orderType=isset($_GET['orderType']) ? intval($_GET['orderType']) : 0;
	if($uid == 0)
	{
		 $res['result'] == "您还没下过订单^_^哦";
		 print_r(json_encode($res));
		 exit;
	}
	switch($orderType){
		case 1:
			$sql="SELECT * 
		FROM  ".$ecs->table('order_info')."  WHERE user_id='$uid' AND pay_status='0' AND order_status != 2 ORDER BY order_id DESC
		LIMIT $page,10";
			break;
		case 2:
			$sql="SELECT * 
		FROM  ".$ecs->table('order_info')."  WHERE user_id='$uid' AND shipping_status='0' AND order_status != 2 ORDER BY order_id DESC
		LIMIT $page,10";
			break;
		case 3:
			$sql="SELECT * 
		FROM  ".$ecs->table('order_info')."  WHERE user_id='$uid'  AND shipping_status='1' AND order_status != 2 ORDER BY order_id DESC
		LIMIT $page,10";
			break;
		case 4:
			$sql="SELECT * 
		FROM  ".$ecs->table('order_info')."  WHERE user_id='$uid' ORDER BY order_id DESC
		LIMIT $page,10";
			break;
		default:
			$sql="SELECT * 
		FROM  ".$ecs->table('order_info')."  WHERE user_id='$uid' ORDER BY order_id DESC
		LIMIT $page,10";
			break;
	}
	$res = $db -> getAll($sql);
	for($i=0;$i<count($res);$i++){
		
		$res[$i]['add_time']=local_date($GLOBALS['_CFG']['time_format'], $res[$i]['add_time']);;
		
		if($res[$i]['order_status']==0){
			$res[$i]['order_status']="未确认";
		}else if($res[$i]['order_status']==1){
			$res[$i]['order_status']="已确认";
		}else if($res[$i]['order_status']==2){
			$res[$i]['order_status']="已取消";
		}else if($res[$i]['order_status']==3){
			$res[$i]['order_status']="无效";
		}else if($res[$i]['order_status']==4){
			$res[$i]['order_status']="退货";
		}else if($res[$i]['order_status']==5){
			$res[$i]['order_status']="已分单";
		}
		
		if($res[$i]['shipping_status']==0){
			$res[$i]['shipping_status']="未发货";
		}else if($res[$i]['shipping_status']==1){
			$res[$i]['shipping_status']="已发货";
		}else if($res[$i]['shipping_status']==2){
			$res[$i]['shipping_status']="已收货";
		}else if($res[$i]['shipping_status']==3){
			$res[$i]['shipping_status']="备货中";
		}else if($res[$i]['shipping_status']==5){
			$res[$i]['shipping_status']="配货中";
		}
		
		if($res[$i]['pay_status']==0){
			$res[$i]['pay_status']="未付款";
		}else if($res[$i]['pay_status']==1){
			$res[$i]['pay_status']="付款中";
		}else if($res[$i]['pay_status']==2){
			$res[$i]['pay_status']="已付款";
			
		}

		$aa = $res[$i]['order_id'];
		$sql ="SELECT g.goods_thumb,s.* FROM ".$ecs->table('order_goods')."as s,".$ecs->table('goods')." as g WHERE s.order_id='$aa' AND s.goods_id=g.goods_id";
		$res[$i]['xiang'] = $db ->getAll($sql);
		if($res[$i]['xiang'])
		{
			$count_money = $res[$i]['xiang'];
			for($j=0;$j<count($count_money);$j++)
			{
				$res[$i]['count_amount'] += $count_money[$j]['goods_number'] * $count_money[$j]['goods_price'];
			}
		}else
		{
			$res[$i]['count_amount'] = $res[$i]['order_amount'];
		}
		
		$res[$i]['count_amount'] = ($res[$i]['count_amount'] + $res[$i]['shipping_fee'] + $res[$i]['insure_fee'] + $res[$i]['pay_fee'] + $res[$i]['pack_fee'] + $res[$i]['card_fee'] + $res[$i]['tax']) - ($res[$i]['discount'] + $res[$i]['bonus'] + $res[$i]['integral_money']);
	}
	
	$smarty->assign('order_list',$res);
	 $res['result'] = $smarty->fetch('orderlist_app.lib');
	print_r(json_encode($res));
	ob_end_flush();
?>

