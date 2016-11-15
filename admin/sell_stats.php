<?php
/**
 * ECSHOP 销售报告：销售统计
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com/
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: sell_stats.php 2015-10-23 13:00:08Z langlibin $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/statistic.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/order.php');
require_once(ROOT_PATH . 'includes/lib_order.php');

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

// 店铺参数
$sel_shop = isset($_REQUEST['sel_shop']) && !empty($_REQUEST['sel_shop']) ? $_REQUEST['sel_shop'] : 0;
// 取得入驻商id
if ($sel_shop == 2)
{
    $supplier_id = $_REQUEST['supplier_id'];
}
else
{
    $supplier_id = 0;
}

$supplier_list_name = array();
$sql_supplier = "SELECT supplier_id, supplier_name FROM "
    . $GLOBALS['ecs']->table("supplier") . " WHERE status = '1' ORDER BY supplier_id";
$res_supplier = $db->query($sql_supplier);
while($row_supplier = $db->fetchRow($res_supplier))
{
    $supplier_list_name[$row_supplier['supplier_id']] = $row_supplier['supplier_name'];
}

/*------------------------------------------------------ */
//--商品销量统计
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    admin_priv('sells_stats');

    // 批量导出
    if(isset($_REQUEST['export']))
    {
        export($start_date, $end_date, $sel_shop, $supplier_id, $_LANG);
    }

    // 查询条件
    $where = ' WHERE o.add_time >=' . $start_date . ' AND o.add_time <=' . $end_date;
    // 按店铺查询
    if($sel_shop == 1)
    {
        // 平台方
        $where .= ' AND o.supplier_id = 0';
    }
    elseif($sel_shop == 2)
    {
        // 入驻商
        if(($supplier_id == 0))
        {
            // 所有入驻商
            $where .= ' AND o.supplier_id <> 0';
        }
        else
        {
            $where .= ' AND o.supplier_id = ' . $supplier_id;
        }
    }

    // 订单状态
    switch($_REQUEST['status'])
    {
        case CS_AWAIT_PAY :
            $where .= order_query_sql('await_pay');
            break;

        case CS_AWAIT_SHIP :
            $where .= order_query_sql('await_ship');
            break;

        case CS_FINISHED :
            $where .= order_query_sql('finished');
            break;

        case PS_PAYING :
            if ($_REQUEST['status'] != -1)
            {
                $where .= " AND o.pay_status = '$_REQUEST[status]' ";
            }
            break;
        case OS_SHIPPED_PART :
            if ($_REQUEST['status'] != -1)
            {
                $where .= " AND o.shipping_status  = '$_REQUEST[status]'-2 ";
            }
            break;
        default:
            if (isset($_REQUEST['status']) && $_REQUEST['status'] != -1)
            {
                $where .= " AND o.order_status = '$_REQUEST[status]' ";
            }
    }

    // 下单金额
    $order_money = $db->getOne(
        'SELECT SUM(o.goods_amount + o.tax + o.shipping_fee + o.insure_fee + o.pay_fee + o.pack_fee + o.card_fee) FROM '
        . $ecs->table('order_info') . ' o ' . $where
    );
    $order_money = empty($order_money) ? 0 : $order_money;

    // 下单量
    $order_count = $db->getOne(
        'SELECT COUNT(*) FROM ' . $ecs->table('order_info') . ' o ' . $where
    );

    // 下单金额统计
    $order_money_list = $db->getAll(
        "SELECT FLOOR((o.add_time - $start_date) / (24 * 3600)) date, "
        . "SUM(o.goods_amount + o.tax + o.shipping_fee + o.insure_fee + o.pay_fee + o.pack_fee + o.card_fee) goods_amount FROM "
        . $ecs->table('order_info') . ' o '  . $where . ' GROUP BY date ORDER BY date'
    );
    foreach($order_money_list as $key=>$value)
    {
        $order_money_list[$key]['date_arr'] = date('Ymd', $start_date + $value['date'] * 86400);
    }

    // 取得日期、赋初始值
    $date_order_money_arr = get_date_arr($start_date, $end_date);
    // 赋值
    foreach($order_money_list as $value)
    {
        $date_order_money_arr[$value['date_arr']] = $value['goods_amount'];
    }
    $count = 1;
    foreach($date_order_money_arr as $key => $value)
    {
        $date .= $key . ',';
        $goods_amount .= $value . ',';
    }

    // 下单量统计
    $order_count_list = $db->getAll(
        "SELECT FLOOR((o.add_time - $start_date) / (24 * 3600)) date, COUNT(*) goods_count FROM "
        . $ecs->table('order_info') . ' o ' . $where . ' GROUP BY date ORDER BY date'
    );
    foreach($order_count_list as $key => $value)
    {
        $order_count_list[$key]['date_arr'] = date('Ymd', $start_date + $value['date'] * 86400);
    }

    // 取得日期、赋初始值
    $date_order_count_arr = get_date_arr($start_date, $end_date);
    // 赋值
    foreach($order_count_list as $value)
    {
        $date_order_count_arr[$value['date_arr']] = $value['goods_count'];
    }
    foreach($date_order_count_arr as $key => $value)
    {
        $goods_count .= $value . ',';
    }

    // 取得订单信息
    $result = get_result_list($start_date, $end_date, $sel_shop, $supplier_id);

    $smarty->assign('ur_here', $_LANG['report_sell']);
    // 开始时间
    $smarty->assign('start_date', local_date($_CFG['date_format'], $start_date));
    // 终了时间
    $smarty->assign('end_date', local_date($_CFG['date_format'], $end_date));
    $smarty->assign('full_page', 1);
    // 状态
    $smarty->assign('status_list', $_LANG['cs']);
    $smarty->assign('status', $_REQUEST['status']);
    // 下单金额
    $smarty->assign('order_money', number_format($order_money, 2));
    // 下单量
    $smarty->assign('order_count', $order_count);
    // 日期字符串
    $smarty->assign('date', $date);
    // 下单金额字符串
    $smarty->assign('goods_amount', $goods_amount);
    // 下单量字符串
    $smarty->assign('goods_count', $goods_count);
    // 订单信息
    $smarty->assign('order_info', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);
    // 入驻商
    $smarty->assign('suppliers_list_name', $supplier_list_name);
    // 选择店铺
    $smarty->assign('sel_shop', $sel_shop);
    // 第三方店铺
    $smarty->assign('supplier_id', $supplier_id);

    /* 显示客服列表页面 */
    assign_query_info();
    $smarty->display('sell_stats.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    admin_priv('sells_stats');

    $result = get_result_list($start_date, $end_date, $sel_shop, $supplier_id);

    $smarty->assign('order_info', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);

    $sort_flag = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('sell_stats.htm'), '',
        array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}

// 批量导出数据
function export($start_date, $end_date, $sel_shop, $supplier_id, $_LANG)
{
    admin_priv('sells_stats');

    // 查询条件
    $where = ' WHERE o.add_time >=' . $start_date . ' AND o.add_time <=' . $end_date;

    // 按店铺查询
    if($sel_shop == 1)
    {
        // 平台方
        $where .= ' AND o.supplier_id = 0';
    }
    elseif($sel_shop == 2)
    {
        // 入驻商
        if(($supplier_id == 0))
        {
            // 所有入驻商
            $where .= ' AND o.supplier_id <> 0';
        }
        else
        {
            $where .= ' AND o.supplier_id = ' . $supplier_id;
        }
    }
    // 订单状态
    switch($_REQUEST['status'])
    {
        case CS_AWAIT_PAY :
            $where .= order_query_sql('await_pay');
            break;

        case CS_AWAIT_SHIP :
            $where .= order_query_sql('await_ship');
            break;

        case CS_FINISHED :
            $where .= order_query_sql('finished');
            break;

        case PS_PAYING :
            if ($_REQUEST['status'] != -1)
            {
                $where .= " AND o.pay_status = '$_REQUEST[status]' ";
            }
            break;
        case OS_SHIPPED_PART :
            if ($_REQUEST['status'] != -1)
            {
                $where .= " AND o.shipping_status  = '$_REQUEST[status]'-2 ";
            }
            break;
        default:
            if (isset($_REQUEST['status']) && $_REQUEST['status'] != -1)
            {
                $where .= " AND o.order_status = '$_REQUEST[status]' ";
            }
    }

    // 查询
    $result = $GLOBALS['db']->getAll(
        "SELECT o.order_sn, u.user_name, o.add_time, (o.goods_amount + o.tax + "
        . "o.shipping_fee + o.insure_fee + o.pay_fee + o.pack_fee + o.card_fee ) goods_amount, o.order_status FROM "
        . $GLOBALS['ecs']->table('order_info') . ' o, ' . $GLOBALS['ecs']->table('users') . ' u '
        . $where . ' AND o.user_id = u.user_id ORDER BY order_sn DESC'
    );
    foreach ($result AS $key => $value)
    {
        $result[$key]['add_time'] = local_date('Y-m-d G:i:s', $value['add_time']);
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
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    // 列名表头文字加粗
    $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);
    // 列表头文字居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // 列名赋值
    $objPHPExcel->getActiveSheet()->setCellValue('A1', '订单编号');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', '买家');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', '下单时间');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', '订单总额');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', '订单状态');

    // 数据起始行
    $row_num = 2;
    // 向每行单元格插入数据
    foreach($result as $value)
    {
        // 设置所有垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'E' . $row_num)->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        // 设置价格为数字格式
        $objPHPExcel->getActiveSheet()->getStyle('D' . $row_num )->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        // 设置单元格数值
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $row_num, $value['order_sn'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row_num, $value['user_name']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row_num, $value['add_time']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row_num, $value['goods_amount']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row_num, $_LANG['os'][$value['order_status']]);
        $row_num++;
    }
    $outputFileName = '销售统计_' . time() . '.xls';
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
 * 分页获取订单信息列表
 *
 * @return array
 */
function get_result_list($start_date, $end_date, $sel_shop, $supplier_id)
{
    $result = get_filter();
    if($result === false)
    {
        $filter = array();
        $filter['start_date'] = empty($_REQUEST['start_date']) ? $start_date : $_REQUEST['start_date'];
        $filter['end_date'] = empty($_REQUEST['end_date']) ? $end_date : $_REQUEST['end_date'];
        $filter['status'] = isset($_REQUEST['status']) ? $_REQUEST['status'] : -1;
        $filter['sel_shop'] = empty($_REQUEST['sel_shop']) ? $sel_shop : $_REQUEST['sel_shop'];
        $filter['supplier_id'] = empty($_REQUEST['supplier_id']) ? $supplier_id : $_REQUEST['supplier_id'];

        // 查询条件
        $where = ' WHERE o.add_time >=' . $filter['start_date'] . ' AND o.add_time <=' . $filter['end_date'];
        // 按店铺查询
        if($filter['sel_shop'] == 1)
        {
            // 平台方
            $where .= ' AND o.supplier_id = 0';
        }
        elseif($filter['sel_shop'] == 2)
        {
            // 入驻商
            if(($filter['supplier_id'] == 0))
            {
                // 所有入驻商
                $where .= ' AND o.supplier_id <> 0';
            }
            else
            {
                $where .= ' AND o.supplier_id = ' . $filter['supplier_id'];
            }
        }
        // 订单状态
        switch($filter['status'])
        {
            case CS_AWAIT_PAY :
                $where .= order_query_sql('await_pay');
                break;

            case CS_AWAIT_SHIP :
                $where .= order_query_sql('await_ship');
                break;

            case CS_FINISHED :
                $where .= order_query_sql('finished');
                break;

            case PS_PAYING :
                if ($filter['status'] != -1)
                {
                    $where .= " AND o.pay_status = '$filter[status]' ";
                }
                break;
            case OS_SHIPPED_PART :
                if ($filter['status'] != -1)
                {
                    $where .= " AND o.shipping_status  = '$filter[status]'-2 ";
                }
                break;
            default:
                if (isset($filter['status']) && $filter['status'] != -1)
                {
                    $where .= " AND o.order_status = '$filter[status]' ";
                }
        }

        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('order_info') . ' o ' . $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询 */
        $sql = "SELECT o.order_sn, u.user_name, o.add_time, (o.goods_amount + o.tax + o.shipping_fee + "
            . "o.insure_fee + o.pay_fee + o.pack_fee + o.card_fee ) goods_amount, o.order_status FROM "
            . $GLOBALS['ecs']->table('order_info') . ' o, ' . $GLOBALS['ecs']->table('users') . ' u '
            . $where . ' AND o.user_id = u.user_id ORDER BY order_sn DESC'
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