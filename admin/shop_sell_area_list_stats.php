<?php

/**
 * ECSHOP 店铺统计：区域分布
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: shop_sell_area_list_stats.php 17217 2015-10-29 16:14:08Z langlibin $
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

// 日期-默认当日日期
$end_date = isset($_REQUEST['end_date']) ? strtotime($_REQUEST['end_date']) : strtotime(date('Y-m-d'));
// 地区-默认按省统计
$area_type = isset($_REQUEST['area_type']) ? $_REQUEST['area_type'] : 0;
// 省
$province = isset($_REQUEST['province']) ? $_REQUEST['province'] : 0;
// 市
$city = isset($_REQUEST['city']) ? $_REQUEST['city'] : 0;

if ($_REQUEST['act'] == 'list')
{
    admin_priv('shops_stats');

    // 地域下拉框选项
    $sql = 'select * from ' . $GLOBALS['ecs']->table('region') . ' where parent_id=' . $GLOBALS['_CFG']['shop_country'];
    $province_list = $GLOBALS['db']->getAll($sql);

    $smarty->assign('ur_here', '店铺统计');
    $smarty->assign('full_page', 1);
    $smarty->assign('province_list',     $province_list);
    // 取得区域分布列表
    $result = get_result_list($end_date, $area_type, $province, $city);
    $smarty->assign('result_list', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);
    $smarty->assign('end_date', date('Y-m-d', $end_date));

    assign_query_info();
    $smarty->display('shop_sell_area_list_stats.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    admin_priv('shops_stats');

    // 取得区域分布列表
    $result = get_result_list($end_date, $area_type, $province, $city);
    $smarty->assign('result_list', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);

    $sort_flag = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('shop_sell_area_list_stats.htm'), '',
        array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}

/**
 * 分页获取区域分布列表
 *
 * @return array
 */
function get_result_list ($end_date, $area_type, $province, $city)
{
    $result = get_filter();
    if($result === false)
    {
        $filter = array();
        $filter['end_date'] = empty($_REQUEST['end_date']) ? $end_date : $_REQUEST['end_date'];
        $filter['area_type'] = empty($_REQUEST['area_type']) ? $area_type : $_REQUEST['area_type'];
        $filter['province'] = empty($_REQUEST['province']) ? $province : $_REQUEST['province'];
        $filter['city'] = empty($_REQUEST['city']) ? $city : $_REQUEST['city'];

        if(isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['end_date'] = strtotime($filter['end_date']);
            if($filter['end_date'] == false)
            {
                $filter['end_date'] = $_REQUEST['end_date'];
            }
        }

        $where = ' WHERE s.`status` = 1 AND s.add_time <= ' . $filter['end_date'];
        $sql = 'SELECT COUNT(*) FROM (SELECT * FROM ' . $GLOBALS['ecs']->table('supplier');
        // 按省统计
        if($filter['area_type'] == 0)
        {
            $sql .= " s $where GROUP BY s.province) t";
        }
        // 按市统计
        elseif($filter['area_type'] == 1)
        {
            $sql .= " s $where AND s.province = " . $filter['province'] . " GROUP BY s.province, s.city) t ";
        }
        // 按区统计
        else
        {
            $sql .= " s $where AND s.province = " . $filter['province']
                . " AND s.city = " . $filter['city'] . " GROUP BY s.province, s.city, s.district) t";
        }
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        // 分页大小
        $filter = page_and_size($filter);

        // 按省统计
        if($filter['area_type'] == 0)
        {
            $sql = "SELECT COUNT(*) count, s.province, 0 city, 0 district, (SELECT r.region_name FROM "
                . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = s.province) province_name FROM '
                . $GLOBALS['ecs']->table('supplier') . ' s ' . $where . ' GROUP BY s.province ORDER BY count DESC';
        }
        // 按市统计
        elseif($filter['area_type'] == 1)
        {
            $sql = "SELECT COUNT(*) count, s.province, s.city, 0 district, (SELECT r.region_name FROM "
                . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = s.province) province_name, (SELECT r.region_name FROM '
                . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = s.city) city_name FROM '
                . $GLOBALS['ecs']->table('supplier') . ' s ' . $where
                . ' AND s.province = ' . $filter['province'] . ' GROUP BY s.province, s.city ORDER BY count DESC';
        }
        // 按区统计
        else
        {
            $sql = 'SELECT COUNT(*) count, s.province, s.city, s.district, (SELECT r.region_name FROM '
                . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = s.province) province_name, (SELECT r.region_name FROM '
                . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = s.city) city_name, (SELECT r.region_name FROM '
                . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = s.district) district_name FROM '
                . $GLOBALS['ecs']->table('supplier') . ' s ' . $where
                . ' AND s.province = ' . $filter['province'] . ' AND s.city = ' . $filter['city']
                . ' GROUP BY s.province, s.city, s.district ORDER BY count DESC';
        }

        $sql .= ' LIMIT ' . $filter['start'] . ", $filter[page_size]";
        set_filter($filter, $sql);
    }
    else
    {
        $sql = $result['sql'];
        $filter = $result['filter'];
    }
    $list = $GLOBALS['db']->getAll($sql);
    foreach($list as $key => $value)
    {
        $list[$key]['no'] = $key + 1;
    }
    $arr = array(
        'item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']
    );
    return $arr;
}

?>