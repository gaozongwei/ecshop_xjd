<?php
/*
  *
  *专题
  *
  *
  *
 */
	define('IN_ECS', true);
	require('../includes/init.php');
	include('../includes/cls_json.php');
	$json   = new JSON;



	$topic_id = $_GET['topic_id'];
	
if($topic_id == '99999901'){


	$search = " and g.is_hot = 1";
	$title='热销排行';


    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, " .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img, ' .
                '(select count(*) from '.$GLOBALS['ecs']->table('order_goods').' og WHERE og.goods_id =g.goods_id ) AS sale_num '.
                'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
                'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
                "WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 $search  order by sale_num desc limit 20 ";

    $res = $GLOBALS['db']->query($sql);

    $sort_goods_arr = array();

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['promote_price'] > 0)
        {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            $row['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
        }
        else
        {
            $row['promote_price'] = '';
        }

        if ($row['shop_price'] > 0)
        {
            $row['shop_price'] =  price_format($row['shop_price']);
        }
        else
        {
            $row['shop_price'] = '';
        }

        $row['url']              = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
        $row['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
        $row['short_name']       = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                                    sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        $row['goods_thumb']      = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $row['short_style_name'] = add_style($row['short_name'], $row['goods_name_style']);
	$row['volume'] = $row['sale_num'];
       
    
	$sort_goods_arr['0'][] = $row;
    }
	$sort_goods_arr['title'] = $title;
	print_r(json_encode($sort_goods_arr));
	
	die;
}
	 $sql = "SELECT * FROM " . $ecs->table('topic') . " WHERE topic_id = '$topic_id'";

    $topic = $db->getRow($sql);
	
    $topic['data'] = addcslashes($topic['data'], "'");
    $tmp = @unserialize($topic["data"]);
    $arr = (array)$tmp;

    $goods_id = array();
	
    foreach ($arr AS $key=>$value)
    {
        foreach($value AS $k => $val)
        {
            $opt = explode('|', $val);
            $arr[$key][$k] = $opt[1];
            $goods_id[] = $opt[1];
        }
    }

    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, " .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img ' .
                'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
                'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
                "WHERE " . db_create_in($goods_id, 'g.goods_id');

    $res = $GLOBALS['db']->query($sql);

    $sort_goods_arr = array();

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['promote_price'] > 0)
        {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            $row['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
        }
        else
        {
            $row['promote_price'] = '';
        }

        if ($row['shop_price'] > 0)
        {
            $row['shop_price'] =  price_format($row['shop_price']);
        }
        else
        {
            $row['shop_price'] = '';
        }

        $row['url']              = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
        $row['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
        $row['short_name']       = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                                    sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        $row['goods_thumb']      = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $row['short_style_name'] = add_style($row['short_name'], $row['goods_name_style']);
		$sql = "select count(*) from ".$GLOBALS['ecs']->table('order_goods')." WHERE goods_id = ".$row['goods_id'];
		$row['volume'] = $GLOBALS['db']->getOne($sql);
        foreach ($arr AS $key => $value)
        {
            foreach ($value AS $val)
            {
                if ($val == $row['goods_id'])
                {
                   // $key = $key == 'default' ? 0 : $key;
                    $sort_goods_arr['0'][] = $row;
                }
            }
        }
    }
	$sort_goods_arr['title'] = $topic['title'];//专题标题
	$sort_goods_arr['topic_img'] = $topic['topic_img'];//专题图片
	$sort_goods_arr['description'] = $topic['description'];//专题描述
	print_r(json_encode($sort_goods_arr));
?>