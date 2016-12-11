<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>大转盘抽奖</title>
	<meta name="viewport" content="initial-scale=1, minimum-scale=1, maximum-scale=1">
    <link type="text/css" rel="stylesheet" href="http://at.alicdn.com/t/font_pb29s3aoqoscerk9.css">
	<style>
		*{padding: 0; margin: 0;}
		body{ background:url(dzp/bg.png); font-family:'微软雅黑'}
		body.bg-green{background:url(dzp/bg.png)}
		.grid{ width: 320px; margin: 0 auto; position: relative; overflow: hidden;}
		#banner{
			width:270px;
			height: 270px;
			background: url(dzp/1.png) no-repeat;
			-webkit-background-size: 270px auto;
			background-size: 270px auto;
			overflow: hidden;
			margin: 0 auto;
		    position:relative;
		  }
			#banner .inner{
				height: 255px;
				width: 255px;
				background:url(dzp/2.png);
				margin: 0 auto;
				-webkit-background-size: 255px auto;
				background-size: 255px auto;
				position: relative;
			}
			
			#banner #zhen{
				height: 224px;
				width: 112px;
				position: absolute;
				left: 50%;
				top: 15px;
				margin-left: -56px;
				background: url(dzp/3.png);
			}


		  .block{
		  	background-color: #FFF9B3;
		  	border: 2px solid #306931;
		  	margin:0 10px 10px 10px;
		  	border-radius: 8px;
		  	padding: 8px;
		  	position: relative;
		  	font-size: 12px;
		  }
		  .block .title{
		  	font-size: 16px;
		  	line-height: 24px;
		  	background:url(dzp/tit.png) no-repeat;
		  	padding-left: 7px;
		  	width: 123px;
		  	color: #fff;
		  	height: 24px;
		  	-webkit-background-size: 123px auto;
		  	background-size: 123px auto;
		  }
		  .block p{
		  	line-height: 22px;
		  	font-size: 14px;
		  	line-height: 30px;
		  }
		

		#mask{
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			z-index: 5;
			background: rgba(0,0,0,.4);
			display: none;
		}
		#dialog{
			position: absolute;
			top: 50%;
			left: 50%;
			height: 100px;
			width: 276px;
			margin-left: -150px;
			margin-top: -50px;

			border: 2px solid #306931;
			padding: 10px;
			background-color: #fff;
			display: none;
			z-index: 6;
			font-weight: bold;
			font-size: 20px;
		}
		#dialog.yes{
			color: #900;
			background-image: url(dzp/y.png);
			background-position: right bottom;
			background-repeat: no-repeat;
			-webkit-background-size:100px auto ;
			background-size:100px auto ;
		}
		#dialog.yes a{ display: block;}

		#dialog.no{ color: #333;}
		#dialog.no button{ display: block;}
		#content{
			text-align: center;
		}
		#dialog button,#dialog a{
			display: none;
			font-size: 14px;
			position: absolute;
			bottom: 10px;
			left: 50%;
			height: 30px;
			width: 80px;
			line-height: 30px;
			margin-left: -40px;
			background-color: #2C6C4E;
			border: none;
			color: #fff;
			text-align: center;
		}

		li{ list-style: none; font-size: 14px; line-height: 30px;}

		.jilu{ height: 30px; width: 100%; overflow: hidden; background-color: #eee; }
		.jilu li{ height: 30px; line-height: 30px; text-align: center;}
		/*tabs-hd*/
		.tabs-hd{ height:50px; line-height:50px; background-color:#fff;}
		.tabs-hd ul{display:-webkit-box; display:-moz-box; }
		.tabs-hd li{ width:33.3%; -webkit-box-flex:1; -moz-box-flex:1; height:48px; line-height:48px; border-bottom:solid 2px #ccc; font-size:18px; text-align:center;}
		.tabs-hd li.current{ border-bottom-color:#f60; color:#f60;}
		/*gift-hd*/
		.gift-hd{ height:40px; line-height:40px; background-color:#f1f1f1; border-top:solid 1px #dedede; border-bottom:solid 1px #dedede;}
		.gift-hd ul{}
		.gift-hd li{ height:40px; line-height:40px; width:50%; float:left; text-align:center; color:#666;}
		.gift-hd li.current{ background-color:#fd7f06; color:#fff;}
		 /*tabs-bd*/
		.tabs-bd{ padding-top:10px; color:#333;}
		.cj-box{ padding-top:50px;}
		/*list-gift*/
		.list-gift{background-color: #FFF9B3;}
		.list-gift li{ padding:0 10px; height:40px; line-height:40px; border-bottom:solid 1px #9dc09e; text-align:center;}
		/*tips-no-data*/
		.tips-no-data{ padding:50px 20px; text-align:center; line-height:40px; font-size:22px; color:#666;}
		.gray{ color:#f60;}
		.cj-num{ padding:0 0 20px; text-align:center; color:#fff;}
		.cj-num .gray{ margin:0 3px;}
		.cj-num .icon-laba{ font-size:18px; margin-right:8px;}
		/*scroll-news*/
		.scroll-news{ padding:0 20px 0 50px; position:relative; height:50px; overflow:hidden; background:rgba(0,0,0,0.3);}
		.scroll-news li{ width:96%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; height:50px; line-height:50px; color:#fff; font-size:16px;}
		.scroll-news .icon-laba{ position:absolute; top:10px; left:20px; color:#fff; font-size:22px;}
	</style>
</head>
<body class="bg-green">
	<div class="tabs-hd">
    	<ul>
        	<li class="current">首页</li>
            <li>中奖查询</li>
            <li>活动说明</li>
        </ul>
    </div><!--end of tabs-hd -->
    <!--首页 begin-->
    <div class="tabs-bd cj-box">
    	<div id="banner">
			<div class="inner">
				<div id="zhen"></div>
			</div>
		</div>


		<div class="cj-num">
			<p >你还可抽奖的次数：<span class="num gray"><?php echo $awardNum;?></span></p>
		</div>

       
		<div class="scroll-news" id="scrollNews">
         <i class="iconfont icon-laba"></i>
        	<ul>

				<?php foreach($award as $l){  ?>
            	<li>恭喜<span><?php echo $l['user_name'] ?></span>抽中了<?php echo $l['title'].$l['class_name'] ?></li>
                <?php }?>
            </ul>
        </div>
        
        
    </div>
    <!--首页 end-->
    <!--中奖查询 begin-->
    <div class="tabs-bd" style="display:none;">
    	<div class="gift-hd">
        	<ul>
            	<li class="current">我的中奖</li>
                <li>中奖排行榜</li>
            </ul>
        </div>
        <!--我的中奖 -->
        <div class="gift-bd">
            <!--中奖纪录有数据时-->
            <ul class="list-gift">
				<?php 
					$temp = 0;
					foreach($my_award as $v){
						$temp = 1;
				?>
				<li><?php echo $v['title'];?>---<?php echo $v['class_name'];?></li>
				<?php }?>
            </ul>
        	<!--中奖纪录为空时 -->
        	<?php  if($temp == 0){  ?>
            <div class="tips-no-data">
           		<p>亲，你还没有中奖纪录，<br>下次继续努力哦~~</p>
             </div>
             <?php } ?>
        </div>
        <!--中奖排行榜 -->
        <div class="gift-bd" style="display:none;">
            <!--中奖纪录有数据时-->
            <ul class="list-gift">
				<?php 
					$tt = 0;
				foreach($award as $v){ 
					$tt = 1;
					?>
				<li><?php echo $v['user_name'];?>---<?php echo $v['class_name'];?></li>
				<?php }?>
            </ul>
        	<?php  if($temp == 0){  ?>
            <!--中奖排行榜为空时 -->
            <div class="tips-no-data">
           		<p>暂无中奖纪录~</p>
             </div>
             <?php } ?>
        </div>
    </div>
    <!--中奖查询 end-->
    <!--活动说明 begin-->
    <div class="tabs-bd" style="display:none;">
        <div class="block">
        	<div class="title">抽奖模式</div>
        	<p>只有VIP才有资格抽奖</p>
        	<p>每次每个VIP只有一次抽奖机会</p>
        	<p>每周二周五进行一次抽奖活动</p>
            <br>
			<div class="title">奖项设置</div>

			<ul>
				<?php foreach($actList as $v){?>
 				<li class="tpl-prize-item">
	                        <span class="prize-num tpl-prize-num"></span>
	                        <span class="prize-name tpl-prize-name"><?php echo $v['title'];?>---<?php echo $v['awardname'];?>积分</span>
	                        <span class="prize-number tpl-prize-number">奖品数量：<?php echo $v['num'] ? $v['num']: '不限';?></span>
	                    </li> 
								<?php }?>
							
            </ul>

            <!-- 
			<ul>
						<li class="tpl-prize-item">
	                        <span class="prize-num tpl-prize-num"></span>
	                        <span class="prize-name tpl-prize-name">一等奖---824积分</span>
	                        <span class="prize-number tpl-prize-number">奖品数量：1</span>
	                    </li>
												<li class="tpl-prize-item">
	                        <span class="prize-num tpl-prize-num"></span>
	                        <span class="prize-name tpl-prize-name">二等奖---275积分</span>
	                        <span class="prize-number tpl-prize-number">奖品数量：2</span>
	                    </li>
												<li class="tpl-prize-item">
	                        <span class="prize-num tpl-prize-num"></span>
	                        <span class="prize-name tpl-prize-name">三等奖---92积分</span>
	                        <span class="prize-number tpl-prize-number">奖品数量：3</span>
	                    </li>
												<li class="tpl-prize-item">
	                        <span class="prize-num tpl-prize-num"></span>
	                        <span class="prize-name tpl-prize-name">幸运奖---9积分</span>
	                        <span class="prize-number tpl-prize-number">奖品数量：30</span>
	                    </li>
												<li class="tpl-prize-item">
	                        <span class="prize-num tpl-prize-num"></span>
	                        <span class="prize-name tpl-prize-name">拆包奖---0积分</span>
	                        <span class="prize-number tpl-prize-number">奖品数量：不限</span>
	                    </li>
															
            </ul>
 -->



		</div>
    </div>
    <!--活动说明 end-->
	<div id="mask"></div>
	<div id="dialog" class="yes">
		<div id="content"></div>
		<a href="javascript:location.reload()">确定</a>
		<button id="close">关闭</button>
	</div>
	
</body>
</html>
<script src="dzp/jq.js"></script>
<script>
    $(function() {
        var data = [
            { type : 1 ,msg :'一等奖'},
            { type : 0 ,msg : '谢谢参与'},
            { type : 0 ,msg : ''},
            { type : 0 ,msg : '要加油哦'},
            { type : 1 ,msg : '三等奖'},
            { type : 0 ,msg : '运气不够'},
            { type : 0 ,msg : ''},
            { type : 0 ,msg : '再接再厉'},
            { type : 1 ,msg : '二等奖'},
            { type : 0 ,msg : '祝你好运'},
            { type : 0 ,msg : ''},
            { type : 0 ,msg : '不要灰心'}
        ]

        var tt = null;
        $("#zhen").click(function() {
            // 显示结果
            var $me = $(this);
            $.getJSON("act_prize.php?aid=3", function(json){
                if(json.msg == 2){
                    $("#content").html(json.prize);
                    $("#dialog").attr("class",'no').show();
                }else{
					var r = 1440 + 30*(json.r-1);
					var style ;
					style = '-webkit-transition-delay:1s;-webkit-transition: all 3s;transition: all 3s;-webkit-transform: rotate('+r+'deg);'+'-moz-transition-delay:1s;-moz-transition: all 3s;transition: all 3s;-moz-transform: rotate('+r+'deg);'+'transition-delay:1s;transition: all 3s;transition: all 3s;transform: rotate('+r+'deg);'+'filter:progid:DXImageTransform.Microsoft.BasicImage(rotation=3);';
					$me.attr('style',style);
					wxch_show(json);
					if(json.num >= 1){
						$(".num").text(json.num-1);
					}else{
						$(".num").text(json.num);
					}
                }
            });
        });

        function wxch_show (json) {
            var angle = 30*(json.r-1);
            setTimeout(function() {
                $("#mask").show();
                $("#zhen").attr('style','-webkit-transform: rotate('+angle+'deg);-moz-transform: rotate('+angle+'deg);transform: rotate('+angle+'deg);')
                if (json.msg == 1) {
                    $("#content").html(json.prize);
                    $("#dialog").attr("class",'yes').show();
                }else {
                    $("#content").html(json.prize);
                    $("#dialog").attr("class",'no').show();
                }
            }, 3000);
        }

        $("#mask").on('click',function() {
            $(this).hide();
            $("#dialog").hide();
        });

        $("#close").click(function() {
            $("#mask").trigger('click');
        });
    });
	
	//选项卡 
	$(".tabs-hd li").bind("click",function(){
		var _this =$(this);
		var index = _this.index();
		//index ==0 ? $("body").addClass("bg-green"):$("body").removeClass("bg-green")
		_this.addClass("current").siblings("li").removeClass("current");
		$(".tabs-bd").eq(index).fadeIn().siblings(".tabs-bd").hide();	
	});
	$(".gift-hd li").bind("click",function(){
		var _this =$(this);
		var index = _this.index();
		_this.addClass("current").siblings("li").removeClass("current");
		$(".gift-bd").eq(index).fadeIn().siblings(".gift-bd").hide();	
	});
	
	//滚动向上
	function scrollNotice(obj){
		$(obj).find("ul").animate({
			marginTop:'-40px'
		},500,function(){
		 $(this).css({marginTop:"0px"}).find("li:first").appendTo(this); 	
		})
	}
	setInterval("scrollNotice('#scrollNews')",2000);
	
</script>