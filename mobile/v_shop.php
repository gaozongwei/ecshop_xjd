<?php


define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/lib_v_user.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

if($_CFG['is_distrib'] == 0)
{
	show_message('没有开启微信分销服务！','返回首页','index.php'); 
}

if(isset($_REQUEST['user_id']) && intval($_REQUEST['user_id']) > 0)
{
	$user_id = intval($_REQUEST['user_id']); 
}
else
{
	if(isset($_SESSION['user_id']) && intval($_SESSION['user_id']))
	{
		$user_id = intval($_SESSION['user_id']); 
	} 
	else
	{
		ecs_header("Location: ./\n");
    	exit; 
	}
}

if (!$smarty->is_cached('v_shop.dwt', $cache_id))
{
    assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置
	
    $smarty->assign('v_shop_img',get_wap_advlist('微分销微店轮播广告', 5));  //微店轮播幻灯广告
    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
	$smarty->assign('user_info',get_user_info_by_user_id($user_id)); 
	$smarty->assign('goods_count',get_distrib_goods_count($user_id));
	$smarty->assign('cat_list',get_cat($user_id));
	$smarty->assign('goods_list',get_all_distrib_goods($user_id));
	$smarty->assign('user_id',$user_id);
	$smarty->assign('dp_info',get_dianpu_by_user_id($user_id));
	$smarty->assign('user_id',$user_id);
    /* 页面中的动态内容 */
    assign_dynamic('v_shop');
}

$smarty->display('v_shop.dwt', $cache_id);

//获取分销商品数量
function get_distrib_goods_count($user_id)
{
	$sql = "SELECT cat_ids FROM " . 
	 		$GLOBALS['ecs']->table('on_sales') . " WHERE user_id = '$user_id'";
	$cat_ids = $GLOBALS['db']->getOne($sql);
	if($cat_ids)
	{
		$sql = "SELECT cat_id,cat_name,type_img FROM " . 
				$GLOBALS['ecs']->table('category') . " WHERE cat_id in ($cat_ids)";
		$list = $GLOBALS['db']->getAll($sql);
		$children = '';
		foreach($list as $key => $val)
		{
			if($key == 0)
			{
				$children .= get_children($val['cat_id']);
			}
			else
			{
				$children .= " OR " . get_children($val['cat_id']);
			}
		}
		$sql = "SELECT COUNT(*) FROM " . 
				$GLOBALS['ecs']->table('ecsmart_distrib_goods') . " as dg " .
				" LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " as g " .
				" ON dg.goods_id = g.goods_id " . 
				"WHERE g.is_on_sale = 1 AND " .
				"(dg.distrib_time = 0 OR (dg.start_time <='" . gmtime() . "' " . 
				"AND dg.end_time >= '" . gmtime() . "')) AND ($children)";
		return $GLOBALS['db']->getOne($sql);
	}
	else
	{
		return 0; 
	}
}

//获取上架分类下的商品
function get_all_distrib_goods($user_id)
{
	$sql = "SELECT cat_ids FROM " . 
	 		$GLOBALS['ecs']->table('on_sales') . " WHERE user_id = '$user_id'";
	$cat_ids = $GLOBALS['db']->getOne($sql);
	if($cat_ids)
	{
		$sql = "SELECT cat_id,cat_name,type_img FROM " . 
				$GLOBALS['ecs']->table('category') . " WHERE cat_id in ($cat_ids)";
		$list = $GLOBALS['db']->getAll($sql);
		$children = '';
		foreach($list as $key => $val)
		{
			if($key == 0)
			{
				$children .= get_children($val['cat_id']);
			}
			else
			{
				$children .= " OR " . get_children($val['cat_id']);
			}
		}
		$sql = "SELECT g.goods_id,g.goods_name,g.goods_thumb,g.shop_price FROM " . 
				$GLOBALS['ecs']->table('ecsmart_distrib_goods') . " as dg " .
				" LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " as g " .
				" ON dg.goods_id = g.goods_id " . 
				"WHERE g.is_on_sale = 1 AND " .
				"(dg.distrib_time = 0 OR (dg.start_time <='" . gmtime() . "' " . 
				"AND dg.end_time >= '" . gmtime() . "')) AND ($children)";
		$list = $GLOBALS['db']->getAll($sql);
		$arr = array();
		foreach($list as $key => $val)
		{
			$arr[$key]['goods_id'] = $val['goods_id'];
			$arr[$key]['goods_name'] = $val['goods_name'];
			$arr[$key]['goods_thumb'] = $val['goods_thumb'];
			$arr[$key]['shop_price'] = $val['shop_price'];
			$arr[$key]['market_price'] = $val['market_price'];
			$arr[$key]['wap_count'] = selled_wap_count($val['goods_id']);
		}
		return $arr;
	}
	else
	{
		return array(); 
	}
}

//获取微店上架商品分类
function get_cat($user_id)
{
	$sql = "SELECT cat_ids FROM " . 
	 		$GLOBALS['ecs']->table('on_sales') . " WHERE user_id = '$user_id'";
	$cat_ids = $GLOBALS['db']->getOne($sql);
	if($cat_ids)
	{
		$sql = "SELECT cat_id,cat_name,type_img FROM " . 
				$GLOBALS['ecs']->table('category') . " WHERE cat_id in ($cat_ids)";
		$list = $GLOBALS['db']->getAll($sql);
		$arr = array();
		foreach($list as $key => $val)
		{
			$arr[$key]['cat_id'] = $val['cat_id'];
			$arr[$key]['cat_name'] = $val['cat_name'];
			$arr[$key]['type_img'] = $val['type_img'];
		}
		return $arr;
	}
	else
	{
		return array(); 
	}
}

function get_wap_advlist( $position, $num )
{
		$arr = array( );
		$sql = "select ap.ad_width,ap.ad_height,ad.ad_id,ad.ad_name,ad.ad_code,ad.ad_link,ad.ad_id from ".$GLOBALS['ecs']->table( "ecsmart_ad_position" )." as ap left join ".$GLOBALS['ecs']->table( "ecsmart_ad" )." as ad on ad.position_id = ap.position_id where ap.position_name='".$position.( "' and UNIX_TIMESTAMP()>ad.start_time and UNIX_TIMESTAMP()<ad.end_time and ad.enabled=1 limit ".$num );
		$res = $GLOBALS['db']->getAll( $sql );
		foreach ( $res as $idx => $row )
		{
				$arr[$row['ad_id']]['name'] = $row['ad_name'];
				$arr[$row['ad_id']]['url'] = "affiche.php?ad_id=".$row['ad_id']."&uri=".$row['ad_link'];
				$arr[$row['ad_id']]['image'] = "data/afficheimg/".$row['ad_code'];
				$arr[$row['ad_id']]['content'] = "<a href='".$arr[$row['ad_id']]['url']."' target='_blank'><img src='data/afficheimg/".$row['ad_code']."' width='".$row['ad_width']."' height='".$row['ad_height']."' /></a>";
				$arr[$row['ad_id']]['ad_code'] = $row['ad_code'];
		}
		return $arr;
}
?>