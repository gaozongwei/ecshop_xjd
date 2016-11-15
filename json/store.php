<?php

/**
 * 店铺搜索
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$key=$_POST['key'];
	$filter['keywords']         = isset($_REQUEST['keywords']) ? trim(addslashes(htmlspecialchars($_REQUEST['keywords']))) : '';

	$sql = "SELECT * FROM ".$ecs->table('supplier_street')." where status=1 and is_show=1 and supplier_id in(SELECT DISTINCT supplier_id FROM ". $ecs->table('supplier_shop_config') . " AS ssc WHERE ( code = 'shop_name' AND value LIKE '%$key%' ) OR ( code = 'shop_keywords' AND value LIKE '%$key%' ))";
	$arr = $GLOBALS['db']->getAll($sql);
	
	foreach($arr as $key=>$val){
		$shopinfo = $GLOBALS['db']->getAll("select code,value from ".$GLOBALS['ecs']->table('supplier_shop_config')." where supplier_id=".$val['supplier_id']." and code in('shop_closed','shop_name','shop_keywords')");
		foreach($shopinfo as $k => $v){
			
			$v['value'] =  str_replace($filter['keywords'],"<font color=red>".$filter['keywords']."</font>",$v['value']);
		
			$arr[$key][$v['code']] = $v['value'];
		}
	}
	//file_put_contents('./asd.txt',var_export($arr,true));
	//print_r($res);
	print_r(json_encode($arr));
	
?>