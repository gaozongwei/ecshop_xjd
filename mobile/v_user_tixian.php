<?php


define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/lib_v_user.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

if($_CFG['is_distrib'] == 0)
{
	show_message('没有开启微信分销服务！','返回首页','index.php'); 
}

if($_SESSION['user_id'] == 0)
{
	ecs_header("Location: ./\n");
    exit;	 
}

$is_distribor = is_distribor($_SESSION['user_id']);
if($is_distribor != 1)
{
	show_message('您还不是分销商！','去首页','index.php');
	exit;
}

if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'act_tixian')
{
	$tixian = array(
           'deposit_money' => $_POST['deposit_money'] > 0 ? $_POST['deposit_money'] : 0,
           'add_time'      => gmtime(),
           'user_id'       => $_SESSION['user_id']
    );
	
	if($tixian['deposit_money'] <= 0)
	{
		show_message('您输入的提现金额不正确！'); 
	}
	
	$user_money = get_total_money_by_user_id($_SESSION['user_id'],1); 
	if($tixian['deposit_money'] > $user_money)
	{
		show_message('您的余额不足，请重新输入！');exit;
	}
	
	if(!empty($_CFG['deposit_least_money']))
	{
		if($tixian['deposit_money'] < $_CFG['deposit_least_money'])
		{
			show_message('每次提现金额不能少于'.$_CFG['deposit_least_money'].'元');exit;
		}
	}
	
	$reserve_money = $_CFG['reserve_money'] > 0 ? $_CFG['reserve_money'] : 0;
	
	if($user_money - $tixian['deposit_money'] < $reserve_money)
	{
		show_message('提现后，账户预留金额不能少于'.$reserve_money.'元');exit; 
	}
	
	$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('deposit'), $tixian, 'INSERT');
	$error_no = $GLOBALS['db']->errno();
    if ($error_no > 0)
    {
        show_message($GLOBALS['db']->errorMsg());
    }
	else
	{
		log_account_change($tixian['user_id'], $tixian['deposit_money'], 0, 0, 0,'分成金额提现到余额');
		write_affiliate_log($tixian['user_id'],$_SESSION['user_name'],-$tixian['deposit_money'],'提现到余额');
		show_message('提现成功！','返回分销中心','v_user.php');
	}
}


if (!$smarty->is_cached('v_user_tixian.dwt', $cache_id))
{
    assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
	$smarty->assign('user_info',get_user_info_by_user_id($_SESSION['user_id']));
	$smarty->assign('info',get_user_info($_SESSION['user_id']));
	$smarty->assign('split_money',get_total_money_by_user_id($_SESSION['user_id'],1));
	$smarty->assign('user_id',$_SESSION['user_id']);
}
$smarty->display('v_user_tixian.dwt', $cache_id);

function write_affiliate_log($uid, $username, $money,$change_desc)
{
    $time = gmtime();
    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('affiliate_log') . "(user_id, user_name, time, money, separate_type,change_desc)".
                                                              " VALUES ('$uid', '$username', '$time', '$money', '4','$change_desc')";
    $GLOBALS['db']->query($sql);
}

?>