<?php
/**
 * ECSHOP 订单统计
 * ============================================================================
 * 版权所有 2005-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com/
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: langlibin $
 * $Id: order_stats.php 2015-10-28 08:45:08Z langlibin $
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

/*------------------------------------------------------ */
//--订单统计
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    admin_priv('orders_stats');
    // 店铺参数
    $sel_shop = isset($_POST['sel_shop']) && !empty($_POST['sel_shop']) ? $_POST['sel_shop'] : 0;
    // 取得入驻商id
    if ($sel_shop == 2)
    {
        $supplier_id = $_POST['supplier_id'];
    }
    else
    {
        $supplier_id = 0;
    }

    // 时间参数
    if (isset($_POST['start_date']) && !empty($_POST['end_date']))
    {
        $start_date = strtotime($_POST['start_date']);
        $end_date = strtotime($_POST['end_date']);
        if ($start_date == $end_date)
        {
            $end_date = $start_date + 86400;
        }
    }
    else
    {
        $today = strtotime(local_date('Y-m-d'));   //本地时间
        $start_date = $today - 86400 * 6;
        $end_date = $today + 86400;               //至明天零时
    }

    // 查询条件
    $where = ' WHERE add_time >=' . $start_date . ' AND add_time <=' . $end_date
        . ' AND ((pay_id = 6 AND shipping_status = 2) OR (pay_id <> 6 AND pay_status = 2))';

    // 按店铺查询
    if($sel_shop > 0)
    {
        if ($supplier_id == -1)
        {
            // 查询所有入驻商
            $where .= ' AND supplier_id <> 0';
        }
        else
        {
            // 根据店铺id查询
            $where .= ' AND supplier_id = ' . $supplier_id;
        }
    }

    // 下单金额
    $order_money = $db->getOne(
        'SELECT SUM(goods_amount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee) FROM '
        . $ecs->table('order_info') . $where
    );
    $order_money = empty($order_money) ? 0 : $order_money;
    // 下单会员数
    $order_member = $db->getOne(
        'SELECT COUNT(*) FROM (SELECT DISTINCT user_id FROM ' . $ecs->table('order_info') . $where . ') o'
    );
    // 下单量
    $order_count = $db->getOne(
        'SELECT COUNT(*) FROM ' . $ecs->table('order_info') . $where
    );
    // 下单商品数
    $order_goods_number = $db->getOne(
        'SELECT COUNT(*) FROM ' . $ecs->table('order_goods') . ' WHERE order_id IN ( '
        . 'SELECT order_id FROM ' . $ecs->table('order_info') . $where . ')'
    );

    // 平均客单价
    $average_member = $order_money / $order_count;
    $average_member = empty($average_member) ? 0 : $average_member;
    // 商品平均价格
    $average_goods = $order_money / $order_goods_number;
    $average_goods = empty($average_goods) ? 0 : $average_goods;

    $where1 = '';
    $where2 = '';
    if($sel_shop > 0)
    {
        if ($supplier_id == -1)
        {
            // 查询所有入驻商
            $where1 .= ' WHERE supplier_id <> 0';
            $where2 .= ' WHERE supplierid <> 0';
        }
        else
        {
            // 根据店铺id查询
            $where1 .= ' WHERE supplier_id = ' . $supplier_id;
            $where2 .= ' WHERE supplierid = ' . $supplier_id;
        }
    }
    // 商品收藏量
    $goods_save = $db->getOne(
        'SELECT COUNT(*) FROM ' . $ecs->table('collect_goods') . 'WHERE goods_id IN ('
        . 'SELECT goods_id FROM ' . $ecs->table('goods') . $where1 . ')'
    );
    // 商品总数
    $goods_number = $db->getOne(
        'SELECT COUNT(*) FROM ' . $ecs->table('goods') . $where1
    );

    // 店铺收藏量
    $shop_save = $db->getOne(
        'SELECT COUNT(*) FROM ' . $ecs->table('supplier_guanzhu') . $where2
    );

    // 销售走势
    $result = $db->getAll(
        "SELECT FLOOR((add_time - $start_date) / (24 * 3600)) date, "
        . "SUM(goods_amount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee) goods_amount FROM "
        . $ecs->table('order_info') . $where . ' GROUP BY date ORDER BY date'
    );

    foreach($result as $key=>$value)
    {
        $result[$key]['date_arr'] = date('Ymd', $start_date + $value['date'] * 86400);
    }
    // 取得日期、赋初始值
    $date_arr = get_date_arr($start_date, $end_date);
    // 赋值
    foreach($result as $value)
    {
        $date_arr[$value['date_arr']] = $value['goods_amount'];
    }
    // 记录循环次数，超过30退出
    $count = 1;
    foreach($date_arr as $key => $value)
    {
        if($count > 30)
        {
            break;
        }
        $date .= $key . ',';
        $goods_amount .= $value . ',';
        $count++;
    }

    $supplier_list_name=array();
    $sql_supplier = "SELECT supplier_id, supplier_name FROM "
        . $GLOBALS['ecs']->table("supplier") . " WHERE status = '1' ORDER BY supplier_id";
    $res_supplier = $db->query($sql_supplier);
    while($row_supplier=$db->fetchRow($res_supplier))
    {
        $supplier_list_name[$row_supplier['supplier_id']] = $row_supplier['supplier_name'];
    }

    // 模板赋值
    $smarty->assign('ur_here', $_LANG['report_order']);
    // 入驻商
    $smarty->assign('suppliers_list_name', $supplier_list_name);
    // 开始时间
    $smarty->assign('start_date', local_date($_CFG['date_format'], $start_date));
    // 终了时间
    $smarty->assign('end_date', local_date($_CFG['date_format'], $end_date));
    // 下单金额
    $smarty->assign('order_money', number_format($order_money, 2));
    // 下单会员数
    $smarty->assign('order_member', $order_member);
    // 下单量
    $smarty->assign('order_count', $order_count);
    // 下单商品数
    $smarty->assign('order_goods_number', $order_goods_number);
    // 平均客单价
    $smarty->assign('average_member', number_format($average_member, 2));
    // 商品平均价格
    $smarty->assign('average_goods', number_format($average_goods, 2));
    // 商品收藏量
    $smarty->assign('goods_save', $goods_save);
    // 商品总数
    $smarty->assign('goods_number', $goods_number);
    // 店铺收藏量
    $smarty->assign('shop_save', $shop_save);
    // 下单高峰期
    $smarty->assign('order_peak', $order_peak);
    // 走势图横坐标
    $smarty->assign('date_arr', $date);
    // 走势图纵坐标
    $smarty->assign('goods_amount_arr', $goods_amount);
    // 选择店铺
    $smarty->assign('sel_shop', $sel_shop);
    // 第三方店铺
    $smarty->assign('supplier_id', $supplier_id);

    assign_query_info();
    $smarty->display('order_stats.htm');
}

/* 取得搜索范围内的日期并赋初始值 */
function get_date_arr($dt_start, $dt_end) {
    $date_arr = array();
    $count = 1;
    do {
        if ($count > 30)
        {
            break;
        }
        $count++;
        // 将 Timestamp 转成 ISO Date 输出
        $date_arr[date('Ymd', $dt_start)] = 0;
    // 重复 Timestamp + 1 天(86400), 直至大于结束日期中止
    } while (($dt_start += 86400) <= $dt_end);
    return $date_arr;
}
?>