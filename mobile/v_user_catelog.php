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

if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'act_on_sale')
{
	$cat_id = $_POST['cat_ids'];
	if(!empty($cat_id))
	{
		$cat_ids = '';
		for($i = 0;$i < count($cat_id); $i++) 
		{
			$cat_ids .= $cat_id[$i].',';
		}
		$cat_ids = rtrim($cat_ids,",");
		$sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('on_sales') . " WHERE user_id = '" . $_SESSION['user_id'] . "'";
		$count = $GLOBALS['db']->getOne($sql);
		if($count == 0)
		{
			$sql = "INSERT INTO " . 
					$GLOBALS['ecs']->table('on_sales') . "(`user_id`,`cat_ids`) " . 
					"values('" . $_SESSION['user_id'] . "','$cat_ids')";
		}
		else
		{
			 $sql = "UPDATE " . $GLOBALS['ecs']->table('on_sales') . 
			 		" SET cat_ids = '$cat_ids' " . 
					"WHERE user_id = '" . $_SESSION['user_id'] . "'";
		}
		$num = $GLOBALS['db']->query($sql);
		if($num > 0)
		{
			show_message('上架商品成功！','去微店','v_shop.php?user_id=' . $_SESSION['user_id']); 
		}
		else
		{
			show_message('上架商品失败！','返回上一页','v_user_catelog.php'); 
		}
	}
	else
	{
		show_message('请选择商品分类！','返回上一页','v_user_catelog.php'); 
	}
}


if (!$smarty->is_cached('v_user_catelog.dwt', $cache_id))
{
    assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
	
	$smarty->assign('cat_list',get_cat_list($_SESSION['user_id']));
}
$smarty->display("v_user_catelog.dwt", $cache_id);

function get_cat_list($user_id)
{
	$sql = "SELECT g.cat_id FROM " . 
		$GLOBALS['ecs']->table('ecsmart_distrib_goods') . " as dg ," . 
		$GLOBALS['ecs']->table('goods') . " as g " . 
		" WHERE dg.goods_id = g.goods_id";
	$list = $GLOBALS['db']->getAll($sql);
	$str = '';
	foreach($list as $key => $val)
	{
		$parent_id = get_cat_id($val['cat_id']);
		if($parent_id > 0)
		{
			for($i = 1; $i > 0; $i++)
			{
				$pid = get_cat_id($parent_id);
				if($pid == 0)
				{
					$str .= $parent_id . ",";
					break; 
				}
				else
				{
					$parent_id = $pid; 
				}
			}
		}
		else
		{
			$str .= $val['cat_id'] . ",";
		}
	}
	
	$ids = rtrim($str,',');
	$cat_ids = explode(',',$ids);
	$cats = array_unique($cat_ids);
	$sql = "SELECT cat_ids FROM " . $GLOBALS['ecs']->table('on_sales') . " WHERE user_id = '$user_id'";
	$u_catids = $GLOBALS['db']->getOne($sql);
	
	$arr = array();
	foreach($cats as $k => $v)
	{
		 $cat_info = get_cats($v);
		 $arr[$k]['checked'] = strpos(',' . $u_catids . ',', ',' . $v. ',') !== false;
		 $arr[$k]['cat_id'] = $cat_info['cat_id'];
		 $arr[$k]['cat_name'] = $cat_info['cat_name'];
	}
	return $arr; 
}


function get_cat_id($cat_id)
{
	$sql = "SELECT parent_id FROM " . 
			$GLOBALS['ecs']->table('category') . 
			" WHERE cat_id = '$cat_id'"; 
	return $GLOBALS['db']->getOne($sql);
}

function get_cats($cat_id)
{
	$sql = "SELECT cat_id,cat_name FROM " .
			$GLOBALS['ecs']->table('category') . 
			" WHERE cat_id = '$cat_id'";
	return $GLOBALS['db']->getRow($sql); 
}


?>