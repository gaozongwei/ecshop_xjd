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
 * $Id: shop_sell_area_detail_stats.php 17217 2015-10-30 13:06:08Z langlibin $
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
$_REQUEST['end_date'] = strtotime(date('Y-m-d'));

$end_date = $_REQUEST['end_date'];
$area_type = $_REQUEST['area_type'];
$province = $_REQUEST['province'];
$city = $_REQUEST['city'];
$district = $_REQUEST['district'];

if ($_REQUEST['act'] == 'list')
{
    admin_priv('shops_stats');

    $smarty->assign('ur_here', '店铺统计');
    $smarty->assign('full_page', 1);

    // 取得店铺列表
    $result = get_result_list($end_date, $area_type, $province, $city, $district);

    $smarty->assign('result_list', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);

    assign_query_info();
    $smarty->display('shop_sell_area_detail_stats.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    admin_priv('shops_stats');

    // 取得店铺列表
    $result = get_result_list($end_date, $area_type, $province, $city, $district);

    $smarty->assign('result_list', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);

    $sort_flag = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('shop_sell_area_detail_stats.htm'), '',
        array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}

/**
 * 分页获取店铺列表
 *
 * @return array
 */
function get_result_list ($end_date, $area_type, $province, $city, $district)
{
    $result = get_filter();
    if($result === false)
    {
        $filter = array();
        $filter['end_date'] = empty($_REQUEST['end_date']) ? $end_date : $_REQUEST['end_date'];
        $filter['area_type'] = empty($_REQUEST['area_type']) ? $area_type : $_REQUEST['area_type'];
        $filter['province'] = empty($_REQUEST['province']) ? $province : $_REQUEST['province'];
        $filter['city'] = empty($_REQUEST['city']) ? $city : $_REQUEST['city'];
        $filter['district'] = empty($_REQUEST['district']) ? $district : $_REQUEST['district'];

        if(isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['end_date'] = strtotime($filter['end_date']);
            if($filter['end_date'] == false)
            {
                $filter['end_date'] = $_REQUEST['end_date'];
            }
        }
        $where = ' WHERE s.`status` = 1 AND s.add_time <= ' . $filter['end_date'];
        $sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('supplier') . ' s ' . $where;
        // 按省统计
        if($filter['area_type'] == 0)
        {
            $sql .= ' AND s.province = ' . $filter['province'];
        }
        // 按市统计
        elseif($filter['area_type'] == 1)
        {
            $sql .= ' AND s.province = ' . $filter['province']
                . ' AND s.city = ' . $filter['city'];
        }
        // 按区统计
        else
        {
            $sql .= ' AND s.province = ' . $filter['province']
                . ' AND s.city = ' . $filter['city']
                . ' AND s.district = ' . $filter['district'];
        }
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        // 分页大小
        $filter = page_and_size($filter);
        $where .= ' AND sa.role_id IS NULL';
        $sql = "SELECT s.supplier_name, sa.user_name, r.rank_name, s.add_time FROM "
            . $GLOBALS['ecs']->table('supplier') . ' s LEFT JOIN ' . $GLOBALS['ecs']->table('supplier_admin_user')
            . ' sa ON s.supplier_id = sa.supplier_id LEFT JOIN ' . $GLOBALS['ecs']->table('supplier_rank')
            . ' r ON s.rank_id = r.rank_id ' . $where;
        ;
        // 按省统计
        if($filter['area_type'] == 0)
        {
            $sql .= ' AND s.province = ' . $filter['province'];
        }
        // 按市统计
        elseif($filter['area_type'] == 1)
        {
            $sql .= ' AND s.province = ' . $filter['province']
                . ' AND s.city = ' . $filter['city'];
        }
        // 按区统计
        else
        {
            $sql .= ' AND s.province = ' . $filter['province']
                . ' AND s.city = ' . $filter['city']
                . ' AND s.district = ' . $filter['district'];
        }
        $sql .= ' ORDER BY s.supplier_id LIMIT ' . $filter['start'] . ", $filter[page_size]";
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
        $list[$key]['add_time'] = local_date('Y-m-d G:i:s', $value['add_time']);
        $list[$key]['no'] = $key + 1;
    }
    $arr = array(
        'item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']
    );
    return $arr;
}

?>