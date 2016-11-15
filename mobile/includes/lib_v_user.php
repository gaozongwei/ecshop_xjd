<?php

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}


//获取用户信息：微信昵称、头像
function get_user_info_by_user_id($user_id)
{
	$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('weixin_user') . " WHERE ecuid = '$user_id'";
	$rows = $GLOBALS['db']->getRow($sql);
	if(!empty($rows))
	{
		return $rows; 
	} 
}

//获取上司信息
function get_boss_by_user_id($user_id)
{
	$sql = "SELECT parent_id from " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '$user_id'";
	$parent_id = $GLOBALS['db']->getOne($sql);
	if($parent_id > 0)
	{
		 $sql = "SELECT user_id,user_name FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '$parent_id'";
		 $user = $GLOBALS['db']->getRow($sql);
		 $info = get_user_info_by_user_id($user['user_id']);
		 $user['headimgurl'] = $info['headimgurl'];
		 return $user;
	}
}

//获取店铺信息
function get_dianpu_by_user_id($user_id)
{
	$sql = "SELECT * from " . $GLOBALS['ecs']->table('dianpu') . " WHERE user_id = '$user_id'";
	return $GLOBALS['db']->getRow($sql);
}

//是否生成二维码
function is_erweima($user_id)
{
	$sql = "SELECT count(*) FROM " . $GLOBALS['ecs']->table('weixin_qcode') . " where `type`='4' and content='$user_id'";
	return $GLOBALS['db']->getOne($sql);
}

//获取用户二维码
function get_erweima_by_user_id($user_id)
{
	$sql = "SELECT * from " . $GLOBALS['ecs']->table('weixin_qcode') . " WHERE `type` = 4 AND content = '$user_id'";
	return $GLOBALS['db']->getRow($sql); 
}

//获取用户余额
function get_user_money_by_user_id($user_id)
{
	$sql = "SELECT user_money FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '$user_id'";
	$user_money = $GLOBALS['db']->getOne($sql);
	if($user_money > 0)
	{
		return $user_money;
	}
	else
	{
		return 0;
	}
}

//获取用户分成金额
function get_split_money_by_user_id($user_id,$uid)
{
	$sql = "SELECT order_id FROM " . 
			$GLOBALS['ecs']->table('order_info') . 
			" WHERE user_id = '$uid' AND is_separate = 1";
	$ids = $GLOBALS['db']->getAll($sql);
	$total_split_money = 0;
	foreach($ids as $key => $val)
	{
		 $total_split_money += get_split_money($user_id,$val['order_id']);
	}
	return $total_split_money;
}

function get_split_money($user_id,$order_id)
{
	 $sql = "SELECT money FROM " . 
	 		$GLOBALS['ecs']->table('affiliate_log') . 
			" WHERE user_id = '$user_id' AND order_id = '$order_id'";
	 $money = $GLOBALS['db']->getOne($sql);
	 if($money > 0)
	 {
		 return $money; 
	 }
	 else
	 {
		 return 0; 
	 }
}

//获取分销商下级会员信息,$level代表哪一级，1代表是一级会员
function get_distrib_user_info($user_id,$level)
{
	$call_username = $GLOBALS['_CFG']['call_username'];
	$up_uid = "'$user_id'";
    for ($i = 1; $i<=$level; $i++)
    {
		$count = 0;
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $GLOBALS['ecs']->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $GLOBALS['db']->query($sql);
            $up_uid = '';
            while ($rt = $GLOBALS['db']->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
				$count++;
            }
        }
	}
	if($count)
	{
		 $sql = "SELECT user_id, user_name, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time ".
                    " FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)";
		 $list = $GLOBALS['db']->getAll($sql);
		 $arr = array();
		 foreach($list as $key => $val)
		 {
			  if($call_username == 1)
			  {
				  $arr[$key]['call_username'] = '会员ID：'.$val['user_id'];
			  }
			  else
			  {
				  $arr[$key]['call_username'] = '会员名称：'.$val['user_name'];
			  }
			  $arr[$key]['user_id'] = $val['user_id'];
			  $arr[$key]['user_name'] = $val['user_name'];
			  $arr[$key]['order_count'] = get_affiliate_count_by_user_id($val['user_id']); //分成订单数量
			  $arr[$key]['split_money'] = get_split_money_by_user_id($user_id,$val['user_id']); //分成金额
			  $info = get_user_info_by_user_id($val['user_id']);
			  $arr[$key]['headimgurl'] = $info['headimgurl'];
		 }
		 if(!empty($arr))
		 {
			 return $arr; 
		 }
	} 
}

//获取分销商下级会员个数,$level代表哪一级，1代表是一级会员
function get_user_count($user_id,$level)
{
    $up_uid = "'$user_id'";
    for ($i = 1; $i<=$level; $i++)
    {
		$count = 0;
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $GLOBALS['ecs']->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $GLOBALS['db']->query($sql);
            $up_uid = '';
            while ($rt = $GLOBALS['db']->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
				$count++;
            }
        }
	}
	if($count)
	{
		$sql = "SELECT count(*) FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id IN($up_uid)";
		return $GLOBALS['db']->getOne($sql);
	}
	else
	{
		return 0;
	}
}

//获取会员分成订单数量
function get_affiliate_count_by_user_id($user_id)
{
	
	$where = '';
	if($GLOBALS['_CFG']['distrib_type'] == 1)
	{
		$where .= " WHERE total_money > 0";
	}	
	
	if($GLOBALS['_CFG']['is_add_distrib'] == 0)
	{
		 $sql_where = " AND supplier_id = 0";
	}
	$sql = "SELECT COUNT(*) FROM 
			(SELECT SUM(split_money) as total_money FROM " . 
			$GLOBALS['ecs']->table('order_info') . " as o ," . 
			$GLOBALS['ecs']->table('order_goods') . " as og where o.order_id = og.order_id AND o.user_id = '$user_id' AND o.shipping_status = 2 $sql_where group by o.order_id ) as oog $where";
	return $GLOBALS['db']->getOne($sql);
}

//获取会员分成订单信息
function get_affiliate_info_by_user_id($user_id,$page,$size)
{
	if($GLOBALS['_CFG']['is_add_distrib'] == 0)
	{
		 $sql_where = " AND supplier_id = 0";
	}
	if($GLOBALS['_CFG']['distrib_type'] == 1)
	{
		$where .= " WHERE total_money > 0";
	}

	$sql = "select order_id,is_separate,supplier_id,total_split_money from " . 
			"(select a.order_id,a.is_separate,supplier_id," . 
			"SUM(split_money) as total_money," .
			"SUM(b.goods_price*b.goods_number) as total_split_money from " .
			$GLOBALS['ecs']->table('order_info') . " as a ," . 
			$GLOBALS['ecs']->table('order_goods') . " as b " . 
			"where a.order_id = b.order_id and a.shipping_status = 2 " . 
			"and a.user_id = '$user_id' $sql_where " . 
		    "group by a.order_id ) as ab " . $where;

	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
	$arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
	{
		$arr[$row['order_id']]['is_separate'] = $row['is_separate'];
		if($row['supplier_id'] == 0)
		{
			$arr[$row['order_id']]['supplier_name'] = '平台方'; 
		}
		else
		{
			$arr[$row['order_id']]['supplier_name'] = get_supplier_name($row['supplier_id']); 
		}
		if($_SESSION['user_id'] > 0)
		{
			$arr[$row['order_id']]['set_money'] = get_total_money_by_orders($_SESSION['user_id'],$row['order_id'],$row['is_separate']);
		}
		else
		{
			$arr[$row['order_id']]['set_money'] = 0;
		}
		$arr[$row['order_id']]['goods'] = get_goods_list($user_id,$row['order_id'],$row['is_separate']);
		$arr[$row['order_id']]['goods_count'] = get_goods_count($row['order_id']);
		$arr[$row['order_id']]['total_split_money'] = $row['total_split_money'];
	}
	return $arr;
}

//获取分销商下所有下线会员分成订单数量
function get_count_distrib_order_by_user_id($user_id,$is_separate)
{
	$up_uid = "'$user_id'";
	$all_uid = '';
    for ($i = 1; $i<=3; $i++)
    {
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $GLOBALS['ecs']->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $GLOBALS['db']->query($sql);
            $up_uid = '';
            while ($rt = $GLOBALS['db']->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
            }
			if($up_uid)
			{
				$all_uid .= $up_uid.',';
			}
        }
	}
	$uids = rtrim($all_uid,',');
	if(!empty($all_uid))
	{
		if($GLOBALS['_CFG']['is_add_distrib'] == 0)
		{
			 $sql_where = " AND supplier_id = 0";
		}
		$sql = "SELECT order_id FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE user_id in($uids) " . $sql_where;
		$order_list = $GLOBALS['db']->getAll($sql);
		$oids = ''; //分销商下所有下级会员的订单id
		for($i = 0; $i < count($order_list); $i++)
		{
			if($i == 0)
			{
				$oids .= $order_list[$i]['order_id'];
			}
			else
			{
				$oids .= ','.$order_list[$i]['order_id'];
			}
		}
	}
	$where = '';
	if($GLOBALS['_CFG']['distrib_type'] == 1)
	{
		$where .= " WHERE total_money > 0";
	}
	
	if(!empty($oids))
	{
		$sql = "SELECT COUNT(*) FROM 
				(SELECT SUM(split_money) as total_money FROM " . 
				$GLOBALS['ecs']->table('order_info') . " as o ," . 
				$GLOBALS['ecs']->table('order_goods') . " as og where o.order_id = og.order_id AND o.order_id in($oids) AND o.shipping_status = 2 AND is_separate = '$is_separate' group by o.order_id ) as oog $where";
		return $GLOBALS['db']->getOne($sql);
	}
	return 0;
}

//获取分销商下所有下线会员分成订单信息
function get_all_distrib_order_by_user_id($user_id,$is_separate,$page,$size)
{
	$call_username = $GLOBALS['_CFG']['call_username'];
	$up_uid = $user_id;
	$all_uid = '';
    for ($i = 1; $i<=3; $i++)
    {
		if($up_uid)
        {
            $sql = "SELECT user_id FROM " . $GLOBALS['ecs']->table('users') . " WHERE parent_id IN($up_uid)";
			$query = $GLOBALS['db']->query($sql);
            $up_uid = '';
            while ($rt = $GLOBALS['db']->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",$rt[user_id]" : "$rt[user_id]";
            }
			if($up_uid)
			{
				$all_uid .= $up_uid.',';
			}
        }
	}
	$uids = rtrim($all_uid,',');
	if(!empty($uids))
	{
		if($GLOBALS['_CFG']['is_add_distrib'] == 0)
		{
			 $sql_where = " AND supplier_id = 0";
		}
		$sql = "SELECT order_id FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE user_id in($uids) " . $sql_where;
		$order_list = $GLOBALS['db']->getAll($sql);
		$oids = ''; //分销商下所有下级会员的订单id
		for($i = 0; $i < count($order_list); $i++)
		{
			if($i == 0)
			{
				$oids .= $order_list[$i]['order_id'];
			}
			else
			{
				$oids .= ','.$order_list[$i]['order_id'];
			}
		}
		if($GLOBALS['_CFG']['distrib_type'] == 1)
		{
			 $where = " WHERE total_money > 0";
		}
		if(!empty($oids))
		{
			$sql = "SELECT  order_id,goods_id,goods_name,user_id," . 
				"supplier_id,goods_thumb,user_name,total_split_money FROM 
				(SELECT og.order_id,og.goods_id,og.goods_name, " . 
				"o.user_id,o.supplier_id,g.goods_thumb,u.user_name," . 
				"SUM(split_money) as total_money," .
				"SUM(og.goods_price*og.goods_number) as total_split_money FROM " . 
				$GLOBALS['ecs']->table('order_info') . " as o ," . 
				$GLOBALS['ecs']->table('order_goods') . " as og ," . 
				$GLOBALS['ecs']->table('goods') . " as g ," .
				$GLOBALS['ecs']->table('users') . " as u " .
				" WHERE o.order_id = og.order_id AND og.goods_id = g.goods_id " .
				" AND o.user_id = u.user_id AND o.order_id in($oids) " . 
				" AND o.shipping_status = 2 AND is_separate = '$is_separate' " . 
				" GROUP BY o.order_id DESC) as oog $where";

			if(isset($size) && isset($page))
			{
				$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
			}
			else
			{
				$res = $GLOBALS['db']->query($sql); 
			}
			$arr = array();
			while ($row = $GLOBALS['db']->fetchRow($res))
			{
				$arr[$row['order_id']]['order_id'] = $row['order_id'];
				$arr[$row['order_id']]['goods'] = get_goods_list($user_id,$row['order_id'],$is_separate);	//订单商品
				if($row['supplier_id'] == 0)
				{
					$arr[$row['order_id']]['supplier_name'] = '平台方'; 
				}
				else
				{
					$arr[$row['order_id']]['supplier_name'] = get_supplier_name($row['supplier_id']); 
				}
				//获取订单商品中可分成商品数量
				$arr[$row['order_id']]['goods_count'] = get_goods_count($row['order_id']);
				$arr[$row['order_id']]['total_split_money'] = $row['total_split_money'];
				$arr[$row['order_id']]['total_money'] = get_total_money_by_orders($user_id,$row['order_id'],$is_separate);
				$info = get_user_info_by_user_id($row['user_id']);
				$arr[$row['order_id']]['nickname'] = $info['nickname'];
				if($call_username == 1)
				{
					$arr[$row['order_id']]['call_username'] = '会员ID：'.$row['user_id'];
				}
				else
				{
					$arr[$row['order_id']]['call_username'] = '会员名称：'.$row['user_name'];
				}
				$arr[$row['order_id']]['user_name'] = $row['user_name'];
				$arr[$row['order_id']]['split_money'] = price_format(get_split_money_by_user_id($user_id,$row['user_id']));
				$arr[$row['order_id']]['level'] = get_level_user($user_id,$row['user_id']);
			}
			if(!empty($arr))
			{
				return $arr; 
			}
		}
	}
	return array();
}

/**
 * 获取当前用户从此订单中得到的总分成金额
 *
 * @param  integer $user_id 用户ID
 * @param  integer $order_id 订单号
 * @param  integer $is_separate 分成状态 0未分成 1已分成 2撤销分成
 *
 * @return floor
 */
function get_total_money_by_orders($user_id,$order_id,$is_separate)
{
	 $total_money = 0;
	 if($is_separate == 1 || $is_separate == 2) 
	 {
		  $sql = "SELECT money FROM " .
		  		 $GLOBALS['ecs']->table('affiliate_log') . 
		  		 " WHERE order_id = '$order_id' AND user_id = '$user_id'";
		  $total_money = $GLOBALS['db']->getOne($sql);
	 }
	 else
	 {
		 $sql = "SELECT user_id FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE order_id = '$order_id'";
	 	 $uid = $GLOBALS['db']->getOne($sql);
	 	 $level = get_level_user($user_id,$uid);
	 	 $affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	 	 $num = count($affiliate['item']);
	 	 for($i = 0; $i <= $num; $i++)
	 	 {
		 	 if(($i+1) == $level)
		 	 {
			 	 $level_money = $affiliate['item'][$i]['level_money']/100; 
		 	 } 
	 	 }
		 if($GLOBALS['_CFG']['distrib_type'] == 1)
		 {
			 $sql = "SELECT SUM(split_money*goods_number) FROM " . 
			 		$GLOBALS['ecs']->table('order_goods') . 
			 		" WHERE order_id = '$order_id'";
	 		 $split_money = $GLOBALS['db']->getOne($sql);
	 		 $total_money = round($split_money*$level_money,2);
		 }
		 else
		 {
			 $total_fee = get_order_total_fee($order_id);
			 $total_money = round(($GLOBALS['_CFG']['distrib_percent']/100)*$total_fee*$level_money,2); 
		 }
	 }
	 return $total_money;
}

/**
 * 获取订单中分销商品
 *
 * @param  integer $user_id 用户ID
 * @param  integer $order_id 订单号
 * @param  integer $is_separate 分成状态 0未分成 1已分成 2撤销分成
 *
 * @return array
 */
function get_goods_list($user_id,$order_id,$is_separate)
{
	 if($is_separate == 1 || $is_separate == 2)
	 {
		 $sql = "SELECT SUM(split_money) FROM " . 
		 		$GLOBALS['ecs']->table('order_goods') . 
				" WHERE order_id = '$order_id'";
		 $split_money = $GLOBALS['db']->getOne($sql);
		 $sql_where = '';
		 if($GLOBALS['_CFG']['distrib_type'] == 1)
		 {
		 	if($split_money > 0)
		 	{
			 	$sql_where .= " AND split_money > 0";
		 	}
		 }
		 $sql = "SELECT og.goods_name,g.goods_thumb,goods_price".
			  		 ",og.goods_number,og.split_money FROM " . 
	 				 $GLOBALS['ecs']->table('order_goods') . " as og, " . 
	 				 $GLOBALS['ecs']->table('goods') . " as g " . 
	 				 " WHERE og.goods_id = g.goods_id " . 
					 "AND order_id = '$order_id'" . $sql_where;
	 }
	 else
	 {
	 
		 if($GLOBALS['_CFG']['distrib_type'] == 1)
		 {
			 $where = " AND split_money > 0 "; 
		 }
		 $sql = "SELECT og.goods_name,g.goods_thumb,goods_price,og.goods_number,og.split_money FROM " . 
				$GLOBALS['ecs']->table('order_goods') . " as og, " . 
				$GLOBALS['ecs']->table('goods') . " as g " . 
				" WHERE og.goods_id = g.goods_id AND order_id = '$order_id'" . $where;
	 }
	 $list = $GLOBALS['db']->getAll($sql);
	 $arr = array();
	 foreach($list as $key => $val)
	 {
		  $arr[$key]['goods_name'] = $val['goods_name'];
		  $arr[$key]['goods_thumb'] = $val['goods_thumb'];
		  $arr[$key]['goods_price'] = $val['goods_price'];
		  $arr[$key]['goods_number'] = $val['goods_number'];
	 }
	 return $arr;
}

/**
 * 获取订单中分成商品的数量
 *
 * @param  integer $order_id 订单号
 * @param  integer $is_separate 分成状态 0未分成 1已分成 2撤销分成
 *
 * @return array
 */
function get_goods_count($order_id,$is_separate)
{
	if($is_separate == 1 || $is_separate == 2)
	{
		 $sql = "SELECT SUM(split_money) FROM " . 
		 		$GLOBALS['ecs']->table('order_goods') . 
				" WHERE order_id = '$order_id'";
		 $split_money = $GLOBALS['db']->getOne($sql);
		 $sql_where = '';
		 if($split_money > 0)
		 {
			  $sql_where .= " AND split_money > 0";
		 }
		 $sql = "SELECT COUNT(*) FROM " . 
				$GLOBALS['ecs']->table('order_goods') . " as og, " . 
				$GLOBALS['ecs']->table('goods') . " as g " . 
				" WHERE og.goods_id = g.goods_id " . 
				"AND order_id = '$order_id'" . $sql_where;
	}
	else
	{
		if($GLOBALS['_CFG']['distrib_type'] == 1)
		{
			 $where = " AND split_money > 0 "; 
		}
		$sql = "SELECT COUNT(*) FROM " . 
			$GLOBALS['ecs']->table('order_goods') . " as og, " . 
			$GLOBALS['ecs']->table('goods') . " as g " . 
			" WHERE og.goods_id = g.goods_id AND order_id = '$order_id'" . $where;
	}
	return $GLOBALS['db']->getOne($sql);
}

//查看某一个会员是当前分销商的几级会员
function get_level_user($user_id,$uid)
{
	$up_uid = "'$user_id'";
	$all_uid = '';
	$level = 0;
    for ($i = 1; $i<=3; $i++)
    {
        if ($up_uid)
        {
            $sql = "SELECT user_id FROM " . $GLOBALS['ecs']->table('users') . " WHERE parent_id IN($up_uid)";
            $query = $GLOBALS['db']->query($sql);
            $up_uid = '';
            while ($rt = $GLOBALS['db']->fetch_array($query))
            {
                $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
				if($rt['user_id'] == $uid)
				{
					$level = $i;
					break;
				}
            }
        }
	}
	return $level;
}

//获取用户分成、未分成、撤销分成总金额
function get_total_money_by_user_id($user_id,$is_separate)
{
	if($is_separate == 1)
	{
		//已分成总额
		$sql = "SELECT SUM(money) FROM " . $GLOBALS['ecs']->table('affiliate_log') . " WHERE user_id = '$user_id'";
		$total_money = $GLOBALS['db']->getOne($sql);
	}
	else if($is_separate == 2)
	{
		//撤销分成总金额
		$sql = "SELECT SUM(money) FROM " . $GLOBALS['ecs']->table('affiliate_log') . " WHERE user_id = '$user_id' AND money < 0";
		$money = $GLOBALS['db']->getOne($sql);
		$total_money = abs($money);
	}
	else
	{
		$up_uid = "'$user_id'";
		$all_uid = '';
		for ($i = 1; $i<=3; $i++)
		{
			if ($up_uid)
			{
				$sql = "SELECT user_id FROM " . $GLOBALS['ecs']->table('users') . " WHERE parent_id IN($up_uid)";
				$query = $GLOBALS['db']->query($sql);
				$up_uid = '';
				while ($rt = $GLOBALS['db']->fetch_array($query))
				{
					$up_uid .= $up_uid ? ",$rt[user_id]" : "$rt[user_id]";
				}
				if($up_uid)
				{
					$all_uid .= $up_uid.",";
				}
			}
		}
		$uids = rtrim($all_uid,',');
		if(!empty($uids))
		{
			if($GLOBALS['_CFG']['distrib_type'] == 0)
			{
				 $total_fee = " (goods_amount - discount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee) AS total_money";
				$sql = "select a.order_id,a.user_id, " . $total_fee . " from " . 
				$GLOBALS['ecs']->table('order_info') . " as a " . 
				"  where  a.user_id in($uids) and is_separate = '$is_separate' AND a.shipping_status = 2";
			}
			else
			{
				$sql = "select a.order_id,a.user_id,sum(split_money*goods_number) as total_money from " . 
				$GLOBALS['ecs']->table('order_info') . " as a ," . 
				$GLOBALS['ecs']->table('order_goods') . 
				" as b where a.order_id = b.order_id and a.user_id in($uids) and is_separate = '$is_separate' and a.shipping_status = 2 " .
				" group by a.order_id";
			}
			$order_ids = $GLOBALS['db']->getAll($sql);
			if(!empty($order_ids))
			{
				$total_money = 0;
				$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);  
				for($j = 0;$j < count($order_ids); $j++)
				{
					if($GLOBALS['_CFG']['distrib_type'] == 0)
					{
						$split_money = $order_ids[$j]['total_money']*($GLOBALS['_CFG']['distrib_percent']/100);
					}
					else
					{
						$split_money = $order_ids[$j]['total_money'];
					}	
					if($split_money > 0)
					{
						$level = get_level_user($user_id,$order_ids[$j]['user_id']);
						$num = count($affiliate['item']);
						for ($k=0; $k < $num; $k++)
						{
							if($level == ($k+1))
							{
								$a = (float)$affiliate['item'][$k]['level_money'];
								if($affiliate['config']['level_money_all']==100 )
								{
									$total_money += $split_money;
								}
								else 
								{
									if ($a)
									{
										$a /= 100;
									}
									$total_money += round($split_money * $a, 2);
								} 
							}
						}
					}
				}
			}
		}
	}
	if($total_money > 0)
	{
		return $total_money; 
	}
	else
	{
		return 0; 
	 }
}

//获取某一个订单的分成金额
function get_split_money_by_orderid($order_id)
{
	 if($GLOBALS['_CFG']['distrib_type'] == 0)
	 {
		 $total_fee = " (goods_amount - discount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee) AS total_money";
		 //按订单分成
		 $sql = "SELECT " . $total_fee . " FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE order_id = '$order_id'";
		 $total_fee = $GLOBALS['db']->getOne($sql);
		 $split_money = $total_fee*($GLOBALS['_CFG']['distrib_percent']/100);
	 }
	 else
	 {
		//按商品分成
	 	$sql = "SELECT sum(split_money*goods_number) FROM " . $GLOBALS['ecs']->table('order_goods') . " WHERE order_id = '$order_id'";
	 	$split_money = $GLOBALS['db']->getOne($sql);
	 }
	 if($split_money > 0)
	 {
		 return $split_money; 
	 }
	 else
	 {
		 return 0; 
	 }
}

//判断会员是否是分销商
function is_distribor($user_id)
{
	 //判断是否是分销商
	$distrib_rank = $GLOBALS['_CFG']['distrib_rank'];
	if($distrib_rank == -1)
	{
		 //所有注册会员都是分销商
		$GLOBALS['db']->query("UPDATE " . $GLOBALS['ecs']->table('users') . " SET is_fenxiao = 1 WHERE is_fenxiao <> 0");
	}
	else
	{
		 $rank = explode(',',$distrib_rank);
		 $ex_where = '';
		 $fx_where = '';
		 for($i = 0; $i < count($rank); $i++)
		 {
			 $sql = "SELECT min_points, max_points, special_rank FROM ".$GLOBALS['ecs']->table('user_rank')." WHERE rank_id = '" . $rank[$i] . "'";
             $row = $GLOBALS['db']->getRow($sql);
			 if($i != 0)
			 {
				 $ex_where .= " or ";
				 $fx_where .= " or ";
			 }
             $ex_where .= " (rank_points >= " . intval($row['min_points']) . " AND rank_points < " . intval($row['max_points']) . ")";
			 $fx_where .= " (rank_points < " . intval($row['min_points']) . " OR rank_points >= " . intval($row['max_points']) . ")";
			 if($row['special_rank'] > 0)
			 {
				 $ex_where .= " or user_rank = '" . $rank[$i] . "'";
			 }
         }
		 //没达到条件的所有会员变为普通会员
		 $GLOBALS['db']->query("UPDATE " . $GLOBALS['ecs']->table('users') . " SET is_fenxiao = 2 WHERE is_fenxiao <> 0 AND " . "(".$fx_where.")");
		 //达到条件的所有会员晋级为分销商
		 $GLOBALS['db']->query("UPDATE " . $GLOBALS['ecs']->table('users') . " SET is_fenxiao = 1 WHERE is_fenxiao <> 0 AND " . "(".$ex_where.")");	
	}
	$sql = "SELECT is_fenxiao FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '$user_id'";
	return $GLOBALS['db']->getOne($sql);
}


//获取用户分成账单信息
function get_users_notes($user_id)
{
	$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('affiliate_log') . " WHERE user_id = '$user_id' order by time desc";
	$list = $GLOBALS['db']->getAll($sql);
	$arr = array();
	foreach($list as $key => $val)
	{
		$arr[$key]['money'] = $val['money'];
		$arr[$key]['time'] = local_date("Y-m-d",$val['time']);
		$arr[$key]['change_desc'] = $val['change_desc'];
	}
	return $arr;
}

//获取供货商名称
function get_supplier_name($supplier_id)
{
	$sql = "SELECT supplier_name FROM " . $GLOBALS['ecs']->table('supplier') . " WHERE supplier_id = '$supplier_id'";
	return $GLOBALS['db']->getOne($sql); 
}

//获取订单总金额
function get_order_total_fee($order_id)
{
	$total_fee = " (goods_amount - discount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee) AS total_fee ";
	$sql = "SELECT ". $total_fee . " FROM " . $GLOBALS['ecs']->table('order_info') .
                " WHERE order_id = '$order_id'";
	return $GLOBALS['db']->getOne($sql);
}

//分成后，推送到各个上级分销商微信
function push_user_msg($ecuid,$order_sn,$split_money){
	$type = 1;
	$text = "订单".$order_sn."分成，您得到的分成金额为".$split_money;
	$user = $GLOBALS['db']->getRow("select * from " . $GLOBALS['ecs']->table('weixin_user') . " where ecuid='{$ecuid}'");
	if($user && $user['fake_id']){
		$content = array(
			'touser'=>$user['fake_id'],
			'msgtype'=>'text',
			'text'=>array('content'=>$text)
		);
		$content = serialize($content);
		$sendtime = $sendtime ? $sendtime : time();
		$createtime = time();
		$sql = "insert into ".$GLOBALS['ecs']->table('weixin_corn')." 

(`ecuid`,`content`,`createtime`,`sendtime`,`issend`,`sendtype`) 
			value ({$ecuid},'{$content}','{$createtime}','{$sendtime}','0',

{$type})";
		$GLOBALS['db']->query($sql);
		return true;
	}else{
		return false;
	}
}

function insert_affiliate_log($oid, $uid, $username, $money, $separate_by,$change_desc)
{
    $time = gmtime();
    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('affiliate_log') . "( order_id, user_id, user_name, time, money, separate_type,change_desc)".
                                                              " VALUES ( '$oid', '$uid', '$username', '$time', '$money', '$separate_by','$change_desc')";
    if ($oid)
    {
        $GLOBALS['db']->query($sql);
    }
}
?>