<?php

//error_reporting(E_ALL^E_NOTICE);
///////////////////////////////////////////////////////////////////////////////////////////////
//	引如微信支付的SDK
//	微信支付的SDK,下载地址https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=11_1
//	这个【企业付款】类跟微信支付SDK没有关系,
//		引入微信支付SDK仅仅是使用微信支付配置的【APPID】【证书】【商户号】及【商户支付秘钥】
//		如果不使用SDK那么自己可以在下方的$config配置信息中写好需要的配置信息
///////////////////////////////////////////////////////////////////////////////////////////////

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
require_once __DIR__."/../../data/WxPay.Config.php";

class MerchPay{
	
	
	/**
     * 企业支付
     * @param string $openid 	用户openID
     * @param string $trade_no 	单号
     * @param string $money 	金额
     * @param string $desc 		描述
     * @return string XML 		结构的字符串
     */
	public function pay($openid,$trade_no,$money,$desc){
		$data = array(
			'mch_appid' => WxPayConfig::APPID,
            'mchid'     => WxPayConfig::MCHID,
            'nonce_str' => self::getNonceStr(),
            //'device_info' => '1000',
            'partner_trade_no' => $trade_no, //商户订单号，需要唯一
            'openid'    => $openid,
            'check_name'=> 'NO_CHECK', //OPTION_CHECK不强制校验真实姓名, FORCE_CHECK：强制 NO_CHECK：
            //'re_user_name' => 'jorsh', //收款人用户姓名
            'amount'    => $money * 100, //付款金额单位为分
            'desc'      => $desc,
            'spbill_create_ip' => self::getip()
		);
		// var_dump($data);die();
		$data['sign'] = self::makeSign($data);
		
		$xmldata = self::array2xml($data);
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $res = self::curl_post_ssl($url, $xmldata);
        if(!$res){
            return array('status'=>1, 'msg'=>"Can't connect the server" );
        }
		// 这句file_put_contents是用来查看服务器返回的结果 测试完可以删除了
		file_put_contents(__DIR__.'/../../data/log2.txt',$res,FILE_APPEND);
		
        $content = self::xml2array($res);
        if(strval($content['return_code']) == 'FAIL'){
            return array('status'=>1, 'msg'=>strval($content['return_msg']));
        }
        if(strval($content['result_code']) == 'FAIL'){
            return array('status'=>1, 'msg'=>strval($content['err_code']),':'.strval($content['err_code_des']));
        }
        $resdata = array(
            'return_code'      => strval($content['return_code']),
			'result_code'      => strval($content['result_code']),
            'nonce_str'        => strval($content['nonce_str']),
            'partner_trade_no' => strval($content['partner_trade_no']),
            'payment_no'       => strval($content['payment_no']),
            'payment_time'     => strval($content['payment_time']),
        );
        return $resdata;
	}
	
	/**
     * 将一个数组转换为 XML 结构的字符串
     * @param array $arr 要转换的数组
     * @param int $level 节点层级, 1 为 Root.
     * @return string XML 结构的字符串
     */
    public function array2xml($arr, $level = 1) {
        $s = $level == 1 ? "<xml>" : '';
        foreach($arr as $tagname => $value) {
            if (is_numeric($tagname)) {
                $tagname = $value['TagName'];
                unset($value['TagName']);
            }
            if(!is_array($value)) {
                $s .= "<{$tagname}>".(!is_numeric($value) ? '<![CDATA[' : '').$value.(!is_numeric($value) ? ']]>' : '')."</{$tagname}>";
            } else {
                $s .= "<{$tagname}>" . $this->array2xml($value, $level + 1)."</{$tagname}>";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s."</xml>" : $s;
    }
	
	/**
	 * 将xml转为array
	 * @param  string 	$xml xml字符串
	 * @return array    转换得到的数组
	 */
	public function xml2array($xml){   
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$result= json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
		return $result;
	}
	
	/**
	 * 
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return 产生的随机字符串
	 */
	public function getNonceStr($length = 32) {
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		} 
		return $str;
	}
	
	/**
	* 生成签名
	* @return 签名
	*/
	public function makeSign($data){
		//获取微信支付秘钥
		$key = WxPayConfig::KEY;
		// 去空
		$data=array_filter($data);
		//签名步骤一：按字典序排序参数
		ksort($data);
		$string_a=http_build_query($data);
		$string_a=urldecode($string_a);
		//签名步骤二：在string后加入KEY
		//$config=$this->config;
		$string_sign_temp=$string_a."&key=".$key;
		//签名步骤三：MD5加密
		$sign = md5($string_sign_temp);
		// 签名步骤四：所有字符转为大写
		$result=strtoupper($sign);
		return $result;
	}
	
	/**
	 * 获取IP地址
	 * @return [String] [ip地址]
	 */
	public function getip() {
        static $ip = '';
        $ip = $_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
            $ip = $_SERVER['HTTP_CDN_SRC_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }
	
	/**
	 * 企业付款发起请求
	 * 此函数来自:https://pay.weixin.qq.com/wiki/doc/api/download/cert.zip
	 */
	public function curl_post_ssl($url, $xmldata, $second=30,$aHeader=array()){
		$ch = curl_init();
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		
		//以下两种方式需选择一种
		
		//第一种方法，cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT,WxPayConfig::SSLCERT_PATH);
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY,WxPayConfig::SSLKEY_PATH);
		
		//第二种方式，两个文件合成一个.pem文件
		//curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');
	 
		if( count($aHeader) >= 1 ){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
		}
	 
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$xmldata);
		$data = curl_exec($ch);
		if($data){
			curl_close($ch);
			return $data;
		}
		else { 
			$error = curl_errno($ch);
			echo "call faild, errorCode:$error\n"; 
			curl_close($ch);
			return false;
		}
	}
	
	public function test(){
		//$appid = WxPayConfig::APPID;
		// $data = array(
		// 	'mch_appid' => '165164314512324',
  //           'mchid'     => '654168564164756941531473',
  //           'nonce_str' => md5(time()),
		// );
		$data = array(
			'mch_appid' => 'wx67bd3728b7a85277',
            'mchid'     => '1395489502',
            'nonce_str' => md5(time()),
		);
		//$str = self::makeSign($data);
		
		$openid = 'oaAiIw7SQuQooIbRlIOAvgsLFr0U';
		$trade_no = date('YmdHis').mt_rand(1000,9999);
		$res = self::pay($openid,$trade_no,1,'提现');
		return $res;
	}
}


?>