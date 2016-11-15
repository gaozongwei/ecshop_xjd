<?php

/**
 * 用户地址列表
*/
	define('IN_ECS', true);
	require('includes/init.php');
	//require('../includes/lib_order.php');
	
	$address_id = isset($_REQUEST['address_id'])  ? intval($_REQUEST['address_id']) : 0;
	$uid = isset($_REQUEST['uid'])  ? intval($_REQUEST['uid']) : 0;
	$_REQUEST['user_meny'] = isset($_REQUEST['user_meny'])  ? intval($_REQUEST['user_meny']) : 0;
	$bonus_id = isset($_REQUEST['bonus_id'])  ? intval($_REQUEST['bonus_id']) : 0;
	$_REQUEST['bonus_id'] = isset($_REQUEST['bonus_id'])  ? intval($_REQUEST['bonus_id']) : 0;
	
	//获取商城后台设置的积分兑换比例
	
	
	$sql="SELECT value FROM ".$ecs->table('shop_config')." WHERE id='211'";
	$shop_config=$db ->getRow($sql);
	$integral_scale=$shop_config['value']/100;
	
	//获取用户的收货地址
	$address = $db -> getRow("SELECT * FROM ".$ecs->table('user_address')."  WHERE  `address_id`='$address_id';");
	
	$sql = "SELECT t.type_id, t.type_name, t.type_money, b.bonus_id " .
            "FROM " . $ecs->table('bonus_type') . " AS t," .
                $ecs->table('user_bonus') . " AS b " .
            "WHERE t.type_id = b.bonus_type_id " .
            "AND b.bonus_id=$bonus_id  " . 
            "AND b.user_id = '$uid' " .
            "AND b.order_id = 0";
	$bous=$db ->getRow($sql);
	$bous_money=empty($bous['type_money'])?0:$bous['type_money'];
	$order_amount=$_REQUEST['total']+$_REQUEST['shipping_fee'];
	$order_amount=$order_amount-$bous_money;
	if($order_amount<$_REQUEST['user_meny']){$_REQUEST['user_meny']=$order_amount;}
	$order_amount=$order_amount-$_REQUEST['user_meny'];
	
	$integral_meny=0;
	if($order_amount>0){
		$integral_meny=$integral_scale*$_REQUEST['integral'];
		if($integral_meny>$order_amount){
			
			$_REQUEST['integral']=($integral_meny-$order_amount)/$integral_scale;
			$order_amount=0;
		}else{
			$order_amount=$order_amount-$integral_meny;
		}
	}else{$_REQUEST['integral']=0;}
	$pay_status=0;
	if($order_amount<=0){
		$order_amount=0;
		$pay_status=2;
	}
	/* 插入订单表 */
	$order['shipping_id'] = 2;
	$order['pay_id'] = $_REQUEST['pay_id'];
	$order['pack_id'] = 0;
	$order['card_id'] = 0;
	$order['card_message'] = 0;
	$order['surplus'] = $_REQUEST['user_meny'];
	$order['integral'] = $_REQUEST['integral'];
	$order['bonus_id'] = $_REQUEST['bonus_id'];
	$order['need_inv'] = 0;
	$order['inv_type'] = '';
	$order['inv_payee'] = '';
	$order['inv_content'] = '';
	$order['postscript'] = '';
	$order['how_oos'] = '等待所有商品备齐后再发';
	$order['need_insure'] = 0;
	$order['user_id'] = $uid;
	$order['add_time'] = gmtime();
	$order['order_status'] = 0;
	$order['shipping_status'] = 0;
	$order['pay_status'] = $pay_status;
	$order['agency_id'] = 0;
	$order['extension_code'] = '';
	$order['extension_id'] = 0;
	$order['address_id'] = $address['address_id'];
	$order['address_name'] = "";
	$order['consignee'] = $address['consignee'];
	$order['email'] = $address['email'];
	$order['country'] = 1;
	$order['province'] = $address['province'];
	$order['city'] = $address['city'];
	$order['district'] = $address['district'];
	$order['address'] = $address['address'];
	$order['zipcode'] = $address['zipcode'];
	$order['tel'] = $address['tel'];
	$order['mobile'] = '';
	$order['sign_building'] = '';
	$order['best_time'] = '';
	$order['bonus'] = $bous_money;
	$order['goods_amount'] = $_REQUEST['total'];
	$order['discount'] = '';
	$order['tax'] = 0;
	$order['shipping_name'] = $_REQUEST['shipping_name'];
	$order['shipping_fee'] = $_REQUEST['shipping_fee'];
	$order['insure_fee'] = 0;
	$order['pay_name'] = $_REQUEST['pay_name'];
	$order['pay_fee'] = 0;
	$order['cod_fee'] = 0;
	$order['pack_fee'] = 0;
	$order['card_fee'] = 0;
	$order['order_amount'] = $order_amount;
	$order['integral_money'] = $integral_meny;
	$order['from_ad'] = 0;
	$order['referer'] = "本站";
	$order['parent_id'] = 0;
	$order_sn=get_order_sn(); //获取新订单号
	$order['order_sn'] = $order_sn; //获取新订单号
	
	$sql="SELECT `pay_code` FROM ".$ecs->table('payment') ." WHERE `pay_id`=".$order['pay_id'];
    $pay_code=$db->getOne($sql);
	
	if($pay_code=='balance')
    {
        $sql="SELECT `user_money` FROM ".$ecs->table('users') ." WHERE `user_id`='$uid'";
        $user_money=$db->getOne($sql);
        if($user_money>$order_amount)
        {
            $sql="UPDATE ".$ecs->table('users') . " SET `user_money`=`user_money`-$order_amount WHERE `user_id`='$uid'";
            $db->query($sql);
            $order['order_status'] = 1;
            $order['pay_status'] = 2;           
        }
    }
	
	
	
	$db->autoExecute($ecs->table('order_info'), $order, 'INSERT');
	$new_order_id = $db->insert_id();
	if($new_order_id){//使用余额支付，减去用户余额
		if($_REQUEST['user_meny']>0){
			$surplus=$_REQUEST['user_meny'];
			$sql="UPDATE ".$ecs->table('users') . " SET `user_money`=`user_money`-$surplus WHERE `user_id`='$uid'";
            $db->query($sql);
		}
	}
	if($_REQUEST['integral']>0){//使用积分支付，减去用户积分
		
		$integral=$_REQUEST['integral'];
		$sql="UPDATE ".$ecs->table('users') . " SET `pay_points`=`pay_points`-$integral WHERE `user_id`='$uid'";
		$db->query($sql);
		
		//添加积分记录
		$order_log=array();
       $order_log['user_id'] = $uid;
       $order_log['user_money'] = '0.00';
       $order_log['frozen_money'] = '0.00';
       $order_log['rank_points'] ='0';
       $order_log['pay_points'] = '-'.$integral;
       $order_log['change_desc'] = 'pay order_sn:'.$order_sn;
       $order_log['change_type'] = '99';
       $order_log['change_time'] = gmtime();
       $db->autoExecute($ecs->table('account_log'), $order_log, 'INSERT');  
		
	}
	/*修改红包状态*/
	$sql="UPDATE  " .$ecs->table('user_bonus') . " SET  `used_time` =  '".gmtime()."' , `order_id` =  '$new_order_id' WHERE  bonus_id ='$bonus_id'";
	
	$db->query($sql);
    if($pay_code=='balance' && $user_money>$order_amount)
    {
       $order_log=array();
       $order_log['user_id'] = $uid;
       $order_log['user_money'] = '-'.$order_amount;
       $order_log['frozen_money'] = '0.00';
       $order_log['rank_points'] ='0';
       $order_log['pay_points'] = '0';
       $order_log['change_desc'] = 'pay order_sn:'.$order_sn;
       $order_log['change_type'] = '99';
       $order_log['change_time'] = gmtime();
       $db->autoExecute($ecs->table('account_log'), $order_log, 'INSERT');                                         
    }
    
	/* 插入订单商品 */
	$goods_list=explode(",",$_REQUEST['goods_id']);
	$goods_number=explode(",",$_REQUEST['goods_number']);
	$goods_attr=explode(",",$_REQUEST['goods_attr']);
	$goods_attr_id=explode(",",$_REQUEST['goods_attr_id']);
	$length=count($goods_list);
	//此订单赠送消费积分和等级积分
	$rank_points=0;
	$pay_points=0;
	
	for($i=0;$i<$length;$i++){
		/*获取商品详细信息*/
		$goods_id=$goods_list[$i];
		$number=$goods_number[$i];
		$give_integral=0;
		$rank_integral=0;
		if(!empty($_REQUEST['goods_attr'])){
			$Gattr=$goods_attr[$i];
		}else{
			$Gattr="";
		}
		if(!empty($_REQUEST['goods_attr_id'])){
			$attr_id=$goods_attr_id[$i];
		}else{
			$attr_id="";
		}
		$sql="SELECT 
			goods_sn,
			goods_name,
			is_promote,
			promote_start_date,
			promote_end_date,
			promote_price,
			shop_price,
			market_price,
			goods_number,
			give_integral,
			rank_integral,
			is_real
		FROM ".$ecs->table('goods')." WHERE goods_id='$goods_id'";
		$goods=$db ->getRow($sql);
		//累加消费积分和等级积分
		if($goods['is_promote']==1&&$goods['promote_start_date']<gmtime()&&$goods['promote_end_date']>gmtime()){
			$goods['shop_price']=$goods['promote_price'];
		}
		if($goods['give_integral']==-1){$goods['give_integral']=$goods['shop_price'];}
		if($goods['rank_integral']==-1){$goods['rank_integral']=$goods['shop_price'];}
		if($goods['give_integral']==-1){
			if($goods['is_promote']==1&&$goods['promote_start_date']<gmtime()&&$goods['promote_end_date']>gmtime()){
				$goods['give_integral']=$goods['promote_price'];
			}else{
				$goods['give_integral']=$goods['shop_price'];
			}
			
		}
		if($goods['rank_integral']==-1){
			if($goods['is_promote']==1&&$goods['promote_start_date']<gmtime()&&$goods['promote_end_date']>gmtime()){
				$goods['rank_integral']=$goods['promote_price'];
			}else{
				$goods['rank_integral']=$goods['shop_price'];
			}
			
		}
		
		
		$rank_points=$rank_points+$goods['give_integral']*$number;
		$pay_points=$pay_points+$goods['rank_integral']*$number;
		
		//修改库存
		$update_number=$goods['goods_number']-$number;
		$db -> query("update ".$ecs->table('goods')." set goods_number = '$update_number' where goods_id = '$goods_id'");
	
		$sql = "INSERT INTO " . $ecs->table('order_goods') . "( " .
					"order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".
					"goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id) VALUES ( ".
				" '$new_order_id', '$goods_id', '".$goods['goods_name']."', '".$goods['goods_sn']."', '0', '$number', '".$goods['market_price']."', ".
					"'".$goods['shop_price']."', '$Gattr', '".$goods['is_real']."', '', '0', '0', '$attr_id')";
		$db->query($sql);
	}
	
	//插入消费积分和等级积分记录
	if($rank_points>0){
		$order_log=array();
       $order_log['user_id'] = $uid;
       $order_log['user_money'] = '0.00';
       $order_log['frozen_money'] = '0.00';
       $order_log['rank_points'] =$rank_points;
       $order_log['pay_points'] = '0';
       $order_log['change_desc'] = 'get order_sn:'.$order_sn;
       $order_log['change_type'] = '99';
       $order_log['change_time'] = gmtime();
       $db->autoExecute($ecs->table('account_log'), $order_log, 'INSERT');
	   $sql="UPDATE ".$ecs->table('users') . " SET `rank_points`=`rank_points`+$rank_points WHERE `user_id`='$uid'";
		$db->query($sql);
	}
	if($pay_points>0){
		$order_log=array();
       $order_log['user_id'] = $uid;
       $order_log['user_money'] = '0.00';
       $order_log['frozen_money'] = '0.00';
       $order_log['rank_points'] ='0';
       $order_log['pay_points'] = $pay_points;
       $order_log['change_desc'] = 'get order_sn:'.$order_sn;
       $order_log['change_type'] = '99';
       $order_log['change_time'] = gmtime();
       $db->autoExecute($ecs->table('account_log'), $order_log, 'INSERT');
	   $sql="UPDATE ".$ecs->table('users') . " SET `pay_points`=`pay_points`+$pay_points WHERE `user_id`='$uid'";
		$db->query($sql);
	}
	
	
	
	
	$sql="SELECT 
			order_amount
		FROM ".$ecs->table('order_info')." WHERE order_sn='$order_sn'";
		$pay=$db ->getRow($sql);
		$order_amount=$pay['order_amount'];
	$result=array();
	$result['code']=1;
	$result['info']="订单提交成功！";
	$result['result']=array($order_sn,$order_amount);
	
	
	print_r(json_encode($result));
	
	
/***************************************ecshop的函数******************************/
function get_order_sn()
{
	/* 选择一个随机的方案 */
	mt_srand((double) microtime() * 1000000);

	return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

?>

