<?php if ($this->_var['full_page'] == 1): ?>
<!DOCTYPE html>
<html>
  <head>
    <?php echo $this->fetch('html_header.htm'); ?>
    <script type="text/javascript">
      function search_stock()
       {
         if(check_form_empty('theForm'))
         {
           $.zalert.add('至少有一项输入不为空！',1)
         }
         else
         {
           $.zcontent.set('goods_name',$('#goods_name').val());
           $.zcontent.set('goods_sn',$('#goods_sn').val());
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
        <div class="order_con" id="con_order_manage_2" style="display:none">
          <div class="order_pd">
            <div class="order order_t">
              <form name="theForm" method="" action="" class="order_search" onsubmit='return search_stock();return false;'>
                <table width="100%" border="0">
                  <tr>
                    <td>
                      <input type="text" name="goods_name" id='goods_name' class="inputBg" placeholder="请输入商品名称" <?php if ($this->_var['filter']['goods_name']): ?>value='<?php echo $this->_var['filter']['goods_name']; ?>'<?php endif; ?>/>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <input type="text" name="goods_sn" id='goods_sn' class="inputBg" placeholder="请输入商品货号"  <?php if ($this->_var['filter']['goods_sn']): ?>value='<?php echo $this->_var['filter']['goods_sn']; ?>'<?php endif; ?>/>
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
        <div class="order_pd goods_pd"  id="con_order_manage_1">
		<?php echo $this->fetch('store_menu.htm'); ?>
          <div class="goods">
            <ul class="goods_list">
              <?php $_from = $this->_var['goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
              <li class="goods_list_info" <?php if ($this->_var['item']['goods_attr']): ?>style="padding-bottom:0;"<?php endif; ?>>
                <p><?php echo $this->_var['item']['goods_name']; ?></p>
                <p>货号：<?php echo $this->_var['item']['goods_sn']; ?></p>
                 <?php if ($this->_var['item']['goods_attr']): ?>
		<p>属性：<?php echo $this->_var['item']['goods_attr_name']; ?></p>
               
                <p class="special_info">
                <ul>
                  <?php $_from = $this->_var['item']['goods_attr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'attr_item');if (count($_from)):
    foreach ($_from AS $this->_var['attr_item']):
?>
                  <li><?php echo $this->_var['attr_item']['goods_attr_name']; ?>&nbsp;&nbsp;<span class="font">(<?php echo $this->_var['attr_item']['product_number']; ?>)</span> </li>
                  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
                </p>
                <?php else: ?>
                <p>库存：<span class="font"><?php echo $this->_var['item']['goods_number']; ?></span></p>
                <?php endif; ?>
              </li>
              <?php endforeach; else: ?>
              <li class="no_goods_list">
                <div class="no_goods">没有找到任何商品！</div>
              </li>
              <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
          </div>
          <?php echo $this->fetch('page.htm'); ?>
        </div>
      </section>
      <?php echo $this->fetch('page_footer.htm'); ?>
      <?php if ($this->_var['full_page'] == 1): ?>
    </div>
    <?php echo $this->fetch('static_div.htm'); ?>
  </body>
</html>
<?php endif; ?>

