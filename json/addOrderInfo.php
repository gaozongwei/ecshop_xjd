<?php

/**
 * 用户地址列表
*/
	define('IN_ECS', true);
	require('../includes/init.php');
	require('../includes/lib_order.php');
	$result=array();
	$shipping_list=array();
	$user=$_POST['uid'];
	$total=$_POST['total'];
	
	/*每100积分可抵多少元现金*/
	$sql="SELECT value FROM ".$ecs->table('shop_config')." WHERE id='211'";
	$shop_config=$db ->getRow($sql);
	$shop_config_integral=$shop_config['value'];
	
	$address_id = $db -> getRow("SELECT address_id,user_money,pay_points FROM ".$GLOBALS['ecs']->table('users')." WHERE `user_id`='$user' ");
	$result['user_money']=$address_id['user_money'];
	$result['user_integral']=$address_id['pay_points'];
	$address_id=$address_id['address_id'];
	$address = $db -> getRow("SELECT * FROM ".$GLOBALS['ecs']->table('user_address')." WHERE `address_id`='$address_id'");
	if(!empty($address)){
		$region_id_list=array($address['country'], $address['province'], $address['city'], $address['district']);
		
		$region_id=$address['country'];
		$province = $db -> getRow("SELECT * FROM ".$GLOBALS['ecs']->table('region')." WHERE region_type ='0' AND `region_id`='$region_id'");
		$address['country']=$province['region_name'];
	
		$region_id=$address['province'];
		$province = $db -> getRow("SELECT * FROM ".$GLOBALS['ecs']->table('region')." WHERE region_type ='1' AND `region_id`='$region_id'");
		$address['province']=$province['region_name'];
		
		$region_id=$address['city'];
		$province = $db -> getRow("SELECT * FROM ".$GLOBALS['ecs']->table('region')." WHERE region_type ='2' AND `region_id`='$region_id'");
		$address['city']=$province['region_name'];
		
		$region_id=$address['district'];
		$province = $db -> getRow("SELECT * FROM ".$GLOBALS['ecs']->table('region')." WHERE region_type ='3' AND `region_id`='$region_id'");
		$address['district']=$province['region_name'];
	
		$sql = 'SELECT s.shipping_id, s.shipping_code, s.shipping_name, ' .
                's.shipping_desc, s.insure, s.support_cod, a.configure ' .
            'FROM ' . $GLOBALS['ecs']->table('shipping') . ' AS s, ' .
                $GLOBALS['ecs']->table('shipping_area') . ' AS a, ' .
                $GLOBALS['ecs']->table('area_region') . ' AS r ' .
            'WHERE r.region_id ' . db_create_in($region_id_list) .
            ' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1 ORDER BY s.shipping_order';
		$shipping_list=$GLOBALS['db']->getAll($sql);
		/**
		 *
		 *计算运费
		 *
		 */
		 $cart_weight_price['weight']=0;
		 $cart_weight_price['amount']=0;
		 $cart_weight_price['number']=0;
		 
		 $goods_id=$_POST['goods_id'];
		 $goods_number=$_POST['goods_number'];
		 
		 
		 $sql="SELECT 
		 g.goods_id,g.is_shipping,g.shop_price,g.goods_weight,g.goods_number,g.integral,g.give_integral,g.rank_integral,
			g.promote_price,
			g.promote_start_date,
			g.promote_end_date,
			g.is_promote
		 FROM ". $GLOBALS['ecs']->table('goods') . " as g WHERE g.is_shipping=0 AND g.goods_id IN (".$goods_id.")";
		 $cart_goods=$GLOBALS['db']->getAll($sql);
		 
		$goods_idArr= explode(",",$goods_id);
		$goods_numberArr= explode(",",$goods_number);
		foreach ($cart_goods AS $k => $v)
		{
			for($i=0;$i<count($goods_idArr);$i++){
				if($v['goods_id']==$goods_idArr[$i]){
					$cart_weight_price['weight'] +=floatval($v['goods_weight'])*$goods_numberArr[$i];
					$cart_weight_price['amount'] +=floatval($v['shop_price'])*$goods_numberArr[$i];
					$result['integral'] +=floatval($v['integral'])*$goods_numberArr[$i];
					
					if($v['give_integral']==-1){
						if($v['is_promote']==1&&$v['promote_start_date']<gmtime()&&$v['promote_end_date']>gmtime()){
							$v['give_integral']=$v['promote_price'];
						}else{
							$v['give_integral']=$v['shop_price'];
						}
						
					}
					$result['give_integral'] +=floatval($v['give_integral'])*$goods_numberArr[$i];
					
					if($v['rank_integral']==-1){
						if($v['is_promote']==1&&$v['promote_start_date']<gmtime()&&$v['promote_end_date']>gmtime()){
							$v['rank_integral']=$v['promote_price'];
						}else{
							$v['rank_integral']=$v['shop_price'];
						}
					
					}
					$result['rank_integral'] +=floatval($v['rank_integral'])*$goods_numberArr[$i];
					$cart_weight_price['number'] +=$goods_numberArr[$i];
				}
			}
		}
		foreach ($shipping_list AS $key => $val)
		{
			
			
			if(count($cart_goods)==0){$shipping_fee=0;}else{
				if (!is_array($val['configure']))
				{
					$shipping_config = unserialize($val['configure']);
				}

				$filename = '../includes/modules/shipping/' . $val['shipping_code'] . '.php';
				if (file_exists($filename))
				{
					include_once($filename);

					$obj = new $val['shipping_code']($shipping_config);
					
					$shipping_fee=$obj->calculate($cart_weight_price['weight'], $cart_weight_price['amount'], $cart_weight_price['number']);
					
				}
				else
				{
					$shipping_fee= 0;
					
				}
			}
			
			
			
			
			
			$shipping_list[$key]['shipping_fee'] = $shipping_fee;
			
		}
		
		
		/**
		 *
		 *计算运费code结束
		 *
		 */
		
	}
	//获取红包
	$day    = getdate();
	
    $today  = mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
	
	$sql = "SELECT t.type_id, t.type_name, t.type_money, b.bonus_id " .
            "FROM " . $GLOBALS['ecs']->table('bonus_type') . " AS t," .
                $GLOBALS['ecs']->table('user_bonus') . " AS b " .
            "WHERE t.type_id = b.bonus_type_id " .
            "AND t.use_start_date <= '$today' " .
            "AND t.use_end_date >= '$today' " .
            "AND t.min_goods_amount <= '$total' " .
            "AND b.user_id<>0 " .
            "AND b.user_id = '$user' " .
            "AND b.order_id = 0";
			
	$bonus=$GLOBALS['db']->getAll($sql);

    $sql="SELECT `user_money` FROM ". $GLOBALS['ecs']->table('users') ." WHERE  `user_id`='$user' ";
    $user_money=$db->getOne($sql);
    if($user_money>$total)
    {
        $sql = "SELECT pay_id,pay_code,pay_name " .
        "FROM " . $GLOBALS['ecs']->table('payment') . " WHERE (pay_code='alipay' OR pay_code='cod' OR pay_code='balance') AND enabled='1' ";
    }
    else
    {
        $sql = "SELECT pay_id,pay_code,pay_name " .
        "FROM " . $GLOBALS['ecs']->table('payment') . " WHERE (pay_code='alipay' OR pay_code='cod') AND enabled='1' ";
        $result['is_balance']=2;
    }

	//获取支付方式（仅仅获取支付宝支付的方式和货到付款）
	$payment=$GLOBALS['db']->getAll($sql);
	
	$result['address']=$address;
	$result['bonus']=$bonus;
	$result['payment']=$payment;
	$result['integral']=$result['integral']/$shop_config_integral*100;
	
	$result['shipping_list']=$shipping_list;
	
	
	
	
	print_r(json_encode($result));

?>

