<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}


if ($_REQUEST['act'] == 'list')
{	
	$smarty->assign('full_page',    1);
	
	$deposit_list = get_deposit();
    
    $smarty->assign('deposit_list',  $deposit_list['arr']);
    $smarty->assign('filter',          $deposit_list['filter']);
    $smarty->assign('record_count',    $deposit_list['record_count']);
    $smarty->assign('page_count',      $deposit_list['page_count']);
	$smarty->display('deposit_list.htm');

}
elseif($_REQUEST['act'] == 'query')
{
	
	$deposit_list = get_deposit();
    
    $smarty->assign('deposit_list',  $deposit_list['arr']);
    $smarty->assign('filter',          $deposit_list['filter']);
    $smarty->assign('record_count',    $deposit_list['record_count']);
    $smarty->assign('page_count',      $deposit_list['page_count']);

	make_json_result($smarty->fetch('deposit_list.htm'), '',array('filter' => $deposit_list['filter'], 'page_count' => $deposit_list['page_count']));
}

function get_deposit()
{
	 $result = get_filter();
     if ($result === false)
     {
		 $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
		 $ex_where = '';
		 if ($filter['keyword'])
		 {
			 $ex_where .= " AND user_name LIKE '%" . $filter['keyword']."%'";
		 }
		 $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('affiliate_log') . 
				" WHERE separate_type = 4 " . $ex_where;
		 $filter['record_count'] = $GLOBALS['db']->getOne($sql);
	
		 $filter = page_and_size($filter);
	
		 $arr = array();
		 $sql = "SELECT * FROM " . 
				$GLOBALS['ecs']->table('affiliate_log') .
				" WHERE separate_type = 4 " . $ex_where . " order by time desc";
		 set_filter($filter, $sql);
	 }
	 else
	 {
		 $sql    = $result['sql'];
         $filter = $result['filter']; 
	 }
	 $res = $GLOBALS['db']->query($sql);

     $list = array();
	 while ($rows = $GLOBALS['db']->fetchRow($res))
	 {
		  $arr['id'] = $rows['log_id'];
		  $arr['money'] = price_format(abs($rows['money']));
		  $arr['add_time'] = local_date('Y-m-d',$rows['time']);
		  $arr['user_name'] = $rows['user_name'];
		  $list[] = $arr;
	 } 
	 return array('arr' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']); 
}
?>