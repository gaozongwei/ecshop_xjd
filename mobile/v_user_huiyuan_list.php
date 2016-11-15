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
	exit;
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


if (!$smarty->is_cached('v_user_huiyuan_list.dwt', $cache_id))
{
    assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
	$user_id = intval($_REQUEST['user_id']);
	$level = get_level_user($_SESSION['user_id'],$user_id);
	if($level == 0)
	{
		show_message('您没有权限查看此会员分成订单信息'); 
	}
	$user_info = get_user_info_by_user_id($user_id); //用户信息，包括昵称和头像
	$smarty->assign('user_info',$user_info);
	
	/* 初始化分页信息 */
	$page = isset($_REQUEST['page'])   && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
	$size = isset($_REQUEST['page_size'])  && intval($_REQUEST['page_size']) > 0 ? intval($_REQUEST['page_size']) : 10;
	$count = get_affiliate_count_by_user_id($user_id);
	$max_page = ($count> 0) ? ceil($count / $size) : 1;
    if ($page > $max_page)
    {
        $page = $max_page;
    }
	$smarty->assign('order_list',get_affiliate_info_by_user_id($user_id,$page,$size));
	assign_pager('v_user_huiyuan_list', '', $count, $size, '', '', $page); // 分页
	$smarty->assign('user_id',$_SESSION['user_id']);
    /* 页面中的动态内容 */
    assign_dynamic('v_user_huiyuan_list');
}

$smarty->display('v_user_huiyuan_list.dwt', $cache_id);



?>