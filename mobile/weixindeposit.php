<?php

define('IN_ECS', true);

require('../includes/init.php');

require('../includes/lib_order.php');

include_once('../includes/lib_payment.php');

error_reporting(E_ALL ^ E_NOTICE);

$out_trade_no =  intval($_GET['out_trade_no']);

$sql = "SELECT order_id FROM " . $GLOBALS['ecs']->table('pay_log') . " WHERE log_id = '$out_trade_no'";
$order_id = $GLOBALS['db']->getOne($sql);


$order = $db->getRow("SELECT * FROM " . $ecs->table('user_account') . " WHERE id = '$order_id'");
if ($order['amount'] > 0){

	$order['pay_id'] = $GLOBALS['db']->getOne("select pay_id from ".$GLOBALS['ecs']->table('payment')." where pay_name = '".$order['payment']."'");

	$order['order_amount'] = $order['amount'];

	$order['order_id']	= $out_trade_no.'-'.time();

	$order['order_sn']	= $out_trade_no;

	$payment = payment_info($order['pay_id']);

	include_once('../includes/modules/payment/' . $payment['pay_code'] . '.php');

	$pay_obj    = new $payment['pay_code'];

	$code = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));

}else{

	echo 1;exit;

}

?>

<html>

<head>

    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>

    <title>微信安全支付</title>

	<script type="text/javascript">

		function jsApiCall()

		{

			WeixinJSBridge.invoke(

				'getBrandWCPayRequest',

				<?php echo $code;?>,

				function(res){

					//alert(res.err_msg);

					//WeixinJSBridge.log(res.err_msg);

					if(res.err_msg == "get_brand_wcpay_request:ok" ) {

						window.location.href = "<?php echo return_url('weixin');?>";

					} else {

						alert("交易取消");

						window.location.href = "./index.php";

					}

				}

			);

		}

		//function callpay()

		window.onload = function ()



		{

			if (typeof WeixinJSBridge == "undefined"){

			    if( document.addEventListener ){

			        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);

			    }else if (document.attachEvent){

			        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 

			        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);

			    }

			}else{

			    jsApiCall();

			}

		}

	</script>

</head>

<body>

<!--	</br></br></br></br>

	<div align="center">

		<button style="width:400px; height:100px; background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:28px;" type="button" onclick="callpay()" >微信支付</button>

	</div>

	-->

</body>

</html>