<?php
/**
 * ECSHOP 销售报告：区域分布
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com/
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: sell_area_stats.php 2015-10-23 13:00:08Z langlibin $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/statistic.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/supplier_order.php');

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

// 第几周
$smarty->assign('week_num', '0');

// 时间参数
if (isset($_REQUEST['stats_type']))
{
    $stats_type = $_REQUEST['stats_type'];
    if ($stats_type == 1)
    {
        $dropweek = $_REQUEST['dropweek'];
        $dropweek_arr = explode(' ', $dropweek);
        $start_date = strtotime($dropweek_arr[0]);
        $end_date = strtotime($dropweek_arr[1]);
        $week_num = $dropweek_arr[2];
        $smarty->assign('stats_type', '1');
        // 第几周
        $smarty->assign('week_num', $week_num);
    }
    else
    {
        $year = $_REQUEST['year'];
        $month = $_REQUEST['month'];
        $allday = date('t', strtotime("$year-$month"));
        $start_date = strtotime($year . '-' . $month . '-1');
        $end_date = strtotime($year . '-' . $month. '-' . $allday);
        $smarty->assign('stats_type', '2');
    }
    $smarty->assign('year', $_REQUEST['year']);
    $smarty->assign('month', $_REQUEST['month']);
}
else
{
    $year = date('Y');
    $month = date('m');
    $allday = date('t');
    $start_date = strtotime($year . '-' . $month . '-1');
    $end_date = strtotime($year . '-' . $month. '-' . $allday);
    $smarty->assign('year', $year);
    $smarty->assign('month', $month);
}

/*------------------------------------------------------ */
//--商品区域分布
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    admin_priv('sell_stats');

    // 查询条件
    $where = ' WHERE o.supplier_id = ' . $_SESSION['supplier_id']
        . ' AND o.add_time >=' . $start_date . ' AND o.add_time <=' . $end_date;
    // 订单状态
    if(isset($_REQUEST['status']) && $_REQUEST['status'] >= 0)
    {
        $where .= ' AND o.order_status = ' . $_REQUEST['status'];
    }
    // 地域：默认按省统计
    $select_type = 'province';

    // 选到市，按区统计
    if(isset($_REQUEST['city']) && $_REQUEST['city'] > 0)
    {
        $select_type = 'district';
        $where .= ' AND o.city = ' . $_REQUEST['city'];
        $smarty->assign('city_id', $_REQUEST['city']);
        $smarty->assign('province_id', $_REQUEST['province']);
    }
    // 选到省，按市统计
    elseif(isset($_REQUEST['province']) && $_REQUEST['province'] > 0)
    {
        $select_type = 'city';
        $where .= ' AND o.province = ' . $_REQUEST['province'];
        $smarty->assign('province_id', $_REQUEST['province']);
    }

    // 下单会员数
    if ($select_type == 'province')
    {
        $order_users = $db->getAll(
            'SELECT r.region_name, COUNT(*) count FROM (SELECT * FROM '
            . $ecs->table('order_info') . ' o ' . $where . ' GROUP BY user_id) so, '
            . $ecs->table('region') . ' r WHERE so.province = r.region_id GROUP BY province ORDER BY count DESC'
        );
    }
    elseif($select_type == 'city')
    {
        $order_users = $db->getAll(
            'SELECT r.region_name, COUNT(*) count FROM (SELECT * FROM '
            . $ecs->table('order_info') . ' o ' . $where . ' GROUP BY user_id) so, '
            . $ecs->table('region') . ' r WHERE so.city = r.region_id GROUP BY city ORDER BY count DESC'
        );
    }
    elseif($select_type == 'district')
    {
        $order_users = $db->getAll(
            'SELECT r.region_name, COUNT(*) count FROM (SELECT * FROM '
            . $ecs->table('order_info') . ' o ' . $where . ' GROUP BY user_id) so, '
            . $ecs->table('region') . ' r WHERE so.district = r.region_id GROUP BY district ORDER BY count DESC'
        );
    }

    foreach($order_users as $value)
    {
        // 下单会员横轴：地区
        $area1 .= "'" . $value['region_name'] . "',";
        // 下单会员横轴：会员数
        $data1 .= $value['count'] . ',';
    }

    // 下单金额
    if ($select_type == 'province')
    {
        $order_users = $db->getAll(
            'SELECT r.region_name, SUM(o.goods_amount) amount FROM ' . $ecs->table('order_info') . ' o, '
            . $ecs->table('region') . ' r ' . $where . ' AND o.province = r.region_id GROUP BY province ORDER BY amount DESC'
        );
    }
    elseif($select_type == 'city')
    {
        $order_users = $db->getAll(
            'SELECT r.region_name, SUM(o.goods_amount) amount FROM ' . $ecs->table('order_info') . ' o, '
            . $ecs->table('region') . ' r ' . $where . ' AND o.city = r.region_id GROUP BY city ORDER BY amount DESC'
        );
    }
    elseif($select_type == 'district')
    {
        $order_users = $db->getAll(
            'SELECT r.region_name, SUM(o.goods_amount) amount FROM ' . $ecs->table('order_info') . ' o, '
            . $ecs->table('region') . ' r ' . $where . ' AND o.district = r.region_id GROUP BY district ORDER BY amount DESC'
        );
    }

    foreach($order_users as $value)
    {
        // 下单金额横轴：地区
        $area2 .= "'" . $value['region_name'] . "',";
        // 下单金额横轴：下单金额
        $data2 .= $value['amount'] . ',';
    }

    // 下单量
    if ($select_type == 'province')
    {
        $order_users = $db->getAll(
            'SELECT r.region_name, COUNT(*) count FROM ' . $ecs->table('order_info') . ' o, '
            . $ecs->table('region') . ' r ' . $where . ' AND o.province = r.region_id GROUP BY province ORDER BY count DESC'
        );
    }
    elseif($select_type == 'city')
    {
        $order_users = $db->getAll(
            'SELECT r.region_name, COUNT(*) count FROM ' . $ecs->table('order_info') . ' o, '
            . $ecs->table('region') . ' r ' . $where . ' AND o.city = r.region_id GROUP BY city ORDER BY count DESC'
        );
    }
    elseif($select_type == 'district')
    {
        $order_users = $db->getAll(
            'SELECT r.region_name, COUNT(*) count FROM ' . $ecs->table('order_info') . ' o, '
            . $ecs->table('region') . ' r ' . $where . ' AND o.district = r.region_id GROUP BY district ORDER BY count DESC'
        );
    }

    foreach($order_users as $value)
    {
        // 下单会员横轴：地区
        $area3 .= "'" . $value['region_name'] . "',";
        // 下单会员横轴：下单量
        $data3 .= $value['count'] . ',';
    }

    // 地域下拉框选项
    $sql = 'select * from ' . $GLOBALS['ecs']->table('region') . ' where parent_id=' . $GLOBALS['_CFG']['shop_country'];
    $province_list = $GLOBALS['db']->getAll($sql);

    $smarty->assign('province_list',     $province_list);
    $smarty->assign('ur_here', $_LANG['report_sell']);
    // 开始时间
    $smarty->assign('start_date', local_date($_CFG['date_format'], $start_date));
    // 终了时间
    $smarty->assign('end_date', local_date($_CFG['date_format'], $end_date));

    $smarty->assign('area1', $area1);
    $smarty->assign('data1', $data1);
    $smarty->assign('area2', $area2);
    $smarty->assign('data2', $data2);
    $smarty->assign('area3', $area3);
    $smarty->assign('data3', $data3);
    // 状态
    $smarty->assign('status_list', $_LANG['cs']);
    $smarty->assign('status', $_REQUEST['status']);

    /* 显示地域分布页面 */
    assign_query_info();
    $smarty->display('sell_area_stats.htm');
}

?>