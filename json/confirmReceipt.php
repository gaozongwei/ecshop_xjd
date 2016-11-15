<?php

/**
 * 确认收货
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$result=array();
	$result1=array();
	$order_sn = isset($_REQUEST['order_sn'])  ? $_REQUEST['order_sn'] : 0;
	$user_id = isset($_REQUEST['user_id'])  ? intval($_REQUEST['user_id']) : 0;
	

   $row = $db -> query("update ".$ecs->table('order_info')." set shipping_status = '2' where order_sn = '$order_sn' AND `user_id`='$user_id'");
   
	if($row){
		$result['code']=1;
		$result['info']='订单取消成功！';
	}else{
		$result['code']=0;
		$result['info']='订单取消失败！';
	}
	
	$sql="SELECT * FROM  ".$ecs->table('order_info')." WHERE order_sn='$order_sn' ";
	
	$res = $db -> getRow($sql);
	
	date_default_timezone_set('PRC');
		$res['add_time']=date('Y-m-d h:m:s',$res['add_time']);
		if($res['order_status']==0){
			$res['order_status']="未确认";
		}else if($res['order_status']==1){
			$res['order_status']="已确认";
		}else if($res['order_status']==2){
			$res['order_status']="已取消";
		}else if($res['order_status']==3){
			$res['order_status']="无效";
		}else if($res['order_status']==4){
			$res['order_status']="退货";
		}else if($res['order_status']==5){
			$res['order_status']="已分单";
		}
		
		if($res['shipping_status']==0){
			$res['shipping_status']="未发货";
		}else if($res['shipping_status']==1){
			$res['shipping_status']="已发货";
		}else if($res['shipping_status']==2){
			$res['shipping_status']="已收货";
		}else if($res['shipping_status']==3){
			$res['shipping_status']="备货中";
		}
		
		if($res['pay_status']==0){
			$res['pay_status']="未付款";
		}else if($res['pay_status']==1){
			$res['pay_status']="付款中";
		}else if($res['pay_status']==2){
			$res['pay_status']="已付款";
			
		}
	$result1['orderInfo']=$res;
	$order_id=$res['order_id'];
	$sql="SELECT goods_name, goods_price,goods_number,goods_attr
		FROM  ".$ecs->table('order_goods')." WHERE order_id='$order_id' ";
	//print_r($sql);
	$res = $db -> getAll($sql);
	$result1['orderGoods']=$res;
	$result['info']=$result1;
	print_r(json_encode($result));

?>