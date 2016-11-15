<ul class="order_tab" style="position:relative">
  <li id="order_manage1" class="first" onclick="toggle_menu();"><?php echo $this->_var['ur_here']; ?>
    <i id="menu_list_marker" class='on'></i>
    <ul class="order_type" style="display:none" id='menu_list'>
      <li <?php if ($this->_var['ur_here'] == '订单列表'): ?>class='curr'<?php endif; ?>><a href="order.php?act=list">订单列表</a></li>
      <li <?php if ($this->_var['ur_here'] == '发货单列表'): ?>class='curr'<?php endif; ?>><a href="order.php?act=delivery_list">发货单列表</a></li>
      <li <?php if ($this->_var['ur_here'] == '佣金列表'): ?>class='curr'<?php endif; ?>><a href="supplier_rebate.php?act=list">佣金列表</a></li>
      <li <?php if ($this->_var['ur_here'] == '库存列表'): ?>class='curr'<?php endif; ?>><a href="goods_stock.php?act=list">库存列表</a></li>
      <li <?php if ($this->_var['ur_here'] == '退款/退货/维修订单列表'): ?>class='curr'<?php endif; ?>><a href="back.php?act=back_list">退换货列表</a></li>
    </ul>
  </li>
  
  <li id="order_manage2" onclick="toggle_search();"><span class="line"></span>查询<i class='search'></i></li>
  <!--<li class="order_tab_li <?php if ($this->_var['filter']['is_pay_ok'] == 0): ?>curr<?php endif; ?>" id="type1" onclick="change_is_pay_ok('0')">本期结算</li>
  <li class="order_tab_li <?php if ($this->_var['filter']['is_pay_ok'] == 1): ?>curr<?php endif; ?>" id="type2" onclick="change_is_pay_ok('1')">往期结算</li>-->
  
</ul>