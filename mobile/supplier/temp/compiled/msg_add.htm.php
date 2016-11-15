<!-- $Id: msg_add.htm 14216 2008-03-10 02:27:21Z testyang $ -->
<?php if ($this->_var['full_page'] == 1): ?>
<!DOCTYPE html>
<html>
  <head>
    <?php echo $this->fetch('html_header.htm'); ?>
    <script>
      Zepto(function($)
      {
        document.forms['theForm'].elements['msg_content'].focus();
      });
      
      function insert_message()
      {
        if($.trim(document.forms['theForm'].elements['msg_title'].value) == '')
        {
          $.zalert.add('请输入标题！',1);
        }
        if($.trim(document.forms['theForm'].elements['msg_content'].value) == '')
        {
          $.zalert.add('请输入内容！',1);
        }
        else
        {
          $.zcontent.set('act','insert');
          $.zcontent.set('order_id',document.forms['theForm'].elements['order_id'].value);
          $.zcontent.set('user_id',document.forms['theForm'].elements['user_id'].value);
          $.zcontent.set('msg_title',document.forms['theForm'].elements['msg_title'].value);
          $.zcontent.set('msg_content',document.forms['theForm'].elements['msg_content'].value);
          $.zcontent.query();
        }
        return false;
      }
      
      function remove_msg(order_id,user_id,msg_id)
      {
        if (confirm('<?php echo $this->_var['lang']['confirm_delete']; ?>'))
        {
          $.zcontent.set('act','remove_msg');
          $.zcontent.set('order_id',order_id);
          $.zcontent.set('user_id',user_id);
          $.zcontent.set('msg_id',msg_id);
          $.zcontent.query();
        }
      }
      
      function refresh()
      {
          $.zcontent.set('act','add');
          $.zcontent.query();
      }
      
      function drop_file(order_id,user_id,msg_id,message_img)
      {
          $.zcontent.set('act','drop_file');
          $.zcontent.set('order_id',order_id);
          $.zcontent.set('user_id',user_id);
          $.zcontent.set('msg_id',msg_id);
          $.zcontent.set('file',message_img);
          $.zcontent.query();
      }
    </script>
  </head>
  <body>
    <div id='container'>
      <?php endif; ?>
      <?php echo $this->fetch('page_header.htm'); ?>
      <div class="blank"></div>
      <section>
        <div class="order_con">
          <div class="order_pd">
            <div class="order">
              <div class="order_info_msg">
                <table width="100%">
                  <?php $_from = $this->_var['msg_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'msg');if (count($_from)):
    foreach ($_from AS $this->_var['msg']):
?>
                  <tr>
                    <td><?php echo htmlspecialchars($this->_var['msg']['msg_title']); ?></td>
                    <td align="right"><a href="javascript:void(0);"  onclick="remove_msg('<?php echo $this->_var['order_id']; ?>','<?php echo $this->_var['user_id']; ?>','<?php echo $this->_var['msg']['msg_id']; ?>')"> <img src="images/delete_icon_16.png"  border="0" /> </a></td>
                  </tr>
                  <tr class="msg_con">
                    <td colspan="2">
                      <?php echo nl2br(htmlspecialchars($this->_var['msg']['msg_content'])); ?>
                      <?php if ($this->_var['msg']['message_img']): ?>
                      <div align="right">
                        <a href="../../data/feedbackimg/<?php echo $this->_var['msg']['message_img']; ?>" target="_bank"><?php echo $this->_var['lang']['view_upload_file']; ?></a>
                        <a href="javascript:void(0)" onclick="drop_file('<?php echo $this->_var['order_id']; ?>','<?php echo $this->_var['user_id']; ?>','<?php echo $this->_var['msg']['msg_id']; ?>','<?php echo $this->_var['msg']['message_img']; ?>')"><?php echo $this->_var['lang']['drop']; ?></a>
                      </div>
                      <?php endif; ?>
                      <div align="right"  nowrap="nowrap">
                        <a href="mailto:<?php echo $this->_var['msg']['user_email']; ?>"><?php echo $this->_var['msg']['user_name']; ?></a>
                        @<?php echo $this->_var['msg']['msg_time']; ?>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </table>
                <form method="post" action="user_msg.php?act=insert" name="theForm"  onsubmit="return insert_message();">
                  <table border="0" width="100%">
                    <tr>
                      <td width="25%"><?php echo $this->_var['lang']['msg_title']; ?>:</td>
                      <td width="75%"><input name="msg_title" id="msg_title"  type="text" value="<?php echo $this->_var['msg']['reply_email']; ?>" class="input_msg"/></td>
                    </tr>
                    <tr>
                      <td><?php echo $this->_var['lang']['msg_content']; ?>:</td>
                      <td><textarea name="msg_content"  rows="4" wrap="VIRTUAL" id="msg_content" ><?php echo $this->_var['msg']['reply_content']; ?></textarea></td>
                    </tr>
                    <tr>
                      <td>&nbsp;</td>
                      <td>
                        <input type="hidden" name="order_id" value="<?php echo $this->_var['order_id']; ?>" />
                        <input type="hidden" name="user_id" value="<?php echo $this->_var['user_id']; ?>" />
                        <input name="Submit" value="<?php echo $this->_var['lang']['button_submit']; ?>" type="submit" class="button2" />
                      </td>
                    </tr>
                  </table>
                </form>
              </div>
            </div>
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