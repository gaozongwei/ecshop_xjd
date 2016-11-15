<?php

/**
 * 管理中心 返佣管理
 * $Author: yangsong
 * 
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
//require_once(ROOT_PATH . 'includes/lib_rebate.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
//require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/supplier.php');
$smarty->assign('lang', $_LANG);


/*------------------------------------------------------ */
//-- 返佣列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
     /* 检查权限 */
     admin_priv('rebate_manage');

	$ur_here_lang = '平台交易统计';
    $smarty->assign('ur_here', $ur_here_lang); // 当前导航

	$rebate_pay = get_rebate_pay();

	$result = supplier_rebate_info_list();

	$today['start'] = local_date('Y-m-d 00:00');
	$today['ends'] = local_date('Y-m-d 00:00',local_strtotime("+1 day"));
	$yestoday['start'] = local_date('Y-m-d 00:00',local_strtotime("-1 day"));
	$yestoday['ends'] = local_date('Y-m-d 00:00',local_strtotime("+1 day"));
	$week['start'] = local_date('Y-m-d 00:00',local_strtotime("-7 day"));
	$week['ends'] = local_date('Y-m-d 00:00',local_strtotime("+1 day"));
	$month['start'] = local_date('Y-m-d 00:00',local_strtotime("-1 month"));
	$month['ends'] = local_date('Y-m-d 00:00',local_strtotime("+1 day"));

	$smarty->assign('supplier_list',    $result['result']);
    $smarty->assign('filter',       $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count',   $result['page_count']);

	$smarty->assign('full_page',        1); // 翻页参数
	$smarty->assign('payinfo',$rebate_pay);
	$smarty->assign('today',$today);
	$smarty->assign('yestoday',$yestoday);
	$smarty->assign('week',$week);
	$smarty->assign('month',$month);

	$supplier_order = get_all_supplier_order();
	$smarty->assign('supplier_order',$supplier_order);

	assign_query_info();
    $smarty->display('supplier_rebate_info.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    check_authz_json('rebate_manage');

    $result = supplier_rebate_info_list();
	$smarty->assign('supplier_list',    $result['result']);
	$smarty->assign('filter',       $result['filter']);
	$smarty->assign('record_count', $result['record_count']);
	$smarty->assign('page_count',   $result['page_count']);

	/* 排序标记 */
	$sort_flag  = sort_flag($result['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);

	make_json_result($smarty->fetch('supplier_rebate_info.htm'), '',
		array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}

//导出
elseif ($_REQUEST['act'] == 'export_goods')
{
	admin_priv('rebate_manage');
	header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=rebate_list.xls");

	$export = "<table border='1'><tr><td colspan='2'>账户变动时间</td><td colspan='2'>订单号</td><td colspan='2'>订单金额（元）</td><td colspan='2'>平台扣除佣金（元）</td><td colspan='2'>商家实际收入金额（元）</td><td colspan='2'>支付方式</td><td colspan='2'>备注</td></tr>";

	$result = supplier_rebate_info_list();
	foreach($result['result'] as $key=>$val)
	{
		$export .= "<tr><td colspan='2'>".$val['add_time']."</td><td colspan='2' style='vnd.ms-excel.numberformat:@'>".$val['order_sn']."</td><td colspan='2'>".$val['all_money']."</td><td colspan='2'>-".$val['rebate_money']."</td><td colspan='2'>".$val['result_money']."</td><td colspan='2'>".$val['pay_name']."</td><td colspan='2'>".$val['texts']."</td></tr>";
		
	}
	$export .= "</table>";
	if (EC_CHARSET != 'utf-8')
    {
        echo ecs_iconv(EC_CHARSET, 'utf-8', $export) . "\t";
    }
    else
    {
        echo $export. "\t";
    }
}

//入驻商详细佣金日志列表
function supplier_rebate_info_list()
{
	$result = get_filter();

	if ($result === false)
    {

		$filter['start_time']    = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
		$filter['end_time']    = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);

		$filter['payid'] = intval($_REQUEST['payid'])>0 ? intval($_REQUEST['payid']) : 0;
		$filter['orderid'] = intval($_REQUEST['orderid'])>0 ? intval($_REQUEST['orderid']) : 0;
		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? ' sr.add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? ' ASC' : trim($_REQUEST['sort_order']);

		$where = (isset($_SESSION['supplier_id']) && intval($_SESSION['supplier_id'])>0) ? 'WHERE sr.supplier_id='.intval($_SESSION['supplier_id']) : 'WHERE 1';

		if ($filter['start_time'])
		{
			$where .= " and sr.add_time >= '" . $filter['start_time']."' ";
		}

		if ($filter['end_time'])
		{
			$where .= " and sr.add_time <= '" . $filter['end_time']."' ";;
		}

		if($filter['payid'])
		{
			$where .= " and sr.pay_id = ".$filter['payid'];
		}

		if($filter['orderid'])
		{
			$where .= " and sr.order_id = ".$filter['orderid'];
		}

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
        {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        }
        elseif (isset($_COOKIE['ECSCP']['page_size']) && intval($_COOKIE['ECSCP']['page_size']) > 0)
        {
            $filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
        }
        else
        {
            $filter['page_size'] = 15;
        }

        /* 记录总数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('supplier_rebate_log') ." AS sr  " . $where;
        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT sr.*, s.supplier_name, s.supplier_rebate ".
                "FROM " . $GLOBALS['ecs']->table("supplier_rebate_log") . " AS  sr left join " .$GLOBALS['ecs']->table("supplier") .  " AS s on sr.supplier_id=s.supplier_id 
                $where
                ORDER BY " . $filter['sort_by'] . " " . $filter['sort_order'];
				if(!isset($_REQUEST['is_export'])){
					$sql .= " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ", " . $filter['page_size'] . " ";
				}
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
	$list=array();
	$res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
	{
		$row['add_time'] = local_date('Y-m-d H:i:s', $row['add_time']);
		$list[]=$row;
	}
    $arr = array('result' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}
//获取所有商家的订单
function get_all_supplier_order()
{
	global $db,$ecs;
	$suppid = intval($_SESSION['supplier_id']);
	$sql = "select order_id,order_sn from ".$ecs->table('supplier_rebate_log')." where supplier_id=".$suppid;
	return $db->getAll($sql);
}
//当前入驻商详细信息中的支付方式
function get_rebate_pay($suppid)
{
	global $db,$ecs;
	$suppid = intval($_SESSION['supplier_id']);
    /* 代码修改 By  www.68ecshop.com Start */
//	$sql = "select pay_id,pay_name from ".$ecs->table('supplier_rebate_log')." where supplier_id=".$suppid;
    $sql = "select DISTINCT pay_id,pay_name from ".$ecs->table('supplier_rebate_log')." where supplier_id=".$suppid;
    /* 代码修改 By  www.68ecshop.com End */
	return $db->getAll($sql);
}
?>