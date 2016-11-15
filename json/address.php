<?php

/**
 * 用户地址列表
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$uid = isset($_REQUEST['uid'])  ? intval($_REQUEST['uid']) : 0;
	$result=array();
	$row = $db -> getAll("SELECT * FROM ".$ecs->table('user_address')." WHERE `user_id`='$uid' ");
    
    $country = $db -> getAll("SELECT region_id,region_name FROM  ".$ecs->table('region')." WHERE  region_type='0' and parent_id='0'  order by region_id asc");


	$result['country']=$country;
    
	for($i=0;$i<count($row);$i++){
        
		$parent_id=$row[$i]['country'];
		$province = $db -> getAll("SELECT region_id,region_name FROM  ".$ecs->table('region')." WHERE  region_type='1' and parent_id='$parent_id'  order by region_id asc");
		$row[$i]['provinceList']=$province;        
        
		$parent_id=$row[$i]['province'];
		$city = $db -> getAll("SELECT region_id,region_name FROM  ".$ecs->table('region')." WHERE  region_type='2' and parent_id='$parent_id'  order by region_id asc");
		$row[$i]['cityList']=$city;
		
		$parent_id=$row[$i]['city'];
		$district = $db -> getAll("SELECT region_id,region_name FROM  ".$ecs->table('region')." WHERE  region_type='3' and parent_id='$parent_id'  order by region_id asc");
		$row[$i]['districtList']=$district;
	}
	$result['address']=$row;
	print_r(json_encode($result));

?>