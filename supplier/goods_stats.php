<?php
/**
 * ECSHOP 商品分析：商品销售排行
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com/
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: goods_stats.php 2015-10-22 10:00:08Z langlibin $
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


/*------------------------------------------------------ */
//--商品销量排行
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    admin_priv('goods_stats');

    $result = get_sell_list($start_date, $end_date);

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

    $sort_flag = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    /* 显示客服列表页面 */
    assign_query_info();
    $smarty->display('goods_stats.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    admin_priv('goods_stats');

    $result = get_sell_list($start_date, $end_date);

    $smarty->assign('sell_list', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);

    $sort_flag = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('goods_stats.htm'), '',
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

    // 查询
    $sql = 'SELECT g.goods_id, g.goods_sn, g.goods_name, g.goods_attr, SUM(g.goods_number) sales_volume, SUM(g.goods_price*g.goods_number) sales_money, '
        . 'AVG(g.goods_price) average_price FROM '
        . $GLOBALS['ecs']->table('order_goods') . ' g, ' . $GLOBALS['ecs']->table('order_info') . ' i ' . $where
        . ' AND g.order_id = i.order_id GROUP BY g.goods_id, g.goods_sn, g.goods_name, g.goods_attr ORDER BY sales_volume DESC';

    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if(!empty($row['goods_attr']))
        {
            $row['goods_name'] .= ' [' . $row['goods_attr'] . ']';
        }
        $result[] = $row;
    }

    // 引入phpexcel核心类文件
    require_once ROOT_PATH . '/includes/phpexcel/Classes/PHPExcel.php';
    // 实例化excel类
    $objPHPExcel = new PHPExcel();
    // 操作第一个工作表
    $objPHPExcel->setActiveSheetIndex(0);
    // 设置sheet名
    $objPHPExcel->getActiveSheet()->setTitle('商品销售排行');
    // 设置表格宽度
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
    // 列名表头文字加粗
    $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);
    // 列表头文字居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // 列名赋值
    $objPHPExcel->getActiveSheet()->setCellValue('A1', '排行');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', '商品名称');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', '货号');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', '销量');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', '销售总额');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', '均价');

    // 数据起始行
    $row_num = 2;
    // 向每行单元格插入数据
    foreach($result as $value)
    {
        // 设置所有垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'F' . $row_num)->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        // 设置价格为数字格式
        $objPHPExcel->getActiveSheet()->getStyle('E' . $row_num . ':' . 'F' . $row_num)->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);

        // 设置单元格数值
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row_num, $row_num - 1);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row_num, $value['goods_name']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row_num, $value['goods_sn']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row_num, $value['sales_volume']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row_num, $value['sales_money']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row_num, $value['average_price']);
        $row_num++;
    }
    $outputFileName = '商品销售排行_' . time() . '.xls';
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
function get_sell_list($start_date, $end_date)
{
    $result = get_filter();
    if($result === false)
    {
    	
        $filter = array();
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'sales_volume' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['start_date'] = empty($_REQUEST['start_date']) ?  $start_date : $_REQUEST['start_date'];
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

        // 记录数
        $cnt = $GLOBALS['db']->getOne(
            'SELECT COUNT(*) FROM (SELECT DISTINCT goods_id FROM '. $GLOBALS['ecs']->table('order_goods')
            .' WHERE order_id IN (SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info') . ' i ' . $where . ')) t'
        );

        $filter['record_count'] = $cnt;
        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询 */
        $sql = 'SELECT g.goods_id, g.goods_sn, g.goods_name, g.goods_attr, SUM(g.goods_number) sales_volume, FORMAT(SUM(g.goods_price*g.goods_number), 2) sales_money, '
            . 'FORMAT(AVG(g.goods_price), 2) average_price FROM '
            . $GLOBALS['ecs']->table('order_goods') . ' g, ' . $GLOBALS['ecs']->table('order_info') . ' i ' . $where
            . ' AND g.order_id = i.order_id GROUP BY g.goods_id, g.goods_sn, g.goods_name, g.goods_attr'
            . " ORDER BY $filter[sort_by] $filter[sort_order] " . " LIMIT " . $filter['start'] . ", $filter[page_size]";

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
        $list[$key]['no'] = $key + 1 + $filter['start'];
        if(!empty($list[$key]['goods_attr']))
        {
            $list[$key]['goods_name'] .= ' [' . $list[$key]['goods_attr'] . ']';
        }
    }
    $arr = array(
        'item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']
    );

    return $arr;
}
?>