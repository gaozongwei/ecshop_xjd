<?php

/* 载入语言文件 */
require_once('../languages/zh_cn/shopping_flow.php');
require_once('../languages/zh_cn/user.php');

$smarty->assign('lang',             $_LANG);

$cache_id = sprintf('%X', crc32('supplier_index_app-' .$_REQUEST['suppId']));
if (!$smarty->is_cached('supplier_index_app.lbi', $cache_id))
{
	 assign_template();
	assign_template_supplier();
	//分解首页三类商品的显示数量
    $index_goods_num[0] = 10;
    $index_goods_num[1] = 10;
    $index_goods_num[2] = 10;
    if(!empty($GLOBALS['_CFG']['shop_index_num'])){
    	$index_goods_info = explode("\r\n",$GLOBALS['_CFG']['shop_index_num']);
    	if(is_array($index_goods_info) && count($index_goods_info) >= 3){
    		$index_goods_num = $index_goods_info;
    	}
    }
    
    //1,2,3对应店铺商品分类中的精品,最新，热门
    $smarty->assign('best_goods',      get_supplier_goods(1,$index_goods_num[0]));    // 精品商品
    $smarty->assign('new_goods',       get_supplier_goods(2,$index_goods_num[1]));     // 最新商品
    $smarty->assign('hot_goods',       get_supplier_goods(3,$index_goods_num[2]));     // 热门商品
    /* 页面中的动态内容 */
    assign_dynamic('supplier_index_app.lbi');
}
$result['result'] = $smarty->fetch('supplier_index_app.lbi', $cache_id);
if(!empty($GLOBALS['_CFG']['service_phone'])){
	$result['call'] = "<span onclick='uexCall.dial(".$GLOBALS['_CFG']['service_phone'].");'>联系</span>";
}else{
	$result['call'] = "<span onclick=\"uexWindow.alert('提示','此店铺暂无电话！','确定');\">联系</span>";
}
	//jx  轮播图片正则匹配和获取
	$aa = $suppinfo['user_id'];
	$sql = "SELECT user_name FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id = '$aa'";
	$name = $GLOBALS['db']->getOne($sql);
	$name .=$suppinfo['supplier_id'];
	$content = @file_get_contents("../data/flashdata/$name/data.js");
	preg_match_all('/"\/.*[gif|jpg]"/i', $content, $aaa);
	$s = '';
	foreach($aaa as $key=>$value)
	{
		foreach($value as $ke=>$val)
		{
			$s[$ke] = trim($val,'"');
		}
	}
	$smarty->assign('lunbo',$s);
$result['lunbo'] = $smarty->fetch('lunbo.lbi');//返回轮播图片页面
//jx
$result['shopname'] = $GLOBALS['_CFG']['shop_name'];
$result['shoplogo'] = $GLOBALS['_CFG']['shop_logo'];

die($json->encode($result));


/*
 * 首页精品,最新，热门商品显示
 * @param int $gtype  三类商品的类型id值
 * @param int $limit  商品首页显示的数量   
 */
function get_supplier_goods($gtype=0,$limit=10){
	$gtype = intval($gtype);
	if($gtype <= 0){
		return ;
	}
	$sql = "SELECT DISTINCT g.goods_id,g.* FROM ". $GLOBALS['ecs']->table('goods') ." AS g, ". $GLOBALS['ecs']->table('supplier_goods_cat') ." AS gc, ". $GLOBALS['ecs']->table('supplier_cat_recommend') ." AS cr 
	WHERE cr.recommend_type =".$gtype." AND cr.supplier_id =".$_GET['suppId']." AND cr.cat_id = gc.cat_id AND gc.goods_id = g.goods_id 
	AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 
	ORDER BY g.sort_order, g.last_update DESC LIMIT ".$limit;
	
	$result = $GLOBALS['db']->getAll($sql);
	
	$goods = array();
	if($result){
		foreach ($result AS $idx => $row)
        {
            if ($row['promote_price'] > 0)
            {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                $goods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
            }
            else
            {
                $goods[$idx]['promote_price'] = '';
            }

            $goods[$idx]['id']           = $row['goods_id'];
            $goods[$idx]['name']         = $row['goods_name'];
            $goods[$idx]['brief']        = $row['goods_brief'];
            $goods[$idx]['brand_name']   = isset($goods_data['brand'][$row['goods_id']]) ? $goods_data['brand'][$row['goods_id']] : '';
            $goods[$idx]['goods_style_name']   = add_style($row['goods_name'],$row['goods_name_style']);

            $goods[$idx]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                                               sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $goods[$idx]['short_style_name']   = add_style($goods[$idx]['short_name'],$row['goods_name_style']);
            $goods[$idx]['market_price'] = price_format($row['market_price']);
            $goods[$idx]['shop_price']   = price_format($row['shop_price']);
            $goods[$idx]['thumb']        = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
            //$goods[$idx]['url']          = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
        }
	}
	
	return $goods;
	
}

?>