<?php
/**
 * ECSHOP 商品分析：商品销售明细
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com/
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: goods_sell_detail.php 2015-10-22 16:30:08Z langlibin $
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
$is_multi = empty($_POST['is_multi']) ? false : true;

// 时间参数
if (isset($_REQUEST['date_type']) && !empty($_REQUEST['date_type']) && $_REQUEST['date_type'] ==1)
{
	if (isset($_REQUEST['start_date']) && !empty($_REQUEST['end_date']))
	{
	    $start_date = local_strtotime($_REQUEST['start_date']);
	    $end_date = local_strtotime($_REQUEST['end_date']);
	    if ($start_date == $end_date)
	    {
	        $end_date = $start_date + 86400;
	    }
	}
	else
	{
	    $today = local_strtotime(local_date('Y-m-d'));   //本地时间
	    $start_date = $today - 86400 * 6;
	    $end_date = $today + 86400;               //至明天零时
	}
}else{
	$today = local_strtotime(local_date('Y-m-d'));   //本地时间
	$start_date = $today - 86400 * 6;
	$end_date = $today + 86400;               //至明天零时
}
$cat_id = isset($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : 0;
$brand_id = isset($_REQUEST['brand_id']) ? $_REQUEST['brand_id'] : 0;

/*------------------------------------------------------ */
//--商品销售明细
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    admin_priv('goods_stats');

    $result = get_sell_detail($start_date, $end_date, $cat_id, $brand_id);

    $smarty->assign('ur_here', $_LANG['report_goods']);
    // 开始时间
    $smarty->assign('start_date', local_date($_CFG['date_format'], $start_date));
    // 终了时间
    $smarty->assign('end_date', local_date($_CFG['date_format'], $end_date));
    $smarty->assign('full_page', 1);

    $smarty->assign('sell_list', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);

    $smarty->assign('cat_list',     cat_list_supplier(0, $cat_id));
    $smarty->assign('brand_list',   get_brand_list());
    $sort_flag = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    /* 显示客服列表页面 */
    assign_query_info();
    $smarty->display('goods_sell_detail.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    admin_priv('goods_stats');

    $result = get_sell_detail($start_date, $end_date, $cat_id, $brand_id);

    $smarty->assign('sell_list', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);

    $sort_flag = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('goods_sell_detail.htm'), '',
        array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}

/*------------------------------------------------------ */
//-- 批量导出数据
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'export')
{
    admin_priv('goods_stats');

    // 查询条件
    $where = ' WHERE i.supplier_id = ' . $_SESSION['supplier_id']
        . ' AND i.add_time >=' . $start_date . ' AND i.add_time <=' . $end_date
        . ' AND ((i.pay_id = 6 AND i.shipping_status = 2) OR (i.pay_id <> 6 AND i.pay_status = 2))';

    // 品牌
    if(!empty($cat_id))
    {
        $where .= ' AND ' . get_children($cat_id);
    }
    // 商品分类
    if(!empty($brand_id))
    {
        $where .= ' AND b.brand_id = ' . $brand_id;
    }

    // 查询
    $sql = "SELECT og.goods_name, og.goods_attr, i.order_sn, og.goods_number sells_count, og.goods_price, i.add_time FROM "
        . $GLOBALS['ecs']->table('order_goods') . ' og LEFT JOIN ' . $GLOBALS['ecs']->table('order_info')
        . ' i ON og.order_id = i.order_id LEFT JOIN ' . $GLOBALS['ecs']->table('goods')
        . ' g ON g.goods_id = og.goods_id LEFT JOIN ' . $GLOBALS['ecs']->table('brand')
        . ' b ON g.brand_id = b.brand_id ' . $where . ' GROUP BY og.goods_name, i.order_sn, '
        . ' og.goods_price, i.add_time ORDER BY sells_count';

    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if(!empty($row['goods_attr']))
        {
            $row['goods_name'] .= ' [' . $row['goods_attr'] . ']';
        }
        $row['add_time'] = local_date('Y-m-d G:i:s', $row['add_time']);
        $result[] = $row;
    }

    // 引入phpexcel核心类文件
    require_once ROOT_PATH . '/includes/phpexcel/Classes/PHPExcel.php';
    // 实例化excel类
    $objPHPExcel = new PHPExcel();
    // 操作第一个工作表
    $objPHPExcel->setActiveSheetIndex(0);
    // 设置sheet名
    $objPHPExcel->getActiveSheet()->setTitle('商品销售明细');
    // 设置表格宽度
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(60);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    // 列名表头文字加粗
    $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);
    // 列表头文字居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // 列名赋值
    $objPHPExcel->getActiveSheet()->setCellValue('A1', '商品名称');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', '订单号');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', '销售数量');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', '销售价格');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', '下单日期');

    // 数据起始行
    $row_num = 2;
    // 向每行单元格插入数据
    foreach($result as $value)
    {
        // 设置所有垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'E' . $row_num)->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        // 设置价格为数字格式
        $objPHPExcel->getActiveSheet()->getStyle('D' . $row_num)->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);

        // 设置单元格数值
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row_num, $value['goods_name']);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $row_num, $value['order_sn'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row_num, $value['sells_count']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row_num, $value['goods_price']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row_num, $value['add_time']);
        $row_num++;
    }
    $outputFileName = '商品销售明细_' . time() . '.xls';
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
 * 分页获取商品销售排行列表
 *
 * @return array
 */
function get_sell_detail($start_date, $end_date, $cat_id, $brand_id)
{
    $result = get_filter();
    if($result === false)
    {
        $filter = array();
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'sells_count' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['cat_id'] = empty($_REQUEST['cat_id']) ? $cat_id : $_REQUEST['cat_id'];
        $filter['brand_id'] = empty($_REQUEST['brand_id']) ? $brand_id : $_REQUEST['brand_id'];
        $filter['start_date'] = empty($_REQUEST['start_date']) ? $start_date : $_REQUEST['start_date'];
        $filter['end_date'] = empty($_REQUEST['end_date']) ? $end_date : $_REQUEST['end_date'];

        if(is_int($_REQUEST['start_date']))
        {
            $start_date = $_REQUEST['start_date'];
        }
        if(is_int($_REQUEST['end_date']))
        {
            $end_date = $_REQUEST['end_date'];
        }
        // 查询条件
        $where = ' WHERE i.supplier_id = ' . $_SESSION['supplier_id']
            . ' AND i.add_time >=' . $start_date . ' AND i.add_time <=' . $end_date
            . ' AND ((i.pay_id = 6 AND i.shipping_status = 2) OR (i.pay_id <> 6 AND i.pay_status = 2))';

        // 品牌
        if(!empty($cat_id))
        {
            $where .= ' AND ' . get_children($cat_id);
        }
        // 商品分类
        if(!empty($brand_id))
        {
            $where .= ' AND b.brand_id = ' . $brand_id;
        }
        // 记录数
        $filter['record_count'] = $GLOBALS['db']->getOne(
            'SELECT COUNT(*) FROM (SELECT og.order_id FROM ' . $GLOBALS['ecs']->table('order_goods')
            . ' og LEFT JOIN '. $GLOBALS['ecs']->table('order_info') . ' i ON og.order_id = i.order_id LEFT JOIN '
            . $GLOBALS['ecs']->table('goods') . ' g ON g.goods_id = og.goods_id LEFT JOIN '
            . $GLOBALS['ecs']->table('brand') . ' b ON g.brand_id = b.brand_id ' . $where
            . ' GROUP BY og.goods_name, i.order_sn, og.goods_price, i.add_time ) t'
        );

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询 */
        $sql = "SELECT og.goods_name, og.goods_attr, i.order_sn, og.goods_number sells_count, og.goods_price, i.add_time FROM "
            . $GLOBALS['ecs']->table('order_goods') . ' og LEFT JOIN ' . $GLOBALS['ecs']->table('order_info')
            . ' i ON og.order_id = i.order_id LEFT JOIN ' . $GLOBALS['ecs']->table('goods')
            . ' g ON g.goods_id = og.goods_id LEFT JOIN ' . $GLOBALS['ecs']->table('brand')
            . ' b ON g.brand_id = b.brand_id ' . $where . ' GROUP BY og.goods_name, i.order_sn, og.goods_price, i.add_time '
            . " ORDER BY $filter[sort_by] $filter[sort_order] " . " LIMIT " . $filter['start'] . ", $filter[page_size]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql = $result['sql'];
        $filter = $result['filter'];
    }
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if(!empty($row['goods_attr']))
        {
            $row['goods_name'] .= ' [' . $row['goods_attr'] . ']';
        }
        $row['add_time'] = local_date('Y-m-d G:i:s', $row['add_time']);
        $list[] = $row;
    }

    $arr = array(
        'item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']
    );

    return $arr;
}
?>