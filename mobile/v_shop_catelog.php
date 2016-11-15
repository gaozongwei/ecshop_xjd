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


if(isset($_SESSION['user_id']))
{
	$user_id = intval($_SESSION['user_id']); 
} 
else
{
	ecs_header("Location: ./\n");
   	exit; 
}


if (!$smarty->is_cached('v_shop_catelog.dwt', $cache_id))
{
    assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置
	
    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
	$smarty->assign('cat_list',get_cat_by_userid($user_id));
    /* 页面中的动态内容 */
    assign_dynamic('v_shop_catelog');
}

$smarty->display('v_shop_catelog.dwt', $cache_id);

function get_cat_by_userid($user_id)
{
	$sql = "SELECT cat_ids FROM " . $GLOBALS['ecs']->table('on_sales') . 
		   " WHERE user_id = '$user_id'"; 
	$cat_ids = $GLOBALS['db']->getOne($sql);
	if($cat_ids)
	{
		$sql = "SELECT cat_id,cat_name FROM " . $GLOBALS['ecs']->table('category') . 
		   " WHERE cat_id in ({$cat_ids})";
		return $GLOBALS['db']->getAll($sql);
	}
	else
	{
		return array(); 
	}
}
?>