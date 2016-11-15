<?php
	/*
	 *
	 *店铺街页面
	 *  jx  2015-05-29
	 *
	 *
	 */
	define('IN_ECS', true);
	require('includes/init.php');
	$user_id = $_GET['user_id'];
	$act = isset($_REQUEST['act'])  ? trim($_REQUEST['act']) : '';
	//店铺街列表
	if(empty($act))
	{
		//得到所有审核通过的店铺
		$sql = "select * from ".$GLOBALS['ecs']->table('supplier_street')." where status=1 and is_show=1";
		$shop_list = $GLOBALS['db']->getAll($sql);
		foreach($shop_list as $key=>$value)
		{
			$sql = "select code,value from ".$GLOBALS['ecs']->table('supplier_shop_config')."where supplier_id = ".$value['supplier_id'];
			$shop = $GLOBALS['db']->getAll($sql);
			foreach($shop as $k=>$v)
			{
				$shop_list[$key][$v['code']] = $v['value'];
			}
			if($user_id != '0')
			{//如果用户登陆，获取到对应用户关注的店铺
				$sql = "select * from ".$GLOBALS['ecs']->table('supplier_guanzhu')." where userid = $user_id and supplierid = ".$value['supplier_id'];
				$gshop = $GLOBALS['db']->getOne($sql);
				if(!empty($gshop))
				{
					$shop_list[$key]['guanzhu'] = '1';
				}else
				{
					$shop_list[$key]['guanzhu'] = '0';
				}
				
			}else
			{
				$shop_list[$key]['guanzhu'] = '0';
			}
		}
		
		//file_put_contents('./12.txt',var_export($shop_list,true));
		print_r(json_encode($shop_list));exit;
	
	}elseif($act == 'attention')//关注店铺
	{
		$supplier_id = $_GET['supplier_id'];
		$sql = "INSERT INTO ". $GLOBALS['ecs']->table('supplier_guanzhu') . " (`userid`, `supplierid`, `addtime`) VALUES ('$user_id','$supplier_id]',".time().")";
		$GLOBALS['db']->query($sql);
		$result['error'] = 1;
    	$result['info'] = '关注成功！';
		print_r(json_encode($result));exit;
	}
?>