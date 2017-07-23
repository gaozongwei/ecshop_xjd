<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}


if ($_REQUEST['act'] == 'info')
{	
	$filter = array();
	$smarty->assign('full_page',    1);
    $smarty->assign('filter',       $filter);
    $sql = "SELECT * FROM " .$ecs->table('server_center') . " limit 1";
    $record = $db->getRow($sql);
    $smarty->assign('record', $record);

	$smarty->display('server_center.htm');

}
elseif($_REQUEST['act'] == 'update')
{
	$vip_moeny_all = $_POST['vip_moeny_all'];
	$vip_goods_commission = $_POST['vip_goods_commission'];
	$ordinary_goods_commission = $_POST['ordinary_goods_commission'];

	$sql = "UPDATE " . $ecs->table('server_center') . " set vip_moeny_all = '$vip_moeny_all',  vip_goods_commission = '$vip_goods_commission',  ordinary_goods_commission = '$ordinary_goods_commission'";
	$db->query($sql);
	$link = "server_center.php?act=info";
    sys_msg("修改成功", 0, $links);
}


?>
