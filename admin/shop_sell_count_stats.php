<?php

/**
 * ECSHOP 店铺统计：销售统计
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: shop_sell_count_stats.php 17217 2015-10-29 15:13:08Z langlibin $
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

    $result = get_result_list($start_date, $end_date);

    $smarty->assign('result_list', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);

    $smarty->assign('ur_here', '店铺统计');
    $smarty->assign('full_page', 1);

    assign_query_info();
    $smarty->display('shop_sell_count_stats.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    admin_priv('shops_stats');

    // 取得下单量列表
    $result = get_result_list($start_date, $end_date);
    $smarty->assign('result_list', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);

    $sort_flag = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('shop_sell_count_stats.htm'), '',
        array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}

/*------------------------------------------------------ */
//-- 导出数据
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'export')
{
    // 查询条件
    $where = ' WHERE o.add_time >=' . $start_date . ' AND o.add_time <=' . $end_date
        . ' AND ((o.pay_id = 6 AND o.shipping_status = 2) OR (o.pay_id <> 6 AND o.pay_status = 2))';
    // 查询
    $sql = "SELECT IFNULL(s.supplier_id, 0) supplier_id, IFNULL(s.supplier_name, '平台自营') shop_name,"
        . ' SUM(o.goods_amount) goods_amount, COUNT(*) goods_count FROM '
        . $GLOBALS['ecs']->table('order_info') . ' o LEFT JOIN ' . $GLOBALS['ecs']->table('supplier')
        . ' s ON o.supplier_id = s.supplier_id' . $where
        . ' GROUP BY shop_name ORDER BY goods_amount DESC';

    $result = $db->getAll($sql);
    foreach($result as $key => $value)
    {
        // 下单会员数
        $user_count = $GLOBALS['db']->getOne(
            'SELECT COUNT(*) FROM (SELECT DISTINCT o.user_id FROM ' . $GLOBALS['ecs']->table('order_info')
            . ' o ' . $where . ' AND supplier_id = ' . $value['supplier_id'] . ' ) t'
        );
        $result[$key]['user_count'] = $user_count;
    }
    // 引入phpexcel核心类文件
    require_once ROOT_PATH . '/includes/phpexcel/Classes/PHPExcel.php';
    // 实例化excel类
    $objPHPExcel = new PHPExcel();
    // 操作第一个工作表
    $objPHPExcel->setActiveSheetIndex(0);
    // 设置sheet名
    $objPHPExcel->getActiveSheet()->setTitle('销售统计');
    // 设置表格宽度
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    // 列名表头文字加粗
    $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
    // 列表头文字居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // 列名赋值
    $objPHPExcel->getActiveSheet()->setCellValue('A1', '店铺名称');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', '下单会员数');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', '下单量');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', '下单金额');

    // 数据起始行
    $row_num = 2;
    // 向每行单元格插入数据
    foreach($result as $value)
    {
        // 设置所有垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'D' . $row_num)->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        // 设置价格为数字格式
        $objPHPExcel->getActiveSheet()->getStyle('D' . $row_num)->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);

        // 设置单元格数值
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row_num, $value['shop_name']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row_num, $value['user_count']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row_num, $value['goods_count']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row_num, $value['goods_amount']);
        $row_num++;
    }
    $outputFileName = '销售统计' . time() . '.xls';
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
 * 分页获取销售统计列表
 *
 * @return array
 */
function get_result_list ($start_date, $end_date)
{
    $result = get_filter();
    if($result === false)
    {
        $filter = array();
        $filter['start_date'] = empty($_REQUEST['start_date']) ? $start_date : trim($_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? $end_date : trim($_REQUEST['end_date']);
        if(isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['start_date'] = json_str_iconv($filter['start_date']);
            $filter['end_date'] = json_str_iconv($filter['end_date']);
        }

        // 查询条件
        $where = ' WHERE o.add_time >=' . $start_date . ' AND o.add_time <=' . $end_date
            . ' AND ((o.pay_id = 6 AND o.shipping_status = 2) OR (o.pay_id <> 6 AND o.pay_status = 2))';

        $sql = 'SELECT COUNT(*) FROM (SELECT DISTINCT supplier_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' o ' . $where . ') t';
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        // 分页大小
        $filter = page_and_size($filter);
        // 查询
        $sql = "SELECT IFNULL(s.supplier_id, 0) supplier_id, IFNULL(s.supplier_name, '平台自营') shop_name,"
            . ' SUM(o.goods_amount) goods_amount, COUNT(*) goods_count FROM '
            . $GLOBALS['ecs']->table('order_info') . ' o LEFT JOIN ' . $GLOBALS['ecs']->table('supplier')
            . ' s ON o.supplier_id = s.supplier_id' . $where
            . ' GROUP BY shop_name ORDER BY goods_amount DESC ' . ' LIMIT ' . $filter['start'] . ", $filter[page_size]";

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
        // 下单会员数
        $user_count = $GLOBALS['db']->getOne(
            'SELECT COUNT(*) FROM (SELECT DISTINCT o.user_id FROM ' . $GLOBALS['ecs']->table('order_info')
            . ' o ' . $where . ' AND supplier_id = ' . $value['supplier_id'] . ' ) t'
        );
        $list[$key]['user_count'] = $user_count;
    }
    $arr = array(
        'item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']
    );
    return $arr;
}
?>