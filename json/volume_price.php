<?php
	/**
	 *商品详情页计算商品价格
	 *
	 *
	 *jx  2015-03-31
	 */
	//require('includes/safety_mysql.php');
	define('IN_ECS', true);
	require('../includes/init.php');
	$goods_id = isset($_REQUEST['goods_id'])  ? intval($_REQUEST['goods_id']) : 0;//商品ID
	$val = isset($_REQUEST['val'])  ? intval($_REQUEST['val']) : 0;//购买的数量
	$user_id = $_GET['user_id'];
	$attr = isset($_REQUEST['attr'])  ? trim($_REQUEST['attr']) : '';//商品属性ID
	if($goods_id == 0)
	{
		$res['error'] = 1;
		$res['message'] = '没有找到该商品';
		die($json->encode($res));
	}
	$sql = "SELECT volume_price FROM ".$GLOBALS['ecs']->table('volume_price')."WHERE goods_id = '$goods_id' and volume_number <= '$val'";
	$volume = $GLOBALS['db']->getOne($sql);//优惠价格
	//商品属性价格  start
	$atr_price=0;
    $goods_att=explode(',',$attr);
    foreach ($goods_att as $value)
    {
        if(!empty($value))
        {
            $sql="SELECT `attr_price` FROM " .$GLOBALS['ecs']->table('goods_attr')." WHERE goods_id='$goods_id' AND `goods_attr_id`='$value'";
            $atr_price+=$GLOBALS['db']->getOne($sql);
        }
        
    }
	//商品属性价格  end
	if($user_id == 0)//用户不存在
	{
		if(!empty($atr_price))//存在属性价格
		{
			$sql = " SELECT * FROM ".$GLOBALS['ecs']->table('goods')."WHERE goods_id = '$goods_id'";
			$res = $GLOBALS['db']->getROW($sql);
			if($res['is_promote'] == 1 && $res['promote_start_date'] < gmtime() && $res['promote_end_date'] > gmtime())//是促销商品
			{
				if(empty($volume))//不存在优惠价格
				{
					if($res['shop_price'] > $res['promote_price'])//本店价  > 促销价格
					{
						$total= ($res['promote_price'] + $atr_price) * $val;//商品的总价
					}else
					{
						$total = ($res['shop_price'] + $atr_price) * $val;//商品的总价
					}
					die(json_encode($total));
				}else//存在优惠价格
				{
					$min = min($volume,$res['shop_price'],$res['promote_price']);//返回优惠  促销 本店 最小的价格
					$total = ($min + $atr_price) * $val;
					die(json_encode($total));
				}
			}else//不是促销商品
			{
				if(empty($volume))//不存在优惠价格
				{
					$total = ($res['shop_price'] + $atr_price) * $val;//商品的总价
					die(json_encode($total));
				}else//存在优惠价格
				{
					$min = min($volume,$res['shop_price']);//返回优惠  促销 本店 最小的价格
					$total = ($min + $atr_price) * $val;
					die(json_encode($total));
				}
			}
		}else
		{
			$sql = " SELECT * FROM ".$GLOBALS['ecs']->table('goods')."WHERE goods_id = '$goods_id'";
			$res = $GLOBALS['db']->getROW($sql);
			if($res['is_promote'] == 1 && $res['promote_start_date'] < gmtime() && $res['promote_end_date'] > gmtime())//是促销商品
			{
				if(empty($volume))//不存在优惠价格
				{
					if($res['shop_price'] > $res['promote_price'])//本店价  > 促销价格
					{
						$total= $res['promote_price'] * $val;//商品的总价
					}else
					{
						$total = $res['shop_price'] * $val;//商品的总价
					}
					die(json_encode($total));
				}else//存在优惠价格
				{
					$min = min($volume,$res['shop_price'],$res['promote_price']);//返回优惠  促销 本店 最小的价格
					$total = $min * $val;
					die(json_encode($total));
				}
			}else//不是促销商品
			{
				if(empty($volume))//不存在优惠价格
				{
					$total = $res['shop_price']  * $val;//商品的总价
					die(json_encode($total));
				}else//存在优惠价格
				{
					$min = min($volume,$res['shop_price']);//返回优惠  促销 本店 最小的价格
					$total = $min  * $val;
					die(json_encode($total));
				}
			}
		}
		
	}else//用户存在
	{
		$row = $GLOBALS['db'] -> getOne("SELECT rank_points FROM ".$GLOBALS['ecs']->table('users')." WHERE `user_id`='$user_id'");
		$sql = "SELECT rank_id FROM " . $GLOBALS['ecs']->table('user_rank') . " WHERE max_points > '$row' ORDER BY max_points ASC LIMIT 1";
		$rank_id = $GLOBALS['db']->getOne($sql);//取得会员等级
		if(empty($rank_id))
		{
			if(!empty($atr_price) && $atr_price != 0)//存在属性价格
			{
				$sql = " SELECT * FROM ".$GLOBALS['ecs']->table('goods')."WHERE goods_id = '$goods_id'";
				$res = $GLOBALS['db']->getROW($sql);
				if($res['is_promote'] == 1 && $res['promote_start_date'] < gmtime() && $res['promote_end_date'] > gmtime())//是促销商品
				{
					if(empty($volume))//不存在优惠价格
					{
						if($res['shop_price'] > $res['promote_price'])//本店价  > 促销价格
						{
							$total= ($res['promote_price'] + $atr_price) * $val;//商品的总价
						}else
						{
							$total = ($res['shop_price'] + $atr_price) * $val;//商品的总价
						}
						die(json_encode($total));
					}else//存在优惠价格
					{
						$min = min($volume,$res['shop_price'],$res['promote_price']);//返回优惠  促销 本店 最小的价格
						$total = ($min + $atr_price) * $val;
						die(json_encode($total));
					}
				}else//不是促销商品
				{
					if(empty($volume))//不存在优惠价格
					{
						$total = ($res['shop_price'] + $atr_price) * $val;//商品的总价
						
						die(json_encode($total));
					}else//存在优惠价格
					{
						$min = min($volume,$res['shop_price']);//返回优惠  促销 本店 最小的价格
						$total = ($min + $atr_price) * $val;
						die(json_encode($total));
					}
				}
			}else
			{
				$sql = " SELECT * FROM ".$GLOBALS['ecs']->table('goods')."WHERE goods_id = '$goods_id'";
				$res = $GLOBALS['db']->getROW($sql);
				if($res['is_promote'] == 1 && $res['promote_start_date'] < gmtime() && $res['promote_end_date'] > gmtime())//是促销商品
				{
					if(empty($volume))//不存在优惠价格
					{
						if($res['shop_price'] > $res['promote_price'])//本店价  > 促销价格
						{
							$total= $res['promote_price']  * $val;//商品的总价
						}else
						{
							$total = $res['shop_price']  * $val;//商品的总价
						}
						die(json_encode($total));
					}else//存在优惠价格
					{
						$min = min($volume,$res['shop_price'],$res['promote_price']);//返回优惠  促销 本店 最小的价格
						$total = $min  * $val;
						die(json_encode($total));
					}
				}else//不是促销商品
				{
					if(empty($volume))//不存在优惠价格
					{
						$total = $res['shop_price']  * $val;//商品的总价
						
						die(json_encode($total));
					}else//存在优惠价格
					{
						$min = min($volume,$res['shop_price']);//返回优惠  促销 本店 最小的价格
						$total = $min  * $val;
						die(json_encode($total));
					}
				}
			}
		}else
		{
			if(!empty($atr_price) && $atr_price != 0)//存在属性价格
			{
				$sql = " SELECT * FROM ".$GLOBALS['ecs']->table('goods')."WHERE goods_id = '$goods_id'";
				$res = $GLOBALS['db']->getROW($sql);
				
				$rank_price = rank_price($rank_id,$res['shop_price']);
				
				if($res['is_promote'] == 1 && $res['promote_start_date'] < gmtime() && $res['promote_end_date'] > gmtime())//是促销商品
				{
					if(empty($volume))//不存在优惠价格
					{
						if($res['shop_price'] > $res['promote_price'])//本店价  > 促销价格
						{
							if($res['promote_price'] > $rank_price)
							{
								$total= ($rank_price + $atr_price) * $val;//商品的总价
							}else
							{
								$total= ($res['promote_price'] + $atr_price) * $val;//商品的总价
							}
							die(json_encode($total));
						}else
						{
							if($res['shop_price'] > $rank_price)
							{
								$total= ($rank_price + $atr_price) * $val;//商品的总价
							}else
							{
								$total = ($res['shop_price'] + $atr_price) * $val;//商品的总价
							}
							die(json_encode($total));
						}
						
					}else//存在优惠价格
					{
						$min = min($volume,$res['shop_price'],$res['promote_price'],$rank_price);//返回优惠  促销 本店 最小的价格
						$total = ($min + $atr_price) * $val;
						die(json_encode($total));
					}
				}else//不是促销商品
				{
					if(empty($volume))//不存在优惠价格
					{
						if($res['shop_price'] > $rank_price)
						{
							$total = ($rank_price + $atr_price) * $val;
						}else
						{
							$total = ($res['shop_price'] + $atr_price) * $val;//商品的总价
						}
						
						
						die(json_encode($total));
					}else//存在优惠价格
					{
						$min = min($volume,$res['shop_price']);//返回优惠  促销 本店 最小的价格
						$total = ($min + $atr_price) * $val;
						die(json_encode($total));
					}
				}
			}else
			{
				$sql = " SELECT * FROM ".$GLOBALS['ecs']->table('goods')."WHERE goods_id = '$goods_id'";
				$res = $GLOBALS['db']->getROW($sql);
				$rank_price = rank_price($rank_id,$res['shop_price']);
				if($res['is_promote'] == 1 && $res['promote_start_date'] < gmtime() && $res['promote_end_date'] > gmtime())//是促销商品
				{
					if(empty($volume))//不存在优惠价格
					{
						if($res['shop_price'] > $res['promote_price'])//本店价  > 促销价格
						{
							if($res['promote_price'] > $rank_price)
							{
								$total = $rank_price * $val;
							}else
							{
								$total= $res['promote_price']  * $val;//商品的总价
							}
							die(json_encode($total));
						}else
						{
							if($res['shop_price'] > $rank_price)
							{
								$total = $rank_price * $val;
							}else
							{
								$total = $res['shop_price']  * $val;//商品的总价
							}
							die(json_encode($total));
						}
						
					}else//存在优惠价格
					{
						$min = min($volume,$res['shop_price'],$res['promote_price'],$rank_price);//返回优惠  促销 本店 最小的价格
						$total = $min  * $val;
						die(json_encode($total));
					}
				}else//不是促销商品
				{
					if($volume == '' && empty($volume))//不存在优惠价格
					{
						if($res['shop_price'] > $rank_price)
						{
							$total = $rank_price * $val;
						}else
						{
							$total = $res['shop_price']  * $val;//商品的总价
						}
						
						die(json_encode($total));
					}else//存在优惠价格
					{
						$min = min($volume,$res['shop_price'],$rank_price);//返回优惠  促销 本店 最小的价格
						$total = $min  * $val;
						die(json_encode($total));
					}
				}
			}
		}
		
	}
	
/*
 *
 *获取对应会员等级的优惠价格
 *
 **/
function  rank_price($rank,$price)
{
	$sql = "SELECT  IFNULL(mp.user_price, r.discount * $price / 100) AS price " .
            'FROM ' . $GLOBALS['ecs']->table('user_rank') . ' AS r ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . " AS mp ".
            "ON mp.goods_id = '$goods_id' AND mp.user_rank = r.rank_id  WHERE r.rank_id = '$rank'";
		$rank_price = $GLOBALS['db']->getOne($sql);//取得会员等级价格
		return $rank_price;
}
?>