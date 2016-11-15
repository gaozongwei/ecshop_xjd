// JavaScript Document
/*******************************************************************************
 * 会员登录
 */
function user_login(){
	var logform = $('form[name="formLogin"]');
	var username = logform.find('#username');
	var password = logform.find('#password');
	var captcha = logform.find('#authcode');
	var error = logform.find('.msg-wrap');
	var back_act = logform.find("input[name='back_act']").val();

	if(username.val()==''){
		error.css({'visibility':'visible'});
		error.find('.msg-error-text').html('请输入账户名');
		username.parents('.item').addClass('item-error');
		return false;
	}

	if(password.val()==''){
		error.css({'visibility':'visible'});
		password.parents('.item').addClass('item-error');
		error.find('.msg-error-text').html('请输入密码');
		return false;
	}
	
	if(captcha.val()==''){
		error.css({'visibility':'visible'});
		captcha.parents('.item-detail').addClass('item-error');
		error.find('.msg-error-text').html('请输入验证码');
		return false;
	}

	Ajax.call( 'user.php?act=act_login', 'username=' + username.val()+'&password='+password.val()+'&captcha='+captcha.val()+'&back_act='+back_act, return_login , 'POST', 'JSON');
return false;
}
function return_login(result){
	if(result.error>0){
		$('form[name="formLogin"]').find('.msg-error-text').html(result.message);
		if(result.message != '对不起，您输入的验证码不正确。'){
			$('#authcode').parents('.item-detail').removeClass('item-error');	
		}
		if(result.message == '对不起，您输入的验证码不正确。'){
			$('#authcode').parents('.item-detail').addClass('item-error');		
		}
		if(result.message == '用户名或密码错误'){
			$('#password,#username').parents('.item').addClass('item-error');
		}
		if(result.message != '用户名或密码错误'){
			$('#password,#username').parents('.item').removeClass('item-error');
		}
	}else{
		$('.pop-login,.pop-mask').hide();
		$('form[name="formLogin"]').find('.msg-error-text').css('visibility','visible');
		top.location.reload();
	}
}