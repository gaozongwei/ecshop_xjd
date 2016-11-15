<?php

define('IN_ECS', true);

require('../includes/init.php');
include('../includes/cls_json.php');
$json   = new JSON;

/*
 * 获取商品信息用于分享到朋友圈
 */


	$goodsid = (isset($_REQUEST['goodsid']) && intval($_REQUEST['goodsid'])>0) ? intval($_REQUEST['goodsid']) : 0;

	if($goodsid>0){
		//商品信息
		$sql = "select goods_name,goods_thumb,goods_img,goods_id from ". $GLOBALS['ecs']->table('goods') ." where goods_id=".$goodsid;
		$row = $GLOBALS['db']->getRow($sql);
		$retinfo = array(
			'thumbImg'=>'http://www.jlv8.com/'.$row['goods_thumb'],
			'wedpageUrl'=>'http://www.jlv8.com/mobile/goods.php?id='.$goodsid,
			//'scene'=>1,
			'title'=>$row['goods_name'],
			'description'=>$row['goods_name']
		);
	}
	die($json->encode($retinfo));


?>
