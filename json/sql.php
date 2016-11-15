<?php

/**
 * sql
*/
	define('IN_ECS', true);
	require('../includes/init.php');

    $sql="SELECT 
		*
	FROM ".$ecs->table('account_log')."";
	$goods=$db ->getAll($sql);
	print_r($goods);

?>

