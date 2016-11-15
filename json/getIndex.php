<?php

/**
 * ��ҳ
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$result=array();

	$first=$_GET['first'];

	$field=$_GET['field'];

	if($first=="Y"){

		$article = $db -> getAll("SELECT article_id,title FROM  ".$ecs->table('article')." WHERE  cat_id= 2 AND is_open=1 order by article_id asc  LIMIT 0 , 5");

		$result['article']=$article;

		$banner = $db -> getAll("SELECT ad_name,ad_code,ad_link FROM  ".$ecs->table('ad')." WHERE position_id='112' and start_time<='".time()."' and end_time>='".time()."' LIMIT 0 , 5");

		$result['banner']=$banner;

			$result['topic_goods_tuijian'] = index_topic('tuijian');//推荐

		$adList1 = $db -> getAll("SELECT ad_name,ad_code,ad_link FROM  ".$ecs->table('ad')." WHERE position_id='113' and start_time<='".time()."' and end_time>='".time()."' order by ad_id asc LIMIT 0 , 4");

		$result['adList1']=$adList1;

		

		$adList2 = $db -> getAll("SELECT ad_name,ad_code,ad_link FROM  ".$ecs->table('ad')." WHERE position_id='114' and start_time<='".time()."' and end_time>='".time()."' order by ad_id asc LIMIT 0 , 3");

		$result['adList2']=$adList2;

	$adList3 = $db -> getAll("SELECT ad_name,ad_code,ad_link FROM  ".$ecs->table('ad')." WHERE position_id='115' and start_time<='".time()."' and end_time>='".time()."' order by ad_id asc LIMIT 0 , 4");
		$result['adList3']=$adList3;
		
		$adList4 = $db -> getAll("SELECT ad_name,ad_code,ad_link FROM  ".$ecs->table('ad')." WHERE position_id='116' and start_time<='".time()."' and end_time>='".time()."' order by ad_id asc LIMIT 0 , 3");
		$result['adList4']=$adList4;
	}
	

/*
	$banner = $db -> getAll("SELECT ad_name,ad_code,ad_link FROM  ".$ecs->table('ad')." WHERE position_id='100' and start_time<='".time()."' and end_time>='".time()."' LIMIT 0 , 5");
	$result['banner']=$banner;
*/
	//填写对应的商品分类id
	$cat_ids="3,5,6,7,11";
	$goods=array();
	$sql="SELECT cat_id,cat_name FROM  ".$ecs->table('category')." WHERE  cat_id IN (".$cat_ids.")";
	$cat_name_arr=array();
	foreach($db -> getAll($sql)as $k=>$v ){
		$cat_name_arr[$v['cat_id']]=$v['cat_name'];
	}
	
	$cat_ids=explode(',',$cat_ids);
	foreach($cat_ids as $v){
		$cat_id=get_children($v);
		$sql="SELECT goods_id,goods_name,shop_price,is_promote,promote_start_date,market_price,promote_end_date,promote_price,goods_thumb FROM  ".$ecs->table('goods')." as g  WHERE $cat_id AND is_delete = '0' AND is_on_sale = '1' order by add_time desc LIMIT 0,3 ";
		$goods[] = array('cat_name'=>$cat_name_arr[$v],'cat_id'=>$v,'list'=>$db -> getAll($sql));
	}
	$result['category']=$goods;
	//jx
	//app头部名称
	$result['app_title'] = 'ECSHOP开发中心';
	//楼层名称
	$result['app_lou1'] = '掌上秒杀';
	$result['app_lou2'] = '值得买';
	$result['app_lou3'] = '精品特惠';
	$result['app_lou4'] = '精品选购';
	$result['app_lou5'] = '新品上市';
	$result['app_lou6'] = '热卖商品';
	$result['app_lou7'] = '';
	$result['app_lou8'] = '';
	
	//分享
	$result['app_fenxiang'] = '分享内容';
	
	//关于我们的
	$result['app_id'] = '5';
	//地图
		//经纬度
		$result['app_J'] = '39.900715';
		$result['app_W'] = '119.538457';
		//企业名称
		$result['enterprisename'] = "ECSHOP";
		//企业简介
		$result['enterprise'] = "ECSHO行业“第一品牌”";
	
	//短信内容
	$result['app_more'] = "发送短信内容";
	/*
	 *修改九宫格跳转到指定的品牌
	 *
	 *brand("品牌的ID","品牌的名称");
	 *九宫格名称
	 *
	 *修改九宫格跳转到指定的商品类目
	 *
	 *category("类目的ID","类目的名称");
	 *九宫格名称
	 *
	 */
	//九宫格
		//第一行第一个
		$result['indexMenu1'] = "gourl('shop_list.html','店铺街')";
		$result['indexMenuName1'] = '店铺街';
		//第一行第二个
		$result['indexMenu2'] = "gourl('brand_list.html','商品品牌')";
		$result['indexMenuName2'] = '商品品牌';
		//第一行第三个
		$result['indexMenu3'] = "gourl('user.html','用户中心')";
		$result['indexMenuName3'] = "用户中心";
		//第一行第四个
		$result['indexMenu4'] = "gourl('article_cat.html','文章分类')";
		$result['indexMenuName4'] = "文章分类";
		//第二行第一个
		$result['indexMenu5'] = "gourl('goods_promote_list.html','促销列表')";
		$result['indexMenuName5'] = "促销列表";
		//第二行第二个
		$result['indexMenu6'] = "openPage('flow.html','购物车')";
		$result['indexMenuName6'] = "购物车";
		//第二行第三个
		$result['indexMenu7'] = "ShowMap()";
		$result['indexMenuName7'] = "地图";
		//第二行第四个
		$result['indexMenu8'] = "CallGeiveMe()";
		$result['indexMenuName8'] = "联系我们";
		
	
	$timeVal=time();
	$sql="SELECT goods_id,goods_name,shop_price,promote_price,market_price,goods_thumb,is_hot,is_new,is_best,is_promote,promote_end_date,promote_start_date,click_count FROM  ".$ecs->table('goods')."  WHERE is_promote='1' AND is_delete = '0' AND is_on_sale = '1' AND promote_end_date>='$timeVal'  order by sort_order,last_update desc LIMIT 0,9 ";
	$result['is_promote']=$db -> getAll($sql);
	
	$sql="SELECT goods_id,goods_name,shop_price,promote_price,market_price,goods_thumb,is_hot,is_new,is_best,is_promote,promote_end_date,promote_start_date,click_count FROM  ".$ecs->table('goods')."  WHERE is_new='1' AND is_delete = '0' AND is_on_sale = '1' order by sort_order,last_update desc LIMIT 0,9 ";
	$result['is_news']=$db -> getAll($sql);
	
	$sql="SELECT goods_id,goods_name,shop_price,promote_price,market_price,goods_thumb,is_hot,is_new,is_best,is_promote,promote_end_date,promote_start_date,click_count FROM  ".$ecs->table('goods')."  WHERE is_best='1' AND is_delete = '0' AND is_on_sale = '1' order by sort_order,last_update desc LIMIT 0,9 ";
	$result['is_best']=$db -> getAll($sql);
	
	$sql="SELECT goods_id,goods_name,shop_price,promote_price,market_price,goods_thumb,is_hot,is_new,is_best,is_promote,promote_end_date,promote_start_date,click_count FROM  ".$ecs->table('goods')."  WHERE is_hot='1' AND is_delete = '0' AND is_on_sale = '1' order by sort_order,last_update desc LIMIT 0,9 ";
	$result['is_hot']=$db -> getAll($sql);
	
	
	/*
	 *换组修改代码结束
	 *
	 *
	 */
	 
	 /*
	|===============================================================
	| 获取联系电话
	|===============================================================
	|
	|
	*/
	
	$sql="SELECT value FROM ".$ecs->table('shop_config')." WHERE id='115'";
	$shop_config=$db ->getRow($sql);
	$result['phone1'] = explode("-",$shop_config['value']);
	$result['phone'] = implode("",$result['phone1']);
	 
	print_r(json_encode($result));
function index_topic($cat)
{
	global $ecs,$db;
	$sql = "SELECT topic_id,title FROM " . $ecs->table('topic') ;
		


	$res = $GLOBALS['db']->query($sql);

	$topic_arr = array();
	while ($row = $GLOBALS['db']->fetchRow($res))
	{
		/*$row['index_img'] = $row['index_img'];
		$row['index_goods1_img'] = $row['index_goods1_img'];
		$row['index_goods2_img'] = $row['index_goods2_img'];
		$row['index_goods3_img'] = $row['index_goods3_img'];*/
		$topic_arr[] = array(
			'topic' => $row,
			//'goods_arr' => index_topic_goods($row['index_goods_id'])
		);

	}
	return $topic_arr;
}
function index_topic_goods($index_goods_id)
{
	global $ecs,$db;
	$buf = array();
	
	$ids = explode(",", $index_goods_id);
	$n_ids = array();
	foreach($ids AS $k => $v){
		$v = intval(trim($v));
		if(!$v)continue;
		$n_ids[] = $v;
	}
	$index_goods_id = implode(",", $n_ids);
	if(!$index_goods_id){
		return $buf;
	}

	$sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, " .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img ' .
                'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
                'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
                "WHERE g.goods_id in ($index_goods_id)";

	$res = $GLOBALS['db']->query($sql);


	while ($row = $GLOBALS['db']->fetchRow($res))
	{
		$row['market_price']  = price_format($row['market_price']);
		$row['shop_price']    = price_format($row['shop_price']);
		$row['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';

		$row['url']              = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
		$row['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
		$row['short_name']       = sub_str($row['goods_name'], 8);
		$row['short_name2']       = sub_str($row['goods_name'], 12);
		$row['goods_thumb']      = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$row['goods_img']      = get_image_path($row['goods_id'], $row['goods_thumb']);

		$row['wap_count']     = selled_wap_count($row['goods_id']);

		$row['short_style_name'] = add_style($row['short_name'], $row['goods_name_style']);
		if($display == 'grid')
		{
		    $row['goods_name']    = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		}
		else
		{
		    $row['goods_name'] = $row['goods_name'];
		}

		$buf[] = $row;
	}
	return $buf;
}


?>



