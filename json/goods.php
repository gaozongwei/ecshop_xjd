<?php

/**
 * 商品内容
*/
	ob_start();
	
	require('includes/safety_mysql.php');
	define('IN_ECS', true);
	require('../includes/init.php');
	//require('../includes/lib_goods.php');
    $act = isset($_REQUEST['act'])  ? trim($_REQUEST['act']) : '';
    $atxt = isset($_REQUEST['atxt'])  ? trim($_REQUEST['atxt']) : '';
	$goods_id = isset($_REQUEST['goods_id'])  ? intval($_REQUEST['goods_id']) : 0;
	$user_id = isset($_REQUEST['user_id'])  ? intval($_REQUEST['user_id']) : 0;
	$result=array();
	/*查找后台配置的库存管理*/
	$sql="SELECT value FROM ".$ecs->table('shop_config')." WHERE id='207'";
	$use_storage=$db ->getRow($sql);
	$result['use_storage']=$use_storage;
	
	/*每100积分可抵多少元现金*/
	$sql="SELECT value FROM ".$ecs->table('shop_config')." WHERE id='211'";
	$shop_config=$db ->getRow($sql);
	$shop_config_integral=$shop_config['value'];
	
	/*获取商品的图片*/
    if(empty($act))
    {  
	$sql="SELECT img_url FROM ".$ecs->table('goods_gallery')." WHERE goods_id='$goods_id'  LIMIT 0 ,5 ";
	$goods_gallery = $db -> getAll($sql);
	/*获取商品的评论*/
	//$sql="SELECT user_name,content,add_time FROM ".$ecs->table('comment')." WHERE id_value='$goods_id'  LIMIT 0 ,5 ";
	//$comment = $db -> getAll($sql);
	
	/*获取商品的类型*/
	
	$sql="SELECT attr.goods_attr_id,attr.attr_value,attribute.attr_name 
	FROM ".$ecs->table('goods_attr')." AS attr,".$ecs->table('attribute')." AS attribute  
	WHERE attr.goods_id='$goods_id' AND attr.attr_id=attribute.attr_id ;";
	
	$sql="SELECT a.attr_id, a.attr_name, a.attr_group, a.is_linked, a.attr_type, g.goods_attr_id, g.attr_value, g.attr_price FROM ".$ecs->table('goods_attr')." AS g LEFT JOIN  ".$ecs->table('attribute')."  AS a ON a.attr_id = g.attr_id WHERE a.attr_group='0' AND  g.goods_id = '$goods_id' ORDER BY a.sort_order, g.attr_price, g.goods_attr_id";
	$res = $db -> getAll($sql);
	$goods_attr=array();
	$attr=array();
	$list=array();
	
	if(count($res)!=0){
		foreach ($res AS $row)
		{
			
			
				$arr[$row['attr_id']]['attr_id'] = $row['attr_id'];
				$arr[$row['attr_id']]['attr_type'] = $row['attr_type'];
				$arr[$row['attr_id']]['name']     = $row['attr_name'];
				$arr[$row['attr_id']]['values'][] = array(
															'label'        => $row['attr_value'],
															'price'        => $row['attr_price'],
															
															'id'           => $row['goods_attr_id']
															);
		
		}
		foreach($arr AS $key =>$row){
			$goods_attr[]=$row;
		}
	}
    
    $atr_price=0;
    foreach ($goods_attr AS $key=>$val)
    {


        if($val['attr_type']==1)
        {        //echo $val['attr_id'];
            $atr_price+=$val['values'][0]['price'];
        }
        
    }
    //exit;

	$sql="SELECT click_count FROM ".$ecs->table('goods')." WHERE goods_id='$goods_id'";
	$goods=$db ->getRow($sql);
	$click_count=$goods['click_count']+1;
	$db -> query("update ".$ecs->table('goods')." set click_count = '$click_count' where goods_id = '$goods_id'");
	
	/*获取商品详细信息*/
	$sql="SELECT 
		g.goods_sn,
		g.goods_name,
		g.click_count,
		g.brand_id,
		g.goods_number,
		g.goods_weight,
		g.shop_price,
		g.market_price,
		g.promote_price,
		g.promote_start_date,
		g.promote_end_date,
		g.goods_desc,
		g.is_real,
		g.is_promote,
		g.integral,
		g.give_integral,
		g.rank_integral,
		g.supplier_id,
		ifnull(ssc.value,'网站自营') as shopname
	FROM ".$ecs->table('goods')." AS g LEFT JOIN ".$ecs->table('supplier_shop_config')." AS ssc ON g.supplier_id=ssc.supplier_id AND ssc.code='shop_name' WHERE g.goods_id='$goods_id'";
	$goods=$db ->getRow($sql);
	$goods['shop_logo'] = $db ->getOne("select value from ".$ecs->table('supplier_shop_config')." where code='shop_logo' and supplier_id=".$goods['supplier_id']);
	$goods['service_phone'] = $db ->getOne("select value from ".$ecs->table('supplier_shop_config')." where code='service_phone' and supplier_id=".$goods['supplier_id']);
		$goods_price = $goods['shop_price'];
    $goods['shop_price']+=$atr_price;
	
	$goods['integral']=$goods['integral']/$shop_config_integral*100;
	if($goods['give_integral']==-1){
		if($goods['is_promote']==1&&$goods['promote_start_date']<gmtime()&&$goods['promote_end_date']>gmtime()){
			$goods['give_integral'] = intval($goods['promote_price']);
		}else{
			$goods['give_integral'] = intval($goods['shop_price']);
		}
	}
	if($goods['rank_integral']==-1){
		if($goods['is_promote']==1&&$goods['promote_start_date']<gmtime()&&$goods['promote_end_date']>gmtime()){
			$goods['rank_integral']=$goods['promote_price'];
		}else{
			$goods['rank_integral']=$goods['shop_price'];
		}
	}
	$goods['volume'] = get_volume($goods_id);//查询商品的优惠价格
	$goods['shop_atr'] = $goods['shop_price']+$atr_price;//不是促销商品的总价格（加上属性价格）
	$goods['promote_atr'] = $goods['promote_price']+$atr_price;//促销商品的总价格（加上属性价格）
//	$result['linked_goods']=get_linked_goods($goods_id);//获取指定商品的关联商品
	$result['goods']=$goods;
	$result['goods_gallery']=$goods_gallery;
	$result['goods_attr']=$goods_attr;
	$result['user_rank_info']=get_rank_info($user_id);
	//$result['user_rank_prices']=get_user_rank_prices($goods_id, $goods['shop_price'],$user_id);
	$result['user_rank_prices']=get_user_rank_prices($goods_id, $goods_price,$user_id,$atr_price,$goods['promote_price']);
	foreach($user_rank_prices as $key => $value)
	{
		
	}
	$result['is_collect_goods']=is_collect_goods($goods_id,$user_id);
	
	print_r(json_encode($result));
  }
  elseif(!empty($atxt))
  {
      /*	$sql="SELECT 
		shop_price	FROM ".$ecs->table('goods')." WHERE goods_id='$goods_id'";
	   $goods_price=$db ->getOne($sql);*/
   // $goods['shop_price']+=$atr_price;
   	$sql="SELECT 
		shop_price,
		promote_price,
		promote_start_date,
		promote_end_date,
		is_promote
		FROM ".$ecs->table('goods')." WHERE goods_id='$goods_id'";
	   $goods=$db ->getRow($sql);
	   $shop_pricr = $goods['shop_price'];
		if($goods['is_promote'] == 1 && $goods['promote_start_date'] < gmtime() && $goods['promote_end_date'] > gmtime() && $goods['shop_price'] > $goods['promote_price'])
	{
		$result['shop_price_shao'] = $goods['promote_price'];
	}else
	{
		$result['shop_price_shao']= $goods['shop_price'];
	}
	if($goods['is_promote'] == 1 && $goods['promote_start_date'] < gmtime() && $goods['promote_end_date'] > gmtime())
	{
		$result['shop_price'] = $goods['promote_price'];
	}else
	{
		$result['shop_price']= $goods['shop_price'];
	}
    $atr_price=0;
    $goods_att=explode('@',$atxt);
	$fuck = array_pop($goods_att);//弹出数组最后一个空值
	sort($goods_att);
	$good_attr = implode('|',$goods_att);
	//判断所选属性是不是有库存
	$sql = "SELECT product_number FROM ".$ecs->table('products')."WHERE goods_id = '$goods_id' and goods_attr = '$good_attr'";
	$product_number = $db ->getOne($sql);
	if(empty($product_number))
	{
		$result['error'] = '1';
		$result['result'] = "所选属性库存不足";
	}
    foreach ($goods_att as $val)
    {
		
        if(!empty($val))
        {
            $sql="SELECT `attr_price` FROM " .$ecs->table('goods_attr')." WHERE goods_id='$goods_id' AND `goods_attr_id`='$val'";
            $atr_price+=$db->getOne($sql);
        }
        
    }
	$result['cart_price'] = $result['shop_price_shao']+$atr_price;		//本店售价加上属性价格
$result['is_promote'] = $goods['is_promote'];//$result['shop_price']=$goods_price+$atr_price;
	$result['promote_start_date'] = $goods['promote_start_date'];
	$result['promote_end_date'] = $goods['promote_end_date'];
	$result['user_rank_info']=get_rank_info($user_id);
	//$result['user_rank_prices']=get_user_rank_prices($goods_id, $result['shop_price'],$user_id);
		$result['user_rank_prices']=get_user_rank_prices($goods_id, $shop_pricr,$user_id,$atr_price,$goods['promote_price']);
	print_r(json_encode($result));
  }
	
/*=====================================ecshop的一些函数方法======================================*/
	
	
	
/**
 * 获得指定商品的各会员等级对应的价格
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  array
 */
function get_user_rank_prices($goods_id, $shop_price,$user_id,$atr_price,$promote_price)
{
	$user_rank = $GLOBALS['db']->getOne("SELECT user_rank FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '$user_id'");
    $sql = "SELECT rank_id, IFNULL(mp.user_price, r.discount * $shop_price / 100) AS price, r.rank_name, r.discount, r.show_price " .
            'FROM ' . $GLOBALS['ecs']->table('user_rank') . ' AS r ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . " AS mp ".
                "ON mp.goods_id = '$goods_id' AND mp.user_rank = r.rank_id ";
    $res = $GLOBALS['db']->query($sql);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {

        $arr[] = array(
                        'rank_name' => htmlspecialchars($row['rank_name']),
						'rank_id'   => $row['rank_id'],
                        'price'     => price_format($row['price']),
                        'show_price'     => $row['show_price'],
						'price_promote'=>price_format($promote_price+$atr_price),
						'price_shop'=> price_format($row['price']+$atr_price));
    }

    return $arr;
}
	
/**
 * 获得会员等级
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  array
 */
function get_rank_info($user_id)
{
    $user_rank = $GLOBALS['db']->getOne("SELECT user_rank FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '$user_id'");
	
	$sql = "SELECT rank_name FROM " . $GLOBALS['ecs']->table('user_rank') . " WHERE rank_id = '$user_rank'";
    $row = $GLOBALS['db']->getRow($sql);
	$rank_name=$row['rank_name'];
	
	return array('rank_name'=>$rank_name);
}
	
	

/**
 * 判断用户是否收藏
 *
 * @param   string  $goods_id    商品编号
 * @param   string  $user_id    用户编号
 *
 * @return  布尔值
 */
 function is_collect_goods($goods_id,$user_id)
{
$sql="SELECT * 
	FROM  ".$GLOBALS['ecs']->table('collect_goods')." WHERE user_id='$user_id' and goods_id='$goods_id'";
	
	$isCollect=$GLOBALS['db'] ->getRow($sql);
	
	if(!empty($isCollect)){return true;}else{return false;}
}	
/**
 *
 *
 *查询商品的优惠价格
 *@param   string  $goods_id    商品编号
 *
 *
 */
function get_volume($goods_id)
{
	$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('volume_price')."WHERE goods_id = '$goods_id' order by volume_number ASC";
	$volume = $GLOBALS['db']->getAll($sql);
	return $volume;
	
}
ob_end_flush();
?>

