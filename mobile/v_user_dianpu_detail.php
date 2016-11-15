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

if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'insert_dianpu')
{
	$dianpu = array(
           'dianpu_name' => $_POST['dianpu_name'] ? $_POST['dianpu_name'] : '',
           'dianpu_desc' => $_POST['dianpu_desc'] ? $_POST['dianpu_desc'] : '',
           'phone' 		 => $_POST['phone'] ? $_POST['phone'] : '',
		   'wechat' 	 => $_POST['wechat'] ? $_POST['wechat'] : '',
		   'qq' 		 => $_POST['qq'] ? $_POST['qq'] : '',
		   'address' 	 => $_POST['address'] ? $_POST['address'] : '',
		   'user_id' 	 => $_SESSION['user_id']
    );
	if($dianpu['dianpu_name'] == '' || $dianpu['dianpu_desc'] == '' || $dianpu['phone'] == '' || $dianpu['wechat'] == '' || $dianpu['address'] == '')
	{
		show_message('店铺信息请填写完整！','返回上一页','v_user_dianpu_detail.php'); 
	}
	$sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('dianpu') . " WHERE user_id = '" . $_SESSION['user_id'] . "'";
	$count = $GLOBALS['db']->getOne($sql);
	if($count == 0)
	{
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('dianpu'), $dianpu, 'INSERT');
		$error_no = $GLOBALS['db']->errno();
    	if ($error_no > 0)
    	{
        	show_message($GLOBALS['db']->errorMsg());
    	}
		else
		{
			ecs_header("Location: v_user_dianpu.php\n"); 
		}
	}
	else
	{
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('dianpu'), $dianpu, 'UPDATE' , "user_id = '" . $_SESSION['user_id'] . "'");
		$error_no = $GLOBALS['db']->errno();
    	if ($error_no > 0)
    	{
        	show_message($GLOBALS['db']->errorMsg());
    	}
		else
		{
			ecs_header("Location: v_user_dianpu.php\n"); 
		}
	}
}


if (!$smarty->is_cached('v_user_dianpu_detail.dwt', $cache_id))
{
    assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
	$smarty->assign('user_info',get_user_info_by_user_id($_SESSION['user_id'])); 
	
	$smarty->assign('dianpu',get_dianpu_by_user_id($_SESSION['user_id']));
	$smarty->assign('user_id',$_SESSION['user_id']);
	
    /* 页面中的动态内容 */
    assign_dynamic('v_user_dianpu_detail');
}

$smarty->display('v_user_dianpu_detail.dwt', $cache_id);


?>