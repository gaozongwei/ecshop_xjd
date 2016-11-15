<p class="page">
  <a href="javascript:<?php if ($this->_var['filter']['page'] == 1): ?>void(0)<?php else: ?>prev_page()<?php endif; ?>" class="prev">上一页</a>
  <span><?php if ($this->_var['filter']['page'] && $this->_var['filter']['page'] >= 0): ?><?php echo $this->_var['filter']['page']; ?><?php else: ?>0<?php endif; ?>/<?php if ($this->_var['filter']['page_count'] && $this->_var['filter']['page_count'] >= 0): ?><?php echo $this->_var['filter']['page_count']; ?><?php else: ?>0<?php endif; ?></span>
  <a href="javascript:<?php if ($this->_var['filter']['page'] == $this->_var['filter']['page_count']): ?>void(0)<?php else: ?>next_page()<?php endif; ?>">下一页</a>  
</p>