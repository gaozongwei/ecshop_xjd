<?php if ($this->_var['full_page'] == 1): ?>
<!DOCTYPE HTML>
<html>
  <head>
    <?php echo $this->fetch('html_header.htm'); ?>
	<script src='js/touch.js'></script>
    <script>
	(function($)
	{
		Zepto(function($)
        {
			init();
			$.zcontent.add_success(init);
        });
	    function init()
	    {
			$('#rebate_paytime_start').intimidatetime({format:'yyyy-MM-dd'});
			$('#rebate_paytime_end').intimidatetime({format:'yyyy-MM-dd'});
	    }
	})(Zepto)
     
      function search_commission()
      {
        if(check_form_empty('theForm'))
        {
          $.zalert.add('至少有一项输入不为空！',1)
        }
        else
        {
          $.zcontent.set('rebate_paytime_start',$('#rebate_paytime_start').val());
          $.zcontent.set('rebate_paytime_end',$('#rebate_paytime_end').val());
          search();
        }
        return false;
      }
      
      function change_is_pay_ok(is_pay_ok)
      {
        $.zcontent.set('is_pay_ok',is_pay_ok);
        search();
      }
	  
    </script>
  </head>
  <body>
    <div id='container'>
      <?php endif; ?>
      <?php echo $this->fetch('page_header.htm'); ?>
      <section>
        <?php echo $this->fetch('menu_list.htm'); ?>
        <div class="order_con" id="con_order_manage_2" style="display:none">
          <div class="order_pd">
            <div class="order order_t">
              <form name="theForm" method="" action="" class="order_search" onsubmit='return search_commission();'>
                <table width="100%" border="0">
                  <tr>
                    <td>
                      <input type="text" name="rebate_paytime_start" id='rebate_paytime_start' class="inputBg" placeholder="请选择开始时间" <?php if ($this->_var['filter']['rebate_paytime_start']): ?>value='<?php echo $this->_var['filter']['rebate_paytime_start']; ?>'<?php endif; ?>/>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <input type="text" name="rebate_paytime_end" id='rebate_paytime_end' class="inputBg" placeholder="请选择结束时间" <?php if ($this->_var['filter']['rebate_paytime_end']): ?>value='<?php echo $this->_var['filter']['rebate_paytime_end']; ?>'<?php endif; ?>/>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <input type="submit" name="" class="button2" value="查找"/>
                    </td>
                  </tr>
                </table>
              </form>
            </div>
          </div>
        </div>
        <div class="order_con" id="con_order_manage_1">
          <div class="order_pd"  id="con_type_1">
            <div class="order">
              <ul class="order_list">
                <?php $_from = $this->_var['supplier_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
              	<li>
                  <table cellpadding="0" cellspacing="0" class="order_manage_table" width="100%">
                    <tr>
                    	<td><span>账户变动时间</span><?php echo $this->_var['item']['add_time']; ?></td>
                    </tr>
                    <tr>
                    	<td><span>订单号</span><?php echo $this->_var['item']['order_sn']; ?></td>
                    </tr>
                    <tr>
                    	<td><span>订单金额</span><?php echo $this->_var['item']['all_money']; ?></td>
                    </tr>
                    <tr>
                    	<td><span>平台扣除佣金</span>-<?php echo $this->_var['item']['rebate_money']; ?></td>
                    </tr>
                    <tr>
                    	<td><span>商家实际收入金额</span>+<?php echo $this->_var['item']['result_money']; ?></td>
                    </tr>
                  	<tr>
                        <td><span>支付方式</span><?php echo $this->_var['item']['pay_name']; ?></td>
                    </tr>
                    <tr>
                    	<td><span>备注</span><?php echo $this->_var['item']['texts']; ?></td>
                    </tr>
                  </table>
                </li>

                <?php endforeach; else: ?>
                <li>
                  <table width="100%" cellpadding="3" cellspacing="1" >
                    <tr>
                      <td align="center" class="font2" width='100%'>找不到任何结算单！</td>
                    </tr>
                  </table>
                </li>
                <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
              </ul>
            </div>
            <?php echo $this->fetch('page.htm'); ?>
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