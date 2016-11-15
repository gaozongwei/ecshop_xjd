<?php 
//  APP第三方登录插件 
/*===========================================================
*   name : QQ
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
	$_LANG['help']['APP_KEY'] = 'QQ应用的APP ID';
	$_LANG['help']['APP_SECRET'] = 'QQ应用的APP KEY';
	
	$_LANG['APP_KEY'] = 'APP ID';
	$_LANG['APP_SECRET'] = 'APP KEY';
	
	$i = isset($web) ? count($web) : 0;
	// 类名
	$web[$i]['name'] = 'QQ';
	
	// 文件名，不包含后缀
	
	$web[$i]['type'] = 'qq';
	
	$web[$i]['className'] = 'qq';
	
	// 作者信息
	$web[$i]['author'] = '68ecshop';
	
	// 作者QQ
	$web[$i]['qq'] = '800007396';
	
	// 作者邮箱
	$web[$i]['email'] = '68ecshop@68ecshop.com';
	
	// 申请网址
	$web[$i]['website'] = 'http://open.qq.com';
	
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
		function __construct()
		{
			$this->userURL = 'https://graph.qq.com/user/get_user_info';
            $this->app_key = APP_KEY;
			$this->meth  = 'GET';
		}

        function getMessage()
		{	
            $pare = array();
			$pare['access_token'] = $this->token['access_token'];
			$pare['oauth_consumer_key'] = $this->app_key;
			$pare['openid'] = $this->openid;
			$pare['format'] = 'json';
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
		ret 		返回码 
		msg 		如果ret<0，会有相应的错误信息提示，返回数据全部用UTF-8编码。 
		nickname 		用户在QQ空间的昵称。 
		figureurl 		大小为30×30像素的QQ空间头像URL。 
		figureurl_1 		大小为50×50像素的QQ空间头像URL。 
		figureurl_2 		大小为100×100像素的QQ空间头像URL。 
		figureurl_qq_1 		大小为40×40像素的QQ头像URL。 
		figureurl_qq_2 		大小为100×100像素的QQ头像URL。需要注意，不是所有的用户都拥有QQ的100x100的头像，但40x40像素则是一定会有。 
		gender 		性别。 如果获取不到则默认返回"男" 
		is_yellow_vip 		标识用户是否为黄钻用户（0：不是；1：是）。 
		vip 		标识用户是否为黄钻用户（0：不是；1：是） 
		yellow_vip_level 		黄钻等级 
		level 		黄钻等级 
		is_yellow_year_vip 		标识是否为年费黄钻用户（0：不是； 1：是） 
		*/
		function message($info)
		{
			$aite_id = 'qq_'.$this->openid;
			$alias = $info['nickname'];
			if($info['gender'] == '男')
			{
				$sex = '1';
			}
			else if($info['gender'] == '女')
			{
				$sex = '2';
			}
			else
			{
				$sex = '0';
			}

			if(empty($info['figureurl_qq_2']))
			{
				$headimg = $info['figureurl_qq_2'];
			}
			else
			{
				$headimg = $info['figureurl_qq_1'];
			}
			$result = array('aite_id'=>$aite_id,'alias'=>$alias,'sex'=>$sex,'headimg'=>$headimg);
			return $result;
		}
	}
}