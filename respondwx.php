<?php

/**
 * ECSHOP 支付响应页面
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: respondwx.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
/* 支付方式代码 */
$pay_code="wxnative";
$_GET["code"]="wxnative";


function logResultx($word = '',$var=array()) {
    $output = $word . print_r($var, true);
    $fp = fopen(ROOT_PATH . "/data/log/wxnative.txt", "a");
    flock($fp, LOCK_EX);
    fwrite($fp, "执行日期：" . strftime("%Y%m%d%H%M%S", time()) . "\n" . $output . "\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

logResultx("resposdwx:",$_GET);

/* 判断是否启用 */
$sql = "SELECT COUNT(*) FROM " . $ecs->table('payment') . " WHERE pay_code = '$pay_code' AND enabled = 1";
if ($db->getOne($sql) == 0)
{
    $msg = $_LANG['pay_disabled'];
}
else
{
    $plugin_file = 'includes/modules/payment/' . $pay_code . '.php';

    /* 检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息 */
    if (file_exists($plugin_file))
    {
        /* 根据支付方式代码创建支付类的对象并调用其响应操作方法 */
        include_once($plugin_file);

        $payment = new $pay_code();
        $msg     = (@$payment->respond()) ? $_LANG['pay_success'] : $_LANG['pay_fail'];
    }
    else
    {
        $msg = $_LANG['pay_not_exist'];
    }

}

assign_template();
$position = assign_ur_here();
$smarty->assign('page_title', $position['title']);   // 页面标题
$smarty->assign('ur_here',    $position['ur_here']); // 当前位置
$smarty->assign('page_title', $position['title']);   // 页面标题
$smarty->assign('ur_here',    $position['ur_here']); // 当前位置
$smarty->assign('helps',      get_shop_help());      // 网店帮助

$smarty->assign('message',    $msg);
$smarty->assign('shop_url',   $ecs->url());

$smarty->display('respond.dwt');

?>