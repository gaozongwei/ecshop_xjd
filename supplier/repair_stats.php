<?php
/**
 * ECSHOP 售后统计：返修统计
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com/
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: refund_stats.php 2015-10-25 16:44:08Z langlibin $
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
//--返修统计
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    admin_priv('after_sell_stats');
    // 查询条件
    $where = ' WHERE b.supplier_id = ' . $_SESSION['supplier_id']
        . ' AND b.add_time >=' . $start_date . ' AND b.add_time <=' . $end_date;

    $sql = "SELECT FLOOR((b.add_time - $start_date) / (24 * 3600)) date, count(*) repair_count FROM "
        . $ecs->table('back_order') . ' b ' . $where . ' AND b.back_type = 3 GROUP BY date ORDER BY date';
    // 返修件数统计
    $result = $db->getAll($sql);
    foreach($result as $key=>$value)
    {
        $result[$key]['date_arr'] = date('Ymd', $start_date + $value['date'] * 86400);
    }

    // 取得日期、赋初始值
    $date_arr = get_date_arr($start_date, $end_date);
    // 赋值
    foreach($result as $value)
    {
        $date_arr[$value['date_arr']] = $value['repair_count'];
    }
    // 取得图表数据
    foreach($date_arr as $key => $value)
    {
        $date .= $key . ',';
        $repair_count .= $value . ',';
    }

    // 取得返修信息
    $result = get_result_list($start_date, $end_date);

    $smarty->assign('ur_here', $_LANG['report_after_sell']);
    // 开始时间
    $smarty->assign('start_date', local_date($_CFG['date_format'], $start_date));
    // 终了时间
    $smarty->assign('end_date', local_date($_CFG['date_format'], $end_date));
    $smarty->assign('full_page', 1);
    $smarty->assign('repair_info', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);
    // 走势图横坐标
    $smarty->assign('date', $date);
    // 走势图纵坐标
    $smarty->assign('repair_count', $repair_count);
    assign_query_info();
    $smarty->display('repair_stats.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    admin_priv('after_sell_stats');
    $result = get_result_list($start_date, $end_date);

    $smarty->assign('repair_info', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);

    $sort_flag = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('repair_stats.htm'), '',
        array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}

/*------------------------------------------------------ */
//-- 批量导出数据
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'export')
{
    admin_priv('after_sell_stats');
    // 查询条件
    $where = ' WHERE b.supplier_id = ' . $_SESSION['supplier_id']
        . ' AND b.add_time >=' . $start_date . ' AND b.add_time <=' . $end_date;

    // 查询
    $result = $db->getAll(
        "SELECT o.order_sn, b.back_id, b.goods_name, u.user_name, b.add_time, "
        . ' b.refund_money_2, b.status_back, b.back_type, b.status_refund FROM ' . $GLOBALS['ecs']->table('order_info') . ' o, '
        . $GLOBALS['ecs']->table('users') . ' u, ' . $GLOBALS['ecs']->table('back_order') . ' b ' . $where
        . ' AND o.order_id = b.order_id AND b.user_id = u.user_id AND b.back_type = 3 ORDER BY order_sn DESC'
    );
    foreach ($result AS $key => $value)
    {
        $result[$key]['add_time'] = local_date('Y-m-d G:i:s', $value['add_time']);
        $result[$key]['status_back_val'] =
            $GLOBALS['_LANG']['bos'][(($value['back_type'] == 4) ? $value['back_type'] : $value['status_back'])] . "-"
            . (($value['back_type'] == 3) ? "申请维修" : $GLOBALS['_LANG']['bps'][$value['status_refund']]);
    }

    // 引入phpexcel核心类文件
    require_once ROOT_PATH . '/includes/phpexcel/Classes/PHPExcel.php';
    // 实例化excel类
    $objPHPExcel = new PHPExcel();
    // 操作第一个工作表
    $objPHPExcel->setActiveSheetIndex(0);
    // 设置sheet名
    $objPHPExcel->getActiveSheet()->setTitle('返修统计');
    // 设置表格宽度
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    // 列名表头文字加粗
    $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);
    // 列表头文字居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // 列名赋值
    $objPHPExcel->getActiveSheet()->setCellValue('A1', '订单编号');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', '返修编号');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', '商品名称');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', '买家会员名');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', '申请时间');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', '返修状态');

    // 数据起始行
    $row_num = 2;
    // 向每行单元格插入数据
    foreach($result as $value)
    {
        // 设置所有垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'F' . $row_num)->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        // 设置价格为数字格式
        $objPHPExcel->getActiveSheet()->getStyle('F' . $row_num )->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        // 设置单元格数值
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $row_num, $value['order_sn'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row_num, $value['back_id']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row_num, $value['goods_name']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row_num, $value['user_name']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row_num, $value['add_time']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row_num, $value['status_back_val']);
        $row_num++;
    }
    $outputFileName = '返修统计_' . time() . '.xls';
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
 * 分页获取退款信息列表
 *
 * @return array
 */
function get_result_list($start_date, $end_date)
{
    $result = get_filter();
    if($result === false)
    {
        $filter = array();
        $filter['start_date'] = empty($_REQUEST['start_date']) ? $start_date : $_REQUEST['start_date'];
        $filter['end_date'] = empty($_REQUEST['end_date']) ? $end_date : $_REQUEST['end_date'];

        // 查询条件
        $where = ' WHERE b.supplier_id = ' . $_SESSION['supplier_id']
            . ' AND b.add_time >=' . $filter['start_date'] . ' AND b.add_time <=' . $filter['end_date'];

        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('back_order') . ' b ' . $where . ' AND b.back_type = 3';
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询 */
        $sql = "SELECT o.order_sn, b.back_id, b.goods_name, u.user_name, b.add_time, "
            . ' b.refund_money_2, b.status_back, b.back_type, b.status_refund FROM ' . $GLOBALS['ecs']->table('order_info') . ' o, '
            . $GLOBALS['ecs']->table('users') . ' u, ' . $GLOBALS['ecs']->table('back_order') . ' b ' . $where
            . ' AND o.order_id = b.order_id AND b.user_id = u.user_id AND b.back_type = 3 ORDER BY order_sn DESC'
            . " LIMIT " . $filter['start'] . ", $filter[page_size]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql = $result['sql'];
        $filter = $result['filter'];
    }
    $list = $GLOBALS['db']->getAll($sql);
    foreach ($list AS $key => $value)
    {
        $list[$key]['add_time'] = local_date('Y-m-d G:i:s', $value['add_time']);
        $list[$key]['status_back_val'] =
            $GLOBALS['_LANG']['bos'][(($value['back_type'] == 4) ? $value['back_type'] : $value['status_back'])] . "-"
            . (($value['back_type'] == 3) ? "申请维修" : $GLOBALS['_LANG']['bps'][$value['status_refund']]);
    }

    $arr = array(
        'item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']
    );

    return $arr;
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