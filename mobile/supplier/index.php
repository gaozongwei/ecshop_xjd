<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH.'includes/lib_order.php');
require_once(ROOT_PATH . 'includes/lib_supplier_common_wap.php');

/*------------------------------------------------------ */
//-- 框架
/*------------------------------------------------------ */
if ($_REQUEST['act'] == '')
{
    //待支付
    $order['await_pay'] = _wap_await_pay_count();
    //缺货登记
    $order['booking_goods'] = _wap_booking_goods_count();
    //部分发货
    $order['shipped_part']  = _wap_shipped_part_count();
    //待收货
    $order['await_receipt'] = _wap_await_receipt();
    //已完成
    $order['finished'] = _wap_finished();
    $today_start=mktime(0,0,0,date('m'),date('d'),date('Y'));
    $today_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
    //今日数据
    $today = array();
    //今日订单总额
    $today['money'] = $db->GetOne('SELECT SUM(money_paid+surplus) AS money FROM ' .$ecs->table('order_info'). ' WHERE `add_time` BETWEEN '.$today_start.' AND '.$today_end.' AND supplier_id='.$_SESSION['supplier_id'].order_query_sql('finished').$where_storeid);
    $today['money'] = price_format($today['money']);
    //今日订单数
    //$today['order'] = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('order_info'). ' WHERE `add_time` BETWEEN '.$today_start.' AND '.$today_end.' AND supplier_id='.$_SESSION['supplier_id'].order_query_sql('finished'));
    $today['order'] = $db->GetOne('SELECT COUNT(*) FROM ' .$ecs->table('order_info'). ' WHERE `confirm_time` BETWEEN '.$today_start.' AND '.$today_end.' AND supplier_id='.$_SESSION['supplier_id'].$where_storeid);
    $smarty->assign('today',$today);
    //待发货、退款、退货
    $smarty->assign('order',$order);
    $smarty->assign('supplier_name',$_SESSION['supplier_name']);
    $headimg = $db->GetOne('SELECT headimg FROM ' .$ecs->table('users'). ' WHERE  user_id = '.$_SESSION['supplier_id']);
    if($headimg)$smarty->assign('headimg',get_pc_url().'/'.$headimg);
    $smarty->assign('back_type_goods',BT_GOODS);
    $smarty->assign('back_type_money',BT_MONEY);
    $smarty->assign('cs_await_pay',CS_AWAIT_PAY);
    $smarty->assign('cs_await_ship',CS_AWAIT_SHIP);
    $smarty->assign('os_shipped_part',OS_SHIPPED_PART);
    _wap_assign_header_info('首页','',1,0,1);
    _wap_assign_footer_order_info();
    _wap_display_page('index.htm');
}