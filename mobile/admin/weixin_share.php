<?php
//decode by QQ:270656184 http://www.yunlu99.com/
/**
 * ECSHOP 分销商管理程序
 * ============================================================================
 * * 版权所有 2008-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: dqy $
 * $Id: distributor.php 17217 2011-01-19 06:29:08Z dqy $
*/
    
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');


/*------------------------------------------------------ */
//-- 分销商列表
/*------------------------------------------------------ */
$_REQUEST['act'] = $_REQUEST['act']?$_REQUEST['act']:'list';
if ($_REQUEST['act'] == 'list')
{
    $smarty->assign('ur_here',     '记录管理');
	
    $user_list = user_list();
    $smarty->assign('user_list',    $user_list['user_list']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);
    $smarty->assign('full_page',    1);

    assign_query_info();
    $smarty->display('weixin/wx_share.html');
}

/*------------------------------------------------------ */
//-- ajax返回分销商列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $user_list = user_list();

    $smarty->assign('user_list',    $user_list['user_list']);
    $smarty->assign('filter',       $user_list['filter']);
    $smarty->assign('record_count', $user_list['record_count']);
    $smarty->assign('page_count',   $user_list['page_count']);

    $sort_flag  = sort_flag($user_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('weixin/wx_share.html'), '', array('filter' => $user_list['filter'], 'page_count' => $user_list['page_count']));
}
elseif ($_REQUEST['act'] == 'del')
{
	$id = intval($_GET['id']);
	$result = $db->query("delete FROM ".$ecs->table('weixin_share')." where id=".$id);
	if($result)
	{
		$link [] = array ('href' => 'weixin_share.php?act=list','text' => '返回分享记录列表');
		sys_msg ( '删除成功', 0, $link );
	}
	else
	{
		$link [] = array ('href' => 'weixin_share.php?act=list','text' => '返回分享记录列表');
		sys_msg ( '删除失败', 0, $link );
	}
}


/**
 *  返回用户列表数据
 *
 * @access  public
 * @param
 *
 * @return void
 */
function user_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['type'] = empty($_REQUEST['type']) ? '' : trim($_REQUEST['type']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }
        
        $ex_where = ' WHERE 1 ';
		
		if($filter['type'])
		{
			if($filter['type'] == 1)
			{
				$ex_where .= " AND type = 1";
			}
			if($filter['type'] == 2)
			{
				$ex_where .= " AND type = 2";
			}
		}
		
        if ($filter['keywords'])
        {
            $ex_where .= " AND user_id =".$filter['keywords'];
        }

        $filter['record_count'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('weixin_share') . $ex_where);

        /* 分页大小 */
        $filter = page_and_size($filter);
		
		$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('weixin_share') . $ex_where .
                " ORDER by id desc " .
                " LIMIT " . $filter['start'] . ',' . $filter['page_size'];

        $filter['keywords'] = stripslashes($filter['keywords']);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $user_list = $GLOBALS['db']->getAll($sql);

    foreach ($user_list as $key => $value) {
        # code...
        $user_list[$key]['user_name'] = $GLOBALS['db']->getOne("SELECT user_name FROM ".$GLOBALS['ecs']->table('users')." where user_id=".$value['user_id']);
        $user_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time'] );
        if($value['type'] == '1'){
            $user_list[$key]['type'] = "分享给朋友";
        }else{
            $user_list[$key]['type'] = "分享朋友圈";
        }
    }


    $arr = array('user_list' => $user_list, 'filter' => $filter,
        'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}