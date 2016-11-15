<?php

/**
 * 我的红包列表
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$page=$_GET['page']*5;
	$uid = isset($_REQUEST['uid'])  ? intval($_REQUEST['uid']) : 0;
	$result=array();
	$sql="SELECT ub.used_time,ub.bonus_sn ,bt.type_name,bt.type_money,bt.min_goods_amount,bt.use_end_date
		FROM  ".$ecs->table('user_bonus')." AS ub,".$ecs->table('bonus_type')." AS bt  WHERE ub.user_id='$uid' AND ub.bonus_type_id=bt.type_id 
		LIMIT $page,5";
	//print_r($sql);
	$result= $db -> getAll($sql);
	$length=count($result);
	for($i=0;$i<$length;$i++){
		if($result[$i]['used_time']==0){
			$result[$i]['is_used']="未使用";
		}else{
			$result[$i]['is_used']="已使用";
		}
		if($result[$i]['bonus_sn']==0){
			$result[$i]['bonus_sn']="N/A";
		}
		$result[$i]['use_end_date']=date("Y-m-d",$result[$i]['use_end_date']);
	}
	print_r(json_encode($result));
	
?>