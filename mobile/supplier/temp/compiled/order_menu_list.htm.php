<script type="text/javascript" src="js/mobile.js"></script>
<script>
;(function($){
	Zepto(function($)
	{
		init_swipe();
		$.zcontent.add_success(init_swipe);
	})
	
	function init_swipe()
	{
		<?php $_from = $this->_var['status_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('scr_key', 'screen');if (count($_from)):
    foreach ($_from AS $this->_var['scr_key'] => $this->_var['screen']):
?>
		$('#status_list_<?php echo $this->_var['scr_key']; ?>').swipeLeft(function()
		{
			$('#status_list_<?php echo $this->_var['scr_key']; ?>').slideLeftOut(200,function(){$('#status_list_<?php if ($this->_var['scr_key'] >= $this->_var['status_scr_count']): ?>1<?php else: ?><?php echo ($this->_var['scr_key']+1); ?><?php endif; ?>').slideLeftIn(200)});
		})
		$('#status_list_<?php echo $this->_var['scr_key']; ?>').swipeRight(function()
		{
			$('#status_list_<?php echo $this->_var['scr_key']; ?>').slideRightOut(200,function(){$('#status_list_<?php if ($this->_var['scr_key'] == 1): ?><?php echo $this->_var['status_scr_count']; ?><?php else: ?><?php echo ($this->_var['scr_key']-1); ?><?php endif; ?>').slideRightIn(200)});
		})
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	}
})(Zepto)
function change_status(status)
{
	$.zcontent.set('composite_status',status);
        $.zcontent.set('page',1);
	search();
}
</script>
<ul class="order_listtop">
<!--<?php $_from = $this->_var['status_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('scr_key', 'screen');$this->_foreach['screen'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['screen']['total'] > 0):
    foreach ($_from AS $this->_var['scr_key'] => $this->_var['screen']):
        $this->_foreach['screen']['iteration']++;
?>-->
 <!--<?php if ($this->_foreach['screen']['iteration'] == 1): ?>-->
 <!--<?php $_from = $this->_var['screen']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('status_id', 'status_name');$this->_foreach['status_list'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['status_list']['total'] > 0):
    foreach ($_from AS $this->_var['status_id'] => $this->_var['status_name']):
        $this->_foreach['status_list']['iteration']++;
?>-->
 <li onclick="change_status('<?php echo $this->_var['status_id']; ?>')"><a <?php if ($this->_var['filter']['composite_status'] == $this->_var['status_id']): ?>class="on"<?php endif; ?>><?php echo $this->_var['status_name']; ?></a>
<!-- <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> -->
  <!--<?php endif; ?>-->
<!--<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>-->
<li class="more_status_list" onClick="show_menu();$('#close_btn').addClass('hid');" id="show_more"><a>更多状态<i class="menu"></i></a></li>
</ul>
<!--更多状态弹出层-->
<div class="more_status_nav hid" id="menu">
        <div class="Triangle">
          <h2></h2>
        </div>
        <ul>
		<!--<?php $_from = $this->_var['status_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('scr_key', 'screen');$this->_foreach['screen'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['screen']['total'] > 0):
    foreach ($_from AS $this->_var['scr_key'] => $this->_var['screen']):
        $this->_foreach['screen']['iteration']++;
?>-->
		 <!--<?php if ($this->_foreach['screen']['iteration'] > 1): ?>-->
		 <!--<?php $_from = $this->_var['screen']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('status_id', 'status_name');$this->_foreach['status_list'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['status_list']['total'] > 0):
    foreach ($_from AS $this->_var['status_id'] => $this->_var['status_name']):
        $this->_foreach['status_list']['iteration']++;
?>-->
                 <!--<?php if ($this->_var['status_name'] == '待收货' || $this->_var['status_name'] == '已完成' || $this->_var['status_name'] == '取消'): ?>-->
          <li><a href="javascript:change_status('<?php echo $this->_var['status_id']; ?>')"><?php echo $this->_var['status_name']; ?></a></li>
          <!--<?php endif; ?>-->
		  <!--<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>-->
 		 <!--<?php endif; ?>-->
		<!--<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>--> 
        </ul>
      </div>