<?php

/**
 * 商品列表
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$cat=$_REQUEST['cat_id'];
	
	
	if(isset($_GET['brand'])){$brand=$_GET['brand'];}else{$brand="";}
	$page=$_GET['page']*10;
	$field=nl2br(htmlspecialchars($_GET['field']));
	$order=$_GET['order'];
	  // 获得分类的相关信息
	$sql = "SELECT * FROM " .$ecs->table('category'). " WHERE cat_id='$cat' LIMIT 1";
    $cat_info=$db->getRow($sql);
	$cat_goods_ids=0;
	if(!empty($cat)){
		if($cat=="undefined"){
			$cat=0;
		}
		$sql = "SELECT * FROM " .$ecs->table('category'). " WHERE parent_id='$cat' ";
		$cat_List=$db->getAll($sql);
		for($i=0;$i<count($cat_List);$i++){
			$cat.=",".$cat_List[$i]['cat_id'];
			$cat_id1=$cat_List[$i]['cat_id'];
			$sql = "SELECT * FROM " .$ecs->table('category'). " WHERE parent_id ='$cat_id1' ";
			$cat_List2=$db->getAll($sql);
			for($j=0;$j<count($cat_List2);$j++){
				$cat.=",".$cat_List2[$j]['cat_id'];
			}
		}
		
		$sql = "SELECT goods_id FROM " .$ecs->table('goods_cat'). " WHERE cat_id IN ($cat) ";
		$cat_goods_ids_arr=$db->getAll($sql);
		for($i=0;$i<count($cat_goods_ids_arr);$i++){
			$cat_goods_ids=$cat_goods_ids.",".$cat_goods_ids_arr[$i]['goods_id'];
		}
	}	
		if(isset($_GET['is_promote'])&&(!empty($_GET['is_promote']))){//促销列表
		$timeVal=time();
		if($field=="goods_number"){
			$sql="SELECT g.goods_id,g.goods_name,g.shop_price,g.goods_thumb,g.promote_price,g.is_promote,promote_end_date,promote_start_date,g.is_hot,g.is_new,g.is_best,g.click_count,SUM( og.goods_number ) AS goods_number FROM  ".$ecs->table('goods')." AS g,".$ecs->table(order_goods)." AS og WHERE is_delete = '0' AND is_on_sale = '1' AND g.goods_number >0 AND og.goods_id = g.goods_id AND g.is_promote = '1' AND g.promote_end_date>='$timeVal' AND g.promote_start_date<='$timeVal' order by goods_number DESC, g.shop_price $order   LIMIT $page,10 ";
		}else{
			$sql="SELECT g.add_time,g.goods_id,g.goods_name,g.shop_price,g.goods_thumb,g.promote_price,g.is_promote,promote_end_date,promote_start_date,g.is_hot,g.is_new,g.is_best,g.click_count FROM  ".$ecs->table('goods')." AS g WHERE is_delete = '0' AND is_on_sale = '1' AND g.goods_number >0 AND is_promote = '1' AND g.promote_end_date>='$timeVal' AND g.promote_start_date<='$timeVal'  order by   g.$field $order  LIMIT $page,10 ";
			
		}
		
		$res = $db -> getAll($sql);
		for($i=0;$i<count($res);$i++){
			$res[$i]['add_time']=date('Y-m-d h:m',$res[$i]['add_time']);
		}
		
		print_r(json_encode($res));
		exit();
		
		}
	
	
	if(isset($_GET['filter_attr'])&&(!empty($_GET['filter_attr']))){
		$filter_attr_str=$_GET['filter_attr'];
		$filter_attr = empty($filter_attr_str) ? '' : explode('.', $filter_attr_str);
		$cat_filter_attr = explode(',', $cat_info['filter_attr']);       //提取出此分类的筛选属性
		$ext_sql = "SELECT DISTINCT(b.goods_id) FROM " . $ecs->table('goods_attr') . " AS a, " . $ecs->table('goods_attr') . " AS b " .  "WHERE ";
		$ext="";
		foreach ($filter_attr AS $k => $v){
			if (is_numeric($v) && $v !=0 &&isset($cat_filter_attr[$k]))
                {
                    $sql = $ext_sql . "b.attr_value = a.attr_value AND b.attr_id = " . $cat_filter_attr[$k] ." AND a.goods_attr_id = " . $v;
                    $ext_group_goods = $db->getColCached($sql);
					$ext .= ' AND ' . db_create_in($ext_group_goods, 'g.goods_id');
                }
		}
		if($field=="goods_number"){
			if(!empty($brand)){
				$sql="SELECT g.goods_id,g.goods_name,g.shop_price,g.goods_thumb,g.promote_price,g.is_promote,promote_end_date,promote_start_date,g.is_hot,g.is_new,g.is_best,g.click_count,SUM( og.goods_number ) AS goods_number FROM  ".$ecs->table('goods')." AS g LEFT JOIN ".$ecs->table('order_goods')." AS og ON is_delete = '0' AND is_on_sale = '1' AND g.brand_id='$brand' AND og.goods_id = g.goods_id AND (g.cat_id IN ($cat) OR g.goods_id IN ($cat_goods_ids) ) ".$ext." GROUP BY g.goods_id order by goods_number DESC , g.shop_price $order   LIMIT $page,10";

			}else{
				$sql="SELECT g.goods_id,g.goods_name,g.shop_price,g.goods_thumb,g.promote_price,g.is_promote,promote_end_date,promote_start_date,g.is_hot,g.is_new,g.is_best,g.click_count,SUM( og.goods_number ) AS goods_number FROM  ".$ecs->table('goods')."  AS g LEFT JOIN ".$ecs->table('order_goods')." AS og ON is_delete = '0' AND is_on_sale = '1' AND og.goods_id = g.goods_id AND (g.cat_id IN ($cat) OR g.goods_id IN ($cat_goods_ids) ) ".$ext." GROUP BY g.goods_id order by goods_number DESC , g.shop_price $order   LIMIT $page,10";
			} 
		}else{
			if(!empty($brand)){
				$sql="SELECT g.goods_id,g.goods_name,g.shop_price,g.goods_thumb,g.promote_price,g.is_promote,promote_end_date,promote_start_date,g.is_hot,g.is_new,g.is_best,g.click_count FROM  ".$ecs->table('goods')." AS g WHERE is_delete = '0' AND is_on_sale = '1' AND g.brand_id='$brand' AND (g.cat_id IN ($cat) OR g.goods_id IN ($cat_goods_ids) )   ".$ext." order by goods_number DESC, g.shop_price $order   LIMIT $page,10";

			}else{
				$sql="SELECT g.goods_id,g.goods_name,g.shop_price,g.goods_thumb,g.promote_price,g.is_promote,promote_end_date,promote_start_date,g.is_hot,g.is_new,g.is_best,g.click_count FROM  ".$ecs->table('goods')." AS g WHERE is_delete = '0' AND is_on_sale = '1' AND (g.cat_id IN ($cat) OR g.goods_id IN ($cat_goods_ids) ) ".$ext." order by goods_number DESC, g.shop_price $order   LIMIT $page,10";
			}
		}
		
		
		$res = $db -> getAll($sql);
		if($field=="goods_number"){
		for($i=0;$i<count($res);$i++){
			for($i=0;$i<count($res);$i++){
			if(empty($res[$i]['goods_number'])){
				$res[$i]['goods_number']=0;
				}
			}
		}
		}
		print_r(json_encode($res));
		exit();
	}
	if($field=="goods_number"){
		if(!empty($cat)){
			if(	$cat==0){
					$sql="SELECT g.goods_id,g.goods_name,g.goods_brief,g.shop_price,g.goods_thumb,g.promote_price,g.is_promote,promote_end_date,promote_start_date,g.is_hot,g.is_new,g.is_best,g.click_count,SUM( og.goods_number ) AS goods_number 
					FROM  ".$ecs->table('goods')." AS g LEFT JOIN ".$ecs->table('order_goods')." AS og 
					ON  og.goods_id = g.goods_id WHERE g.is_delete = '0' AND g.is_on_sale = '1' 
					GROUP BY g.goods_id order by goods_number DESC , g.shop_price $order   LIMIT $page,10 ";
			}else{
				$sql="SELECT g.goods_id,g.goods_name,g.goods_brief,g.shop_price,g.goods_thumb,g.promote_price,g.is_promote,promote_end_date,promote_start_date,g.is_hot,g.is_new,g.is_best,g.click_count,SUM( og.goods_number ) AS goods_number 
				FROM  ".$ecs->table('goods')." AS g LEFT JOIN ".$ecs->table('order_goods')." AS og ON  og.goods_id = g.goods_id where g.is_delete = '0' AND g.is_on_sale = '1' AND (g.cat_id IN ($cat) OR g.goods_id IN ($cat_goods_ids) )  GROUP BY g.goods_id order by  goods_number DESC, g.shop_price $order   LIMIT $page,10";
			}
		}
		if(!empty($brand)){
			$sql="SELECT g.goods_id,g.goods_name,g.goods_brief,g.shop_price,g.goods_thumb,g.promote_price,g.is_promote,promote_end_date,promote_start_date,g.is_hot,g.is_new,g.is_best,g.click_count,SUM( og.goods_number ) AS goods_number 
			FROM  ".$ecs->table('goods')." AS g  LEFT JOIN ".$ecs->table('order_goods')." AS og ON  og.goods_id = g.goods_id where g.is_delete = '0' AND g.is_on_sale = '1' AND g.brand_id='$brand' AND  GROUP BY g.goods_id order by goods_number DESC , g.shop_price $order   LIMIT $page,10";

		}
		$res = $db -> getAll($sql);
		if(empty($res[0]['goods_id'])&&count($res)==1){
			$result=array();
			print_r(json_encode($result));
			exit();
		}else{
			for($i=0;$i<count($res);$i++){
			if(empty($res[$i]['goods_number'])){
				$res[$i]['goods_number']=0;
				}
			}
			print_r(json_encode($res));
			exit();
		}
	}else{
		if(!empty($cat)){
			if(	$cat==0){
					$sql="SELECT g.add_time,g.goods_id,g.goods_name,g.goods_brief,g.shop_price,g.goods_thumb,g.promote_price,g.is_promote,promote_end_date,promote_start_date,g.is_hot,g.is_new,g.is_best,g.click_count FROM  ".$ecs->table('goods')." AS g WHERE is_delete = '0' AND is_on_sale = '1' order by   g.$field $order  LIMIT $page,10 ";
			}else{
				$sql="SELECT g.add_time,g.goods_id,g.goods_name,g.goods_brief,g.shop_price,g.goods_thumb,g.promote_price,g.is_promote,promote_end_date,promote_start_date,g.is_hot,g.is_new,g.is_best,g.click_count FROM  ".$ecs->table('goods')." AS g WHERE is_delete = '0' AND is_on_sale = '1' AND (g.cat_id IN ($cat) OR g.goods_id IN ($cat_goods_ids) ) order by  g.$field $order   LIMIT $page,10";
			}
		}
		if(!empty($brand)){
			$sql="SELECT g.add_time,g.goods_id,g.goods_name,g.goods_brief,g.shop_price,g.goods_thumb,g.promote_price,g.is_promote,promote_end_date,promote_start_date,g.is_hot,g.is_new,g.is_best,g.click_count FROM  ".$ecs->table('goods')." AS g WHERE is_delete = '0' AND is_on_sale = '1' AND g.brand_id='$brand' order by g.$field $order   LIMIT $page,10";
		}
	}
	
	
	$res = $db -> getAll($sql);
	for($i=0;$i<count($res);$i++){
		$res[$i]['add_time']=date('Y-m-d h:m',$res[$i]['add_time']);
	}
	
	print_r(json_encode($res));
	
	

?>