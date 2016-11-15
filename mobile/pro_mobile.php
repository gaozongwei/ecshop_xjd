<?php

/**
 * ECSHOP 搜索程序
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: search.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');


$_REQUEST['intro'] = 'exclusive';
$act = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'list';

$last = isset($_REQUEST['last'])?trim($_REQUEST['last']):'';
$amount = isset($_REQUEST['amount'])?trim($_REQUEST['amount']):'';
$sort= $_REQUEST['sort'];
if ($_REQUEST['act'] == 'ajax_list'){

     include('includes/cls_json.php');

	$limit = " limit $last,$amount";//每次加载的个数
    $json   = new JSON;

    $goodslist = get_pro_search_list($limit,$sort);
    foreach($goodslist as $key=>$val){
            $GLOBALS['smarty']->assign('goods',$val);
            $GLOBALS['smarty']->assign('key',$key);
            $res[]['info']  = $GLOBALS['smarty']->fetch('library/pro_mobile_list.lbi');
    }
    die($json->encode($res));
}

/**
 * 获取查询列表
 * @param type $limit
 * @return array
 */

function get_pro_search_list($limit){
    /* 初始化搜索条件 */
    $keywords  = '';
    $tag_where = '';
    if (!empty($_REQUEST['keywords']))
    {
        $arr = array();
        if (stristr($_REQUEST['keywords'], ' AND ') !== false)
        {
            /* 检查关键字中是否有AND，如果存在就是并 */
            $arr        = explode('AND', $_REQUEST['keywords']);
            $operator   = " AND ";
        }
        elseif (stristr($_REQUEST['keywords'], ' OR ') !== false)
        {
            /* 检查关键字中是否有OR，如果存在就是或 */
            $arr        = explode('OR', $_REQUEST['keywords']);
            $operator   = " OR ";
        }
        elseif (stristr($_REQUEST['keywords'], ' + ') !== false)
        {
            /* 检查关键字中是否有加号，如果存在就是或 */
            $arr        = explode('+', $_REQUEST['keywords']);
            $operator   = " OR ";
        }
        else
        {
            /* 检查关键字中是否有空格，如果存在就是并 */
            $arr        = explode(' ', $_REQUEST['keywords']);
            $operator   = " AND ";
        }

        $keywords = 'AND (';
        $goods_ids = array();
        foreach ($arr AS $key => $val)
        {
            if ($key > 0 && $key < count($arr) && count($arr) > 1)
            {
                $keywords .= $operator;
            }
            $val        = mysql_like_quote(trim($val));
            $sc_dsad    = $_REQUEST['sc_ds'] ? " OR goods_desc LIKE '%$val%'" : '';
            $keywords  .= "(goods_name LIKE '%$val%' OR goods_sn LIKE '%$val%' OR keywords LIKE '%$val%' $sc_dsad)";

            $sql = 'SELECT DISTINCT goods_id FROM ' . $GLOBALS['ecs']->table('tag') . " WHERE tag_words LIKE '%$val%' ";
            $res = $db->query($sql);
            while ($row = $db->FetchRow($res))
            {
                $goods_ids[] = $row['goods_id'];
            }

            $db->autoReplace($GLOBALS['ecs']->table('keywords'), array('date' => local_date('Y-m-d'),
                'searchengine' => 'ecshop', 'keyword' => addslashes(str_replace('%', '', $val)), 'count' => 1), array('count' => 1));
        }
        $keywords .= ')';

        $goods_ids = array_unique($goods_ids);
        $tag_where = implode(',', $goods_ids);
        if (!empty($tag_where))
        {
            $tag_where = 'OR g.goods_id ' . db_create_in($tag_where);
        }
    }

    $sort = $_REQUEST['sort'];
    $order = $_REQUEST['order'];
    $display  = $_REQUEST['display'];
    $_SESSION['display_search'] = $display;

    $intromode = '';    //方式，用于决定搜索结果页标题图片

    if (!empty($_REQUEST['intro']))
    {
        switch ($_REQUEST['intro'])
        {
            case 'best':
                $intro   = ' AND g.is_best = 1';
                $intromode = 'best';
                $ur_here = $_LANG['best_goods'];
                break;
            case 'new':
                $intro   = ' AND g.is_new = 1';
                $intromode ='new';
                $ur_here = $_LANG['new_goods'];
                break;
            case 'hot':
                $intro   = ' AND g.is_hot = 1';
                $intromode = 'hot';
                $ur_here = $_LANG['hot_goods'];
                break;
            case 'promotion':
                $time    = gmtime();
                $intro   = " AND g.promote_price > 0 AND g.promote_start_date <= '$time' AND g.promote_end_date >= '$time'";
                $intromode = 'promotion';
                $ur_here = $_LANG['promotion_goods'];
                break;
            case 'exclusive':
                $intro = ' AND g.exclusive > 0 ';
                $intromode = 'exclusive';
                $ur_here = $_LANG['exclusive'];
                break;
            default:
                $intro   = '';
        }
    }
    else
    {
        $intro = '';
    }
    if (empty($ur_here))
    {
        $ur_here = $_LANG['search_goods'];
    }

    /*------------------------------------------------------ */
    //-- 属性检索
    /*------------------------------------------------------ */
    $attr_in  = '';
    $attr_num = 0;
    $attr_url = '';
    $attr_arg = array();

    if (!empty($_REQUEST['attr']))
    {
        $sql = "SELECT goods_id, COUNT(*) AS num FROM " . $GLOBALS['ecs']->table("goods_attr") . " WHERE 0 ";
        foreach ($_REQUEST['attr'] AS $key => $val)
        {
            if (is_not_null($val) && is_numeric($key))
            {
                $attr_num++;
                $sql .= " OR (1 ";

                if (is_array($val))
                {
                    $sql .= " AND attr_id = '$key'";

                    if (!empty($val['from']))
                    {
                        $sql .= is_numeric($val['from']) ? " AND attr_value >= " . floatval($val['from'])  : " AND attr_value >= '$val[from]'";
                        $attr_arg["attr[$key][from]"] = $val['from'];
                        $attr_url .= "&amp;attr[$key][from]=$val[from]";
                    }

                    if (!empty($val['to']))
                    {
                        $sql .= is_numeric($val['to']) ? " AND attr_value <= " . floatval($val['to']) : " AND attr_value <= '$val[to]'";
                        $attr_arg["attr[$key][to]"] = $val['to'];
                        $attr_url .= "&amp;attr[$key][to]=$val[to]";
                    }
                }
                else
                {
                    /* 处理选购中心过来的链接 */
                    $sql .= isset($_REQUEST['pickout']) ? " AND attr_id = '$key' AND attr_value = '" . $val . "' " : " AND attr_id = '$key' AND attr_value LIKE '%" . mysql_like_quote($val) . "%' ";
                    $attr_url .= "&amp;attr[$key]=$val";
                    $attr_arg["attr[$key]"] = $val;
                }

                $sql .= ')';
            }
        }

        /* 如果检索条件都是无效的，就不用检索 */
        if ($attr_num > 0)
        {
            $sql .= " GROUP BY goods_id HAVING num = '$attr_num'";

            $row = $db->getCol($sql);
            if (count($row))
            {
                $attr_in = " AND " . db_create_in($row, 'g.goods_id');
            }
            else
            {
                $attr_in = " AND 0 ";
            }
        }
    }
    elseif (isset($_REQUEST['pickout']))
    {
        /* 从选购中心进入的链接 */
        $sql = "SELECT DISTINCT(goods_id) FROM " . $GLOBALS['ecs']->table('goods_attr');
        $col = $db->getCol($sql);
        //如果商店没有设置商品属性,那么此检索条件是无效的
        if (!empty($col))
        {
            $attr_in = " AND " . db_create_in($col, 'g.goods_id');
        }
    }
    $shop_price_sql =  "if(g.promote_start_date < ".gmtime()."  and g.promote_end_date >  ".gmtime()."  and g.promote_price > 0 and g.promote_price < IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]'), g.promote_price, IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]'))";
    if($sort == 'final_price'){
        $sort = 'cast('.$sort.' as DECIMAL( 10,2))';           
    }
    /* 查询商品 */
    $sql = "SELECT IFNULL(SUM(o.goods_number),0) as salenum, g.goods_id, g.goods_name, g.market_price, g.is_new, g.is_best, g.is_hot,g.goods_number, g.shop_price AS org_price, ".
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.exclusive, ".
             "IF(g.exclusive > 0 and g.exclusive < $shop_price_sql , g.exclusive, $shop_price_sql ) as final_price, " .
                "g.promote_price, g.promote_start_date, g.promote_end_date, g.goods_thumb, g.goods_img, g.goods_brief, g.goods_type ".
            "FROM " .$GLOBALS['ecs']->table('goods'). " AS g ".
            "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
                    "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
            " LEFT JOIN " . $GLOBALS['ecs']->table('order_goods') . " as o ON o.goods_id = g.goods_id " . 
            "WHERE g.is_delete = 0 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 $attr_in ".
                "AND (( 1 "  . $keywords    . $intro  . " ) ".$tag_where." ) " .
            " GROUP BY g.goods_id ORDER BY $sort $order";
    $sql .= " $limit";
    $res = $GLOBALS['db']->query($sql);

    $arr = array();
    while ($row = $GLOBALS['db']->FetchRow($res))
    {
        if ($row['promote_price'] > 0)
        {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        }
        else
        {
            $promote_price = 0;
        }

        /* 处理商品水印图片 */
        /* 处理商品水印图片 */
        $watermark_img = '';

        if ($promote_price != 0)
        {
            $watermark_img = "watermark_promote_small";
        }
        elseif ($row['is_new'] != 0)
        {
            $watermark_img = "watermark_new_small";
        }
        elseif ($row['is_best'] != 0)
        {
            $watermark_img = "watermark_best_small";
        }
        elseif ($row['is_hot'] != 0)
        {
            $watermark_img = 'watermark_hot_small';
        }

        if ($watermark_img != '')
        {
            $arr[$row['goods_id']]['watermark_img'] =  $watermark_img;
        }

        $arr[$row['goods_id']]['goods_id']      = $row['goods_id'];
        if($display == 'grid')
        {
            $arr[$row['goods_id']]['goods_name']    = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        }
        else
        {
            $arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
        }
		$arr[$row['goods_id']]['goods_id']      = $row['goods_id'];
        $arr[$row['goods_id']]['type']          = $row['goods_type'];
		$arr[$row['goods_id']]['goods_number']      = $row['goods_number'];
        $arr[$row['goods_id']]['market_price']  = price_format($row['market_price']);
        $arr[$row['goods_id']]['shop_price']    = price_format($row['shop_price']);
        $arr[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
        $arr[$row['goods_id']]['goods_brief']   = $row['goods_brief'];
        $arr[$row['goods_id']]['goods_thumb']   = get_pc_url().'/'.get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img']     = get_pc_url().'/'.get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['url']           = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
        $arr[$row['goods_id']]['salenum'] = $row['salenum'];
        $arr[$row['goods_id']]['exclusive'] = $row['exclusive'];
        $arr[$row['goods_id']]['final_price'] = price_format($row['final_price']);
        $arr[$row['goods_id']]['goods_brief'] = $row['goods_brief'];



		$time = gmtime();
        if ($time >= $row['promote_start_date'] && $time <= $row['promote_end_date'])
        {
            $arr[$row['goods_id']]['gmt_end_time'] = local_date('M d, Y H:i:s',$row['promote_end_date']);
        }
        else
        {
            $arr[$row['goods_id']]['gmt_end_time'] = 0;
        }

		
		if($row['shop_price'] != 0)
{
    //dqy add start 2011-8-24
	 $arr[$row['goods_id']]['zhekou'] = (number_format(intval($row['promote_price'])/intval($row['shop_price']),2))*10;
}
		
	$arr[$row['goods_id']]['jiesheng'] = $row['shop_price'] - $row['promote_price'];
	
	
	
		$arr[$row['goods_id']]['count1']            = selled_count($row['goods_id']);

    }

//    if($display == 'grid')
//    {
//        if(count($arr) % 2 != 0)
//        {
//            $arr[] = array();
//        }
//    }
    return $arr;
}

/*------------------------------------------------------ */
//-- 搜索结果
/*------------------------------------------------------ */

if($act == 'list'){
    /* 排序、显示方式以及类型 */
    $default_display_type = $_CFG['show_order_type'] == '0' ? 'list' : ($_CFG['show_order_type'] == '1' ? 'grid' : 'text');
    $default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';
    $default_sort_order_type   = $_CFG['sort_order_type'] == '0' ? 'goods_id' : ($_CFG['sort_order_type'] == '1' ? 'final_price' : 'last_update');
    $sort = (isset($_REQUEST['sort'])  && in_array(trim(strtolower($_REQUEST['sort'])), array('goods_id', 'final_price', 'salenum' ,'last_update'))) ? trim($_REQUEST['sort'])  : $default_sort_order_type;
    $order = (isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC'))) ? trim($_REQUEST['order']) : $default_sort_order_method;
    $display  = (isset($_REQUEST['display']) && in_array(trim(strtolower($_REQUEST['display'])), array('list', 'grid', 'text'))) ? trim($_REQUEST['display'])  : (isset($_SESSION['display_search']) ? $_SESSION['display_search'] : $default_display_type);
    $pager['sort'] =  $sort;
    $pager['order'] =  $order;
    $pager['display'] =  $display;
    $smarty->assign('script_name', 'pro_mobile');
    assign_template();
    assign_dynamic('search');
    $position = assign_ur_here(0, $ur_here . ($_REQUEST['keywords'] ? '_' . $_REQUEST['keywords'] : ''));
    $smarty->assign('page_title', $position['title']);    // 页面标题
    $smarty->assign('ur_here',    $position['ur_here']);  // 当前位置
    $smarty->assign('intromode',      $intromode);
    $smarty->assign('pager',$pager);
    $smarty->assign('categories', get_categories_tree()); // 分类树
    $smarty->assign('helps',       get_shop_help());      // 网店帮助
    $smarty->assign('top_goods',  get_top10());           // 销售排行
    $smarty->assign('promotion_info', get_promotion_info());
//ll添加start
	//$smarty->assign('wap_group_ad',get_wap_group('wap团购页面广告',1));  //wap团购幻灯广告位
//ll添加end
    $smarty->display('pro_mobile.dwt');

}
?>