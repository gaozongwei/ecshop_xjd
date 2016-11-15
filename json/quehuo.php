<?php

define('IN_ECS', true);

require('../includes/init.php');
include('../includes/cls_json.php');
$json   = new JSON;


$smarty->template_dir = ROOT_PATH . 'json/tpl';//app部分模板所在位置

$res    = array('error' => 0, 'result' => '', 'message' => '');
/*
 * 获取购物车中的商品
 */
if (!empty($_REQUEST['act']) && $_REQUEST['act'] == 'goodsinfo')
{
	$goodsid = isset($_REQUEST['goodsid'])   && intval($_REQUEST['goodsid'])  > 0 ? intval($_REQUEST['goodsid'])  : 0;
	
    if($goodsid > 0){
    	
    	$sql = " select g.goods_name ".
            	'from ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
            	"WHERE g.goods_id = ".$goodsid." limit 1";
    	//$res = $GLOBALS['db']->query($sql);
    	$arr = $GLOBALS['db']->getRow($sql);
    	$res['result'] = $arr['goods_name'];
    }else{
    	$res['error'] = 1;
		$res['message'] = '没有此商品信息';
    }
	die($json->encode($res));
}elseif($_REQUEST['act'] == 'addquehuo'){

	

    $booking = array(
		'user_id'     => isset($_POST['user_id'])      ? intval($_POST['user_id'])     : 0,
        'goods_id'     => isset($_POST['goods_id'])      ? intval($_POST['goods_id'])     : 0,
        'goods_amount' => isset($_POST['goods_number'])  ? intval($_POST['goods_number']) : 0,
        'desc'         => isset($_POST['goods_desc'])    ? trim($_POST['goods_desc'])     : '',
        'linkman'      => isset($_POST['link_man']) ? trim($_POST['link_man'])  : '',
        'email'        => isset($_POST['email'])   ? trim($_POST['email'])    : '',
        'tel'          => isset($_POST['tel'])     ? trim($_POST['tel'])      : '',
        'booking_id'   => isset($_POST['rec_id'])  ? intval($_POST['rec_id']) : 0
    );

	 // 查看此商品是否已经登记过
	$sql = 'SELECT COUNT(*) '.
           'FROM ' .$GLOBALS['ecs']->table('booking_goods').
           "WHERE user_id = ".$booking['user_id']." AND goods_id = ".$booking['goods_id']." AND is_dispose = 0";

    $rec_id = $GLOBALS['db']->getOne($sql);

    if ($rec_id > 0)
    {
		$res['error'] = 1;
		$res['message'] = '已经做了登记!';
        //show_message($_LANG['booking_rec_exist'], $_LANG['back_page_up'], '', 'error');
		die($json->encode($res));

    }

	$sql = "INSERT INTO " .$GLOBALS['ecs']->table('booking_goods').
            " VALUES ('', ".$booking['user_id'].", '$booking[email]', '$booking[linkman]', ".
                "'$booking[tel]', '$booking[goods_id]', '$booking[desc]', ".
                "'$booking[goods_amount]', '".gmtime()."', 0, '', 0, '')";

    if ($GLOBALS['db']->query($sql))
    {
        $res['message'] = '登记成功!';
    }
    else
    {
		$res['error'] = 2;
        $res['message'] = '登记失败!';
    }

	die($json->encode($res));
}elseif($_REQUEST['act'] == 'list'){
	$size = 3;
	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$user_id = isset($_REQUEST['userid']) ? intval($_REQUEST['userid']) : 0;
	$sql = "SELECT bg.rec_id, bg.goods_id, bg.goods_number, bg.goods_desc, bg.booking_time, bg.dispose_note,g.goods_id, g.goods_name ,g.goods_thumb,g.supplier_id".
           " FROM " .$GLOBALS['ecs']->table('booking_goods')." AS bg , " .$GLOBALS['ecs']->table('goods')." AS g WHERE bg.goods_id = g.goods_id AND bg.user_id = '$user_id' ORDER BY bg.booking_time DESC LIMIT ".($page-1)*$size.",".$size;
	$res = $GLOBALS['db']->query($sql);
	
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
		if($row['supplier_id'] == '0')
		{
			$row['supplier_name'] = "网站自营";
		}else
		{
			$row['supplier_name'] = $GLOBALS['db']->getOne("select supplier_name from ".$GLOBALS['ecs']->table('supplier')." where supplier_id = ".$row['supplier_id']);
		}
		
		$booking[] = array('rec_id'       => $row['rec_id'],
                           'goods_id'     => $row['goods_id'],
                           'goods_name'   => $row['goods_name'],
                           'goods_number' => $row['goods_number'],
						   'goods_thumb'  => $row['goods_thumb'],
						   'goods_desc'	  => $row['goods_desc'],
						   'supplier_name'=> $row['supplier_name'],
                           'booking_time' => local_date($GLOBALS['_CFG']['date_format'], $row['booking_time']),
                           'dispose_note' => $row['dispose_note']);
    }
	
	$smarty->assign('booking',       $booking);
	$smarty->assign('page',       $page);
	$result['result'] = $smarty->fetch('quehuolist_app.lbi');
	die($json->encode($result));
}elseif($_REQUEST['act'] == 'del'){
	$booking_id = isset($_REQUEST['recid']) ? intval($_REQUEST['recid']) : 1;
	$user_id = isset($_REQUEST['userid']) ? intval($_REQUEST['userid']) : 0;
	 $sql = 'DELETE FROM ' .$GLOBALS['ecs']->table('booking_goods').
           " WHERE rec_id = '$booking_id' AND user_id = '$user_id'";
	 if($GLOBALS['db']->query($sql)){
		 $result['result'] = $booking_id;
	 }
	 die($json->encode($result));
}
?>
