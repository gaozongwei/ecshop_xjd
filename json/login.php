<?php

/**
 * 获取登录信息
*/
	require('includes/safety_mysql.php');
	define('IN_ECS', true);
	require('includes/init.php');
	$user=$_POST['user'];
	
	$pwd=$_POST['pwd'];
	
	$row = $db -> getRow("SELECT * FROM ".$ecs->table('users')." WHERE `user_id`='$user' or `email`='$user' or `user_name`='$user'");
	$result=array();
	if(empty($row)){
		$result['code']=0;
		$result['info']="不存在该用户";
	}else{
		$result['code']=0;
		if(empty($row['ec_salt'])){
			if(md5($pwd)!=$row['password']){
				$result['code']=0;
				$result['info']="密码不正确";
			}else{
				$result['code']=1;
				/*查找代付款的数据   jx*/
				$user_id = $row['user_id'];
				$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND pay_status = 0";
				$row['payment'] = $GLOBALS['db']->getOne($sql);
				/*查找代发货的数据   jx*/
				$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND shipping_status = 0";
				$row['deliver'] = $GLOBALS['db']->getOne($sql);
				/*查找代收货的数据   jx*/
				$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND shipping_status = 1";
				$row['receipt'] = $GLOBALS['db']->getOne($sql);
				/*查找全部订单数据   jx*/
				$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id'";
				$row['quan'] = $GLOBALS['db']->getOne($sql);
				/*会员等级    jx*/
				if($row['user_rank'] == 0)
				{
					$row['user_rank'] = "非会员";
					$result['info']=$row;
				}else
				{
					$rank_id = $row['user_rank'];
					$sql = "SELECT rank_name FROM ".$GLOBALS['ecs']->table('user_rank')."WHERE rank_id='$rank_id'";
					$row['user_rank'] = $GLOBALS['db']->getOne($sql);
					$result['info'] = $row; 
				}
			}
		}else{
			if(md5(md5($pwd).$row['ec_salt'])!=$row['password']){
				$result['code']=0;
				$result['info']="密码不正确";
			}else{
				$result['code']=1;
				/*查找代付款的数据   jx*/
				$user_id = $row['user_id'];
				$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND pay_status = 0";
				$row['payment'] = $GLOBALS['db']->getOne($sql);
				/*查找代发货的数据   jx*/
				$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND shipping_status = 0";
				$row['deliver'] = $GLOBALS['db']->getOne($sql);
				/*查找代收货的数据   jx*/
				$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND shipping_status = 1";
				$row['receipt'] = $GLOBALS['db']->getOne($sql);
				/*查找全部订单数据   jx*/
				$sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id'";
				$row['quan'] = $GLOBALS['db']->getOne($sql);
				/*会员等级    jx*/
				if($row['user_rank'] == 0)
				{
					$row['user_rank'] = "非会员";
					$result['info']=$row;
				}else
				{
					$rank_id = $row['user_rank'];
					$sql = "SELECT rank_name FROM ".$GLOBALS['ecs']->table('user_rank')."WHERE rank_id='$rank_id'";
					$row['user_rank'] = $GLOBALS['db']->getOne($sql);
					$result['info'] = $row; 
				}
				
				
			}
		}
		

	}
	print_r(json_encode($result));

?>

