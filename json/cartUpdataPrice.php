<?php

/**
 * 购物车更新商品价格
*/
	define('IN_ECS', true);
	require('../includes/init.php');
	//require('../includes/lib_goods.php');
    
	$goods_id_arr = isset($_REQUEST['goods_id_arr'])  ? trim($_REQUEST['goods_id_arr']) : 0;
	$user_id = isset($_REQUEST['user_id'])  ? intval($_REQUEST['user_id']) : 0;
	
	$sql="SELECT g.goods_id,g.shop_price,g.is_promote,g.promote_price,g.promote_start_date,g.promote_end_date FROM  ".$GLOBALS['ecs']->table('goods')." AS g WHERE is_delete = '0' AND is_on_sale = '1' and g.goods_number >0 AND  g.goods_id IN ($goods_id_arr)";

	$row = $GLOBALS['db'] -> getAll($sql);
	
	$user_rank_name=get_rank_info($user_id);
	$time=time();
	foreach ($row  as $k=>$value) {
		$user_rank_prices=get_user_rank_prices($value['goods_id'],$value['shop_price'],$user_id);
		foreach ($user_rank_prices  as $rank_prices_value) {
			if($user_rank_name['rank_name']==$rank_prices_value['rank_name']){
			if($value['is_promote']==1){
				
				//if($value['promote_price']<$rank_prices_value['price']&&$value['promote_start_date']<=$time&&$value['promote_end_date']>$time){
					//$row[$k]['shop_price']=str_replace('¥','',$value['promote_price']);
				//}else{
					$row[$k]['shop_price']=str_replace('¥','',$rank_prices_value['price']);
				//}
				
			}else{
				$row[$k]['shop_price']=str_replace('¥','',$rank_prices_value['price']);
			}
				
			}
		}
		
	}
	print_r(json_encode($row));
	
/*=====================================ecshop的一些函数方法======================================*/
	

	
	
/**
 * 获得指定商品的各会员等级对应的价格
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  array
 */
function get_user_rank_prices($goods_id, $shop_price,$user_id)
{

	$user_rank = $GLOBALS['db']->getOne("SELECT user_rank FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '$user_id'");
    $sql = "SELECT rank_id, IFNULL(mp.user_price, r.discount * $shop_price / 100) AS price, r.rank_name, r.discount " .
            'FROM ' . $GLOBALS['ecs']->table('user_rank') . ' AS r ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . " AS mp ".
                "ON mp.goods_id = '$goods_id' AND mp.user_rank = r.rank_id " .
            "WHERE r.show_price = 1 OR r.rank_id = '$user_rank'";
    $res = $GLOBALS['db']->query($sql);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {

        $arr[] = array(
                        'rank_name' => htmlspecialchars($row['rank_name']),
                        'price'     => price_format($row['price']));
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
	
	
	
	
	
	

	
?>

