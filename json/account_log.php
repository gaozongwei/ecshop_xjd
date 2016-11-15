<?php
/**
 * 我的账号资金
*/
	ob_start();
	define('IN_ECS', true);
	define('INIT_NO_SMARTY', true);
	
	require('../includes/init.php');
	require('../includes/lib_clips.php');
	$result=array();
	$user_id = isset($_REQUEST['uid'])  ? intval($_REQUEST['uid']) : 0;
	$page = isset($_REQUEST['page'])  ? intval($_REQUEST['page']) : 0;
	//获取剩余余额
    
	$sql = "SELECT user_money FROM " .$ecs->table('users').
           " WHERE user_id = '$user_id'";

    $surplus_amount = $db->getOne($sql);
	if(empty($surplus_amount)){
	 $surplus_amount="0.00";
	
	}
	$result['surplus_amount']=$surplus_amount;
	
	//获取余额记录
    $account_log = array();
	$account_type = 'user_money';
    $sql = "SELECT * FROM " . $ecs->table('account_log') .
           " WHERE user_id = '$user_id'" .
           " AND $account_type <> 0 " .
           " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, 10, 10*$page);
    while ($row = $db->fetchRow($res))
    {
        $row['change_time'] = local_date($_CFG['date_format'], $row['change_time']);
        $row['type'] = $row[$account_type] > 0 ? "增加" : "减少";
        $row['user_money'] = price_format(abs($row['user_money']), false);
        $row['frozen_money'] = price_format(abs($row['frozen_money']), false);
        $row['rank_points'] = abs($row['rank_points']);
        $row['pay_points'] = abs($row['pay_points']);
        $row['short_change_desc'] = sub_str($row['change_desc'], 60);
        $row['amount'] = $row[$account_type];
        $account_log[] = $row;
    }
	$result['account_log']=$account_log;
	
	print_r(json_encode($result));
	ob_end_flush();
?>