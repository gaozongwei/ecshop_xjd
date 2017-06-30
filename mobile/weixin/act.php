<?php
require(dirname(__FILE__) . '/api.class.php');


$user_id = $_SESSION['user_id'];
// if(!$user_id){   //未登录
//     show_message($_LANG['require_login'], array('登录', '返回首页'), array('user.php?act=login', $ecs->url()), 'error', false);
// }
// else{
//   $sql = "SELECT rank_points, vip_award FROM ".
//                   $GLOBALS['ecs']->table('users') .
//                   " WHERE user_id = '$user_id' limit 0,1";
//   $user = $db->getAll($sql);
//   if($user = $user[0]){

//     if($user['rank_points'] < 10000000){
//         show_message("非VIP会员，</br>请先升级为VIP会员", array('升级VIP', '返回首页'), array('goods.php?id=292', $ecs->url()), 'error', false);
//     }

//     if($user['vip_award'] <= 0){
//         show_message("您抽奖金额已累计达到最高", array('再次购买VIP', '返回首页'), array('goods.php?id=292', $ecs->url()), 'error', false);
//     }

//   }else{
//     show_message($_LANG['require_login'], array('登录', '返回首页'), array('user.php?act=login', $ecs->url()), 'error', false);
//   }

// }

$aid = intval($_GET['aid']);
$act = $db->getRow ( "SELECT * FROM " . $GLOBALS['ecs']->table('weixin_act') . " WHERE `aid` = $aid and isopen=1" );
if(!$act) exit("抽奖活动已经关闭");
$actList = (array)$db->getAll ( "SELECT * FROM " . $GLOBALS['ecs']->table('weixin_actlist') . " where aid=$aid and isopen=1" );
if(!$actList) exit("活动未设置奖项");
$sql = "SELECT wa.*, u.user_name, wu.nickname FROM " . 
		$GLOBALS['ecs']->table('weixin_actlog') . " AS wa  left join " . 
		$GLOBALS['ecs']->table('users') . " AS u on wa.uid = u.user_id left join ". 
		$GLOBALS['ecs']->table('weixin_user') . " AS wu on wu.ecuid = u.user_id ".
		"where code!='' and aid=$aid order by lid desc limit 0,15";
$award = $db->getAll ( $sql );

// 我的中奖纪录
$sql = "SELECT wa.*, u.user_name FROM " . 
		$GLOBALS['ecs']->table('weixin_actlog') . " AS wa  left join " . 
		$GLOBALS['ecs']->table('users') . " AS u on wa.uid = u.user_id 
		where code!='' AND user_id = $_SESSION[user_id] and aid=$aid order by lid desc limit 0,15";
$my_award = $db->getAll ( $sql );
$uid = intval($_SESSION['user_id']);
$api = new weixinapi();
$awardNum = intval($api->getAwardNum($aid));
require("award_{$act['tpl']}.php");
?>