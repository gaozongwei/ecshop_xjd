<?php

/**
 * 储值卡充值
*/
	define('IN_ECS', true);
	require('../includes/init.php');
	$vc_pwd = isset($_REQUEST['vc_pwd'])  ? $_REQUEST['vc_pwd'] : '';
	$vc_sn = isset($_REQUEST['vc_sn'])  ? trim($_REQUEST['vc_sn']) : 0;
	$user_id = isset($_REQUEST['user_id'])  ? intval($_REQUEST['user_id']) : 0;
	$result=array();
	$nowtime =gmtime();
	if(!empty($user_id)&&$user_id!=0){
		$sql="select vc.*, vt.type_money, vt.use_start_date, vt.use_end_date from ". $ecs->table('valuecard') ." AS vc ".
				" left join " . $ecs->table('valuecard_type')." AS vt ".
				"on vc.vc_type_id = vt.type_id where vc.vc_sn= '$vc_sn' ";
		$vcrow=$GLOBALS['db']->getRow($sql);

		if(!$vcrow)
		{
			$result['code']=0;
			$result['info']="该储值卡号不存在";
			print_r(json_encode($result));
			exit();
		}
		
		if($vc_pwd!=$vcrow['vc_pwd'])
		{
			
			$result['code']=0;
			$result['info']="密码错误";
			print_r(json_encode($result));
			exit();
		}
		if($nowtime < $vcrow['use_start_date'])
		{
			$result['code']=0;
			$result['info']="对不起，该储值卡还未到开始使用日期";
			print_r(json_encode($result));
			exit();
			
		}
		if($nowtime > $vcrow['use_end_date'])
		{
			$result['code']=0;
			$result['info']="对不起，该储值卡已过期";
			print_r(json_encode($result));
			exit();
			
		}
		if($vcrow['user_id'])
		{
			$result['code']=0;
			$result['info']="对不起，该储值卡已使用";
			print_r(json_encode($result));
			exit();
			
		}
		
		$sql = 'INSERT INTO ' .$GLOBALS['ecs']->table('user_account').
           ' (user_id, admin_user, amount, add_time, paid_time, admin_note, user_note, process_type, payment, is_paid)'.
            " VALUES ('$user_id', '', '$vcrow[type_money]', '".gmtime()."', '". gmtime() ."', '', '储值卡充值', '0', '储值卡号：$vc_sn', 1)";
		$GLOBALS['db']->query($sql);
		
		/* 插入帐户变动记录 */
		$account_log = array(
			'user_id'       => $user_id,
			'user_money'    => $vcrow['type_money'],
			'frozen_money'  => 0,
			'rank_points'   => 0,
			'pay_points'    => 0,
			'change_time'   => gmtime(),
			'change_desc'   => '储值卡充值，卡号：'.$vc_sn,
			'change_type'   => 0
		);
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('account_log'), $account_log, 'INSERT');
	
		/* 更新用户信息 */
		$user_money=$vcrow['type_money'];
		$sql = "UPDATE " . $GLOBALS['ecs']->table('users') .
				" SET user_money = user_money + ('$user_money')," .
				" frozen_money = frozen_money + ('0')," .
				" rank_points = rank_points + ('0')," .
				" pay_points = pay_points + ('0')" .
				" WHERE user_id = '$user_id' LIMIT 1";
		$GLOBALS['db']->query($sql);
		$sql="update ". $ecs->table('valuecard') ." set user_id='$user_id', used_time='$nowtime' where vc_id='$vcrow[vc_id]' ";
		$GLOBALS['db']->query($sql);
		
		$row = $GLOBALS['db'] -> getRow("SELECT * FROM ".$GLOBALS['ecs']->table('users')." WHERE `user_id`='$user_id'");
		$result['code']=1;
		$result['info']=$row;
		
	}else{
		$result['code']=0;
		$result['info']="非法操作";
	}
	
	
	print_r(json_encode($result));

?>

