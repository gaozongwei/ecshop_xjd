<?php

/**
 * 支付宝回调
*/
	define('IN_ECS', true);
	require('includes/init.php');
	/* $_POST['notify_data']="<notify><partner>2088011455446653</partner><discount>0.00</discount><payment_type>1</payment_type><subject>测试支付</subject><trade_no>2013092333205432</trade_no><buyer_email>945586976@qq.com</buyer_email><gmt_create>2013-09-23 09:22:21</gmt_create><quantity>1</quantity><out_trade_no>2013092390803</out_trade_no><seller_id>2088011455446653</seller_id><trade_status>TRADE_FINISHED</trade_status><is_total_fee_adjust>N</is_total_fee_adjust><total_fee>0.01</total_fee><gmt_payment>2013-09-23 09:22:22</gmt_payment><seller_email>13518753698@163.com</seller_email><gmt_close>2013-09-23 09:22:22</gmt_close><price>0.01</price><buyer_id>2088502846564329</buyer_id><use_coupon>N</use_coupon></notify>"; */
	if(!empty($_POST['notify_data'])){
		$notify_data = $_POST['notify_data'];
		$doc = new DOMDocument();
		$doc->loadXML($notify_data);
		if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
			//商户订单号
			$out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
			//支付宝交易号
			$trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
			//交易状态
			$trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
			
			if($trade_status  == 'TRADE_FINISHED') {
				
				$row = $db -> query("update ".$ecs->table('order_info')." set pay_status = '2' where order_sn = '$out_trade_no'");
				if($row){
					echo "success";	
				}else{
					echo "fail";	
				}
				
			}
			else if ($trade_status  == 'TRADE_SUCCESS') {
				
				$row = $db -> query("update ".$ecs->table('order_info')." set pay_status = '2' where order_sn = '$out_trade_no'");
			
				if($row){
					echo "success";	
				}else{
					echo "fail";	
				}
			}
		}
		/* $of = fopen('post.txt','w');//创建并打开dir.txt
		if($of){
		$str=date('Y-m-d h:m:s',time())."||";
		foreach ($_POST as $key => $value){
		$str.=$key."=>".$value."||";
		}
		 fwrite($of,$str);//把执行文件的结果写入txt文件
		}
		fclose($of); */
	}
	
	

?>