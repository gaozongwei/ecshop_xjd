<?php

/**
 * 获取登录信息
*/
	define('IN_ECS', true);
	require('includes/init.php');

if($_POST['acr'] == 'app')
{
	if($_POST['act'] == 'reg')
	{
		$user= isset($_POST['user'])? $_POST['user'] : '';
		$email= isset($_POST['email']) ? $_POST['email'] : '';
		$sex= $_POST['sex'];
		$pwd=md5($_POST['pwd']);
		$reg_list = $_POST['reg_field'];//会员注册项
	
		$res_field = explode(',',$reg_list);//拆分数组
		$reg_field = array_filter($res_field);//去除数组中的空值
		$msn = '';
		$qq = '';
		$office_phone = '';
		$home_phone = '';
		$mobile_phone = '';
		$extend_field_str = '';
		if($reg_field)
		{
			foreach($reg_field as $value)
			{
				$field = explode('@',$value);
				if($field['0'] == 1)
				{
					$msn = $field['1'];
				}
				if($field['0'] == 2)
				{
					$qq = $field['1'];
				}
				if($field['0'] == 3)
				{
					$office_phone = $field['1'];
				}
				if($field['0'] == 4)
				{
					$home_phone = $field['1'];
				}
				if($field['0'] == 5)
				{
					$mobile_phone = $field['1'];
				}
				if($field['0'] > 6)
				{
					//$extend_field_str .= " ('" . $_SESSION['user_id'] . "', '" . $field['0'] . "', '" . $field['1'] . "'),";
					$extend_field_str.= $field['0'].",".$field['1']."@";
				}
			}
		}
		$result=array();
		$row = $db -> getRow("SELECT * FROM ".$ecs->table('users')."  WHERE  `user_name`='$user'");
		if(!empty($row)){
			$result['code']=2;
			$result['info']="该用户名已经存在";
			print_r(json_encode($result));
			exit();
		}
		$row = $db -> getRow("SELECT * FROM ".$ecs->table('users')."  WHERE  `email`='$email'");
		if(!empty($row)){
			$result['code']=3;
			$result['info']="该邮箱已经存在";
			print_r(json_encode($result));
			exit();
		}

		$add = array(  
			'user_name' => $user,
			'email' => $email, 
			'password' => $pwd, 
			'reg_time' => gmtime(), 
			'sex' => $sex,
			//注册项
			'msn' => $msn,
			'qq' => $qq,
			'office_phone' => $office_phone,
			'home_phone' => $home_phone,
			'mobile_phone' => $mobile_phone,
			'froms' => 'app'
		); 
		$set=$db->autoExecute($ecs->table('users'), $add, 'INSERT');
		if($set){
			/*注册赠送积分*/
			$sql="SELECT value FROM ".$ecs->table('shop_config')." WHERE id='220'";
			$shop_config=$db ->getRow($sql);
			$shop_config_integral=intval($shop_config['value']);
			if($shop_config_integral>0){
				$row = $db -> getRow("SELECT * FROM ".$ecs->table('users')."  WHERE  `user_name`='$user'");
				$uid=$row['user_id'];
				$user_log=array();
			   $user_log['user_id'] = $uid;
			   $user_log['user_money'] = '0.00';
			   $user_log['frozen_money'] = '0.00';
			   $user_log['rank_points'] ='0';
			   $user_log['pay_points'] = $shop_config_integral;
			   $user_log['change_desc'] = '注册赠送积分';
			   $user_log['change_type'] = '99';
			   $user_log['change_time'] = gmtime();
			   $db->autoExecute($ecs->table('account_log'), $user_log, 'INSERT');
			   $sql="UPDATE ".$ecs->table('users') . " SET `pay_points`=`pay_points`+$shop_config_integral WHERE `user_id`='$uid'";
				$db->query($sql);
			}
			if ($extend_field_str)      //插入注册扩展数据
			{
				$extend_str = explode('@',$extend_field_str);//拆分数组
				$extend_field = array_filter($extend_str);//去除数组中的空值
				foreach($extend_field as $value)
				{
					$extend = explode(',',$value);
					$extend_list.=" (' $uid ', '" . $extend['0'] . "', '" . $extend['1'] . "'),";
				}
				 $extend_list = substr($extend_list, 0, -1);
				$sql = 'INSERT INTO '. $ecs->table('reg_extend_info') . ' (`user_id`, `reg_field_id`, `content`) VALUES' . $extend_list;
				$db->query($sql);
			}
			
			$row = $db -> getRow("SELECT * FROM ".$ecs->table('users')."  WHERE  `user_name`='$user'");
			$result['code']=1;
			$result['info']=$row;
			print_r(json_encode($result));
			exit();
		}
	}
}
else
{
	$redirect_url =  "http://".$_SERVER["HTTP_HOST"].str_replace("user.php", "index.php");
	header('Location: '.$redirect_url);
}
?>