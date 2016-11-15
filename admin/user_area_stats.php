<?php

/**
 * ECSHOP 会员统计：区域分布
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: user_area_stats.php 17217 2015-11-04 20:45:08Z langlibin $
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

// 地区-默认按省统计
$area_type = isset($_REQUEST['area_type']) ? $_REQUEST['area_type'] : 0;
// 省
$province = isset($_REQUEST['province']) ? $_REQUEST['province'] : 0;
// 市
$city = isset($_REQUEST['city']) ? $_REQUEST['city'] : 0;

if ($_REQUEST['act'] == 'list')
{
    admin_priv('users_stats');

    // 地域下拉框选项
    $sql = 'select * from ' . $GLOBALS['ecs']->table('region') . ' where parent_id=' . $GLOBALS['_CFG']['shop_country'];
    $province_list = $GLOBALS['db']->getAll($sql);

    $smarty->assign('ur_here', '会员统计');
    $smarty->assign('full_page', 1);
    $smarty->assign('province_list',     $province_list);

    $result = get_result_list($area_type, $province, $city);
    $smarty->assign('result_list', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);

    assign_query_info();
    $smarty->display('user_area_stats.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    admin_priv('users_stats');

    // 取得区域分布列表
    $result = get_result_list($area_type, $province, $city);
    $smarty->assign('result_list', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);

    $sort_flag = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('user_area_stats.htm'), '',
        array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}

/*------------------------------------------------------ */
//-- 批量导出数据
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'export')
{
    admin_priv('users_stats');
    // 查询条件
    $where = ' WHERE (o.pay_id = 6 AND o.shipping_status = 2) OR (o.pay_id <> 6 AND o.pay_status = 2) ';
    // 按省统计
    if($area_type == 0)
    {
        $sql = "SELECT COUNT(*) order_count, SUM(o.goods_amount) goods_amount, o.province, 0 city, 0 district, (SELECT r.region_name FROM "
            . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = o.province) province_name FROM '
            . $GLOBALS['ecs']->table('order_info') . ' o ' . $where . ' GROUP BY o.province ORDER BY order_count DESC';
    }
    // 按市统计
    elseif($area_type == 1)
    {
        $sql = "SELECT COUNT(*) order_count, SUM(o.goods_amount) goods_amount, o.province, o.city, 0 district, (SELECT r.region_name FROM "
            . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = o.province) province_name, (SELECT r.region_name FROM '
            . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = o.city) city_name FROM '
            . $GLOBALS['ecs']->table('order_info') . ' o ' . $where
            . ' AND o.province = ' . $province . ' GROUP BY o.province, o.city ORDER BY order_count DESC';
    }
    // 按区统计
    else
    {
        $sql = 'SELECT COUNT(*) order_count, SUM(o.goods_amount) goods_amount, o.province, o.city, o.district, (SELECT r.region_name FROM '
            . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = o.province) province_name, (SELECT r.region_name FROM '
            . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = o.city) city_name, (SELECT r.region_name FROM '
            . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = o.district) district_name FROM '
            . $GLOBALS['ecs']->table('order_info') . ' o ' . $where
            . ' AND o.province = ' . $province . ' AND o.city = ' . $city
            . ' GROUP BY o.province, o.city, o.district ORDER BY order_count DESC';
    }

    // 查询
    $result = $db->getAll($sql);
    // 下单会员数
    foreach($result as $key => $value)
    {
        // 按省统计
        if($filter['area_type'] == 0)
        {
            $user_count = $GLOBALS['db']->getOne(
                'SELECT COUNT(*) FROM (SELECT DISTINCT o.user_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' o '
                . $where . ' AND o.province = ' . $value['province'] . ' )t'
            );
        }
        // 按市统计
        elseif($filter['area_type'] == 1)
        {
            $user_count = $GLOBALS['db']->getOne(
                'SELECT COUNT(*) FROM (SELECT DISTINCT o.user_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' o '
                . $where . ' AND o.city = ' . $value['city'] . ' )t'
            );
        }
        // 按区统计
        else
        {
            $user_count = $GLOBALS['db']->getOne(
                'SELECT COUNT(*) FROM (SELECT DISTINCT o.user_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' o '
                . $where . ' AND o.district = ' . $value['district'] . ' )t'
            );
        }

        $result[$key]['user_count'] = $user_count;
    }

    // 引入phpexcel核心类文件
    require_once ROOT_PATH . '/includes/phpexcel/Classes/PHPExcel.php';
    // 实例化excel类
    $objPHPExcel = new PHPExcel();
    // 操作第一个工作表
    $objPHPExcel->setActiveSheetIndex(0);
    // 设置sheet名
    $objPHPExcel->getActiveSheet()->setTitle('区域分布');
    // 设置表格宽度
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    // 列名表头文字加粗
    $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);
    // 列表头文字居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // 列名赋值
    $objPHPExcel->getActiveSheet()->setCellValue('A1', '省');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', '市');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', '区/县');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', '下单会员数');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', '下单金额');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', '下单量');

    // 数据起始行
    $row_num = 2;
    // 向每行单元格插入数据
    foreach($result as $value)
    {
        // 设置所有垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'F' . $row_num)->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        // 设置价格为数字格式
        $objPHPExcel->getActiveSheet()->getStyle('E' . $row_num )->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        // 设置单元格数值
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row_num, $value['province_name']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row_num, $value['city_name']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row_num, $value['district_name']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row_num, $value['user_count']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row_num, $value['goods_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row_num, $value['order_count']);
        $row_num++;
    }
    $outputFileName = '区域分布_' . time() . '.xls';
    $xlsWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header('Content-Disposition:inline;filename="' . $outputFileName . '"');
    header("Content-Transfer-Encoding: binary");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: no-cache");
    $xlsWriter->save("php://output");
    echo file_get_contents($outputFileName);
}

/**
 * 分页获取区域分布列表
 *
 * @return array
 */
function get_result_list ($area_type, $province, $city)
{
    $result = get_filter();
    if($result === false)
    {
        $filter = array();
        $filter['area_type'] = empty($_REQUEST['area_type']) ? $area_type : $_REQUEST['area_type'];
        $filter['province'] = empty($_REQUEST['province']) ? $province : $_REQUEST['province'];
        $filter['city'] = empty($_REQUEST['city']) ? $city : $_REQUEST['city'];

        $where = ' WHERE (o.pay_id = 6 AND o.shipping_status = 2) OR (o.pay_id <> 6 AND o.pay_status = 2) ';
        $sql = 'SELECT COUNT(*) FROM (SELECT * FROM ' . $GLOBALS['ecs']->table('order_info');
        // 按省统计
        if($filter['area_type'] == 0)
        {
            $sql .= " o $where GROUP BY o.province) t";
        }
        // 按市统计
        elseif($filter['area_type'] == 1)
        {
            $sql .= " o $where AND o.province = " . $filter['province'] . " GROUP BY o.province, o.city) t ";
        }
        // 按区统计
        else
        {
            $sql .= " o $where AND o.province = " . $filter['province']
                . " AND o.city = " . $filter['city'] . " GROUP BY o.province, o.city, o.district) t";
        }
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        // 分页大小
        $filter = page_and_size($filter);

        // 按省统计
        if($filter['area_type'] == 0)
        {
            $sql = "SELECT COUNT(*) order_count, SUM(o.goods_amount) goods_amount, o.province, 0 city, 0 district, (SELECT r.region_name FROM "
                . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = o.province) province_name FROM '
                . $GLOBALS['ecs']->table('order_info') . ' o ' . $where . ' GROUP BY o.province ORDER BY order_count DESC';
        }
        // 按市统计
        elseif($filter['area_type'] == 1)
        {
            $sql = "SELECT COUNT(*) order_count, SUM(o.goods_amount) goods_amount, o.province, o.city, 0 district, (SELECT r.region_name FROM "
                . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = o.province) province_name, (SELECT r.region_name FROM '
                . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = o.city) city_name FROM '
                . $GLOBALS['ecs']->table('order_info') . ' o ' . $where
                . ' AND o.province = ' . $filter['province'] . ' GROUP BY o.province, o.city ORDER BY order_count DESC';
        }
        // 按区统计
        else
        {
            $sql = 'SELECT COUNT(*) order_count, SUM(o.goods_amount) goods_amount, o.province, o.city, o.district, (SELECT r.region_name FROM '
                . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = o.province) province_name, (SELECT r.region_name FROM '
                . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = o.city) city_name, (SELECT r.region_name FROM '
                . $GLOBALS['ecs']->table('region') . ' r WHERE r.region_id = o.district) district_name FROM '
                . $GLOBALS['ecs']->table('order_info') . ' o ' . $where
                . ' AND o.province = ' . $filter['province'] . ' AND o.city = ' . $filter['city']
                . ' GROUP BY o.province, o.city, o.district ORDER BY order_count DESC';
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

    // 下单会员数
    foreach($list as $key => $value)
    {
        // 按省统计
        if($filter['area_type'] == 0)
        {
            $user_count = $GLOBALS['db']->getOne(
                'SELECT COUNT(*) FROM (SELECT DISTINCT o.user_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' o '
                . $where . ' AND o.province = ' . $value['province'] . ' )t'
            );
        }
        // 按市统计
        elseif($filter['area_type'] == 1)
        {
            $user_count = $GLOBALS['db']->getOne(
                'SELECT COUNT(*) FROM (SELECT DISTINCT o.user_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' o '
                . $where . ' AND o.city = ' . $value['city'] . ' )t'
            );
        }
        // 按区统计
        else
        {
            $user_count = $GLOBALS['db']->getOne(
                'SELECT COUNT(*) FROM (SELECT DISTINCT o.user_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' o '
                . $where . ' AND o.district = ' . $value['district'] . ' )t'
            );
        }

        $list[$key]['user_count'] = $user_count;
    }

    $arr = array(
        'item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']
    );
    return $arr;
}

?>