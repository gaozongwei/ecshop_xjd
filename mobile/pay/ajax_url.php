<?php

/**
 * 支付宝回调
*/
	define('IN_ECS', true);
	require('../../includes/init.php');
	/*========================支付宝sdk--bug修改代码片段开始==============================*/
	require_once("alipay.config.php");
	require_once("lib/alipay_notify.class.php");
	require('../includes/lib_payment.php');
	//初始化配置
	$alipay_config = array(
				"partner" => PARTNER,
				"key" => KEY,
				"private_key_path" => PRIVATE_KEY_PATH,
				"ali_public_key_path" => ALI_PUBLIC_KEY_PATH,
				"sign_type" => SIGN_TYPE,
				"input_charset" => INPUT_CHARSET,
				"cacert" => CACERT,
				"transport" => TRANSPORT 
				);
	
	//计算得出通知验证结果
	$alipayNotify = new AlipayNotify($alipay_config);
	$verify_result = $alipayNotify->verifyNotify();
	
	if($verify_result) {

	if(!empty($_POST['notify_data'])){
		$notify_data = $_POST['notify_data'];
		
		
		$doc = new DOMDocument();
		$doc->loadXML($notify_data);
		if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
			//商户订单日志号
			$out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
			//支付宝交易号
			$trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
			//交易状态
			$trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
			//交易金额
			$total_fee = $doc->getElementsByTagName( "total_fee" )->item(0)->nodeValue;
			$pay_time=time();
			order_paid($out_trade_no,2);
			
		}
	}
}

?>