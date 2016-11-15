<?php

/**
 * ECSHOP 拍卖前台文件
 * ============================================================================
 * 版权所有 2005-2011 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: auction.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

/*------------------------------------------------------ */
//-- act 操作项的初始化
/*------------------------------------------------------ */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}

/*------------------------------------------------------ */
//-- 拍卖活动列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 取得拍卖活动总数 */
    $count = auction_count();
    if ($count > 0)
    {
        /* 取得每页记录数 */
        $size = isset($_CFG['page_size']) && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;

        /* 计算总页数 */
        $page_count = ceil($count / $size);

        /* 取得当前页 */
        $page = isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0 ? intval($_REQUEST['page']) : 1;
        $page = $page > $page_count ? $page_count : $page;

        /* 缓存id：语言 - 每页记录数 - 当前页 */
        $cache_id = $_CFG['lang'] . '-' . $size . '-' . $page;
        $cache_id = sprintf('%X', crc32($cache_id));
    }
    else
    {
        /* 缓存id：语言 */
        $cache_id = $_CFG['lang'];
        $cache_id = sprintf('%X', crc32($cache_id));
    }

    /* 如果没有缓存，生成缓存 */
    if (!$smarty->is_cached('auction_list.dwt', $cache_id))
    {
        if ($count > 0)
        {
            /* 取得当前页的拍卖活动 */
            $auction_list = auction_list($size, $page);
            $auction_list_hot = auction_list($size, $page, "act_count");
            $smarty->assign('auction_list',      $auction_list);
            $smarty->assign('auction_list_hot',  $auction_list_hot);

            /* 设置分页链接 */
            $pager = get_pager('auction.php', array('act' => 'list'), $count, $page, $size);
            $smarty->assign('pager', $pager);
        }

        /* 模板赋值 */
        $smarty->assign('cfg', $_CFG);
        assign_template();
        $position = assign_ur_here();
        $smarty->assign('page_title', $position['title']);    // 页面标题
        $smarty->assign('ur_here',    $position['ur_here']);  // 当前位置
        $smarty->assign('categories', get_categories_tree()); // 分类树
        $smarty->assign('helps',      get_shop_help());       // 网店帮助
        $smarty->assign('top_goods',  get_top10());           // 销售排行
        $smarty->assign('promotion_info', get_promotion_info());
        $smarty->assign('feed_url',         ($_CFG['rewrite'] == 1) ? "feed-typeauction.xml" : 'feed.php?type=auction'); // RSS URL

        assign_dynamic('auction_list');
    }

    /* 显示模板 */
    $smarty->display('auction_list.dwt', $cache_id);
}

/*------------------------------------------------------ */
//-- 拍卖商品 --> 商品详情
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'view')
{
    /* 取得参数：拍卖活动id */
    $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    if ($id <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 取得拍卖活动信息 */
    $auction = auction_info($id);
    if (empty($auction))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 缓存id：语言，拍卖活动id，状态，如果是进行中，还要最后出价的时间（如果有的话） */
    $cache_id = $_CFG['lang'] . '-' . $id . '-' . $auction['status_no'];
    if ($auction['status_no'] == UNDER_WAY)
    {
        if (isset($auction['last_bid']))
        {
            $cache_id = $cache_id . '-' . $auction['last_bid']['bid_time'];
        }
    }
    elseif ($auction['status_no'] == FINISHED && $auction['last_bid']['bid_user'] == $_SESSION['user_id']
        && $auction['order_count'] == 0)
    {
        $auction['is_winner'] = 1;
        $cache_id = $cache_id . '-' . $auction['last_bid']['bid_time'] . '-1';
		
		$sql_order = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE extension_id = " . $id . " AND order_status NOT IN (2,3)";
		if ($GLOBALS['db']->getOne($sql_order) > 0)
		{
			$auction['is_winner_ok'] = 1;			
		}
    }

    $cache_id = sprintf('%X', crc32($cache_id));

    /* 如果没有缓存，生成缓存 */
    if (!$smarty->is_cached('auction.dwt', $cache_id))
    {
        //取货品信息
        if ($auction['product_id'] > 0)
        {
            $goods_specifications = get_specifications_list($auction['goods_id']);

            $good_products = get_good_products($auction['goods_id'], 'AND product_id = ' . $auction['product_id']);

            $_good_products = explode('|', $good_products[0]['goods_attr']);
            $products_info = '';
            foreach ($_good_products as $value)
            {
                $products_info .= ' ' . $goods_specifications[$value]['attr_name'] . '：' . $goods_specifications[$value]['attr_value'];
            }
            $smarty->assign('products_info',     $products_info);
            unset($goods_specifications, $good_products, $_good_products,  $products_info);
        }

        $auction['gmt_end_time'] = local_strtotime($auction['end_time']);
        $smarty->assign('auction', $auction);

        /* 取得拍卖商品信息 */
        $goods_id = $auction['goods_id'];
        $goods = goods_info($goods_id);
        if (empty($goods))
        {
            ecs_header("Location: ./\n");
            exit;
        }
        $goods['url'] = build_uri('goods', array('gid' => $goods_id), $goods['goods_name']);
        $smarty->assign('auction_goods', $goods);

        /* 出价记录 */
        $smarty->assign('auction_log', auction_log($id));

        //模板赋值
		$smarty->assign('auction_log_count', auction_log_count($id));
        $smarty->assign('cfg', $_CFG);
        assign_template();

        $position = assign_ur_here(0, $goods['goods_name']);
        $smarty->assign('page_title', $position['title']);    // 页面标题
        $smarty->assign('ur_here',    $position['ur_here']);  // 当前位置

        $smarty->assign('categories', get_categories_tree()); // 分类树
        $smarty->assign('helps',      get_shop_help());       // 网店帮助
        $smarty->assign('top_goods',  get_top10());           // 销售排行
        $smarty->assign('promotion_info', get_promotion_info());

        assign_dynamic('auction');
    }
    /* 代码增加_start  By  www.68ecshop.com */
    $goods['supplier_name'] ="网站自营";
    if ($goods['supplier_id'] > 0)
    {
        $sql_supplier = "SELECT s.supplier_id,s.supplier_name,s.add_time,sr.rank_name FROM ". $ecs->table("supplier") . " as s left join ". $ecs->table("supplier_rank") ." as sr ON s.rank_id=sr.rank_id
 WHERE s.supplier_id=".$goods[supplier_id]." AND s.status=1";
        $shopuserinfo = $db->getRow($sql_supplier);
        $goods['supplier_name']= $shopuserinfo['supplier_name'];
        get_dianpu_baseinfo($goods['supplier_id'],$shopuserinfo);
    }
    /* 代码增加_end  By  www.68ecshop.com */

    //更新商品点击次数
    $sql = 'UPDATE ' . $ecs->table('goods') . ' SET click_count = click_count + 1 '.
           "WHERE goods_id = '" . $auction['goods_id'] . "'";
    $db->query($sql);

    $smarty->assign('now_time',  gmtime());           // 当前系统时间
    $smarty->display('auction.dwt', $cache_id);
}

/*------------------------------------------------------ */
//-- 拍卖商品 --> 出价
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'bid')
{
    include_once(ROOT_PATH . 'includes/lib_order.php');

    /* 取得参数：拍卖活动id */
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($id <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 取得拍卖活动信息 */
    $auction = auction_info($id);
    if (empty($auction))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 活动是否正在进行 */
    if ($auction['status_no'] != UNDER_WAY)
    {
        show_message($_LANG['au_not_under_way'], '', '', 'error');
    }

    /* 是否登录 */
    $user_id = $_SESSION['user_id'];
    if ($user_id <= 0)
    {
        show_message($_LANG['au_bid_after_login']);
    }
    $user = user_info($user_id);

    /* 取得出价 */
    $bid_price = isset($_POST['price']) ? round(floatval($_POST['price']), 2) : 0;
    if ($bid_price <= 0)
    {
        show_message($_LANG['au_bid_price_error'], '', '', 'error');
    }

    /* 如果有一口价且出价大于等于一口价，则按一口价算 */
    $is_ok = false; // 出价是否ok
    if ($auction['end_price'] > 0)
    {
        if ($bid_price >= $auction['end_price'])
        {
            $bid_price = $auction['end_price'];
            $is_ok = true;
        }
    }

    /* 出价是否有效：区分第一次和非第一次 */
    if (!$is_ok)
    {
        if ($auction['bid_user_count'] == 0)
        {
            /* 第一次要大于等于起拍价 */
            $min_price = $auction['start_price'];
        }
        else
        {
            /* 非第一次出价要大于等于最高价加上加价幅度，但不能超过一口价 */
            $min_price = $auction['last_bid']['bid_price'] + $auction['amplitude'];
            if ($auction['end_price'] > 0)
            {
                $min_price = min($min_price, $auction['end_price']);
            }
        }

        if ($bid_price < $min_price)
        {
            show_message(sprintf($_LANG['au_your_lowest_price'], price_format($min_price, false)), '', '', 'error');
        }
    }

    /* 检查联系两次拍卖人是否相同 */
    if ($auction['last_bid']['bid_user'] == $user_id && $bid_price != $auction['end_price'])
    {
        show_message($_LANG['au_bid_repeat_user'], '', '', 'error');
    }

    /* 是否需要保证金 */
    if ($auction['deposit'] > 0)
    {
        /* 可用资金够吗 */
        if ($user['user_money'] < $auction['deposit'])
        {
            show_message($_LANG['au_user_money_short'], '', '', 'error');
        }

        /* 如果不是第一个出价，解冻上一个用户的保证金 */
        if ($auction['bid_user_count'] > 0)
        {
            log_account_change($auction['last_bid']['bid_user'], $auction['deposit'], (-1) * $auction['deposit'],
                0, 0, sprintf($_LANG['au_unfreeze_deposit'], $auction['act_name']));
        }

        /* 冻结当前用户的保证金 */
        log_account_change($user_id, (-1) * $auction['deposit'], $auction['deposit'],
            0, 0, sprintf($_LANG['au_freeze_deposit'], $auction['act_name']));
    }

    /* 插入出价记录 */
    $auction_log = array(
        'act_id'    => $id,
        'bid_user'  => $user_id,
        'bid_price' => $bid_price,
        'bid_time'  => gmtime()
    );
    $db->autoExecute($ecs->table('auction_log'), $auction_log, 'INSERT');
	$act_count = $_POST['act_count'] + 1;
	$db->query("UPDATE " . $ecs->table('goods_activity') . " SET act_count = " . $act_count . " WHERE act_id = " . $id);

    /* 出价是否等于一口价 */
    if ($bid_price == $auction['end_price'])
    {
        /* 结束拍卖活动 */
        $sql = "UPDATE " . $ecs->table('goods_activity') . " SET is_finished = 1 WHERE act_id = '$id' LIMIT 1";
        $db->query($sql);
    }

    /* 跳转到活动详情页 */
    ecs_header("Location: auction.php?act=view&id=$id\n");
    exit;
}

/*------------------------------------------------------ */
//-- 拍卖商品 --> 购买
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'buy')
{
    /* 查询：取得参数：拍卖活动id */
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($id <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 查询：取得拍卖活动信息 */
    $auction = auction_info($id);
    if (empty($auction))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 查询：活动是否已结束 */
    if ($auction['status_no'] != FINISHED)
    {
        show_message($_LANG['au_not_finished'], '', '', 'error');
    }

    /* 查询：有人出价吗 */
    if ($auction['bid_user_count'] <= 0)
    {
        show_message($_LANG['au_no_bid'], '', '', 'error');
    }

    /* 查询：是否已经有订单 */
    if ($auction['order_count'] > 0)
    {
        show_message($_LANG['au_order_placed']);
    }

    /* 查询：是否登录 */
    $user_id = $_SESSION['user_id'];
    if ($user_id <= 0)
    {
        show_message($_LANG['au_buy_after_login']);
    }

    /* 查询：最后出价的是该用户吗 */
    if ($auction['last_bid']['bid_user'] != $user_id)
    {
        show_message($_LANG['au_final_bid_not_you'], '', '', 'error');
    }

    /* 查询：取得商品信息 */
    $goods = goods_info($auction['goods_id']);

    /* 查询：处理规格属性 */
    $goods_attr = '';
    $goods_attr_id = '';
    if ($auction['product_id'] > 0)
    {
        $product_info = get_good_products($auction['goods_id'], 'AND product_id = ' . $auction['product_id']);

        $goods_attr_id = str_replace('|', ',', $product_info[0]['goods_attr']);

        $attr_list = array();
        $sql = "SELECT a.attr_name, g.attr_value " .
                "FROM " . $ecs->table('goods_attr') . " AS g, " .
                    $ecs->table('attribute') . " AS a " .
                "WHERE g.attr_id = a.attr_id " .
                "AND g.goods_attr_id " . db_create_in($goods_attr_id);
        $res = $db->query($sql);
        while ($row = $db->fetchRow($res))
        {
            $attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
        }
        $goods_attr = join(chr(13) . chr(10), $attr_list);
    }
    else
    {
        $auction['product_id'] = 0;
    }

    /* 清空购物车中所有拍卖商品 */
    include_once(ROOT_PATH . 'includes/lib_order.php');
    clear_cart(CART_AUCTION_GOODS);

    /* 加入购物车 */
     $cart = array(
        'user_id'        => $user_id,
        'session_id'     => SESS_ID,
        'goods_id'       => $auction['goods_id'],
        'goods_sn'       => addslashes($goods['goods_sn']),
        'goods_name'     => addslashes($goods['goods_name']),
        'market_price'   => $goods['market_price'],
    	'cost_price'     => $goods['cost_price'],
    	'promote_price'  => $goods['promote_price'],
        'goods_price'    => $auction['last_bid']['bid_price'],
        'goods_number'   => 1,
        'goods_attr'     => $goods_attr,
        'goods_attr_id'  => $goods_attr_id,
        'is_real'        => $goods['is_real'],
        'extension_code' => addslashes($goods['extension_code']),
        'parent_id'      => 0,
        'rec_type'       => CART_AUCTION_GOODS,
        'is_gift'        => 0
    );
    $db->autoExecute($ecs->table('cart'), $cart, 'INSERT');
    
    $_SESSION['sel_cartgoods'] = $db->insert_id();
    /* 记录购物流程类型：团购 */
    $_SESSION['flow_type'] = CART_AUCTION_GOODS;
    $_SESSION['extension_code'] = 'auction';
    $_SESSION['extension_id'] = $id;

    /* 进入收货人页面 */
    ecs_header("Location: ./flow.php?step=checkout\n");
    exit;
}

/**
 * 取得拍卖活动数量
 * @return  int
 */
function auction_count()
{
    $now = gmtime();
    $sql = "SELECT COUNT(*) " .
            "FROM " . $GLOBALS['ecs']->table('goods_activity') .
            "WHERE act_type = '" . GAT_AUCTION . "' " .
            "AND start_time <= '$now' AND end_time >= '$now' AND is_finished < 2";

    return $GLOBALS['db']->getOne($sql);
}

/**
 * 取得某页的拍卖活动
 * @param   int     $size   每页记录数
 * @param   int     $page   当前页
 * @return  array
 */
function auction_list($size, $page)
{
    $auction_list = array();
    $auction_list['finished'] = $auction_list['finished'] = array();

    $now = gmtime();
    /* 代码修改 By  www.68ecshop.com Start */
//    $sql = "SELECT a.*,g.original_img, IFNULL(g.goods_thumb, '') AS goods_thumb " .
        $sql = "SELECT s.supplier_id,a.*,g.original_img, IFNULL(g.goods_thumb, '') AS goods_thumb, IFNULL(s.supplier_name, '平台自营') suppliername " .
    /* 代码修改 By  www.68ecshop.com End */
        "FROM " . $GLOBALS['ecs']->table('goods_activity') . " AS a " .
                "LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ON a.goods_id = g.goods_id " .
            /* 代码增加 By  www.68ecshop.com Start */
            'LEFT JOIN ' . $GLOBALS['ecs']->table('supplier') . ' s ON s.supplier_id = a.supplier_id ' .
            /* 代码增加 By  www.68ecshop.com End */
            "WHERE a.act_type = '" . GAT_AUCTION . "' " .
            /* 代码增加 By  www.68ecshop.com Start */
            " AND g.is_delete = 0 AND g.is_on_sale = 1 " .
            /* 代码增加 By  www.68ecshop.com End */
            "AND a.start_time <= '$now' AND a.end_time >= '$now' AND a.is_finished < 2 ORDER BY a.act_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $ext_info = unserialize($row['ext_info']);
        $auction = array_merge($row, $ext_info);
        $auction['status_no'] = auction_status($auction);

        $auction['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $auction['start_time']);
        $auction['end_time']   = local_date($GLOBALS['_CFG']['time_format'], $auction['end_time']);
        $auction['formated_start_price'] = price_format($auction['start_price']);
        $auction['formated_end_price'] = price_format($auction['end_price']);
        $auction['formated_deposit'] = price_format($auction['deposit']);
		
		$sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('auction_log') .
				" WHERE act_id = " . $auction['act_id'];
		$auction['bid_user_count'] = $GLOBALS['db']->getOne($sql);
		if ($auction['bid_user_count'] > 0)
		{
			$auction['formated_bid_price'] = $GLOBALS['db']->getOne("select bid_price from " . $GLOBALS['ecs']->table('auction_log') . " where act_id = '" . $auction['act_id'] . "' order by bid_price desc limit 0,1");
		}
		$auction['current_price'] = isset($auction['formated_bid_price']) ? $auction['formated_bid_price'] : $auction['start_price'];
		$auction['formated_current_price'] = price_format($auction['current_price'], false);
		
        $auction['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
		$auction['original_img']= get_image_path($row['goods_id'], $row['original_img']);
        $auction['url'] = build_uri('auction', array('auid'=>$auction['act_id']));
      
       

        if($auction['status_no'] < 2)
        {
            $auction_list['under_way'][] = $auction;
        }
        else
        {
            $auction_list['finished'][] = $auction;
        }
    }
   
    $auction_list = @array_merge($auction_list['under_way'], $auction_list['finished']);

    return $auction_list;
}

/* 代码增加_start  By  www.68ecshop.com */
/*
 * 获取商品所对应店铺的店铺基本信息
 * @param int $suppid 店铺id
 * @param int $suppinfo 入驻商的信息
 */
function get_dianpu_baseinfo($suppid=0,$suppinfo){
    if(intval($suppid) <= 0){
        return ;
    }
    global $smarty;
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('supplier_shop_config'). " WHERE supplier_id = " . $suppid;
    $shopinfo = $GLOBALS['db']->getAll($sql);

    $_goods_attr = array();
    foreach ($shopinfo as $value)
    {
        $_goods_attr[$value['code']] = $value['value'];
    }
    $sql1 = "SELECT AVG(comment_rank) FROM " . $GLOBALS['ecs']->table('comment') . " c" . " LEFT JOIN " . $GLOBALS['ecs']->table('order_info') . " o"." ON o.order_id = c.order_id"." WHERE c.status > 0 AND  o.supplier_id = " . $suppid;
    $avg_comment = $GLOBALS['db']->getOne($sql1);
    $avg_comment = round($avg_comment,1);

    $sql2 = "SELECT AVG(server), AVG(shipping) FROM " . $GLOBALS['ecs']->table('shop_grade') . " s" . " LEFT JOIN " . $GLOBALS['ecs']->table('order_info') . " o"." ON o.order_id = s.order_id"." WHERE s.is_comment > 0 AND  s.server >0 AND o.supplier_id = " . $suppid;
    $row = $GLOBALS['db']->getRow($sql2);

    $avg_server = round($row['AVG(server)'],1);
    $avg_shipping = round($row['AVG(shipping)'],1);

    $sql3 = " SELECT c.comment_rank,s.send,s.shipping FROM ".$GLOBALS['ecs']->table('shop_grade') ." AS s ".
        " LEFT JOIN ". $GLOBALS['ecs']->table('comment') ." AS c ON c.order_id = s.order_id " .
        " LEFT JOIN ". $GLOBALS['ecs']->table('order_info') ." AS o ON o.order_id = s.order_id".
        " WHERE s.is_comment >0 AND  s.server >0 AND o.supplier_id = " . $suppid;

    $h = $GLOBALS['db']->getAll($sql3);
    foreach($h as $key=>$value)
    {
        $count += array_sum($value);
    }

    $haoping = (($count/3)/count($h))/5*100;
    $haoping = round($haoping,1);

    $smarty->assign('ghs_css_path',        'themes/'.$_goods_attr['template'].'/images/ghs/css/ghs_style.css');//入驻商所选模板样式路径
    $shoplogo = empty($_goods_attr['shop_logo']) ? 'themes/'.$_goods_attr['template'].'/images/dianpu.jpg' : $_goods_attr['shop_logo'];
    $smarty->assign('shoplogo',        $shoplogo);//商家logo
    $smarty->assign('shopname',        htmlspecialchars($_goods_attr['shop_name']));//店铺名称
    $smarty->assign('suppid',        $suppinfo['supplier_id']);//商家名称
    $smarty->assign('suppliername',        htmlspecialchars($suppinfo['supplier_name']));//商家名称
    $smarty->assign('userrank',        htmlspecialchars($suppinfo['rank_name']));//商家等级
    $smarty->assign('region', get_province_city($_goods_attr['shop_province'],$_goods_attr['shop_city']));
    $smarty->assign('address', $_goods_attr['shop_address']);
    $qq = $GLOBALS['db']->getAll("SELECT cus_no FROM " . $GLOBALS['ecs']->table('chat_third_customer') . " WHERE is_master = 1 AND cus_type = 0 AND supplier_id = $suppid");
    $ww = $GLOBALS['db']->getAll("SELECT cus_no FROM " . $GLOBALS['ecs']->table('chat_third_customer') . " WHERE is_master = 1 AND cus_type = 1 AND supplier_id = $suppid");
    $arr_qq[] = array();
    $arr_ww = array();
    foreach ($qq as $v)
    {
        $arr_qq[] = $v['cus_no'];
    }
    foreach ($ww as $v)
    {
        $arr_ww[] = $v['cus_no'];
    }
    $smarty->assign('serviceqq', $arr_qq);
    $smarty->assign('serviceww', $arr_ww);
    $smarty->assign('serviceemail', $_goods_attr['service_email']);
    $smarty->assign('servicephone', $_goods_attr['service_phone']);
    $smarty->assign('createtime',      gmdate('Y-m-d',$suppinfo['add_time']));//商家创建时间
    $smarty->assign('c_rank', $avg_comment);
    $smarty->assign('serv_rank', $avg_server);
    $smarty->assign('shipp_rank', $avg_shipping);
    $smarty->assign('haoping', $haoping);
    $suppid = (intval($suppid)>0) ? intval($suppid) : intval($_GET['suppId']);
}
/* 代码增加_end  By  www.68ecshop.com */
?>