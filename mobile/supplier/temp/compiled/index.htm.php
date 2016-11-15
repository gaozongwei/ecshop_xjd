<?php if ($this->_var['full_page'] == 1): ?>
<!DOCTYPE HTML>
<html>
  <head>
    <?php echo $this->fetch('html_header.htm'); ?>
    <script></script>
  </head>
  <body>
    <div id='container' class='shouye'>
      <?php endif; ?>
      <?php echo $this->fetch('page_header_index.htm'); ?>
	<!--我的订单-->
	<div class="Order">
    	<dl><a href="order.php"><dt><strong>全部订单</strong><span>查看全部订单</span></dt></a></dl>
		<ul>
			<li><a href="order.php?act=list&composite_status=100"><em class="ordem2"><i><?php if ($this->_var['order']['await_pay']): ?><?php echo $this->_var['order']['await_pay']; ?><?php else: ?>0<?php endif; ?></i></em><span>待付款</span></a></li>
			<li><a href="order.php?act=list&composite_status=101"><em class="ordem3"><i><?php if ($this->_var['footer_order']['await_ship']): ?><?php echo $this->_var['footer_order']['await_ship']; ?><?php else: ?>0<?php endif; ?></i></em><span>待发货</span></a></li>
			<li><a href="order.php?act=list&composite_status=105"><em class="ordem1"><i><?php if ($this->_var['orde']['await_receipt']): ?><?php echo $this->_var['orde']['await_receipt']; ?><?php else: ?>0<?php endif; ?></i></em><span>待收货</span></a></li>
			<li><a href="order.php?act=list&composite_status=102"><em class="ordem4"><i><?php if ($this->_var['order']['finished']): ?><?php echo $this->_var['order']['finished']; ?><?php else: ?>0<?php endif; ?></i></em><span>已完成</span></a></li>
		</ul>
	</div>
      <section>
      	<ul class="border_b">
        	<li class="border_r">
            	<a href="supplier_rebate.php?act=list&is_pay_ok=0">
                	<span><img alt="" src="images/n1.png"></span>
                    <p>佣金管理</p>
                </a>
            </li>
            <li class="border_r">
            	<a href="order.php?act=list">
                	<span><img alt="" src="images/n2.png"></span>
                    <p>订单列表</p>
                </a>
            </li>
            <li>
            	<a href="goods_stock.php?act=list">
                	<span><img alt="" src="images/n3.png"></span>
                    <p>库存列表</p>
                </a>
            </li>
        </ul>
        <ul class="border_b">
        	<li class="border_r">
            	<a href="order.php?act=delivery_list">
                	<span><img alt="" src="images/n4.png"></span>
                    <p>发货单</p>
                </a>
            </li>
            <li class="border_r">
            	<a href="back.php?act=back_list&order_type=2&back_type=<?php echo $this->_var['back_type_money']; ?>">
                	<span><img alt="" src="images/n5.png"></span>
                    <p>新退款</p>
                </a>
            </li>
            <li>
            	<a href="back.php?act=back_list&order_type=2&back_type=<?php echo $this->_var['back_type_goods']; ?>">
                	<span><img alt="" src="images/n6.png"></span>
                    <p>新退货</p>
                </a>
            </li>
        </ul>
      </section>
      <?php echo $this->fetch('page_footer.htm'); ?>
      <?php if ($this->_var['full_page'] == 1): ?>
    </div>
  </body>
</html>
<?php endif; ?>