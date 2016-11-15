<?php

/**
 * ECSHOP 店铺统计：热卖排行
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: shop_hot_sell_stats.php 17217 2015-10-29 13:52:08Z langlibin $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/statistic.php');

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
    if (isset($_REQUEST['stats_type']))
    {
        $stats_type = $_REQUEST['stats_type'];
    }
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
    admin_priv('shops_stats');

    // 查询条件
    $where = ' WHERE o.add_time >=' . $start_date . ' AND o.add_time <=' . $end_date
        . ' AND ((o.pay_id = 6 AND o.shipping_status = 2) OR (o.pay_id <> 6 AND o.pay_status = 2))';

    // 店铺热卖TOP15
    $sql = "SELECT IFNULL(s.supplier_name, '平台自营') shop_name,"
        . ' SUM(o.goods_amount) goods_amount, COUNT(*) goods_count FROM '
        . $ecs->table('order_info') . ' o LEFT JOIN ' . $ecs->table('supplier')
        . ' s ON o.supplier_id = s.supplier_id' . $where
        . ' GROUP BY shop_name ORDER BY goods_amount DESC LIMIT 15';

    $shop_hot_sell = $db->getAll($sql);
    // 添加序号
    foreach($shop_hot_sell as $key=>$value)
    {
        $shop_hot_sell[$key]['no'] = $key + 1;
    }

    $smarty->assign('ur_here', '店铺统计');
    $smarty->assign('shop_hot_sell', $shop_hot_sell);

    assign_query_info();
    $smarty->display('shop_hot_sell_stats.htm');
}

?>