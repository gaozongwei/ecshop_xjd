<?php if ($this->_var['full_page'] == 1): ?>
<!DOCTYPE HTML>
<html>
<head>
<?php echo $this->fetch('html_header.htm'); ?>
<script lang='javascript' type='text/javascript'>
      Zepto(function($)
      {
        $('#hidebg').css('height',$('body').height());
      });
      
      function open_surplus(){
        
        $('#hidebg').fadeIn(200,function()
        {
         $('#popup_window').fadeIn(200);
        });
      }
      
      function close_surplus(){
         $('#popup_window').fadeOut(200,function()
        {
         $('#hidebg').fadeOut(200);
        });
      }
      
      function toggle_message()
      {
        $("#user_message_div").slideToggle();
      }
      
    </script>
</head>
<body>
<div id='container'>
<?php endif; ?>
      <?php echo $this->fetch('page_header.htm'); ?>
<form  action="back.php?act=operate" method="post">      
<section style="padding-bottom:60px;">
<?php if ($this->_var['back_order']['image_arr']): ?>
<div id="hidebg" onclick='close_surplus()'></div>
<div id="popup_window" style="position:fixed;display:none;"> <a class='close' onclick="close_surplus()"></a>
  <div class="buyer">
    <div class="info info_img"> <?php $_from = $this->_var['back_order']['image_arr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'img');if (count($_from)):
    foreach ($_from AS $this->_var['img']):
?> <img src="<?php echo $this->_var['img']; ?>" /> <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> </div>
  </div>
</div>
<?php endif; ?>
<div class="back_info_con">
<div class="order_pass">
  <p class="edit"><span><?php echo $this->_var['lang']['base_info']; ?></span></p>
  <div class="order_pass_info">
    <table width="100%" >
      <tr>
        <td width="18%"><div><?php echo $this->_var['lang']['label_order_sn']; ?></div></td>
        <td width="34%"><?php echo $this->_var['back_order']['order_sn']; ?></td>
      </tr>
      <tr>
        <td><div><?php echo $this->_var['lang']['label_order_time']; ?></div></td>
        <td><?php echo $this->_var['base_order']['add_time']; ?></td>
      </tr>
      <tr>
        <td width="18%"><div>服务类型</div></td>
        <td width="34%"> <?php if ($this->_var['back_order']['back_type'] == 1): ?>退货<?php endif; ?>
          <?php if ($this->_var['back_order']['back_type'] == 2): ?>换货<?php endif; ?>
          <?php if ($this->_var['back_order']['back_type'] == 3): ?>维修<?php endif; ?>
          <?php if ($this->_var['back_order']['back_type'] == 4): ?>退款（无需退货）<?php endif; ?> </td>
      </tr>
      <tr> 
        <td><div>退款方式</div></td>
        <td> <?php if ($this->_var['back_order']['back_pay'] == 1): ?>退款至账户余额<?php endif; ?>
          <?php if ($this->_var['back_order']['back_pay'] == 2): ?>原支付方式返回<?php endif; ?> </td>
      </tr>
      <tr>
        <td><div><?php echo $this->_var['lang']['label_user_name']; ?></div></td>
        <td><?php echo empty($this->_var['back_order']['user_name']) ? $this->_var['lang']['anonymous'] : $this->_var['back_order']['user_name']; ?></td>
      </tr>
      <tr> 
        <td><div><?php echo $this->_var['lang']['label_how_oos']; ?></div></td>
        <td><?php echo $this->_var['base_order']['how_oos']; ?></td>
      </tr>
      <tr>
        <td><div><?php echo $this->_var['lang']['label_shipping']; ?></div></td>
        <td><?php if ($this->_var['base_order']['shipping_id'] > 0): ?><?php echo $this->_var['base_order']['shipping_name']; ?><?php endif; ?> </td>
      </tr>
      <tr> 
        <td><div><?php echo $this->_var['lang']['label_shipping_fee']; ?></div></td>
        <td><?php echo $this->_var['base_order']['shipping_fee']; ?></td>
      </tr>
      <tr>
        <td><div><?php echo $this->_var['lang']['label_insure_yn']; ?></div></td>
        <td><?php if ($this->_var['base_order']['insure_yn']): ?><?php echo $this->_var['lang']['yes']; ?><?php else: ?><?php echo $this->_var['lang']['no']; ?><?php endif; ?></td>
      </tr>
      <tr> 
        <td><div ><?php echo $this->_var['lang']['label_insure_fee']; ?></div></td>
        <td><?php echo empty($this->_var['base_order']['insure_fee']) ? '0.00' : $this->_var['base_order']['insure_fee']; ?></td>
      </tr>
      <tr>
        <td><div ><?php echo $this->_var['lang']['label_invoice_no']; ?></div></td>
        <td ><?php echo $this->_var['base_order']['invoice_no']; ?></td>
      </tr>
      <tr> 
        <td><div ><?php echo $this->_var['lang']['label_shipping_time']; ?></div></td>
        <td><?php echo $this->_var['base_order']['shipping_time']; ?></td>
      </tr>
    </table>
  </div>
</div>
<div class="order_pass">
  <p class="edit"><span><?php echo $this->_var['lang']['back_info']; ?></span></p>
  <div class="order_pass_info">
    <table width="100%" >
      <tr>
        <td><div>申请退货/维修时间</div></td>
        <td><?php echo $this->_var['back_order']['formated_add_time']; ?></td>
      </tr>
      <tr>
        <td><div >申请人用户名</div></td>
        <td><?php echo $this->_var['back_order']['user_name']; ?></td>
      </tr>
      <tr>
        <td><div>换回商品收件人</div></td>
        <td><?php echo htmlspecialchars($this->_var['back_order']['consignee']); ?></td>
      </tr>
      <tr>
        <td><div>联系电话</div></td>
        <td><?php echo $this->_var['base_order']['tel']; ?></td>
      </tr>
      <tr>
        <td><div>换回商品收货人地址</div></td>
        <td ><?php echo htmlspecialchars($this->_var['back_order']['address']); ?></td>
      </tr>
      <tr>
        <td><div>邮编</div></td>
        <td><?php echo htmlspecialchars($this->_var['back_order']['zipcode']); ?></td>
      </tr>
      <tr>
        <td><div>退货/维修原因</div></td>
        <td colspan=3><?php echo $this->_var['back_order']['back_reason']; ?></td>
      </tr>
      <tr>
        <td><div>用户退回商品所用快递</div></td>
        <td><?php echo htmlspecialchars($this->_var['back_order']['shipping_name']); ?></td>
      </tr>
      <tr>
        <td><div>运单号</div></td>
        <td><?php echo $this->_var['back_order']['invoice_no']; ?></td>
      </tr>
      <?php if ($this->_var['back_order']['image_arr']): ?>
      <tr>
        <td><div>图片</div></td>
        <td><a href="javascript:open_surplus();" style='color : #FFC486;'>查看图片</a></td>
      </tr>
      <?php endif; ?>
    </table>
  </div>
</div>
<?php if ($this->_var['back_order']['postscript'] || $this->_var['back_order']['back_reply']): ?>
<div class="order_qita" onclick='toggle_message();'>
  <p class="edit" id="order_qita"><span>客户留言</span><i></i></p>
  <div class="qita_info qita_info_t" id='user_message_div'>
    <table width="100%" >
      <?php if ($this->_var['back_order']['postscript']): ?>
      <tr>
        <td width='10%' align='left'>用户：</td>
        <td width='65%' align='left'><?php echo $this->_var['back_order']['postscript']; ?>[<?php echo $this->_var['back_order']['formated_add_time']; ?>]</td>
      </tr>
      <?php endif; ?>
      <?php $_from = $this->_var['back_order']['back_reply']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'reply');if (count($_from)):
    foreach ($_from AS $this->_var['reply']):
?>
      <tr> <?php if ($this->_var['reply']['type'] == 1): ?>
        <td width='10%' align='left'>用户：</td>
        <?php else: ?>
        <td width='10%' align='left'>客服：</td>
        <?php endif; ?>
        <td width='65%' align='left'><?php echo $this->_var['reply']['message']; ?>[<?php echo $this->_var['reply']['add_time']; ?>]</td>
      </tr>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </table>
  </div>
</div>
<?php endif; ?>
<div class="order_pass">
  <p class="edit"><span>原订单-商品信息</span></p>
  <div class="order_pass_info">
    <div class="order_pass_info">
        <div class="order_goods_info" style="<?php if (($this->_foreach['name']['iteration'] <= 1)): ?>padding-top:0; border-top:0;<?php endif; ?>">
            <div class="goods_name">
                <strong><?php echo $this->_var['order_goods']['goods_name']; ?></strong>
                <span><?php echo $this->_var['lang']['goods_sn']; ?>：<?php echo $this->_var['order_goods']['goods_id']; ?></span>
                <span><?php echo $this->_var['lang']['product_sn']; ?>：<?php echo $this->_var['order_goods']['product_id']; ?></span>
            </div>
            <div class="goods_num">
                <span><?php echo nl2br($this->_var['order_goods']['goods_attr']); ?></span>
                <em>x<?php echo $this->_var['order_goods']['send_number']; ?></em>
            </div>
        </div>
	</div>
  </div>
</div>
<div class="order_pass">
  <p class="edit"><span>退货/返修 - 商品信息</span></p>
  <div class="order_pass_info">
      <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['name'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['name']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['name']['iteration']++;
?>
      <div class="order_goods_info" style="border-top:1px solid #eee;<?php if (($this->_foreach['name']['iteration'] <= 1)): ?>padding-top:0; border-top:0;<?php endif; ?>">
        <div class="goods_name">
            <strong><?php if ($this->_var['goods']['goods_id'] > 0 && $this->_var['goods']['extension_code'] != 'package_buy'): ?><?php if ($this->_var['goods']['brand_name']): ?><i>[ <?php echo $this->_var['goods']['brand_name']; ?> ]</i><?php endif; ?><?php echo $this->_var['goods']['goods_name']; ?><?php endif; ?></strong>
            <span><?php echo $this->_var['lang']['goods_sn']; ?>：<?php echo $this->_var['goods']['goods_id']; ?></span>
            <span><?php echo $this->_var['lang']['product_sn']; ?>：<?php echo $this->_var['goods']['product_id']; ?></span>
        </div>
        <div class="goods_num">
            <span><?php echo nl2br($this->_var['goods']['goods_attr']); ?></span>
            <em>x<?php echo $this->_var['goods']['back_goods_number']; ?></em>
        </div>
        <div style="clear:both;"></div>
        <div class="order_goods_bottom"><span class="fl">应退金额：<i><?php echo $this->_var['goods']['back_goods_money']; ?></i></span><?php if ($this->_var['goods']['back_type_name']): ?><span class="fr">业务：<?php echo $this->_var['goods']['back_type_name']; ?></span><?php endif; ?></div>
	  </div>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
  </div>  
</div>
<div class="order_pass">
    <p class="edit"><span>操作备注</span></p>
    <textarea name="action_note" style="width:100%;"></textarea>
</div>
<div class="operate">
<table width="100%" >
  <tr>
   <?php if ($this->_var['back_order']['status_back'] < 6): ?>
   <?php if ($this->_var['back_order']['status_back'] == 5): ?>
   <?php if ($this->_var['operable_list']['ok']): ?>
    <input name="ok" type="submit" value="通过申请" class="button" />
    <?php endif; ?>
    <?php if ($this->_var['operable_list']['no']): ?>
    <input name="no" type="submit" value="拒绝申请" class="button" />
    <?php endif; ?>
    <?php else: ?>
    <td> <?php if ($this->_var['operable_list']['confirm'] && $this->_var['back_order']['back_type'] != 4 && $this->_var['back_order']['status_back'] == 0 && ( $this->_var['back_order']['status_refund'] == 0 || $this->_var['back_order']['status_refund'] == 9 )): ?>
      <input name="confirm" type="submit" value="收到用户寄回商品" class="button" />
      <?php endif; ?> 
      <?php if (( $this->_var['back_order']['back_type'] == 4 || ( $this->_var['back_order']['back_type'] == 1 && ( $this->_var['back_order']['status_back'] == 1 || $this->_var['back_order']['status_back'] == 2 ) ) ) && ( $this->_var['back_order']['status_refund'] == 0 || $this->_var['back_order']['status_refund'] == 9 )): ?>
      <input name="refund" type="submit" value="去退款" class="button" />
      <?php endif; ?>
        <?php if ($this->_var['operable_list']['backshipping'] && $this->_var['back_order']['back_type'] == 3 && $this->_var['back_order']['status_back'] == 1): ?>
        <input name="backshipping" type="submit" class="button" value="换出商品寄出" />
        <?php endif; ?>
      <?php if ($this->_var['operable_list']['backfinish'] && ( $this->_var['back_order']['status_refund'] == 1 || $this->_var['back_order']['status_back'] == 2 )): ?>
      <input name="backfinish" type="submit" value="完成退换货" class="button" />
      <?php endif; ?>
      <?php endif; ?>
      <input name="after_service" type="submit" value="<?php echo $this->_var['lang']['op_after_service']; ?>" class="button" />
      <input name="back_id" type="hidden" value="<?php echo $_REQUEST['back_id']; ?>"  class='button'></td>
    	<?php else: ?>
		<?php if ($this->_var['back_order']['status_back'] == 6): ?>
		此单已被管理员拒绝
		<?php endif; ?>
		<?php if ($this->_var['back_order']['status_back'] == 7): ?>
		此单已被系统取消
		<?php endif; ?>
		<?php if ($this->_var['back_order']['status_back'] == 8): ?>
		此单已被用户自行取消
		<?php endif; ?>
	<?php endif; ?>
  </tr>
</table>

<script>
//$(function(){
//	$('.operate input').first().css('margin',0)
//})
</script>
</div>
</div>
</section>
</form>
<?php echo $this->fetch('page_footer.htm'); ?>
      <?php if ($this->_var['full_page'] == 1): ?>
</div>
<?php echo $this->fetch('static_div.htm'); ?>
</body>
</html>
<?php endif; ?>