<?php if ($this->_var['full_page'] == 1): ?>
<!DOCTYPE HTML>
<html>
  <head>
    <?php echo $this->fetch('html_header.htm'); ?>
	<script src='js/touch.js'></script>
    <script>

      function search_order()
      {
        if(check_form_empty('theForm'))
        {
          $.zalert.add('至少有一项输入不为空！',1)
        }
        else
        {
          $.zcontent.set('order_sn',$('#order_sn').val());
          $.zcontent.set('user_name',$('#user_name').val());
		 
          search();
        }
        return false;
      }
    </script>
  </head>
  <body>
    <div id='container'>
      <?php endif; ?>
      <?php echo $this->fetch('page_header.htm'); ?>
      <section>
      	<?php echo $this->fetch('menu_list.htm'); ?>
        <?php echo $this->fetch('order_menu_list.htm'); ?>
        <div class="order_con" id="con_order_manage_2" style="display:none">
          <div class="query_stock">
            <div class='order_search'>
              <form name="theForm" class="order_search" onsubmit='return search_order();'>
                <table width="100%" border="0">
                  <tr>
                    <td>
                      <input type="text" name="order_sn" id='order_sn' class="inputBg" placeholder="请输入订单号" <?php if ($this->_var['filter']['order_sn']): ?>value='<?php echo $this->_var['filter']['order_sn']; ?>'<?php endif; ?>/>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <input type="text" name="user_name" id='user_name' class="inputBg" placeholder="请输入买家姓名"  <?php if ($this->_var['filter']['user_name']): ?>value='<?php echo $this->_var['filter']['user_name']; ?>'<?php endif; ?>/>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <input type="submit" class="button2" value="查找"/>
                    </td>
                  </tr>
                </table>
              </form>
            </div>
          </div>
        </div>
        <div class="order_con" id="con_order_manage_1">
          <div class="order_pd"  id="con_type_1">
            <div class="order order_manage_list">
              <ul class="order_list">
                <?php $_from = $this->_var['orders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'order');$this->_foreach['name'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['name']['total'] > 0):
    foreach ($_from AS $this->_var['order']):
        $this->_foreach['name']['iteration']++;
?>
                <li <?php if (($this->_foreach['name']['iteration'] == $this->_foreach['name']['total'])): ?>style="margin-bottom:0;"<?php endif; ?>>
                	<div class="mt">
                    	<div class="mt_t">
                            <div class="user_name">
                                <span class="user_icon"></span>
                                <em><?php echo $this->_var['order']['buyer']; ?></em>
                            </div>
                            <span class="order_status"><?php echo $this->_var['lang']['os'][$this->_var['order']['order_status']]; ?>,<?php echo $this->_var['lang']['ps'][$this->_var['order']['pay_status']]; ?>,<?php echo $this->_var['lang']['ss'][$this->_var['order']['shipping_status']]; ?></span>
                        </div>
                        <div class="mt_b">
                        	<span class="order_time"><?php echo $this->_var['order']['short_order_time']; ?></span>
                            <span class="order_num">订单号：<a href="order.php?act=info&order_id=<?php echo $this->_var['order']['order_id']; ?>"><?php echo $this->_var['order']['order_sn']; ?></a></span>
                        </div>
                    </div>
                    <div class="mc">
                        <?php $_from = $this->_var['order']['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['goods']):
?>
                    	<div class="order_goods <?php if ($this->_var['k'] > 1): ?> order_goods2 hide <?php endif; ?>">
                        	<a href="order.php?act=info&order_id=<?php echo $this->_var['order']['order_id']; ?>">
                                <div class="goods_img">
                                    <img src="<?php echo $this->_var['goods']['goods_thumb']; ?>">
                                </div>
                                <div class="goods_name">
                                	<strong><?php echo sub_str($this->_var['goods']['goods_name'],20); ?></strong>
                                    <span><?php echo $this->_var['goods']['goods_attr']; ?></span>
                                </div>
                                <div class="goods_price"><?php echo $this->_var['goods']['goods_price']; ?><em>x<?php echo $this->_var['goods']['goods_number']; ?></em></div>
                            </a>
                        </div>
                        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                        <?php if ($this->_var['k'] > 1): ?><div class="show_goods_num">显示更多</div><?php endif; ?>
                    </div>
                    
                    <div class="order_fee">
                    	<span>金额：<?php echo $this->_var['order']['formated_total_fee']; ?></span>
                    </div>
                    <div class="m_b">
                    	<a href="navigate.php?act=navigate&order_id=<?php echo $this->_var['order']['order_id']; ?>"><img src="images/location.png" style="height:15px;"/>订单导航</a>
                        <a href="order.php?act=info&order_id=<?php echo $this->_var['order']['order_id']; ?>" class="font" >查看订单</a>
                    </div>
                </li>
                <?php endforeach; else: ?>
                <li>
                  <div class="no_order" style="">没有找到任何订单！</div>
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
    <script type="text/javascript">
    $(function(){
		check_more();	
	})
    </script>
  </body>
</html>
<?php endif; ?>

