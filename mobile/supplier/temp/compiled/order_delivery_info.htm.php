<!DOCTYPE html>
<html>
  <head>
    <?php echo $this->fetch('html_header.htm'); ?>
  </head>
          <?php echo $this->fetch('page_header.htm'); ?>
    <form action="order.php?act=operate_post" method="post" name="theForm">
           <div class="order_base">
            <p class="edit" id="order_base"  onclick='$(".base_info_one").slideToggle()'><span><?php echo $this->_var['lang']['base_info']; ?></span> <i></i></p>
            <div class="base_info_one">
              <p><?php echo $this->_var['lang']['label_order_sn']; ?>：<?php echo $this->_var['order']['order_sn']; ?></p>
              <p><?php echo $this->_var['lang']['label_user_name']; ?>：<?php echo empty($this->_var['order']['user_name']) ? $this->_var['lang']['anonymous'] : $this->_var['order']['user_name']; ?> <a href="javascript:open_surplus();" class="">购货人信息</a><a href="user_msg.php?act=add&order_id=<?php echo $this->_var['order']['order_id']; ?>&user_id=<?php echo $this->_var['order']['user_id']; ?>"><?php echo $this->_var['lang']['send_message']; ?></a></p>
              <p><?php echo $this->_var['lang']['label_order_time']; ?>：<?php echo $this->_var['order']['formated_add_time']; ?></p>
              <p><?php echo $this->_var['lang']['label_how_oos']; ?>：<?php echo $this->_var['order']['how_oos']; ?></p>
              <p><?php echo $this->_var['lang']['label_shipping']; ?>：<?php if ($this->_var['exist_real_goods']): ?><?php if ($this->_var['order']['shipping_id'] > 0): ?><?php echo $this->_var['order']['shipping_name']; ?><?php else: ?><?php echo $this->_var['lang']['require_field']; ?><?php endif; ?> <?php if ($this->_var['order']['insure_fee'] > 0): ?>（<?php echo $this->_var['lang']['label_insure_fee']; ?><?php echo $this->_var['order']['formated_insure_fee']; ?>）<?php endif; ?><?php endif; ?></p>
            </div>
            <div class="base_info">
              <p><?php echo $this->_var['lang']['label_shipping_fee']; ?>：<?php echo $this->_var['lang']['os'][$this->_var['order']['order_status']]; ?>,<?php echo $this->_var['lang']['ps'][$this->_var['order']['pay_status']]; ?>,<?php echo $this->_var['lang']['ss'][$this->_var['order']['shipping_status']]; ?></p>
              <p><?php echo $this->_var['lang']['label_insure_yn']; ?>：<?php if ($this->_var['insure_yn']): ?><?php echo $this->_var['lang']['yes']; ?><?php else: ?><?php echo $this->_var['lang']['no']; ?><?php endif; ?></p>
              <p><?php echo $this->_var['lang']['label_insure_fee']; ?>：<?php echo empty($this->_var['order']['insure_fee']) ? '0.00' : $this->_var['order']['insure_fee']; ?></p>
            
            </div>
          </div>   
          <div class="consignee_info">
            <p class="edit"><span>收货人信息<a href="order.php?act=edit&order_id=<?php echo $this->_var['order']['order_id']; ?>&step=consignee" class="special" style="display:none">编辑</a></span></p>
            <div class="order_consign">
              <table width="100%" >
                <tr>
                  <td>收货人：<?php echo $this->_var['order']['consignee']; ?></td>
                  <td><a href="tel:<?php echo $this->_var['order']['mobile']; ?>" class="phone"><?php echo $this->_var['order']['mobile']; ?></a></td>
                </tr>
                <tr>
                  <td colspan="2">地址：<?php echo $this->_var['order']['country_name']; ?>&nbsp;<?php echo $this->_var['order']['city_name']; ?>&nbsp;<?php echo $this->_var['order']['district_name']; ?>&nbsp;<?php echo $this->_var['order']['address']; ?><a href='navigate.php?act=navigate&order_id=<?php echo $this->_var['order']['order_id']; ?>' target='_blank'><img src='images/location.png' style='height:15px;margin-left:10px'/></a>&nbsp;&nbsp;</td>
                </tr>
                <tr>
                  <td colspan="2">最佳送货时间：<?php echo $this->_var['order']['best_time']; ?></td>
                </tr>
              </table>
            </div>
          </div>
          <div class="goods_info">
            <p class="edit"><span><?php echo $this->_var['lang']['goods_info']; ?></span></p>
            <div class="order_goods">
<table width="100%" cellpadding="3" cellspacing="1">
  <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
    <!--礼包-->
    <?php if ($this->_var['goods']['goods_id'] > 0 && $this->_var['goods']['extension_code'] == 'package_buy'): ?>
      <tr>
          <td colspan="2"><?php echo $this->_var['goods']['goods_name']; ?><span style="color:#FF0000;"><?php echo $this->_var['lang']['remark_package']; ?></span></td>
      </tr>
      <tr>
        <td><?php echo $this->_var['lang']['goods_sn']; ?>：<?php echo $this->_var['goods']['goods_sn']; ?></td>
        <td><?php echo $this->_var['lang']['product_sn']; ?>：&nbsp;<!--货品货号--></td>
      </tr>
      <tr>
        <td><?php echo $this->_var['lang']['goods_attr']; ?>: &nbsp;<!--属性--></td> 
        <td><?php echo $this->_var['lang']['suppliers_name']; ?>:</td>
      </tr>
      <tr>
        <td><?php echo $this->_var['lang']['storage']; ?>: </td>
        <td><?php echo $this->_var['lang']['goods_number']; ?>: <?php echo $this->_var['goods']['goods_number']; ?></td>
      </tr>
      <tr>
        <td><?php echo $this->_var['lang']['goods_delivery']; ?>: </td>
        <td><?php echo $this->_var['lang']['goods_delivery_curr']; ?>:</td>
      </tr>
      <?php $_from = $this->_var['goods']['package_goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'package');if (count($_from)):
    foreach ($_from AS $this->_var['package']):
?>
      <tr>
          <td colspan="2"><?php echo $this->_var['package']['goods_name']; ?></a></td>
      </tr>
      <tr>
        <td><?php echo $this->_var['lang']['goods_sn']; ?>: <?php echo $this->_var['package']['goods_sn']; ?></td>
        <td><?php echo $this->_var['lang']['product_sn']; ?>：<?php echo $this->_var['package']['product_sn']; ?></td>
      </tr>
      <tr>
        <td><?php echo $this->_var['lang']['goods_attr']; ?>: <?php echo $this->_var['package']['goods_attr_str']; ?></td>
        
        <td><?php echo $this->_var['lang']['suppliers_name']; ?>:<?php if ($this->_var['suppliers_list'] != 0): ?> <?php echo empty($this->_var['suppliers_name'][$this->_var['package']['suppliers_id']]) ? $this->_var['lang']['restaurant'] : $this->_var['suppliers_name'][$this->_var['package']['suppliers_id']]; ?><?php endif; ?></td>
       
      </tr>
      <tr>
        <td><?php echo $this->_var['lang']['storage']; ?>: <?php echo $this->_var['package']['storage']; ?></td>
        <td><?php echo $this->_var['lang']['goods_number']; ?>: <?php echo $this->_var['package']['order_send_number']; ?></td>
      </tr>
      <tr>
        <td><?php echo $this->_var['lang']['goods_delivery']; ?>: <?php echo $this->_var['package']['sended']; ?></td>
        <td><?php echo $this->_var['lang']['goods_delivery_curr']; ?>: <input name="send_number[<?php echo $this->_var['goods']['rec_id']; ?>][<?php echo $this->_var['package']['g_p']; ?>]" type="text" id="send_number_<?php echo $this->_var['goods']['rec_id']; ?>_<?php echo $this->_var['package']['g_p']; ?>" value="<?php echo $this->_var['package']['send']; ?>" size="10" maxlength="11" <?php echo $this->_var['package']['readonly']; ?>/></td>
      </tr>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

    <?php else: ?>
    <tr>
        <td colspan="2">
      <?php if ($this->_var['goods']['goods_id'] > 0 && $this->_var['goods']['extension_code'] != 'package_buy'): ?>
      <?php echo $this->_var['goods']['goods_name']; ?> <?php if ($this->_var['goods']['brand_name']): ?>[ <?php echo $this->_var['goods']['brand_name']; ?> ]<?php endif; ?>
      <?php if ($this->_var['goods']['is_gift']): ?><?php if ($this->_var['goods']['goods_price'] > 0): ?><?php echo $this->_var['lang']['remark_favourable']; ?><?php else: ?><?php echo $this->_var['lang']['remark_gift']; ?><?php endif; ?><?php endif; ?>
      <?php if ($this->_var['goods']['parent_id'] > 0): ?><?php echo $this->_var['lang']['remark_fittings']; ?><?php endif; ?>
      <?php endif; ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->_var['lang']['goods_sn']; ?>: <?php echo $this->_var['goods']['goods_sn']; ?></td>
      <td><?php echo $this->_var['lang']['product_sn']; ?>：<?php echo $this->_var['goods']['product_sn']; ?></td>
    </tr>
    <tr>
      <td><?php echo $this->_var['lang']['goods_attr']; ?>: <?php echo nl2br($this->_var['goods']['goods_attr']); ?></td>
      
      <td><?php echo $this->_var['lang']['suppliers_name']; ?>: <?php if ($this->_var['suppliers_list'] != 0): ?><?php echo empty($this->_var['suppliers_name'][$this->_var['goods']['suppliers_id']]) ? $this->_var['lang']['restaurant'] : $this->_var['suppliers_name'][$this->_var['goods']['suppliers_id']]; ?><?php endif; ?></td>
      
    </tr>
    <tr>
      <td><?php echo $this->_var['lang']['storage']; ?>: <?php echo $this->_var['goods']['storage']; ?></td>
      <td><?php echo $this->_var['lang']['goods_number']; ?>: <?php echo $this->_var['goods']['goods_number']; ?></td>
    </tr>
    <tr>
      <td><?php echo $this->_var['lang']['goods_delivery']; ?>: <?php echo $this->_var['goods']['sended']; ?></td>
      <td><?php echo $this->_var['lang']['goods_delivery_curr']; ?>: <input name="send_number[<?php echo $this->_var['goods']['rec_id']; ?>]" type="text" id="send_number_<?php echo $this->_var['goods']['rec_id']; ?>" value="<?php echo $this->_var['goods']['send']; ?>" size="10" maxlength="11" <?php echo $this->_var['goods']['readonly']; ?>/></td>
    </tr>
    <?php endif; ?>
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
    </div>
</div>
  
 <div class="consignee_info">
    <p><span><?php echo $this->_var['lang']['label_action_note']; ?></span></p>
    <div>
         <textarea name="action_note" cols="40" rows="3"><?php echo $this->_var['action_note']; ?></textarea>
    </div>
    <div class="operate">
        <input name="delivery_confirmed" type="submit" value="<?php echo $this->_var['lang']['op_confirm']; ?><?php echo $this->_var['lang']['op_split']; ?>" class="button"/>
        <input type="button" value="<?php echo $this->_var['lang']['cancel']; ?>" class="button" onclick="location.href='order.php?act=info&order_id=<?php echo $this->_var['order_id']; ?>'" />
    </div>
 </div>
 <div style="height: 80px;"></div>
        <input name="order_id" type="hidden" value="<?php echo $this->_var['order']['order_id']; ?>">
        <input name="delivery[order_sn]" type="hidden" value="<?php echo $this->_var['order']['order_sn']; ?>">
        <input name="delivery[add_time]" type="hidden" value="<?php echo $this->_var['order']['order_time']; ?>">
        <input name="delivery[user_id]" type="hidden" value="<?php echo $this->_var['order']['user_id']; ?>">
        <input name="delivery[how_oos]" type="hidden" value="<?php echo $this->_var['order']['how_oos']; ?>">
        <input name="delivery[shipping_id]" type="hidden" value="<?php echo $this->_var['order']['shipping_id']; ?>">
        <input name="delivery[shipping_fee]" type="hidden" value="<?php echo $this->_var['order']['shipping_fee']; ?>">

        <input name="delivery[consignee]" type="hidden" value="<?php echo $this->_var['order']['consignee']; ?>">
        <input name="delivery[address]" type="hidden" value="<?php echo $this->_var['order']['address']; ?>">
        <input name="delivery[country]" type="hidden" value="<?php echo $this->_var['order']['country']; ?>">
        <input name="delivery[province]" type="hidden" value="<?php echo $this->_var['order']['province']; ?>">
        <input name="delivery[city]" type="hidden" value="<?php echo $this->_var['order']['city']; ?>">
        <input name="delivery[district]" type="hidden" value="<?php echo $this->_var['order']['district']; ?>">
        <input name="delivery[sign_building]" type="hidden" value="<?php echo $this->_var['order']['sign_building']; ?>">
        <input name="delivery[email]" type="hidden" value="<?php echo $this->_var['order']['email']; ?>">
        <input name="delivery[zipcode]" type="hidden" value="<?php echo $this->_var['order']['zipcode']; ?>">
        <input name="delivery[tel]" type="hidden" value="<?php echo $this->_var['order']['tel']; ?>">
        <input name="delivery[mobile]" type="hidden" value="<?php echo $this->_var['order']['mobile']; ?>">
        <input name="delivery[best_time]" type="hidden" value="<?php echo $this->_var['order']['best_time']; ?>">
        <input name="delivery[postscript]" type="hidden" value="<?php echo $this->_var['order']['postscript']; ?>">

        <input name="delivery[how_oos]" type="hidden" value="<?php echo $this->_var['order']['how_oos']; ?>">
        <input name="delivery[insure_fee]" type="hidden" value="<?php echo $this->_var['order']['insure_fee']; ?>">
        <input name="delivery[shipping_fee]" type="hidden" value="<?php echo $this->_var['order']['shipping_fee']; ?>">
        <input name="delivery[agency_id]" type="hidden" value="<?php echo $this->_var['order']['agency_id']; ?>">
        <input name="delivery[shipping_name]" type="hidden" value="<?php echo $this->_var['order']['shipping_name']; ?>">
        <input name="operation" type="hidden" value="<?php echo $this->_var['operation']; ?>">      
    </form>        
      <?php echo $this->fetch('page_footer.htm'); ?>
  </body>
</html>

<?php echo $this->smarty_insert_scripts(array('files'=>'topbar.js,../js/utils.js,listtable.js,selectzone.js,../js/common.js')); ?>


<script language="JavaScript">

  var oldAgencyId = <?php echo empty($this->_var['order']['agency_id']) ? '0' : $this->_var['order']['agency_id']; ?>;

  onload = function()
  {
    // 开始检查订单
    startCheckOrder();
  }

</script>


<?php echo $this->fetch('pagefooter.htm'); ?>