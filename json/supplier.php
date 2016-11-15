<?php

define('IN_ECS', true);

require('../includes/init_supplier.php');
include('../includes/cls_json.php');
$json   = new JSON;

$result = array('error'=>0,'result'=>'');

$_REQUEST['suppId'] = intval($_REQUEST['suppId']);

if($_REQUEST['suppId']<=0){
	
	$result['error'] = 1;$result['result'] = '非法操作！';
	die($json->encode($result));
	ecs_header("Location: index.php");
    exit;
}

$sql="SELECT s.*,sr.rank_name FROM ". $ecs->table("supplier") . " as s left join ". $ecs->table("supplier_rank") ." as sr ON s.rank_id=sr.rank_id
 WHERE s.supplier_id=".$_REQUEST['suppId']." AND s.status=1";
$suppinfo=$db->getRow($sql);
if(empty($suppinfo['supplier_id']) || $_REQUEST['suppId'] != $suppinfo['supplier_id'])
{
	$result['error'] = 1;$result['result'] = '非法操作！';
	die($json->encode($result));
	 ecs_header("Location: index.php");
     exit;
}

if($_CFG['shop_closed'] == '1'){

	//echo "对不起！，此店铺因为".$_CFG['close_comment']."临时关闭！";
	$result['error'] = 2;$result['result'] = "对不起！，此店铺因为".$_CFG['close_comment']."临时关闭！";
	die($json->encode($result));
	exit;
}


$typeinfo = array('index','category','search','article','other');
$go = (isset($_REQUEST['go']) && !empty($_REQUEST['go'])) ? $_REQUEST['go'] : 'index';

if(!in_array($go,$typeinfo)){
	$result['error'] = 1;$result['result'] = "";
	die($json->encode($result));
	ecs_header("Location: index.php");
    exit;
}else{
	$index_price = '';
	if(!empty($GLOBALS['_CFG']['shop_search_price'])){
    	$index_price = array_values(array_filter(explode("\r\n",$GLOBALS['_CFG']['shop_search_price'])));
    }
	$userid = intval($_REQUEST['userid']);
	if($userid > 0){
	    //需要获取用户登陆的相关信息
	    $_SESSION['user_id'] = $userid;
	}
	$smarty->template_dir = ROOT_PATH . 'json/tpl/supplier';//app部分模板所在位置
	require('supplier_'.$go.'.php');
}

?>