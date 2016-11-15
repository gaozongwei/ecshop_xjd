<?php

define('IN_ECS', true);

require('../includes/init.php');
include('../includes/cls_json.php');
$json   = new JSON;


$smarty->template_dir = ROOT_PATH . 'json/tpl';//app部分模板所在位置


/*
 * 获取购物车中的商品
 */
if (!empty($_REQUEST['act']) && $_REQUEST['act'] == 'txm')
{
	$page = isset($_REQUEST['page'])   && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
	$size = 6;//一页显示几个
	
    $res    = array('error' => 0, 'result' => '', 'message' => '');
    
    
    $txm = intval($_REQUEST['txm']);
    if($txm > 0){
    	
    	$sql = " select g.goods_id,g.goods_name,g.market_price,g.shop_price,g.promote_price,".
    	        " g.promote_start_date, g.promote_end_date,g.supplier_id,g.goods_thumb,g.goods_img, ifnull( ssc.value, '网站自营' ) AS shopname ".
    			'FROM ' . $GLOBALS['ecs']->table('bar_code') . ' AS bc ' .
            	'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
                "ON bc.goods_id = g.goods_id " .
    			'LEFT JOIN ' . $GLOBALS['ecs']->table('supplier_shop_config') . ' AS ssc ' .
    			"ON g.supplier_id = ssc.supplier_id AND ssc.code='shop_name'".
            	"WHERE bc.bar_code=".$txm." ORDER BY g.supplier_id LIMIT ".($page-1)*$size.",".$size;
    	//$res = $GLOBALS['db']->query($sql);
    	$arr = $GLOBALS['db']->getAll($sql);
    	foreach($arr as $key=>$row){
    		if (intval($row['promote_price']) > 0)
	        {
	            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
	        }
	        else
	        {
	            $promote_price = 0;
	        }
	        $arr[$key]['market_price']     = price_format($row['market_price']);
        	$arr[$key]['shop_price']       = price_format($row['shop_price']);
        	$arr[$key]['promote_price']    = (intval($promote_price) > 0) ? price_format($promote_price) : price_format($row['shop_price']);
        	$arr[$key]['goods_thumb']      = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        	$arr[$key]['goods_img']        = get_image_path($row['goods_id'], $row['goods_img']);
    	}
    	/*
    	$arr = array();
    	while ($row = $GLOBALS['db']->fetchRow($res))
    	{
	    	if ($row['promote_price'] > 0)
	        {
	            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
	        }
	        else
	        {
	            $promote_price = 0;
	        }
	        $row['market_price']     = price_format($row['market_price']);
        	$row['shop_price']       = price_format($row['shop_price']);
        	$row['promote_price']    = ($promote_price > 0) ? price_format($promote_price) : price_format($row['shop_price']);
        	$row['goods_thumb']      = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        	$row['goods_img']        = get_image_path($row['goods_id'], $row['goods_img']);
        	$arr[] = $row;
    	}*/
    	$smarty->assign('goods_list',$arr);
    	$res['result'] = $smarty->fetch('goodlist_app.lbi');
    }else{
    	$res['error'] = 1;
    }
	die($json->encode($res));
}
?>
