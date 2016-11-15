<?php

/**
 * ECSHOP 活动列表
 * ============================================================================
 * 版权所有 2005-2011 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: activity.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
include_once(ROOT_PATH . 'includes/lib_transaction.php');

/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');


/*------------------------------------------------------ */
//-- act 操作项的初始化
/*------------------------------------------------------ */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}

/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */
if($_REQUEST['is_ajax'] && $_REQUEST['act']=='show_choose_attr'){
    include('includes/cls_json.php');
    $json   = new JSON;
    $act_id = empty($_REQUEST['act_id'])?0:  intval($_REQUEST['act_id']);
    $activity_info = get_activity_info($act_id);
    //var_dump($activity_info);
    $smarty->assign('activity',  $activity_info);
    $output = $GLOBALS['smarty']->fetch('library/activity.lbi');
    die($json->encode($output));
}


assign_template();
assign_dynamic('activity');
$position = assign_ur_here(0, $_LANG['shopping_activity']);
$smarty->assign('page_title',       $position['title']);    // 页面标题
$smarty->assign('ur_here',          $position['ur_here']);  // 当前位置


$last = isset($_REQUEST['last'])?trim($_REQUEST['last']):'';
$amount = isset($_REQUEST['amount'])?trim($_REQUEST['amount']):'';

if($_REQUEST['act'] == 'ajax_list'){
     
    include('includes/cls_json.php');

    $limit = " limit $last,$amount";//每次加载的个数
    $json   = new JSON;
$nowtime = time();
$sql = "SELECT fa.* FROM " . $GLOBALS['ecs']->table('favourable_activity'). " AS fa ".
		"WHERE  fa.start_time<=".$nowtime." AND fa.end_time>=".$nowtime.
		" ORDER BY fa.`sort_order` ASC,fa.`end_time` DESC ";
 $sql .= " $limit";
$res = $GLOBALS['db']->getAll($sql);
foreach ($res as $key=>$row)
{
    $row['start_time']  = local_date('Y-m-d H:i', $row['start_time']);
    $row['end_time']    = local_date('Y-m-d H:i', $row['end_time']);
    if($row['supplier_id'] > 0){
	    $sql = "select code,value from " . $GLOBALS['ecs']->table('supplier_shop_config'). 
	    		" where supplier_id=".$row['supplier_id'].
	    		" AND code in('shop_name','shop_logo')";
	    $r = $GLOBALS['db']->getAll($sql);
	    foreach($r as $k=>$v){
	    	$row[$v['code']] = $v['value'];
	    }
    }else{
        $row['shop_name'] = '网站自营';
    	$row['shop_logo'] = 'admin/'.$_CFG['shop_logo'];
    }
    $activity[] = $row;
}
    foreach($activity as $key=>$val){
            $GLOBALS['smarty']->assign('val',$val);
            $result[]['info']  = $GLOBALS['smarty']->fetch('library/activity_list.lbi');
    }
    die($json->encode($result));
}


if ($_REQUEST['act'] == 'list'){
    $smarty->assign('helps',            get_shop_help());       // 网店帮助
    $smarty->assign('lang',             $_LANG);
    $smarty->assign('feed_url',         ($_CFG['rewrite'] == 1) ? "feed-typeactivity.xml" : 'feed.php?type=activity'); // RSS URL
    $smarty->display('activity.dwt');
}
/**
 * 根据优惠活动id  获取详细信息
 * @param type $act_id
 * @return string
 */
function get_activity_info($act_id){
    $_LANG = $GLOBALS['_LANG'];
        /* 取得用户等级 */
    $user_rank_list = array();
    $user_rank_list[0] = $_LANG['not_user'];
    $sql = "SELECT rank_id, rank_name FROM " . $GLOBALS['ecs']->table('user_rank');
    $res_u = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res_u))
    {
        $user_rank_list[$row['rank_id']] = $row['rank_name'];
    }

    $nowtime = time();
$sql = "SELECT fa.* FROM " . $GLOBALS['ecs']->table('favourable_activity'). " AS fa ".
		"WHERE  fa.start_time<=".$nowtime." AND fa.end_time>=".$nowtime. " AND act_id = $act_id ".
		" ORDER BY fa.`sort_order` ASC,fa.`end_time` DESC ";
$res = $GLOBALS['db']->getRow($sql);

    $res['start_time']  = local_date('Y-m-d H:i', $res['start_time']);
    $res['end_time']    = local_date('Y-m-d H:i', $res['end_time']);
    //享受优惠会员等级
    $user_rank = explode(',', $res['user_rank']);
    $res['user_rank'] = array();
    foreach($user_rank as $val)
    {
        if (isset($user_rank_list[$val]))
        {
            $res['user_rank'][] = $user_rank_list[$val];
        }
    }

    //优惠范围类型、内容
    if ($res['act_range'] != FAR_ALL && !empty($res['act_range_ext']))
    {
        if ($res['act_range'] == FAR_CATEGORY)
        {
            
            $res['act_range'] = $_LANG['far_category'];
            $res['program'] = 'category.php?id=';
            if($res['supplier_id']){
            $sql = "SELECT cat_id AS id, cat_name AS name FROM " . $GLOBALS['ecs']->table('supplier_category') .
                " WHERE cat_id " . db_create_in($res['act_range_ext']);
            }else{
                 $sql = "SELECT cat_id AS id, cat_name AS name FROM " . $GLOBALS['ecs']->table('category') .
                " WHERE cat_id " . db_create_in($res['act_range_ext']);
            }
        }
        elseif ($res['act_range'] == FAR_BRAND)
        {
            $res['act_range'] = $_LANG['far_brand'];
            $res['program'] = 'brand.php?id=';
            $sql = "SELECT brand_id AS id, brand_name AS name FROM " . $GLOBALS['ecs']->table('brand') .
                " WHERE brand_id " . db_create_in($res['act_range_ext']);
        }
        else
        {
            $res['act_range'] = $_LANG['far_goods'];
            $res['program'] = 'goods.php?id=';
            $sql = "SELECT goods_id AS id, goods_name AS name, goods_thumb AS thumb FROM " . $GLOBALS['ecs']->table('goods') .
                " WHERE goods_id " . db_create_in($res['act_range_ext']);
        }
        $act_range_ext = $GLOBALS['db']->getAll($sql);
        foreach($act_range_ext as $key=>$value){
            $act_range_ext[$key]['thumb'] = get_pc_url().'/'.get_image_path($value['goods_id'],$value['thumb']);
        }
        $res['act_range_ext'] = $act_range_ext;
    }
    else
    {
        $res['act_range'] = $_LANG['far_all'];
    }

    //优惠方式
    $res['act_type_num'] = $res['act_type'];
    switch($res['act_type'])
    {
        case 0:
            $res['act_type'] = $_LANG['fat_goods'];
            $res['gift'] = unserialize($res['gift']);
            if(is_array($res['gift']))
            {
                foreach($res['gift'] as $k=>$v)
                {
                    $res['gift'][$k]['thumb'] = get_pc_url().'/'.get_image_path($v['id'], $GLOBALS['db']->getOne("SELECT goods_thumb FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id = '" . $v['id'] . "'"), true);
                }
            }
            break;
        case 1:
            $res['act_type'] = $_LANG['fat_price'];
            $res['act_type_ext'] .= $_LANG['unit_yuan'];
            $res['gift'] = array();
            break;
        case 2:
            $res['act_type'] = $_LANG['fat_discount'];
            $res['act_type_ext'] .= "%";
            $res['gift'] = array();
            break;
    }
    
    if($res['supplier_id'] > 0){
	    $sql = "select code,value from " . $GLOBALS['ecs']->table('supplier_shop_config'). 
	    		" where supplier_id=".$res['supplier_id'].
	    		" AND code in('shop_name','shop_logo')";
	    $r = $GLOBALS['db']->getAll($sql);
	    foreach($r as $k=>$v){
	    	$res[$v['code']] = $v['value'];
	    }
    }else{
        $res['shop_name'] = '网站自营';
    	$res['shop_logo'] =  get_pc_url().'/themes/68ecshopcom_360buy/images/ziying.jpg';
    }
   
    return $res;
}