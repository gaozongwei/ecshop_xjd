<?php

	/*
	 *
	 *退出操作
	 *
	*/
	require('includes/safety_mysql.php');
	define('IN_ECS', true);
	require('../includes/init.php');
	require('../includes/lib_passport.php');
	//$user = $_POST['user'];
	$uid = $_POST['uid'];
	$user->logout();
	$ucdata = empty($user->ucdata)? "" : $user->ucdata;
?>