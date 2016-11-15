<?php
//decode by QQ:270656184 http://www.yunlu99.com/
define('IN_ECS', true);
define('ECS_ADMIN', true);

require('../includes/init.php');
$is_weixin = is_weixin();
if($is_weixin && !empty($_POST['id']) && !empty($_POST['uid']) && !empty($_POST['show']) && !empty($_POST['type']) && !empty($_GET['act']) && !empty($_SESSION['user_id'])){
	if($_GET['act'] == 'mycheck')
	{
		if($_POST['type'] == 'pengyouquan'){
			$weixin_info = $db->getRow("select * from ".$ecs->table('weixin_config')." where id='1'");
			if($weixin_info['appid'] && $weixin_info['appsecret'] && $weixin_info['title'] && $weixin_info['is_pengyouquan'] == '1'){
				$nowtime = mktime();
				$yestime = strtotime(date('Y-m-d'));

				if($weixin_info['is_everyday'] == '1'){
					$count = $db->getOne("select count(*) from ".$ecs->table('weixin_share')." where type='2' and user_id=".$_SESSION['user_id']." and create_time > '$yestime'");
					if($count <= $weixin_info['pengyouquan_times'] ){
						$info = date('Y-m-d H:i:s')."微信分享朋友圈,获得".$weixin_info['pengyouquan_money'].'元,'.$weixin_info['pengyouquan_point'].'积分';
						log_account_change($_SESSION['user_id'], $weixin_info['pengyouquan_money'], 0, 0, $weixin_info['pengyouquan_point'], $info);
					}
				}
				else
				{
					if($count <= $weixin_info['pengyou_times'] ){
						$info = date('Y-m-d H:i:s')."微信分享朋友圈,获得".$weixin_info['pengyouquan_money'].'元,'.$weixin_info['pengyouquan_point'].'积分';
						log_account_change($_SESSION['user_id'], $weixin_info['pengyouquan_money'], 0, 0, $weixin_info['pengyouquan_point'], $info);
					}
				}
				$db->query("insert into ".$ecs->table('weixin_share')." (`user_id`,`type`,`create_time`) values ('$_SESSION[user_id]','2','$nowtime') ");
			}
		}

		if($_POST['type'] == 'pengyou'){
			$weixin_info = $db->getRow("select * from ".$ecs->table('weixin_config')." where id='1'");
			if($weixin_info['appid'] && $weixin_info['appsecret'] && $weixin_info['title'] && $weixin_info['is_pengyou'] == '1'){
				$nowtime = mktime();
				$yestime = strtotime(date('Y-m-d'));

				if($weixin_info['is_everyday'] == '1'){
					$count = $db->getOne("select count(*) from ".$ecs->table('weixin_share')." where type='1' and user_id=".$_SESSION['user_id']." and create_time > '$yestime'");
					if($count <= $weixin_info['pengyou_times'] ){
						$info = date('Y-m-d H:i:s')."微信分享给朋友,获得".$weixin_info['pengyou_money'].'元,'.$weixin_info['pengyou_point'].'积分';
						log_account_change($_SESSION['user_id'], $weixin_info['pengyou_money'], 0, 0, $weixin_info['pengyou_point'], $info);
					}
				}
				else
				{
					if($count <= $weixin_info['pengyou_times'] ){
						$info = date('Y-m-d H:i:s')."微信分享给朋友,获得".$weixin_info['pengyou_money'].'元,'.$weixin_info['pengyou_point'].'积分';
						log_account_change($_SESSION['user_id'], $weixin_info['pengyou_money'], 0, 0, $weixin_info['pengyou_point'], $info);
					}
				}
				$db->query("insert into ".$ecs->table('weixin_share')." (`user_id`,`type`,`create_time`) values ('$_SESSION[user_id]','1','$nowtime') ");
			}
		}
	}
}

function is_weixin()
{
	$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
	if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false )
	{
		return false;
	}
	else
	{
		return true;
	}
}