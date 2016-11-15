<?php

define('IN_ECS', true);

require('../includes/init.php');
include('../includes/cls_json.php');
$json   = new JSON;
/* 载入语言文件 */
require_once('../languages/zh_cn/user.php');

//$user_id = $_SESSION['user_id'];

if ($_POST['act'] == 'signin' && $_POST['acr'] == 'app')
{

    $username = !empty($_POST['user']) ? trim($_POST['user']) : '';
    $password = !empty($_POST['pwd']) ? trim($_POST['pwd']) : '';
    $result   = array('code' => 0, 'info' => '');

    if ($user->login($username, $password))
    {
        update_user_info();  //更新用户信息
        recalculate_price(); // 重新计算购物车中的商品价格
        //$smarty->assign('user_info', get_user_info());
        $result['code'] = 1;
       	
        $user = get_user_info();
        /*查找代付款的数据   jx*/
        $user_id = $user['user_id'];
        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND pay_status = 0 AND order_status != 2 ";
        $user['payment'] = $GLOBALS['db']->getOne($sql);
        /*查找代发货的数据   jx*/
        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND shipping_status = 0 AND order_status != 2";
        $user['deliver'] = $GLOBALS['db']->getOne($sql);
        /*查找代收货的数据   jx*/
        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND shipping_status = 1 AND order_status != 2";
        $user['receipt'] = $GLOBALS['db']->getOne($sql);
        /*查找全部订单数据   jx*/
        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id'";
        $user['quan'] = $GLOBALS['db']->getOne($sql);
        $result['info']=$user;
       // $ucdata = empty($user->ucdata)? "" : $user->ucdata;
        //$result['ucdata'] = $ucdata;
    }
    else
    {
        $result['info'] = $_LANG['login_failure'];
    }
    die($json->encode($result));
}
elseif($_POST['act'] == 'oath_login')
{
	$result   = array('code' => 0, 'info' => '');
//	file_put_contents('1.txt','weibo:'.var_export($_REQUEST,true));
    $type = empty($_POST['type']) ? '' : trim($_POST['type']);
	$openid = $_POST['openid'];
	$access_token = $_POST['access_token'];
	if(empty($type) || empty($openid) || empty($access_token))
	{
		$result['info'] = '参数错误';
		die(json_encode($result));
	}
    include_once(ROOT_PATH . 'json/includes/website/jntoo.php');
	$c = &website($type);
	if($c)
	{
		$c->setOpenId($openid);
		$c->setAccessToken(array('access_token'=>$access_token));
		$info = $c->getMessage();
	}
	else
	{
		$result['info'] = '服务器错误';
		die(json_encode($result));
	}
	
	$count = $db->getOne('SELECT COUNT(*) FROM '.$ecs->table('users').' WHERE aite_id="'.$info['aite_id'].'"');
	
	if($count == 0)
	{
		$sql = 'INSERT INTO '.$ecs->table('users').'(user_name,password,aite_id,sex,alias,reg_time,froms) VALUES("'.$type.'_'.rand().'","'.MD5($info['aite_id']).'","'.$info['aite_id'].'","'.$info['sex'].'","'.$info['alias'].'","'.time().'","app")';
		$try = 0;
		while(!$db->query($sql) && $try < 10)
		{
			$try ++;
		}
		$user_id = $db->insert_id();
		$_SESSION['user_id'] = $user_id;
	}
	else if($count == 1)
	{
		$user_id = $db->getOne('SELECT user_id FROM '.$ecs->table('users').' WHERE aite_id="'.$aite_id.'"');
		$_SESSION['user_id'] = $user_id;
	}
	else
	{
		$result['info'] = '未知错误';
		die(json_encode($result));
	}
	
    update_user_info();  //更新用户信息
    recalculate_price(); // 重新计算购物车中的商品价格
    //$smarty->assign('user_info', get_user_info());
    $result['code'] = 1;
   
    $user = get_user_info();
    /*查找代付款的数据   jx*/
    $user_id = $user['user_id'];
    $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND pay_status = 0 AND order_status != 2 ";
    $user['payment'] = $GLOBALS['db']->getOne($sql);
    /*查找代发货的数据   jx*/
    $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND shipping_status = 0 AND order_status != 2";
    $user['deliver'] = $GLOBALS['db']->getOne($sql);
    /*查找代收货的数据   jx*/
    $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id' AND shipping_status = 1 AND order_status != 2";
    $user['receipt'] = $GLOBALS['db']->getOne($sql);
    /*查找全部订单数据   jx*/
    $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('order_info')."WHERE user_id = '$user_id'";
    $user['quan'] = $GLOBALS['db']->getOne($sql);
    $result['info']=$user;
	//file_put_contents('1.txt',var_export($result,true));
	die(json_encode($result));
   // $ucdata = empty($user->ucdata)? "" : $user->ucdata;
    //$result['ucdata'] = $ucdata;
}
elseif($_POST['act'] == 'getinfo'){
    $result   = array('code' => 0, 'info' => '');
    $userid = intval($_POST['user_id']);
    if($userid > 0){
        $result['code'] = 1;
        $result['info']=get_user_info($userid);
    }else{
        $result['info']='用户信息获取失败，请重新登陆!';
    }
    die($json->encode($result));
}else
{
    $redirect_url =  "http://".$_SERVER["HTTP_HOST"].str_replace("user.php", "index.php");
    header('Location: '.$redirect_url);
}
?>