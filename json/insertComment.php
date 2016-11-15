<?php

/**
 * 添加商品评论
*/
	define('IN_ECS', true);
	require('includes/init.php');
	//require('../includes/lib_goods.php');
	$goods_id = isset($_REQUEST['goods_id'])  ? intval($_REQUEST['goods_id']) : 0;
	$uid = isset($_REQUEST['uid'])  ? intval($_REQUEST['uid']) : 0;
	$content=$_POST['content'];
	$comment_rank=$_GET['comment_rank'];
	
	/*查找后台配置的库存管理*/
	$sql="SELECT value FROM ".$ecs->table('shop_config')." WHERE id='227'";
	$config=$db ->getRow($sql);
	switch ($config['value'])
	{
	case 1:
		$sql="SELECT * FROM ".$ecs->table('users')." WHERE user_id='$uid';";
		$user = $db -> getRow($sql);
		if(empty($user)){
			$result['code']=0;
			$result['info']="您还未登录不可以评论哦！";
			print_r(json_encode($result));
			exit();
		}
	  break;
	case 2:
	  $sql="SELECT * FROM ".$ecs->table('order_info')." WHERE user_id='$uid';";
		$order_info = $db -> getRow($sql);
		if(empty($order_info)){
			$result['code']=0;
			$result['info']="您还未购买过不可以评论哦！";
			print_r(json_encode($result));
			exit();
		}
	  break;
	case 3:
		$sql="SELECT oi.order_id FROM ".$ecs->table('order_info')." as oi, ".$ecs->table('order_goods')." as og   WHERE oi.user_id='$uid' and og.order_id=oi.order_id and og.goods_id='$goods_id'";
		$goods_sn = $db -> getRow($sql);
		if(empty($goods_sn)){
			$result['code']=0;
			$result['info']="您还未购买过该商品不可以评论哦！";
			print_r(json_encode($result));
			exit();
		}
	  break;
	
	}
	$result=array();
	$sql="SELECT * FROM ".$ecs->table('users')." WHERE user_id='$uid';";
	$user = $db -> getRow($sql);
	$comment_check = $_CFG['comment_check'];
	$status = ($comment_check == 0) ? '1' : '0';
	$sql="INSERT INTO ".$ecs->table('comment')." (`comment_id`, `comment_type`, `id_value`, `email`, `user_name`, `content`, `comment_rank`, `add_time`, `ip_address`, `status`, `parent_id`, `user_id`) VALUES (NULL ,  '0',  '$goods_id',  '".$user['email']."',  '".$user['user_name']."',  '$content',  '$comment_rank',  '".time()."',  '',  ".$status.",  '0',  '$uid');";
	$res=$db->query($sql);
	if($res){
		$result['code']=1;
		$result['comment_check'] = $comment_check;
//		$result['goods_sn']=$goods_sn;
		$result['info']="评论提交成功！";
	}else{
		$result['code']=0;
		$result['info']="评论提交失败！";
	}
	print_r(json_encode($result));
	
?>