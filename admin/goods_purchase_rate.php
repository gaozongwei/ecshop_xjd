<?php
/**
 * ECSHOP 商品分析：访问购买率
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com/
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: goods_purchase_rate.php 2015-10-23 10:23:08Z langlibin $
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
$cat_id = isset($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : 0;
$brand_id = isset($_REQUEST['brand_id']) ? $_REQUEST['brand_id'] : 0;

$supplier_list_name = array();
$sql_supplier = "SELECT supplier_id, supplier_name FROM "
    . $GLOBALS['ecs']->table("supplier") . " WHERE status = '1' ORDER BY supplier_id";
$res_supplier = $db->query($sql_supplier);
while($row_supplier = $db->fetchRow($res_supplier))
{
    $supplier_list_name[$row_supplier['supplier_id']] = $row_supplier['supplier_name'];
}

/*------------------------------------------------------ */
//--商品访问购买率
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    admin_priv('goods_stats');

    $result = get_purchase_rate($start_date, $end_date, $cat_id, $brand_id, $sel_shop, $supplier_id);

    $smarty->assign('ur_here', $_LANG['report_goods']);
    // 入驻商
    $smarty->assign('suppliers_list_name', $supplier_list_name);
    // 开始时间
    $smarty->assign('start_date', local_date($_CFG['date_format'], $start_date));
    // 终了时间
    $smarty->assign('end_date', local_date($_CFG['date_format'], $end_date));
    $smarty->assign('full_page', 1);

    $smarty->assign('purchase', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);
    // 选择店铺
    $smarty->assign('sel_shop', $sel_shop);
    // 第三方店铺
    $smarty->assign('supplier_id', $supplier_id);
    // 品牌
    $smarty->assign('brand_list',   get_brand_list());
    $sort_flag = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    /* 显示客服列表页面 */
    assign_query_info();
    $smarty->display('goods_purchase_rate.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    admin_priv('goods_stats');

    $result = get_purchase_rate($start_date, $end_date, $cat_id, $brand_id, $sel_shop, $supplier_id);

    $smarty->assign('purchase', $result['item']);
    $smarty->assign('filter', $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count', $result['page_count']);

    $sort_flag = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('goods_purchase_rate.htm'), '',
        array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}

/*------------------------------------------------------ */
//-- 批量导出数据
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'export')
{
    admin_priv('goods_stats');

    // 查询条件
    $where1 = ' WHERE i.add_time >=' . $start_date . ' AND i.add_time <=' . $end_date
        . ' AND ((i.pay_id = 6 AND i.shipping_status = 2) OR (i.pay_id <> 6 AND i.pay_status = 2))';
    $where2 = ' WHERE g.is_delete = 0 AND g.is_virtual = 0 ';

    // 按店铺查询
    if($sel_shop == 1)
    {
        // 平台方
        $where2 .= ' AND g.supplier_id = 0';
    }
    elseif($sel_shop == 2)
    {
        // 入驻商
        if(($supplier_id == 0))
        {
            // 所有入驻商
            $where2 .= ' AND g.supplier_id <> 0 AND g.supplier_status = 1 ';
        }
        else
        {
            $where2 .= ' AND g.supplier_status = 1 AND g.supplier_id = ' . $supplier_id;
        }
    }

    // 品牌
    if(!empty($cat_id))
    {
        $where2 .= ' AND ' . get_children($cat_id);
    }
    // 商品分类
    if(!empty($brand_id))
    {
        $where2 .= ' AND g.brand_id = ' . $brand_id;
    }

    // 记录数
    $cnt = $GLOBALS['db']->getOne(
        'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' g ' . $where2
    );

    // 查询
    $result = $db->getAll(
        'SELECT g.goods_name, g.click_count, (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_goods')
        . ' og LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' i ON og.order_id = i.order_id ' . $where1
        . ' AND og.goods_id = g.goods_id ) purchase_number, IFNULL(FORMAT((SELECT COUNT(*) FROM '
        . $GLOBALS['ecs']->table('order_goods')
        . ' og LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' i ON og.order_id = i.order_id ' . $where1
        . ' AND og.goods_id = g.goods_id )/click_count*100, 2), 0.00) purchase_rate FROM '
        . $GLOBALS['ecs']->table('goods') . ' g LEFT JOIN '
        . $GLOBALS['ecs']->table('brand') . ' b ON b.brand_id = g.brand_id ' . $where2
        . ' GROUP BY g.goods_name, g.click_count, purchase_number ORDER BY click_count DESC'
    );

    // 引入phpexcel核心类文件
    require_once ROOT_PATH . '/includes/phpexcel/Classes/PHPExcel.php';
    // 实例化excel类
    $objPHPExcel = new PHPExcel();
    // 操作第一个工作表
    $objPHPExcel->setActiveSheetIndex(0);
    // 设置sheet名
    $objPHPExcel->getActiveSheet()->setTitle('访问购买率');
    // 设置表格宽度
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(60);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    // 列名表头文字加粗
    $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
    // 列表头文字居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // 列名赋值
    $objPHPExcel->getActiveSheet()->setCellValue('A1', '商品名称');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', '访问人气');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', '购买次数');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', '访问购买率');

    // 数据起始行
    $row_num = 2;
    // 向每行单元格插入数据
    foreach($result as $value)
    {
        // 设置所有垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'E' . $row_num)->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        // 设置单元格数值
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row_num, $value['goods_name']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row_num, $value['click_count']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row_num, $value['purchase_number']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $row_num, $value['purchase_rate'] . '%');
        $row_num++;
    }
    $outputFileName = '访问购买率_' . time() . '.xls';
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
 * 分页获取商品访问购买率列表
 *
 * @return array
 */
function get_purchase_rate($start_date, $end_date, $cat_id, $brand_id, $sel_shop, $supplier_id)
{
    $result = get_filter();
    if($result === false)
    {
        $filter = array();
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'click_count' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['cat_id'] = empty($_REQUEST['cat_id']) ? $cat_id : $_REQUEST['cat_id'];
        $filter['brand_id'] = empty($_REQUEST['brand_id']) ? $brand_id : $_REQUEST['brand_id'];
        $filter['sel_shop'] = empty($_REQUEST['sel_shop']) ? $sel_shop : $_REQUEST['sel_shop'];
        $filter['supplier_id'] = empty($_REQUEST['supplier_id']) ? $supplier_id : $_REQUEST['supplier_id'];
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
        $where1 = ' WHERE i.add_time >=' . $start_date . ' AND i.add_time <=' . $end_date
            . ' AND ((i.pay_id = 6 AND i.shipping_status = 2) OR (i.pay_id <> 6 AND i.pay_status = 2))';
        $where2 = ' WHERE g.is_delete = 0 AND g.is_virtual = 0 ';

        // 按店铺查询
        if($filter['sel_shop'] == 1)
        {
            // 平台方
            $where2 .= ' AND g.supplier_id = 0';
        }
        elseif($filter['sel_shop'] == 2)
        {
            // 入驻商
            if(($filter['supplier_id'] == 0))
            {
                // 所有入驻商
                $where2 .= ' AND g.supplier_id <> 0 AND g.supplier_status = 1 ';
            }
            else
            {
                $where2 .= ' AND g.supplier_status = 1 AND g.supplier_id = ' . $filter['supplier_id'];
            }
        }

        // 品牌
        if(!empty($cat_id))
        {
            $where2 .= ' AND ' . get_children($cat_id);
        }
        // 商品分类
        if(!empty($brand_id))
        {
            $where2 .= ' AND g.brand_id = ' . $brand_id;
        }

        // 记录数
        $cnt = $GLOBALS['db']->getOne(
            'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('goods') . ' g ' . $where2
        );

        $filter['record_count'] = $cnt;
        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询 */
        $sql = 'SELECT g.goods_name, g.click_count, (SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_goods')
            . ' og LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' i ON og.order_id = i.order_id ' . $where1
            . ' AND og.goods_id = g.goods_id ) purchase_number, IFNULL(FORMAT((SELECT COUNT(*) FROM '
            . $GLOBALS['ecs']->table('order_goods')
            . ' og LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' i ON og.order_id = i.order_id ' . $where1
            . ' AND og.goods_id = g.goods_id )/click_count*100, 2), 0.00) purchase_rate FROM '
            . $GLOBALS['ecs']->table('goods') . ' g LEFT JOIN '
            . $GLOBALS['ecs']->table('brand') . ' b ON b.brand_id = g.brand_id ' . $where2
            . ' GROUP BY g.goods_name, g.click_count, purchase_number ORDER BY ' . " $filter[sort_by] $filter[sort_order] "
            . " LIMIT " . $filter['start'] . ", $filter[page_size]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql = $result['sql'];
        $filter = $result['filter'];
    }
    $list = $GLOBALS['db']->getAll($sql);

    $arr = array(
        'item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']
    );

    return $arr;
}
?>