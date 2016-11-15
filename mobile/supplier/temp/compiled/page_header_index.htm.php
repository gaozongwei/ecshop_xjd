<header id="header" class='header'>
<div class="toplog">
    <dl>
    <dt><img src="<?php if ($this->_var['headimg']): ?><?php echo $this->_var['headimg']; ?><?php else: ?>images/logo1.png<?php endif; ?>"></dt>
    <dd><span><?php echo $this->_var['supplier_name']; ?>管理员</span></dd>
    </dl>
</div>
<div class="topnav">
    <ul>
    <li class="bain"><a href="javascript:void(0)" ><span><?php echo $this->_var['today']['money']; ?></span>今日销售额</a></li>
    <li class="bain"><a href="javascript:void(0)"><span><?php echo $this->_var['today']['order']; ?></span>今日订单数</a></li>
    <li style=" border:0"><a href="order.php?act=list&composite_status=101"><span><?php if ($this->_var['footer_order']['await_ship']): ?><?php echo $this->_var['footer_order']['await_ship']; ?><?php else: ?>0<?php endif; ?></span>待发货订单</a></li>
    </ul>
</div>
</header>