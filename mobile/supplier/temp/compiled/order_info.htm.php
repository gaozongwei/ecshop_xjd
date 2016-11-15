<?php if ($this->_var['full_page'] == 1): ?>
<!DOCTYPE html>
<html>
  <head>
    <?php echo $this->fetch('html_header.htm'); ?>
    <script type="text/javascript">
        function open_surplus()
        {
           $('#hidebg').css('height',$(document).height());
           $('#hidebg').css('display','block');
           $('#popup_window').css('display','block');
        }
        
        function close_surplus()
        {
           $('#hidebg').css('display','none');
           $('#popup_window').css('display','none');
        }
      
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
      <!-- 显示购货人信息 start -->
      <div id="hidebg" onclick='close_surplus()'></div>
      <div id="popup_window" style="position:fixed;display:none;">
        <a class='close' onclick="close_surplus()"></a>
        <div class="buyer">
          <h4>购货人信息</h4>
          <div class="info">
            <table width="100%" >
              <tr>
                <td width="30%">电子邮件：</td>
                <td><a href='mailto:<?php echo $this->_var['user']['email']; ?>' class='phone'><?php echo $this->_var['user']['email']; ?></a></td>
              </tr>
              <tr>
                <td>账户余额：</td>
                <td><?php echo $this->_var['user']['user_money']; ?></td>
              </tr>
              <tr>
                <td>消费积分：</td>
                <td><?php echo $this->_var['user']['pay_points']; ?></td>
              </tr>
              <tr>
                <td>等级积分：</td>
                <td><?php echo $this->_var['user']['rank_points']; ?></td>
              </tr>
              <tr>
                <td>会员等级：</td>
                <td><?php echo $this->_var['user']['rank_name']; ?></td>
              </tr>
              <tr>
                <td>红包数量：</td>
                <td><?php echo $this->_var['user']['bonus_count']; ?></td>
              </tr>
            </table>
          </div>
          <div class="info">
          <?php $_from = $this->_var['address_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'address');if (count($_from)):
    foreach ($_from AS $this->_var['address']):
?>
  <table width="100%" border="0">
    <caption><strong> <?php echo $this->_var['lang']['consignee']; ?> : <?php echo htmlspecialchars($this->_var['address']['consignee']); ?> </strong></caption>
    <tr>
      <td> <?php echo $this->_var['lang']['email']; ?> </td>
      <td> <a href="mailto:<?php echo htmlspecialchars($this->_var['address']['email']); ?>"><?php echo htmlspecialchars($this->_var['address']['email']); ?></a> </td>
    </tr>
    <tr>
      <td> <?php echo $this->_var['lang']['address']; ?> </td>
      <td> <?php echo htmlspecialchars($this->_var['address']['address']); ?> </td>
    </tr>
    <tr>
      <td> <?php echo $this->_var['lang']['zipcode']; ?> </td>
      <td> <?php echo htmlspecialchars($this->_var['address']['zipcode']); ?> </td>
    </tr>
    <tr>
      <td> <?php echo $this->_var['lang']['tel']; ?> </td>
      <td> <?php echo htmlspecialchars($this->_var['address']['tel']); ?> </td>
    </tr>
    <tr>
      <td> <?php echo $this->_var['lang']['mobile']; ?> </td>
      <td> <?php echo htmlspecialchars($this->_var['address']['mobile']); ?> </td>
    </tr>
  </table>
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
          </div>
        </div>
      </div>
      <?php echo $this->fetch('page_header.htm'); ?>
      <section style="padding-bottom:60px;">
      	<div class="order_info_con">
         <div class="one_delivery_box">
         <?php if ($this->_var['order']['pay_status'] == 2 && $this->_var['order']['shipping_status'] != 1 && $this->_var['order']['order_status'] == 1 && $this->_var['order']['pay_code'] != "cod"): ?>
        <form method='POST' action='order.php' onsubmit='return quick_delivery();'>
            <p class="one_delivery">
              <input type="text" name="express_no" id='express_no' placeholder="请输入快递单号" class="inputBg"/>
              <input type='hidden' name='order_id' value='<?php echo $this->_var['order']['order_id']; ?>'/>
			  <input type='hidden' name='act' value='quick_delivery'/>
              <input type="submit" value="一键发货" class="one_delivery_btn" />
            </p>
        </form>
        <?php endif; ?>
        <form action="order.php?act=operate" method="post" name="theForm">
        <input type='hidden' name='order_id' value='<?php echo $this->_var['order']['order_id']; ?>'/>
        <!-- 货到付款的处理 -->
        <p class="one_delivery_more">
        <?php if ($this->_var['order']['order_amount'] < 0): ?><input name="refund" type="button" value="<?php echo $this->_var['lang']['refund']; ?>" class="one_delivery_btn_min" 
        onclick="location.href='order.php?act=process&func=load_refund&anonymous=<?php if ($this->_var['order']['user_id'] <= 0): ?>1<?php else: ?>0<?php endif; ?>&order_id=<?php echo $this->_var['order']['order_id']; ?>&refund_amount=<?php echo $this->_var['order']['money_refund']; ?>'" /><?php endif; ?>    
        <?php if ($this->_var['operable_list']['confirm']): ?>
        <input name="confirm" type="submit" value="<?php echo $this->_var['lang']['op_confirm']; ?>" class="one_delivery_btn_min" />
        <?php endif; ?> <?php if ($this->_var['operable_list']['pay'] && $this->_var['order']['pay_code'] == "cod"): ?>
        <input name="pay" type="submit" value="<?php echo $this->_var['lang']['op_pay']; ?>" class="one_delivery_btn_min" />
        <?php endif; ?> <?php if ($this->_var['operable_list']['unpay']): ?>
        <!--<input name="unpay" type="submit" value="<?php echo $this->_var['lang']['op_unpay']; ?>" class="one_delivery_btn_min" />-->
        <?php endif; ?> <?php if ($this->_var['operable_list']['prepare'] && ( $this->_var['is_pre_sale'] == 0 || $this->_var['pre_sale_success'] == 1 )): ?>
        <input name="prepare" type="submit" value="<?php echo $this->_var['lang']['op_prepare']; ?>" class="one_delivery_btn_min" />
        <?php endif; ?> <?php if ($this->_var['operable_list']['split'] && ( $this->_var['is_pre_sale'] == 0 || $this->_var['pre_sale_success'] == 1 )): ?>
        <input name="ship" type="submit" value="<?php echo $this->_var['lang']['op_split']; ?>" class="one_delivery_btn_min" />
        <?php endif; ?> <?php if ($this->_var['operable_list']['unship']): ?>
        <!--<input name="unship" type="submit" value="<?php echo $this->_var['lang']['op_unship']; ?>" class="one_delivery_btn_min" />-->
        <?php endif; ?> <?php if ($this->_var['operable_list']['receive']): ?>
        <!--<input name="receive" type="submit" value="<?php echo $this->_var['lang']['op_receive']; ?>" class="one_delivery_btn_min" />-->
        <?php endif; ?> <?php if ($this->_var['operable_list']['cancel']): ?>
        <input name="cancel" type="submit" value="<?php echo $this->_var['lang']['op_cancel']; ?>" class="one_delivery_btn_min" />
        <?php endif; ?> <?php if ($this->_var['operable_list']['invalid']): ?>
        <input name="invalid" type="submit" value="<?php echo $this->_var['lang']['op_invalid']; ?>" class="one_delivery_btn_min" />
        <?php endif; ?> <?php if ($this->_var['operable_list']['return']): ?>
        <!--<input name="return" type="submit" value="<?php echo $this->_var['lang']['op_return']; ?>" class="button" />-->
        <?php endif; ?> <?php if ($this->_var['operable_list']['to_delivery']): ?>
        <input name="to_delivery" type="submit" value="<?php echo $this->_var['lang']['op_to_delivery']; ?>" class="one_delivery_btn_min"/>
        <input name="order_sn" type="hidden" value="<?php echo $this->_var['order']['order_sn']; ?>" />
        <?php endif; ?> <input name="after_service" type="submit" value="<?php echo $this->_var['lang']['op_after_service']; ?>" class="one_delivery_btn_min" /><?php if ($this->_var['operable_list']['remove']): ?>
        <input name="remove" type="submit" value="<?php echo $this->_var['lang']['remove']; ?>" class="one_delivery_btn_min" onClick="return window.confirm('<?php echo $this->_var['lang']['js_languages']['remove_confirm']; ?>');" />
        <?php endif; ?>
        <?php if ($this->_var['order']['extension_code'] == "group_buy"): ?><?php echo $this->_var['lang']['notice_gb_ship']; ?><?php endif; ?>
        <?php if ($this->_var['order']['extension_code'] == "pre_sale"): ?><?php echo $this->_var['lang']['notice_ps_ship']; ?><?php endif; ?>
        <?php if ($this->_var['agency_list']): ?>
        <input name="assign" type="submit" value="<?php echo $this->_var['lang']['op_assign']; ?>" class="one_delivery_btn_min" onclick="return assignTo(document.forms['theForm'].elements['agency_id'].value)" />
        <select name="agency_id"><option value="0"><?php echo $this->_var['lang']['select_please']; ?></option>
        <?php $_from = $this->_var['agency_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'agency');if (count($_from)):
    foreach ($_from AS $this->_var['agency']):
?>
        <option value="<?php echo $this->_var['agency']['agency_id']; ?>" <?php if ($this->_var['agency']['agency_id'] == $this->_var['order']['agency_id']): ?>selected<?php endif; ?>><?php echo $this->_var['agency']['agency_name']; ?></option>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </select>
        <?php endif; ?>
        </p>
        </form>
        </div>
        </div>
        
        <div class="order_info_con">
          <div class="order_fee">
            <p class="edit" id="order_fee" onclick='$(".fee_info").slideToggle()'><span>费用信息<a href="order.php?act=edit&order_id=<?php echo $this->_var['order']['order_id']; ?>&step=money" class="special">编辑</a></span> <i></i></p>
            <div class="fee_info">
              <p>商品总金额：<?php echo $this->_var['order']['formated_goods_amount']; ?> - 折扣：<?php echo $this->_var['order']['formated_discount']; ?> + 发票税额：<?php echo $this->_var['order']['formated_tax']; ?> + 配送费用：<?php echo $this->_var['order']['formated_shipping_fee']; ?> + 保价费用：<?php echo $this->_var['order']['formated_insure_fee']; ?> + 支付费用：<?php echo $this->_var['order']['formated_pay_fee']; ?> + 包装费用：<?php echo $this->_var['order']['formated_pack_fee']; ?> + 贺卡费用：<?php echo $this->_var['order']['formated_card_fee']; ?></p>
              <p>= 订单总金额：<?php echo $this->_var['order']['formated_total_fee']; ?></p>
              <p>- 已付款金额：<?php echo $this->_var['order']['formated_money_paid']; ?> - 使用余额： <?php echo $this->_var['order']['formated_surplus']; ?> - 使用积分： <?php echo $this->_var['order']['formated_integral_money']; ?> - 使用红包： <?php echo $this->_var['order']['formated_bonus']; ?></p>
            </div>
            <p class="order_amount">= <?php if ($this->_var['order']['order_amount'] >= 0): ?>应付款金额：<?php echo $this->_var['order']['formated_order_amount']; ?><?php else: ?>应退款金额：<?php echo $this->_var['order']['formated_money_refund']; ?><?php endif; ?></p>
          </div>
          <div class="consignee_info">
            <p class="edit"><span>收货人信息<a href="order.php?act=edit&order_id=<?php echo $this->_var['order']['order_id']; ?>&step=consignee" class="special">编辑</a></span></p>
            <div class="order_consign">
              <table width="100%" >
                <tr>
                  <td>收货人：<?php echo $this->_var['order']['consignee']; ?></td>
                  <td><a href="tel:<?php echo $this->_var['order']['mobile']; ?>" class="phone"><?php echo $this->_var['order']['mobile']; ?></a></td>
                </tr>
                <tr>
                  <td colspan="2">地址：<?php echo $this->_var['order']['country_name']; ?>&nbsp;<?php echo $this->_var['order']['city_name']; ?>&nbsp;<?php echo $this->_var['order']['district_name']; ?>&nbsp;<?php echo $this->_var['order']['address']; ?><a href='navigate.php?act=navigate&order_id=<?php echo $this->_var['order']['order_id']; ?>'><img src='images/location.png' style='height:20px;'/></a>&nbsp;&nbsp;</td>
                </tr>
                <tr>
                  <td colspan="2">最佳送货时间：<?php echo $this->_var['order']['best_time']; ?></td>
                </tr>
              </table>
            </div>
          </div>
          <div class="goods_info">
            <p class="edit"><span>商品信息</span></p>
            <div class="order_goods">
              <?php if ($this->_var['goods_list']): ?>
              <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['name'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['name']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['name']['iteration']++;
?>
              <table width="100%" <?php if (($this->_foreach['name']['iteration'] == $this->_foreach['name']['total'])): ?>style="border:0;"<?php endif; ?>>
                <tr>
                  <td colspan="3"><?php if ($this->_var['goods']['brand_name']): ?>[<?php echo $this->_var['goods']['brand_name']; ?>]<?php endif; ?><?php echo $this->_var['goods']['goods_name']; ?><span class="attr"><?php echo $this->_var['goods']['goods_attr']; ?></span></td>
                </tr>
                <tr>
                  <td><?php echo $this->_var['goods']['formated_goods_price']; ?> x <span><?php echo $this->_var['goods']['goods_number']; ?></span></td>
                  <td>库存：<?php echo $this->_var['goods']['storage']; ?></td>
                  <td>小计：<?php echo $this->_var['goods']['formated_subtotal']; ?></td>
                </tr>
              </table>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
              <?php else: ?>
              <table width="100%" >
                <tr>
                  <td colspan="3"><span class="attr">没有任何商品！</span></td>
                </tr>
              </table>
              <?php endif; ?>
            </div>
          </div>
          <div class="order_base">
            <p class="edit" id="order_base"  onclick='$(".base_info_one").slideToggle()'><span>基本信息</span> <i></i></p>
            <div class="base_info_one">
              <p>订单号：<?php echo $this->_var['order']['order_sn']; ?></p>
              <p>购货人：<?php echo $this->_var['order']['user_name']; ?> <a href="javascript:open_surplus();" class="">购货人信息</a><a href="user_msg.php?act=add&order_id=<?php echo $this->_var['order']['order_id']; ?>&user_id=<?php echo $this->_var['order']['user_id']; ?>"><?php echo $this->_var['lang']['send_message']; ?></a></p>
              <p>下单时间：<?php echo $this->_var['order']['formated_add_time']; ?></p>
              <p>付款时间：<?php if ($this->_var['order']['pay_time'] > 0): ?><?php echo $this->_var['order']['formatted_pay_time']; ?><?php else: ?>未支付<?php endif; ?></p>
              <p>发货时间：<?php if ($this->_var['order']['shipping_time'] > 0): ?><?php echo $this->_var['order']['formatted_shipping_time']; ?><?php else: ?>未收货<?php endif; ?></p>
            </div>
            <div class="base_info">
              <p>订单状态：<?php echo $this->_var['lang']['os'][$this->_var['order']['order_status']]; ?>,<?php echo $this->_var['lang']['ps'][$this->_var['order']['pay_status']]; ?>,<?php echo $this->_var['lang']['ss'][$this->_var['order']['shipping_status']]; ?></p>
              <p>支付方式：<?php echo $this->_var['order']['pay_name']; ?><a href="order.php?act=edit&order_id=<?php echo $this->_var['order']['order_id']; ?>&step=payment" class="special">编辑</a></p>
              <p>配送方式：<?php echo $this->_var['order']['shipping_name']; ?><a href="order.php?act=edit&order_id=<?php echo $this->_var['order']['order_id']; ?>&step=shipping" class="special">编辑</a></p>
              <!--如果配送方式是“门店自提”，则显示以下自提点 star-->
              <?php if ($this->_var['order']['is_pickup'] == 1 && $this->_var['order']['pickup_point'] > 0): ?>
              <p>所选自提点：[<?php echo $this->_var['order']['pickup_point_info']['province_name']; ?>&nbsp;<?php echo $this->_var['order']['pickup_point_info']['city_name']; ?>&nbsp;<?php echo $this->_var['order']['pickup_point_info']['district_name']; ?>&nbsp;<?php echo $this->_var['order']['pickup_point_info']['address']; ?>]<?php echo $this->_var['order']['pickup_point_info']['shop_name']; ?></p>
              <?php endif; ?>
              <!--如果配送方式是“门店自提”，则显示以下自提点 end-->
              <?php if ($this->_var['order']['invoice_no'] > 0): ?>
              <p>发货单号：<?php echo $this->_var['order']['invoice_no']; ?></p>
              <?php endif; ?>
            </div>
          </div>
          <div class="order_qita">
            <p class="edit" id="order_qita"  onclick='$(".qita_info").slideToggle()'>
              <span>其他信息
              <?php if ($this->_var['order']['inv_type']): ?>
              <a href="javascript:void(0)" class="special"><?php echo $this->_var['lang'][$this->_var['order']['inv_type']]; ?></a>
              <?php endif; ?>
              </span>
              <i></i>
            </p>
            <div class="qita_info">
              <!--{如果有普通发票 start*}-->
              <?php if ($this->_var['order']['inv_type']): ?>
              <p>发票类型：<?php echo $this->_var['lang'][$this->_var['order']['inv_type']]; ?></p>
              <?php endif; ?>
              <?php if ($this->_var['order']['inv_type'] == 'normal_invoice'): ?>
              <p>发票抬头：<?php echo $this->_var['order']['inv_payee']; ?></p>
              <p>发票内容：<?php echo $this->_var['order']['inv_content']; ?></p>
              <?php elseif ($this->_var['order']['inv_type'] == 'vat_invoice'): ?>
              <h4>公司信息：</h4>
              <p>单位名称：<?php echo $this->_var['order']['vat_inv_company_name']; ?></p>
              <p>纳税人识别号：<?php echo $this->_var['order']['vat_inv_taxpayer_id']; ?></p>
              <p>注册地址：<?php echo $this->_var['order']['vat_inv_registration_address']; ?></p>
              <p>注册电话：<?php echo $this->_var['order']['vat_inv_registration_phone']; ?></p>
              <p>开户银行：<?php echo $this->_var['order']['vat_inv_deposit_bank']; ?></p>
              <p>银行账户：<?php echo $this->_var['order']['vat_inv_bank_account']; ?></p>
              <h4>收票人信息：</h4>
              <p>收票人姓名：<?php echo $this->_var['order']['inv_consignee_name']; ?></p>
              <p>收票人手机：<?php echo $this->_var['order']['inv_consignee_phone']; ?></p>
              <p>收票人地址：[<?php echo $this->_var['order']['inv_consignee_province_name']; ?>&nbsp;<?php echo $this->_var['order']['inv_consignee_city_name']; ?>&nbsp;<?php echo $this->_var['order']['inv_consignee_district_name']; ?>]<?php echo $this->_var['order']['inv_consignee_address']; ?></p>
              <?php endif; ?>
              <!--{如果有发票 end}-->
              <?php if ($this->_var['order']['postscript']): ?>
              <p>客户给商家的留言：<?php echo $this->_var['order']['postscript']; ?></p>
              <?php endif; ?>
              <p>缺货处理：等待商家备齐后再发</p>
              <?php if ($this->_var['order']['to_buyer']): ?>
              <p>商家给客户的留言：<?php echo $this->_var['order']['to_buyer']; ?></p>
              <?php endif; ?>
            </div>
          </div>
            
            <div class="order_history">
            <p class="edit" id="order_history"  onclick='$(".history_info").slideToggle()'>
              <span>操作 </span>
              <i></i>
            </p>
            <div class="history_info">
              <?php $_from = $this->_var['action_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'action');$this->_foreach['name'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['name']['total'] > 0):
    foreach ($_from AS $this->_var['action']):
        $this->_foreach['name']['iteration']++;
?>
              <div class="history_info_list" <?php if (($this->_foreach['name']['iteration'] <= 1)): ?>style="border:0;"<?php endif; ?>>
              	<h5><span>操作者：<?php echo $this->_var['action']['action_user']; ?></span></h5>
                <ul>
                	<li><span>订单状态</span><em><?php echo $this->_var['action']['order_status']; ?></em></li>
                    <li><span>付款状态</span><em><?php echo $this->_var['action']['pay_status']; ?></em></li>
                    <li><span>发货状态</span><em><?php echo $this->_var['action']['shipping_status']; ?></em></li>
                </ul>
                <p>备注：<?php echo nl2br($this->_var['action']['action_note']); ?></p>
              </div>
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </div>
       </div>
      </section>
      <?php echo $this->fetch('page_footer.htm'); ?>
      <?php if ($this->_var['full_page'] == 1): ?>
    </div>
    <?php echo $this->fetch('static_div.htm'); ?>
  </body>
</html>
<?php endif; ?>