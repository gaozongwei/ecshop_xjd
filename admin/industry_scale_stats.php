<?php

/**
 * ECSHOP 行业分析：行业规模
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: industry_scale_stats.php 17217 2015-10-26 11:06:08Z langlibin $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/statistic.php');
require_once(ROOT_PATH . '/includes/lib_goods.php');

$smarty->assign('lang', $_LANG);

// act操作项的初始化
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}

// 时间参数
if (isset($_REQUEST['stats_type']))
{
    $stats_type = $_REQUEST['stats_type'];
    if ($stats_type == 0)
    {
        $start_date = strtotime($_REQUEST['date']);
        $end_date = strtotime($_REQUEST['date']);
        $smarty->assign('date', $_REQUEST['date']);
        $smarty->assign('week_num', '0');
        $smarty->assign('stats_type', '0');
    }
    elseif ($stats_type == 1)
    {
        $dropweek = $_REQUEST['dropweek'];
        $dropweek_arr = explode(' ', $dropweek);
        $start_date = strtotime($dropweek_arr[0]);
        $end_date = strtotime($dropweek_arr[1]);
        $week_num = $dropweek_arr[2];
        $smarty->assign('date', $_REQUEST['date']);
        $smarty->assign('week_num', $week_num);
        $smarty->assign('stats_type', '1');
    }
    else
    {
        $year = $_REQUEST['year'];
        $month = $_REQUEST['month'];
        $allday = date('t', strtotime("$year-$month"));
        $start_date = strtotime($year . '-' . $month . '-1');
        $end_date = strtotime($year . '-' . $month. '-' . $allday);
        $smarty->assign('date', $_REQUEST['date']);
        $smarty->assign('week_num', '0');
        $smarty->assign('stats_type', '2');
    }
    $smarty->assign('year', $_REQUEST['year']);
    $smarty->assign('month', $_REQUEST['month']);
}
else
{
    // 默认按月统计
    $year = date('Y');
    $month = date('m');
    $allday = date('t');
    $start_date = strtotime($year . '-' . $month . '-1');
    $end_date = strtotime($year . '-' . $month. '-' . $allday);
    $smarty->assign('year', $year);
    $smarty->assign('month', $month);
    $smarty->assign('date', date('Y-m-d'));
    $smarty->assign('week_num', '0');
    $smarty->assign('stats_type', '2');
}
// 设置结束时间
$end_date += 86399;

if ($_REQUEST['act'] == 'list')
{
    admin_priv('industry_stats');

    // 查询条件
    $where = ' WHERE oi.add_time >=' . $start_date . ' AND oi.add_time <=' . $end_date;

    if(isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']))
    {
        $cat_id = $_REQUEST['cat_id'];
        $cat_list = get_child_tree($_REQUEST['cat_id']);
        $smarty->assign('goods_cat_id', $cat_id);
        $smarty->assign('goods_cat_name', $cat_name);
    }
    else{
        // 取得所有一级分类
        $cat_list = $db->getAll(
            'SELECT cat_id id, cat_name name FROM ' . $ecs->table('category') . ' WHERE parent_id = 0 AND is_virtual = 0 ORDER BY cat_id'
        );
    }

    // 分类名称字符串
    $cat_name_arr = '';
    // 下单金额字符串
    $goods_price_arr = '';
    // 下单商品数字符串
    $goods_count_arr = '';
    // 下单量数字符串
    $orders_count_arr = '';

    foreach($cat_list as $value)
    {
        // 分类id
        $cat_id = $value['id'];
        // 分类名称
        $cat_name = $value['name'];
        // 查询分类下下单金额
        $sql = 'SELECT IFNULL(SUM(og.goods_price), 0) goods_price FROM ' . $ecs->table('order_info') . ' oi, '
            . $ecs->table('order_goods') . ' og, ' . $ecs->table('goods') . ' g ' . $where
            . ' AND og.goods_id = g.goods_id AND og.order_id = oi.order_id AND ' . get_children($cat_id);
        // 取得下单金额
        $goods_price = $db->getOne($sql);
        $cat_name_arr .= "'" . $cat_name . "',";
        $goods_price_arr .= "'" . $goods_price . "',";

        // 查询分类下下单商品数
        $sql = 'SELECT SUM(og.goods_number) goods_count FROM ' . $ecs->table('order_info') . ' oi, '
            . $ecs->table('order_goods') . ' og, ' . $ecs->table('goods') . ' g ' . $where
            . ' AND og.goods_id = g.goods_id AND og.order_id = oi.order_id AND ' . get_children($cat_id);
        // 取得下单商品数
        $goods_count = $db->getOne($sql);
        $goods_count_arr .= "'" . $goods_count . "',";

        // 查询分类下下单量
        $sql = 'SELECT COUNT(*) goods_count FROM ' . $ecs->table('order_info') . ' oi, '
            . $ecs->table('order_goods') . ' og, ' . $ecs->table('goods') . ' g ' . $where
            . ' AND og.goods_id = g.goods_id AND og.order_id = oi.order_id AND ' . get_children($cat_id);
        // 取得下单量
        $orders_count = $db->getOne($sql);
        $orders_count_arr .= "'" . $orders_count . "',";
    }

    $smarty->assign('ur_here', '行业分析');

    $smarty->assign('cat_name_arr', $cat_name_arr);
    $smarty->assign('goods_price_arr', $goods_price_arr);
    $smarty->assign('goods_count_arr', $goods_count_arr);
    $smarty->assign('orders_count_arr', $orders_count_arr);

    assign_query_info();
    $smarty->display('industry_scale_stats.htm');
}

?>