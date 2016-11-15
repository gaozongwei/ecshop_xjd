<?php if ($this->_var['full_page'] == 1): ?>
<!DOCTYPE HTML>
<html>
<head>
<?php echo $this->fetch('html_header.htm'); ?>
<script>
    var shipping_fee = new Number(<?php echo $this->_var['refund']['shipping_fee']; ?>);
    function change_shipping(is_shipping)
    {
    if (is_shipping == '1')
    {
      document.forms['theForm'].elements['refund_shipping_fee'].value = shipping_fee.toFixed(2);
    }
     else
    {
      document.forms['theForm'].elements['refund_shipping_fee'].value = '0.00';
    }

    }
    function check()
    {
      if ( document.forms['theForm'].elements['action_note'].value == '')
      {
        $.zalert.add('请输入操作备注！',1);
        return false;
      }
    if (document.forms['theForm'].elements['refund_money_1'].value == '')
    {
      $.zalert.add('请输入退款金额！',1);
      return false;
    }
      return true;
    }
    </script>
</head>
<body>
<div id='container'> <?php endif; ?>
  <?php echo $this->fetch('page_header.htm'); ?>
  <section>
    <form name="theForm" method="get" action="back.php" onsubmit="return check()">
      <div class="order_info_con">
      	<div class="list-div order_operate">
        <table>
          <tr>
            <td width="30%" valign="top"><span>操作备注</span></td>
            <td><textarea name="action_note" cols="40" rows="3"></textarea>
              <span class="require-field">*</span></td>
          </tr>
          <tr>
            <td valign="top"><span>退款金额</span></td>
            <td>
            	<p class="p_input">金额 <input type="text" name="refund_money_2"  value="<?php echo $this->_var['refund']['refund_money_1']; ?>" /></p>
              	<p class="p_input">运费 <input type="text" name="refund_shipping_fee" value="0.00"></p>
              	<p><input type="radio" name="refund_shipping" value="0" onclick="javascript:change_shipping(0);" checked=checked class="input_radio no-ml"/>不退运费</p>
              	<p><input type="radio" name="refund_shipping" value="1" onclick="javascript:change_shipping(1);" class="input_radio no-ml"/>退运费 </p>
              </td>
          </tr>
          <tr>
            <td valign="top"><span>退款方式</span></td>
            <td><p>
                <label>
                  <input type="radio" name="refund_type" value="1" class="input_radio no-ml"/>
                  退回用户余额</label>
                </p>
                <p>
                <label>
                  <input type="radio" name="refund_type" value="2" checked=checked class="input_radio no-ml"/>
                  线下退款</label>
                <br>
              </p></td>
          </tr>
          <tr>
            <td><span>退款说明</span></td>
            <td><textarea name="refund_desc" cols="60" rows="3" id="refund_desc"></textarea></td>
          </tr>
        </table>
      	</div>
        <p class="order_btn_p">
        	<input type="submit" name="submit" value=" 确定 " class="one_button" />
            <input type="hidden" name="back_id" value="<?php echo $this->_var['back_id']; ?>" />
            <input type="hidden" name="act" value="operate_refund" />
        </p>
      </div>
    </form>
  </section>
  <?php echo $this->fetch('page_footer.htm'); ?>
  <?php if ($this->_var['full_page'] == 1): ?> </div>
<?php echo $this->fetch('static_div.htm'); ?>
</body>
</html>
<?php endif; ?>