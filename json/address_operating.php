<?php

/**
 * 用户地址列表
*/
	define('IN_ECS', true);
	require('includes/init.php');
	$act=$_GET['act'];
	
	
	$result=array();
	if($act=="update"){
		$id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
		$uid = isset($_REQUEST['uid'])  ? intval($_REQUEST['uid']) : 0;
		$consignee=$_POST['consignee'];
		$tel=$_GET['tel'];
		$email=$_GET['email'];
		$zipcode=$_GET['zipcode'];
		$address=$_POST['address'];
        $country=$_GET['country'];
		$province=$_GET['province'];
		$city=$_GET['city'];
		$district=$_GET['district'];
		$res = $db -> query("update ".$ecs->table('user_address')." set 
							consignee = '$consignee',
							mobile = '$tel',
							email = '$email',
							zipcode = '$zipcode',
							address = '$address',
							country = '$country',
							province = '$province',
							city = '$city',
							district = '$district'
						where address_id = '$id'");
		$address_id = $db -> query("update ".$ecs->table('users')." set 
							address_id = '$id'
						where user_id = '$uid'");
		$result['act']="update";
		if($res){
			$result['code']=1;
			$result['info']="修改成功！";
		}else{
			$result['code']=0;
			$result['info']="修改失败！";
		}
	}
	if($act=="add"){
		$consignee=$_GET['consignee'];
		$user_id=$_GET['uid'];
		$tel=$_GET['tel'];
		$email=$_GET['email'];
		$zipcode=$_GET['zipcode'];
		$address=$_GET['address'];
        $country=$_GET['country'];
		$province=$_GET['province'];
		$city=$_GET['city'];
		$district=$_GET['district'];
		
		$address = array(
        'user_id'    => $user_id,
        'address_id' => "",
        'country'    => $country,
        'province'   => $province,
        'city'       => $city,
        'district'   => $district,
        'address'    => $address,
        'consignee'  => $consignee,
        'email'      => $email,
        'mobile'        => $tel,
        'best_time'  => '',
        'sign_building' => '',
        'zipcode'       => $zipcode,
        );
		/* 插入一条新记录 */
        $res=$db->autoExecute($ecs->table('user_address'), $address, 'INSERT');
        $address_id = $db->insert_id();
		$set_defaut_address= $db -> query("update ".$ecs->table('users')." set 
							address_id = '$address_id'
						where user_id = '$user_id'");
		$result['add']="add";
		
		if($res>0){
			$result['code']=1;
			$result['info']="添加成功！";
		}else{
			$result['code']=0;
			$result['info']="添加失败！";
		}
	}
	if($act=="del"){
		$id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
		$sql="DELETE FROM ".$ecs->table('user_address')." WHERE address_id='$id' ";
		$res=$db -> query($sql);
		$result['act']="del";
		if($res){
			$result['code']=1;
			$result['info']="删除成功！";
		}else{
			$result['code']=0;
			$result['info']="删除失败！";
		}
		
	}
	
	print_r(json_encode($result));

?>