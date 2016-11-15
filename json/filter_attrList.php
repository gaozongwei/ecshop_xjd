<?php

/**
 * 筛选内容
*/
	define('IN_ECS', true);
	//require('includes/init.php');
	require('../includes/init.php');
	$result=array();
	$result2=array();
	$cat_id = isset($_REQUEST['cat_id'])  ? intval($_REQUEST['cat_id']) : 0;
	$children = get_children($cat_id);
	/* 品牌筛选 */
	
    $sql = "SELECT b.brand_id, b.brand_name, COUNT(*) AS goods_num ".
            "FROM " . $GLOBALS['ecs']->table('brand') . "AS b, ".
                $GLOBALS['ecs']->table('goods') . " AS g LEFT JOIN ". $GLOBALS['ecs']->table('goods_cat') . " AS gc ON g.goods_id = gc.goods_id " .
            "WHERE g.brand_id = b.brand_id AND ($children OR " . 'gc.cat_id ' . db_create_in(array_unique(array_merge(array($cat_id), array_keys(cat_list($cat_id, 0, false))))) . ") AND b.is_show = 1 " .
            " AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ".
            "GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY b.sort_order, b.brand_id ASC";
	
   $brands = $GLOBALS['db']->getAll($sql);
	
	
	
	//获取该分类的商品品牌
	/*
	$sql="SELECT b.brand_id, b.brand_name, COUNT(*) AS goods_num FROM ".$GLOBALS['ecs']->table('brand')." AS b, ".$GLOBALS['ecs']->table('goods')." AS g LEFT JOIN ".$GLOBALS['ecs']->table('goods_cat')." AS gc ON g.goods_id = gc.goods_id WHERE g.brand_id = b.brand_id AND (g.cat_id IN ('$cat_id') OR gc.cat_id IN ('$cat_id') ) AND b.is_show = 1 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 GROUP BY b.brand_id HAVING goods_num > 0 ORDER BY b.sort_order, b.brand_id ASC";
	$brands = $GLOBALS['db']->getAll($sql);
	*/
	$result['brands']=$brands;
	
	
	$filter_attr = $GLOBALS['db'] -> getRow("SELECT filter_attr FROM  ".$GLOBALS['ecs']->table('category')." WHERE cat_id='$cat_id'");
	
	//获取该分类的筛选信息
	if(!empty($filter_attr['filter_attr'])){
		$cat_filter_attr = explode(',', $filter_attr['filter_attr']);       //提取出此分类的筛选属性
		$all_attr_list = array();
		 foreach ($cat_filter_attr AS $key => $value)
		 {
			/*
		     $sql="SELECT a.attr_name FROM ".$GLOBALS['ecs']->table('attribute')." AS a, ".$GLOBALS['ecs']->table('goods_attr')." AS ga, ".$GLOBALS['ecs']->table('goods')." AS g WHERE (g.cat_id IN ('$cat_id') OR g.goods_id IN ('') ) AND a.attr_id = ga.attr_id AND g.goods_id = ga.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND a.attr_id='$value'";
			 */
			 $sql = "SELECT a.attr_name FROM " . $GLOBALS['ecs']->table('attribute') . " AS a, " . $GLOBALS['ecs']->table('goods_attr') . " AS ga, " . $GLOBALS['ecs']->table('goods') . " AS g WHERE ($children OR " . get_extension_goods($children) . ") AND a.attr_id = ga.attr_id AND g.goods_id = ga.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND a.attr_id='$value'";
			 
			 if($temp_name = $GLOBALS['db']->getOne($sql))
            {
				$all_attr_list[$key]['filter_attr_name'] = $temp_name;
				/*
				$sql="SELECT a.attr_id, MIN(a.goods_attr_id ) AS goods_attr_id, a.attr_value AS attr_value FROM ".$GLOBALS['ecs']->table('goods_attr')." AS a, ".$GLOBALS['ecs']->table('goods')." AS g WHERE (g.cat_id IN ('$cat_id') OR g.goods_id IN ('') ) AND g.goods_id = a.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND a.attr_id='$value' GROUP BY a.attr_value";
				*/
				$sql = "SELECT a.attr_id, MIN(a.goods_attr_id ) AS goods_id, a.attr_value AS attr_value FROM " . $ecs->table('goods_attr') . " AS a, " . $ecs->table('goods') .
                       " AS g" .
                       " WHERE ($children OR " . get_extension_goods($children) . ') AND g.goods_id = a.goods_id AND g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 '.
                       " AND a.attr_id='$value' ".
                       " GROUP BY a.attr_value";
				$attr_list = $GLOBALS['db']->getAll($sql);
				$all_attr_list[$key]['attr_list'] = $attr_list;
			}
		}
		$result['filter_attr']=$all_attr_list;
	}else{
		$result['filter_attr']=$result2;
	}
	

	print_r(json_encode($result));
	
	
?>

