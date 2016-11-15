<?php 
//  APP第三方登录插件 
/*===========================================================
*   name : 微信
*   author : `68ecshop'
*   QQ : 800007396
*   VERSION : 1.0v
*   DATE : 2015-7-10
*   尊重作者,保留版权信息
*   版权所有 `68ecshop'
*   使用；不允许对程序代码以任何形式任何目的的再发布。
**/


if (defined('WEBSITE'))
{
	
	global $_LANG;
	$_LANG['help']['APP_KEY'] = '微信应用的AppID';
	$_LANG['help']['APP_SECRET'] = '微信应用的AppSecret';
	
	$_LANG['APP_KEY'] = 'AppID';
	$_LANG['APP_SECRET'] = 'AppSecret';
	
	$i = isset($web) ? count($web) : 0;
	// 类名
	$web[$i]['name'] = '微信';
	
	// 文件名，不包含后缀
	
	$web[$i]['type'] = 'weixin';
	
	$web[$i]['className'] = 'weixin';
	
	// 作者信息
	$web[$i]['author'] = '68ecshop';
	
	// 作者QQ
	$web[$i]['qq'] = '800007396';
	
	// 作者邮箱
	$web[$i]['email'] = '68ecshop@68ecshop.com';
	
	// 申请网址
	$web[$i]['website'] = 'http://open.weixin.qq.com';
	
	// 版本号
	$web[$i]['version'] = '1.0v';
	
	// 更新日期
	$web[$i]['date']  = '2015-7-10';
	
	// 配置信息
	$web[$i]['config'] = array(
		array('type'=>'text' , 'name'=>'APP_KEY', 'value'=>''),
		array('type'=>'text' , 'name' => 'APP_SECRET' , 'value' => ''),
	);
}

if (!defined('WEBSITE'))
{
	include 'oath2.class.php';
	class website extends oath2
	{
		function __construct(){
			$this->userURL = 'https://api.weixin.qq.com/sns/userinfo';
			$this->app_key = APP_KEY;
            $this->app_secret = APP_SECRET;
			$this->meth  = 'GET';
		}
        
        function getMessage()
		{
			$pare = array();
			$pare['openid'] = $this->openid;
			$pare['access_token'] = $this->token['access_token'];
			
			if(!empty($this->token['refresh_token']))
			{
				$pare['refresh_token'] = $this->token['refresh_token'];
			}
			
			$p = array_merge( $pare , $this->token , $this->post_msg);
			$p = $this->unset_null($p);
			
			if(method_exists($this , 'sign'))
			{
				$this->sign( $p );
			}
			
			$result = $this->http($this->userURL , $this->meth  , $p);
			
			if(method_exists($this , 'is_error'))
			{
				$info = $this->is_error($result);
			}
			else
			{
				$info = json_decode($result , true);
			}
			
			if( method_exists($this , 'message') )
			{
				$info = $this->message($info);
			}
			return $info;
		}

		/*
		"openid":"OPENID",
		"nickname":"NICKNAME",
		"sex":1,
		"province":"PROVINCE",
		"city":"CITY",
		"country":"COUNTRY",
		"headimgurl": "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0",
		"privilege":[
		"PRIVILEGE1", 
		"PRIVILEGE2"
		],
		"unionid": " o6_bmasdasdsad6_2sgVt7hMZOPfL"
		*/
        function message($info)
		{
			$aite_id = 'weixin_'.$info['openid'];
			$alias = $info['nickname'];
			$sex = $info['sex'];
			$headimg = $info['headimgurl'];
			return array('aite_id'=>$aite_id,'alias'=>$alias,'sex'=>$sex,'headimg'=>$headimg);
		}
	}
}