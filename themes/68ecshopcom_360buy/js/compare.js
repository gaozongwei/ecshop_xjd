$('#compareBox .menu li').click(function(e) {
	$('#compareBox .menu li').each(function(index, element) {
		$(this).removeClass('current');
	});
	if($(this).attr('data-value') == 'compare'){
		$('#historyList').hide();
		$('#compareList').show();
	}else{
		$('#historyList').show();
		$('#compareList').hide();
	}
	$(this).addClass('current');
});
if($('#historyList li').length > 4){
	$('#historyList ul').css('width',226*$('#historyList li').length);
	$('#historyList #sc-prev').addClass('disable');
	var click_num = 0;
	$('#historyList #sc-next').click(function(e) {
		if(($('#historyList li').length-4) > 0){
			click_num++;
			$('#historyList #sc-prev').removeClass('disable');
			if(click_num == ($('#historyList li').length-4)){
				$('#historyList #sc-next').addClass('disable');
			}
			if(click_num>($('#historyList li').length-4)){
				click_num=$('#historyList li').length-4;
			}
			$('#historyList ul').animate({marginLeft:-226*click_num});
		}
	});
	$('#historyList #sc-prev').click(function(e) {
		if(click_num > 0){
			click_num--;
			$('#historyList #sc-next').removeClass('disable');
			if(click_num == 0){
				$('#historyList #sc-prev').addClass('disable');
			}
			if(click_num <0){
				click_num = 0;	
			}
			$('#historyList ul').animate({marginLeft:-226*click_num});
		}
	});
}else{
	$('#historyList #sc-prev,#historyList #sc-next').hide();
}
var compareData = new Object();
var compareCookie = document.getCookie('compareItems');
var count = 0;
if(compareCookie != null){
	compareData = JSON.parse(compareCookie);
	for(var k in compareData){
		if(typeof(compareData[k])=="function")
			continue;
		$('.compare-btn').each(function(index, element) {
			if(k == $(this).attr('data-goods'))
				$(this).addClass('curr');
		});
		count ++;
	}
}
if(count>0){
	$('#compareBox').show();
	$('.mpbtn-contrast').parents('li').addClass('current');
}
//侧边栏点击隐藏对比栏
$('.mpbtn-contrast,.hide-compare').click(function(){
	$('#compareBox').toggle();
	$('.mpbtn-contrast').parents('li').toggleClass('current');
});
