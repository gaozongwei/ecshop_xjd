<?php

/**
 * ECSHOP YeePay易宝插件
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liuhui $
 * $Id: yeepay.php 17063 2010-03-25 06:35:46Z liuhui $
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/yeepay.php';

if (file_exists($payment_lang))
{
    global $_LANG;

    include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'yp_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'ECSHOP TEAM';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.yeepay.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.1';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'yp_account', 'type' => 'text', 'value' => ''),
        array('name' => 'yp_key',     'type' => 'text', 'value' => ''),
    );

    return;
}

/**
 * 类
 */
class yeepay
{
    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function yeepay()
    {
    }

    function __construct()
    {
        $this->yeepay();
    }

    /**
     * 生成支付代码
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
    function get_code($order, $payment)
    {
        $data_merchant_id = $payment['yp_account'];
        $data_order_id    = $order['order_sn'];
        $data_amount      = $order['order_amount'];
        $message_type     = 'Buy';
        $data_cur         = 'CNY';
        $product_id       = '';
        $product_cat      = '';
        $product_desc     = '';
        $address_flag     = '0';
		#	支付通道编码
		##默认为""，到易宝支付网关.若不需显示易宝支付的页面，直接跳转到各银行、神州行支付、骏网一卡通等支付页面，该字段可依照附录:银行列表设置参数值.			
		$pd_FrpId					= "";

		#	应答机制
		##默认为"1": 需要应答机制;
		$pr_NeedResponse	= "1";
        $data_return_url  = return_url(basename(__FILE__, '.php'));
        $merchantKey     = $payment['yp_key'];
        $data_pay_account = $payment['yp_key'];
        $mct_properties   = $order['log_id'];
        $def_url = $message_type.$data_merchant_id.$data_order_id . $data_amount . $data_cur . $product_id . $product_cat
                             . $product_desc . $data_return_url.$address_flag . $mct_properties .$pd_FrpId .$pr_NeedResponse;
		
		logstr($data_order_id,$def_url,HmacMd5($def_url,$merchantKey));
        $MD5KEY = HmacMd5($def_url,$merchantKey);
		
        $def_url  = "\n<form action='https://www.yeepay.com/app-merchant-proxy/node' method='post' target='_blank'>\n";
        $def_url .= "<input type='hidden' name='p0_Cmd' value='".$message_type."'>\n";
        $def_url .= "<input type='hidden' name='p1_MerId' value='".$data_merchant_id."'>\n";
        $def_url .= "<input type='hidden' name='p2_Order' value='".$data_order_id."'>\n";
        $def_url .= "<input type='hidden' name='p3_Amt' value='".$data_amount."'>\n";
        $def_url .= "<input type='hidden' name='p4_Cur' value='".$data_cur."'>\n";
        $def_url .= "<input type='hidden' name='p5_Pid' value='".$product_id."'>\n";
        $def_url .= "<input type='hidden' name='p6_Pcat' value='".$product_cat."'>\n";
        $def_url .= "<input type='hidden' name='p7_Pdesc' value='".$product_desc."'>\n";
        $def_url .= "<input type='hidden' name='p8_Url' value='".$data_return_url."'>\n";
        $def_url .= "<input type='hidden' name='p9_SAF' value='".$address_flag."'>\n";
        $def_url .= "<input type='hidden' name='pa_MP' value='".$mct_properties."'>\n";
		$def_url .= "<input type='hidden' name='pd_FrpId' value='" .$pd_FrpId."'>\n";
		$def_url .= "<input type='hidden' name='pr_NeedResponse' value='" .$pr_NeedResponse."'>\n";
        $def_url .= "<input type='hidden' name='hmac' value='".$MD5KEY."'>\n";
        $def_url .= "<input type='submit' value='" . $GLOBALS['_LANG']['pay_button'] . "'>";
        $def_url .= "</form>\n";

        return $def_url;
    }

    /**
     * 响应操作
     */
    function respond()
    {
        $payment        = get_payment('yeepay');

        $merchant_id    = $payment['yp_account'];       // 获取商户编号
        $merchant_key   = $payment['yp_key'];           // 获取秘钥

        $message_type   = trim($_REQUEST['r0_Cmd']);
        $succeed        = trim($_REQUEST['r1_Code']);   // 获取交易结果,1成功,-1失败
        $trxId          = trim($_REQUEST['r2_TrxId']);
        $amount         = trim($_REQUEST['r3_Amt']);    // 获取订单金额
        $cur            = trim($_REQUEST['r4_Cur']);    // 获取订单货币单位
        $product_id     = trim($_REQUEST['r5_Pid']);    // 获取产品ID
        $orderid        = trim($_REQUEST['r6_Order']);  // 获取订单ID
        $userId         = trim($_REQUEST['r7_Uid']);    // 获取产品ID
        $merchant_param = trim($_REQUEST['r8_MP']);     // 获取商户私有参数
        $bType          = trim($_REQUEST['r9_BType']);  // 获取订单ID

        $mac            = trim($_REQUEST['hmac']);      // 获取安全加密串

       /* ///生成加密串,注意顺序
        $ScrtStr  = $merchant_id . $message_type . $succeed . $trxId . $amount . $cur . $product_id .
                     $orderid . $userId . $merchant_param . $bType;

        logstr($orderid,$ScrtStr, HmacMd5($ScrtStr, $merchant_key));
		
        $mymac = HmacMd5($ScrtStr,$merchantKey);*/
		#	解析返回参数.
		$return = getCallBackValue($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);

		#	判断返回签名是否正确（True/False）
		$bRet = CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);
		#	以上代码和变量不需要修改.
	
        if($bRet){
			if($r1_Code=="1"){
				
			#	需要比较返回的金额与商家数据库中订单的金额是否相等，只有相等的情况下才认为是交易成功.
			#	并且需要对返回的处理进行事务控制，进行记录的排它性处理，在接收到支付结果通知后，判断是否进行过业务逻辑处理，不要重复进行业务逻辑处理，防止对同一条交易重复发货的情况发生.      	  	
				
				if($r9_BType=="1"){
					order_paid($merchant_param, 2);
					return true;
				}elseif($r9_BType=="2"){
					#如果需要应答机制则必须回写流,以success开头,大小写不敏感.
					order_paid($merchant_param, 2);
					return true;
				}
			}
			
		}else{
			return false;
		}
    }
}

function getReqHmacString($p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse)
{
  global $p0_Cmd;
  global $p9_SAF;

	#进行签名处理，一定按照文档中标明的签名顺序进行
  $sbOld = "";
  #加入业务类型
  $sbOld = $sbOld.$p0_Cmd;
  #加入商户编号
  $sbOld = $sbOld.$p1_MerId;
  #加入商户订单号
  $sbOld = $sbOld.$p2_Order;     
  #加入支付金额
  $sbOld = $sbOld.$p3_Amt;
  #加入交易币种
  $sbOld = $sbOld.$p4_Cur;
  #加入商品名称
  $sbOld = $sbOld.$p5_Pid;
  #加入商品分类
  $sbOld = $sbOld.$p6_Pcat;
  #加入商品描述
  $sbOld = $sbOld.$p7_Pdesc;
  #加入商户接收支付成功数据的地址
  $sbOld = $sbOld.$p8_Url;
  #加入送货地址标识
  $sbOld = $sbOld.$p9_SAF;
  #加入商户扩展信息
  $sbOld = $sbOld.$pa_MP;
  #加入支付通道编码
  $sbOld = $sbOld.$pd_FrpId;
  #加入是否需要应答机制
  $sbOld = $sbOld.$pr_NeedResponse;
	logstr($p2_Order,$sbOld,HmacMd5($sbOld,$merchantKey));
  return HmacMd5($sbOld,$merchantKey);
  
} 

function getCallbackHmacString($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType)
{
	$payment        = get_payment('yeepay');

    $p1_MerId    = $payment['yp_account'];       // 获取商户编号
    $merchantKey   = $payment['yp_key'];           // 获取秘钥
  
	#取得加密前的字符串
	$sbOld = "";
	#加入商家ID
	$sbOld = $sbOld.$p1_MerId;
	#加入消息类型
	$sbOld = $sbOld.$r0_Cmd;
	#加入业务返回码
	$sbOld = $sbOld.$r1_Code;
	#加入交易ID
	$sbOld = $sbOld.$r2_TrxId;
	#加入交易金额
	$sbOld = $sbOld.$r3_Amt;
	#加入货币单位
	$sbOld = $sbOld.$r4_Cur;
	#加入产品Id
	$sbOld = $sbOld.$r5_Pid;
	#加入订单ID
	$sbOld = $sbOld.$r6_Order;
	#加入用户ID
	$sbOld = $sbOld.$r7_Uid;
	#加入商家扩展信息
	$sbOld = $sbOld.$r8_MP;
	#加入交易结果返回类型
	$sbOld = $sbOld.$r9_BType;

	logstr($r6_Order,$sbOld,HmacMd5($sbOld,$merchantKey));
	return HmacMd5($sbOld,$merchantKey);

}


#	取得返回串中的所有参数
function getCallBackValue(&$r0_Cmd,&$r1_Code,&$r2_TrxId,&$r3_Amt,&$r4_Cur,&$r5_Pid,&$r6_Order,&$r7_Uid,&$r8_MP,&$r9_BType,&$hmac)
{  
	$r0_Cmd		= $_REQUEST['r0_Cmd'];
	$r1_Code	= $_REQUEST['r1_Code'];
	$r2_TrxId	= $_REQUEST['r2_TrxId'];
	$r3_Amt		= $_REQUEST['r3_Amt'];
	$r4_Cur		= $_REQUEST['r4_Cur'];
	$r5_Pid		= $_REQUEST['r5_Pid'];
	$r6_Order	= $_REQUEST['r6_Order'];
	$r7_Uid		= $_REQUEST['r7_Uid'];
	$r8_MP		= $_REQUEST['r8_MP'];
	$r9_BType	= $_REQUEST['r9_BType']; 
	$hmac			= $_REQUEST['hmac'];
	
	return null;
}

function CheckHmac($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac)
{
	if($hmac==getCallbackHmacString($r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType))
		return true;
	else
		return false;
}
		
  
function HmacMd5($data,$key)
{
// RFC 2104 HMAC implementation for php.
// Creates an md5 HMAC.
// Eliminates the need to install mhash to compute a HMAC
// Hacked by Lance Rushing(NOTE: Hacked means written)

//需要配置环境支持iconv，否则中文参数不能正常处理
$key = iconv("GB2312","UTF-8",$key);
$data = iconv("GB2312","UTF-8",$data);

$b = 64; // byte length for md5
if (strlen($key) > $b) {
$key = pack("H*",md5($key));
}
$key = str_pad($key, $b, chr(0x00));
$ipad = str_pad('', $b, chr(0x36));
$opad = str_pad('', $b, chr(0x5c));
$k_ipad = $key ^ $ipad ;
$k_opad = $key ^ $opad;

return md5($k_opad . pack("H*",md5($k_ipad . $data)));
}

function logstr($orderid,$str,$hmac)
{
include 'merchantProperties.php';
$james=fopen($logName,"a+");
fwrite($james,"\r\n".date("Y-m-d H:i:s")."|orderid[".$orderid."]|str[".$str."]|hmac[".$hmac."]");
fclose($james);
}

?>