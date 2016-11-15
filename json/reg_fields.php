<?php
	/*
	 *
	 *获取会员测试项
	 *
	 *
	 */
	 
	require('includes/safety_mysql.php');
	define('IN_ECS', true);
	require('includes/init.php');
	if($_GET['act'] == 'list')
	{
		$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('reg_fields')."ORDER BY dis_order, id";
		$reg_list = $GLOBALS['db']->getAll($sql);
		print_r(json_encode($reg_list));
	}
	
?>