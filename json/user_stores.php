<?php
	/*
	 *
	 *用户中心店铺关注 和取消关注
	 *  jx  2015-05-29 
	 *
	 *
	 */
	define('IN_ECS', true);
	require('includes/init.php');
	$user_id = $_GET['user_id'];
	$act = isset($_REQUEST['act'])  ? trim($_REQUEST['act']) : '';
	//获取用户关注的店铺
	if(empty($act))
	{
		$sql = "select *from ".$GLOBALS['ecs']->table('supplier_guanzhu')." where userid = ".$user_id;
		$list = $GLOBALS['db']->getAll($sql);
		foreach($list as $key=>$value)
		{
			$sql = "select code,value from ".$GLOBALS['ecs']->table('supplier_shop_config')." where supplier_id = ".$value['supplierid'];
			$shop = $GLOBALS['db']->getAll($sql);
			foreach($shop as $k=>$v)
			{
				$list[$key][$v['code']] = $v['value'];
			}
			$sql = "select supplier_name from ".$GLOBALS['ecs']->table('supplier')." where supplier_id = ".$value['supplierid'];
			$shop_name = $GLOBALS['db']->getOne($sql);
			$list[$key]['user_shop_name'] = $shop_name;
		}
	
		print_r(json_encode($list));exit;
	
	}elseif($act == 'del')//取消关注
	{
		$id = $_GET['id'];
		$sql = "DELETE FROM " .$ecs->table('supplier_guanzhu'). " WHERE id='$id' AND userid ='$user_id'";
		$GLOBALS['db']->query($sql);
		$result['error'] = 1;
    	$result['info'] = '取消关注成功';
		print_r(json_encode($result));exit;
	}
?>