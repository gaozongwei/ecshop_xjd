<?php if ($this->_var['full_page'] == 1): ?>
<!DOCTYPE HTML>
<html>
  <head>
    <?php echo $this->fetch('html_header.htm'); ?>
    <script>
      Zepto(function($)
      {
        $('#order_base').click(function()
        {
          $('#base_info_one').slideToggle();
        });
		 $('#consignee_base').click(function()
        {
          $('#consignee_info_2').slideToggle();
        });
      });
      
      function quick_delivery()
      {
        if($.trim($('#express_no').val()) == '')
        {
          $.zalert.add('请输入快递单号！',1);
          return false;
        }
        else
        {
          return true;
        }
      }
    </script>
  </head>
  <body>
    <div id='container'>
      <?php endif; ?>
      <?php echo $this->fetch('page_header.htm'); ?>
      <section style="padding-bottom:60px;">
        <div class="order_info_con">
          <div class="order_base">
            <p class="edit" id="order_base"><span><?php echo $this->_var['lang']['base_info']; ?></span> <i></i></p>
			<table width="100%" >
			  <tr>
				<td align="left" width='35%'>
				  <div ><?php echo $this->_var['lang']['delivery_sn_number']; ?></div>
				</td>
				<td align="left" width='65%'><?php echo $this->_var['delivery_order']['delivery_sn']; ?></td>
			  </tr>
			  <tr>
				<td align="left">
				  <div ><?php echo $this->_var['lang']['label_shipping_time']; ?></div>
				</td>
				<td align="left"><?php echo $this->_var['delivery_order']['formated_update_time']; ?></td>
			  </tr>
			  <tr>
				<td align="left">
				  <div><?php echo $this->_var['lang']['label_order_sn']; ?></div>
				</td>
				<td align="left"><?php echo $this->_var['delivery_order']['order_sn']; ?><?php if ($this->_var['delivery_order']['extension_code'] == "group_buy"): ?><a href="group_buy.php?act=edit&id=<?php echo $this->_var['delivery_order']['extension_id']; ?>"><?php echo $this->_var['lang']['group_buy']; ?></a><?php elseif ($this->_var['delivery_order']['extension_code'] == "exchange_goods"): ?><a href="exchange_goods.php?act=edit&id=<?php echo $this->_var['delivery_order']['extension_id']; ?>"><?php echo $this->_var['lang']['exchange_goods']; ?></a><?php endif; ?>
			  </tr>
			  <tr>
				<td align="left">
				  <div><?php echo $this->_var['lang']['label_order_time']; ?></div>
				</td>
				<td align="left"><?php echo $this->_var['delivery_order']['formated_add_time']; ?></td>
			  </tr>
			  </table>
			  <div style='display:none' id='base_info_one'>
			  <table width='100%'>
				<tr>
				  <td  align="left" width='35%'>
					<div><?php echo $this->_var['lang']['label_user_name']; ?></div>
				  </td>
				  <td  align="left" width='65%'><?php echo empty($this->_var['delivery_order']['user_name']) ? $this->_var['lang']['anonymous'] : $this->_var['delivery_order']['user_name']; ?></td>
				</tr>
				<tr>
				  <td align="left">
					<div><?php echo $this->_var['lang']['label_how_oos']; ?></div>
				  </td>
				  <td align="left"><?php echo $this->_var['delivery_order']['how_oos']; ?></td>
				</tr>
				<tr>
				  <td align="left">
					<div><?php echo $this->_var['lang']['label_shipping']; ?></div>
				  </td>
				  <td align="left"><?php if ($this->_var['exist_real_goods']): ?><?php if ($this->_var['delivery_order']['shipping_id'] > 0): ?><?php echo $this->_var['delivery_order']['shipping_name']; ?><?php else: ?><?php echo $this->_var['lang']['require_field']; ?><?php endif; ?> <?php if ($this->_var['delivery_order']['insure_fee'] > 0): ?>（<?php echo $this->_var['lang']['label_insure_fee']; ?><?php echo $this->_var['delivery_order']['formated_insure_fee']; ?>）<?php endif; ?><?php endif; ?></td>
				</tr>
				<tr>
				  <td align="left">
					<div><?php echo $this->_var['lang']['label_shipping_fee']; ?></div>
				  </td>
				  <td align="left"><?php echo $this->_var['delivery_order']['shipping_fee']; ?></td>
				</tr>
				<tr>
				  <td align="left">
					<div><?php echo $this->_var['lang']['label_insure_yn']; ?></div>
				  </td>
				  <td align="left"><?php if ($this->_var['insure_yn']): ?><?php echo $this->_var['lang']['yes']; ?><?php else: ?><?php echo $this->_var['lang']['no']; ?><?php endif; ?></td>
				</tr>
				<tr>
				  <td align="left">
					<div><?php echo $this->_var['lang']['label_insure_fee']; ?></div>
				  </td>
				  <td align="left"><?php echo empty($this->_var['delivery_order']['insure_fee']) ? '0.00' : $this->_var['delivery_order']['insure_fee']; ?></td>
				</tr>
				<tr>
				  <td align="left">
					<div><?php echo $this->_var['lang']['label_invoice_no']; ?></div>
				  </td>
				  <td align="left"><?php echo $this->_var['delivery_order']['invoice_no']; ?></td>
				</tr>
			</table>
			</div>
          </div>
          <div class="order_base">
		  <p class="edit" id="consignee_base"><span><?php echo $this->_var['lang']['consignee_info']; ?></span> <i></i></p>
            <div class="order_consign">
              <table width="100%" >
                <tr>
					<td  align="left" width='35%'><div><?php echo $this->_var['lang']['label_consignee']; ?></div></td>
					<td  align="left" width='65%'><?php echo htmlspecialchars($this->_var['delivery_order']['consignee']); ?></td>
				</tr>
				<tr>
					<td align="left" ><div><?php echo $this->_var['lang']['label_email']; ?></div></td>
					<td align="left" ><?php echo $this->_var['delivery_order']['email']; ?></td>
				</tr>
				<tr>
					<td align="left" ><div><?php echo $this->_var['lang']['label_address']; ?></div></td>
					<td align="left" >[<?php echo $this->_var['delivery_order']['region']; ?>] <?php echo htmlspecialchars($this->_var['delivery_order']['address']); ?></td>
				</tr>
				</table>
				<div style='display:none;' id='consignee_info_2'>
				<table width='100%'>
				<tr>
					<td align="left"  width='35%'><div><?php echo $this->_var['lang']['label_zipcode']; ?></div></td>
					<td align="left"  width='65%'><?php echo htmlspecialchars($this->_var['delivery_order']['zipcode']); ?></td>
				</tr>
				<tr>
					<td align="left" ><div><?php echo $this->_var['lang']['label_tel']; ?></div></td>
					<td align="left" ><?php echo $this->_var['delivery_order']['tel']; ?></td>
				</tr>
				<tr>
					<td align="left" ><div><?php echo $this->_var['lang']['label_mobile']; ?></div></td>
					<td align="left" ><?php echo htmlspecialchars($this->_var['delivery_order']['mobile']); ?></td>
				</tr>
				<tr>
					<td align="left" ><div><?php echo $this->_var['lang']['label_sign_building']; ?></div></td>
					<td align="left" ><?php echo htmlspecialchars($this->_var['delivery_order']['sign_building']); ?></td>
				</tr>
				<tr>
					<td align="left" ><div><?php echo $this->_var['lang']['label_best_time']; ?></div></td>
					<td align="left" ><?php echo htmlspecialchars($this->_var['delivery_order']['best_time']); ?></td>
				</tr>
				<tr>
					<td align="left" ><div><?php echo $this->_var['lang']['label_postscript']; ?></div></td>
					<td align="left" ><?php echo $this->_var['delivery_order']['postscript']; ?></td>
				</tr>
			  </table>
			  </div>
            </div>
          </div>
          <div class="goods_info">
            <p class="edit"><span><?php echo $this->_var['lang']['goods_info']; ?></span></p>
            <div class="order_goods">
              <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['name'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['name']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['name']['iteration']++;
?>
              <div class="order_goods_info" style="<?php if (($this->_foreach['name']['iteration'] <= 1)): ?>padding-top:0; border-top:0;<?php endif; ?>">
              	<div class="goods_name">
                	<strong><?php if ($this->_var['goods']['brand_name']): ?><i>[ <?php echo $this->_var['goods']['brand_name']; ?> ]</i><?php endif; ?><?php echo $this->_var['goods']['goods_name']; ?></strong>
                    <span><?php echo $this->_var['lang']['goods_sn']; ?>：<?php echo $this->_var['goods']['goods_sn']; ?></span>
                    <span><?php echo $this->_var['lang']['product_sn']; ?>：<?php echo $this->_var['goods']['product_sn']; ?></span>
                </div>
                <div class="goods_num">
                	<span><?php echo nl2br($this->_var['goods']['goods_attr']); ?></span>
                	<em>x<?php echo $this->_var['goods']['send_number']; ?></em>
                </div>
              </div>
              <?php endforeach; else: ?>
              <table width="100%" cellpadding="3" cellspacing="1">
			  	<tr>
                  <td align='center'><span>找不到任何商品！</span></td>
                </tr>
				</table>
              <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
              
            </div>
          </div>
        </div>
        <?php if ($this->_var['delivery_order']['status'] == 2): ?>
        <form method='POST' action='order.php?act=delivery' onsubmit='return quick_delivery();'>
          <div class="order_info_con">
            <p class="one_delivery">
              <input type="text" name="express_no" id='express_no' placeholder="请输入发货单号/快递号" class="inputBg"/>
              <input type='hidden' name='order_id' value='<?php echo $this->_var['delivery_order']['order_id']; ?>'/>
              <input type='hidden' name='delivery_id' value='<?php echo $this->_var['delivery_order']['delivery_id']; ?>'/>
              <input type="submit" value="发货" class="one_delivery_btn" />
            </p>
          </div>
        </form>
        <?php else: ?>
        <form method='POST' action='order.php?act=cancel_delivery'>
          <div class="order_info_con">
            <p class="cancel_delivery">
              <input type='hidden' name='delivery_id' value='<?php echo $this->_var['delivery_order']['delivery_id']; ?>'/>
              <input type='hidden' name='order_id' value='<?php echo $this->_var['delivery_order']['order_id']; ?>'/>
              <input type="submit" value="取消发货" class="one_delivery_btn" />
            </p>
          </div>
        </form>
        <?php endif; ?>
      </section>
      <?php echo $this->fetch('page_footer.htm'); ?>
      <?php if ($this->_var['full_page'] == 1): ?>
    </div>
    <?php echo $this->fetch('static_div.htm'); ?>
  </body>
</html>
<?php endif; ?>

