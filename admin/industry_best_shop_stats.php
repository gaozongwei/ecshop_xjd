<?php

/**
 * ECSHOP 行业分析：行业排行：行业店铺
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: industry_best_shop_stats.php 17217 2015-10-27 08:35:08Z langlibin $
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
        $where .= ' AND ' . get_children($cat_id);
        $smarty->assign('goods_cat_id', $cat_id);
        $smarty->assign('goods_cat_name', $cat_name);
    }

    // 查询分类下下单商品数
    $sql = "SELECT IFNULL(s.supplier_name, '平台自营') shop_name, COUNT(*) goods_count FROM "
            . $ecs->table('order_goods') . ' og LEFT JOIN '
            . $ecs->table('order_info') . ' oi ON og.order_id = oi.order_id LEFT JOIN '
            . $ecs->table('goods') . ' g ON og.goods_id = g.goods_id LEFT JOIN '
            . $ecs->table('supplier') . ' s ON s.supplier_id = g.supplier_id ' . $where
            . ' GROUP BY supplier_name ORDER BY goods_count DESC LIMIT 30';
    $result = $db->getAll($sql);
    foreach($result as $key => $value)
    {
        $result[$key]['no'] = $key + 1;
        // 下单商品数字符串
        $goods_count_arr .= "'" . $value['goods_count'] . "',";
    }
    // 排行：1~30
    for($i = 1; $i <= 30; $i++)
    {
        $shop_no_arr .= "'" . $i . "',";
    }

    $smarty->assign('ur_here', '行业分析');

    $smarty->assign('goods_count_arr', $goods_count_arr);
    $smarty->assign('shop_no_arr', $shop_no_arr);
    $smarty->assign('full_page', 1);

    // 店铺排行
    $smarty->assign('shop_list', $result);

    assign_query_info();
    $smarty->display('industry_best_shop_stats.htm');
}

?>