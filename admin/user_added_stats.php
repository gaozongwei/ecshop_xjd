<?php

/**
 * ECSHOP 会员统计：新增会员
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: user_added_stats.php 17217 2015-10-27 09:43:08Z langlibin $
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
    admin_priv('users_stats');

    // 当日新增会员数
    $user_added_today = $db->getAll(
        "SELECT FLOOR((u.reg_time - $start_date) / (24 * 3600)) reg_time, COUNT(*) user_count FROM "
        . $ecs->table('users') . ' u ' . ' WHERE u.reg_time >=' . $start_date . ' AND u.reg_time <='
        . $end_date . " GROUP BY FLOOR((u.reg_time - $start_date) / (24 * 3600)) ORDER BY reg_time"
    );
    foreach($user_added_today as $key=>$value)
    {
        $user_added_today[$key]['reg_time'] = date('Ymd', $start_date + $value['reg_time'] * 86400);
    }

    // 前日新增会员数
    $user_added_yesterday = $db->getAll(
        "SELECT FLOOR((u.reg_time + 86400 - $start_date) / (24 * 3600)) reg_time, COUNT(*) user_count FROM "
        . $ecs->table('users') . ' u ' . ' WHERE u.reg_time >=' . ($start_date - 86399) . ' AND u.reg_time <='
        . ($end_date - 86399) . " GROUP BY FLOOR((u.reg_time + 86400 - $start_date) / (24 * 3600)) ORDER BY reg_time"
    );
    foreach($user_added_yesterday as $key=>$value)
    {
        $user_added_yesterday[$key]['reg_time'] = date('Ymd', $start_date + $value['reg_time'] * 86400);
    }

    // 时间轴字符串
    $time_today_arr = 0;
    $time_yesterday_arr = 0;
    // 按日统计
    if (isset($stats_type) && $stats_type == 0)
    {
        $time_today_arr = date('Ymd');
        $time_yesterday_arr = date('Ymd', strtotime('-1 day'));
    }
    // 按周、月统计
    else
    {
        // 取得日期、赋初始值
        $time_arr = get_date_arr($start_date, $end_date);
        foreach($user_added_today as $value)
        {
            $time_arr[$value['reg_time']] = $value['user_count'];
        }
        foreach($time_arr as $key => $value)
        {
            $user_reg_time .= "'" . $key . "',";
            $user_today_count .= "'" . $value . "',";
        }
        // 前日
        $time_arr = get_date_arr($start_date, $end_date);
        foreach($user_added_yesterday as $value)
        {
            $time_arr[$value['reg_time']] = $value['user_count'];
        }
        foreach($time_arr as $key => $value)
        {
            $user_yesterday_count .= "'" . $value . "',";
        }
    }

    $smarty->assign('ur_here', '会员统计');
    // 日期字符串
    $smarty->assign('user_reg_time', $user_reg_time);
    // 当日新增会员数字符串
    $smarty->assign('user_today_count', $user_today_count);
    // 前日新增会员数字符串
    $smarty->assign('user_yesterday_count', $user_yesterday_count);

    assign_query_info();
    $smarty->display('user_added_stats.htm');
}

/* 取得搜索范围内的日期并赋初始值 */
function get_date_arr($dt_start, $dt_end)
{
    $date_arr = array();
    do {
        // 将 Timestamp 转成 ISO Date 输出
        $date_arr[date('Ymd', $dt_start)] = 0;
        // 重复 Timestamp + 1 天(86400), 直至大于结束日期中止
    } while (($dt_start += 86400) <= $dt_end);
    return $date_arr;
}
?>