<?php

/**
 * ECSHOP 前台公用文件
 * ============================================================================
 * * 版权所有 2008-2015 秦皇岛商之翼网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.68ecshop.com;
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: derek $
 * $Id: init.php 17217 2011-01-19 06:29:08Z derek $
*/

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

error_reporting(E_ALL);

if (__FILE__ == '')
{
    die('Fatal error code: 0');
}

/* 取得当前ecshop所在的根目录 */
define('ROOT_PATH', str_replace('includes/init.php', '', str_replace('\\', '/', __FILE__)));
define('TOKEN', "leileiceshi");
define('ROOT_PATH_WAP', str_replace('/mobile','',ROOT_PATH));
if (!file_exists(ROOT_PATH . '../data/install.lock') && !file_exists(ROOT_PATH . '../includes/install.lock')
    && !defined('NO_CHECK_INSTALL'))
{
    header("Location: ./../install/index.php\n");

    exit;
}

/* 初始化设置 */
@ini_set('memory_limit',          '64M');
@ini_set('session.cache_expire',  180);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies',   1);
@ini_set('session.auto_start',    0);
@ini_set('display_errors',        1);

if (DIRECTORY_SEPARATOR == '\\')
{
    @ini_set('include_path', '.;' . ROOT_PATH);
}
else
{
    @ini_set('include_path', '.:' . ROOT_PATH);
}

require(ROOT_PATH . '../data/config.php');

if (defined('DEBUG_MODE') == false)
{
    define('DEBUG_MODE', 0);
}

if (PHP_VERSION >= '5.1' && !empty($timezone))
{
    date_default_timezone_set($timezone);
}

$php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
if ('/' == substr($php_self, -1))
{
    $php_self .= 'index.php';
}
define('PHP_SELF', $php_self);

require(ROOT_PATH . 'includes/inc_constant.php');
require(ROOT_PATH . 'includes/cls_ecshop.php');
require(ROOT_PATH . 'includes/cls_error.php');
require(ROOT_PATH . 'includes/lib_time.php');
require(ROOT_PATH . 'includes/lib_base.php');
require(ROOT_PATH . 'includes/lib_common.php');
require(ROOT_PATH . 'includes/lib_main.php');
require(ROOT_PATH . 'includes/lib_insert.php');
require(ROOT_PATH . 'includes/lib_goods.php');
require(ROOT_PATH . 'includes/lib_article.php');

/* 对用户传入的变量进行转义操作。*/
if (!get_magic_quotes_gpc())
{
    if (!empty($_GET))
    {
        $_GET  = addslashes_deep($_GET);
    }
    if (!empty($_POST))
    {
        $_POST = addslashes_deep($_POST);
    }

    $_COOKIE   = addslashes_deep($_COOKIE);
    $_REQUEST  = addslashes_deep($_REQUEST);
}

/* 创建 ECSHOP 对象 */
$ecs = new ECS($db_name, $prefix);
define('DATA_DIR', $ecs->data_dir());
define('IMAGE_DIR', $ecs->image_dir());

/* 初始化数据库类 */
require(ROOT_PATH . 'includes/cls_mysql.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db->set_disable_cache_tables(array($ecs->table('sessions'), $ecs->table('sessions_data'), $ecs->table('cart')));
$db_host = $db_user = $db_pass = $db_name = NULL;

/* 创建错误处理对象 */
$err = new ecs_error('message.dwt');

/* 载入系统参数 */
$_CFG = load_config();

/* 载入语言文件 */
require(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/common.php');

if ($_CFG['shop_closed'] == 1)
{
    /* 商店关闭了，输出关闭的消息 */
    header('Content-type: text/html; charset='.EC_CHARSET);

    die('<div style="margin: 150px; text-align: center; font-size: 14px"><p>' . $_LANG['shop_closed'] . '</p><p>' . $_CFG['close_comment'] . '</p></div>');
}

if (is_spider())
{
    /* 如果是蜘蛛的访问，那么默认为访客方式，并且不记录到日志中 */
    if (!defined('INIT_NO_USERS'))
    {
        define('INIT_NO_USERS', true);
        /* 整合UC后，如果是蜘蛛访问，初始化UC需要的常量 */
        if($_CFG['integrate_code'] == 'ucenter')
        {
             $user = & init_users();
        }
    }
    $_SESSION = array();
    $_SESSION['user_id']     = 0;
    $_SESSION['user_name']   = '';
    $_SESSION['email']       = '';
    $_SESSION['user_rank']   = 0;
    $_SESSION['discount']    = 1.00;
}

if (!defined('INIT_NO_USERS'))
{
    /* 初始化session */
    include(ROOT_PATH . 'includes/cls_session.php');

    $sess = new cls_session($db, $ecs->table('sessions'), $ecs->table('sessions_data'));

    define('SESS_ID', $sess->get_session_id());
}
if(isset($_SERVER['PHP_SELF']))
{
    $_SERVER['PHP_SELF']=htmlspecialchars($_SERVER['PHP_SELF']);
}
if (!defined('INIT_NO_USERS'))
{
    /* 会员信息 */
    $user =& init_users();

    if (!isset($_SESSION['user_id']))
    {
        /* 获取投放站点的名称 */
        $site_name = isset($_GET['from'])   ? htmlspecialchars($_GET['from']) : addslashes($_LANG['self_site']);
        $from_ad   = !empty($_GET['ad_id']) ? intval($_GET['ad_id']) : 0;

        $_SESSION['from_ad'] = $from_ad; // 用户点击的广告ID
        $_SESSION['referer'] = stripslashes($site_name); // 用户来源

        unset($site_name);

        if (!defined('INGORE_VISIT_STATS'))
        {
            visit_stats();
        }
    }

    if (empty($_SESSION['user_id']))
    {
        if ($user->get_cookie())
        {
            /* 如果会员已经登录并且还没有获得会员的帐户余额、积分以及优惠券 */
            if ($_SESSION['user_id'] > 0)
            {
                update_user_info();
            }
        }
        else
        {
            $_SESSION['user_id']     = 0;
            $_SESSION['user_name']   = '';
            $_SESSION['email']       = '';
            $_SESSION['user_rank']   = 0;
            $_SESSION['discount']    = 1.00;
            if (!isset($_SESSION['login_fail']))
            {
                $_SESSION['login_fail'] = 0;
            }
        }
    }

    /* 设置推荐会员 */
    if (isset($_GET['u']))
    {
        set_affiliate();
    }

    /* session 不存在，检查cookie */
    if (!empty($_COOKIE['ECS']['user_id']) && !empty($_COOKIE['ECS']['password']))
    {
        // 找到了cookie, 验证cookie信息
        $sql = 'SELECT user_id, user_name, password ' .
                ' FROM ' .$ecs->table('users') .
                " WHERE user_id = '" . intval($_COOKIE['ECS']['user_id']) . "' AND password = '" .$_COOKIE['ECS']['password']. "'";

        $row = $db->GetRow($sql);

        if (!$row)
        {
            // 没有找到这个记录
           $time = time() - 3600;
           setcookie("ECS[user_id]",  '', $time, '/');
           setcookie("ECS[password]", '', $time, '/');
        }
        else
        {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['user_name'];
            update_user_info();
        }
    }

    if (isset($smarty))
    {
        $smarty->assign('ecs_session', $_SESSION);
    }
}

if ((DEBUG_MODE & 1) == 1)
{
    error_reporting(E_ALL);
}
else
{
    error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
}
if ((DEBUG_MODE & 4) == 4)
{
    include(ROOT_PATH . 'includes/lib.debug.php');
}

/* 判断是否支持 Gzip 模式 */
if (!defined('INIT_NO_SMARTY') && gzip_enabled())
{
    ob_start('ob_gzhandler');
}
else
{
    ob_start();
}


/*68ecshop.com mobile start*/
if (!defined('INIT_NO_SMARTY'))
{
    header('Cache-control: private');
    header('Content-type: text/html; charset='.EC_CHARSET);

    /* 创建 Smarty 对象。*/
    require(ROOT_PATH . 'includes/cls_template.php');
    $smarty = new cls_template;

    $smarty->cache_lifetime = $_CFG['cache_time'];
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$uachar = "/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile)/i";
if(($ua == '' || preg_match($uachar, $ua))&& !strpos(strtolower($_SERVER['REQUEST_URI']),'wap'))
{
$smarty->template_dir   = ROOT_PATH . 'themesmobile/68ecshopcom_mobile';
$smarty->cache_dir      = ROOT_PATH . 'temp/caches';
$smarty->compile_dir    = ROOT_PATH . 'temp/compiled';
}
else
{
    $smarty->template_dir   = ROOT_PATH . 'themesmobile/68ecshopcom_mobile';
    $smarty->cache_dir      = ROOT_PATH . 'temp/caches';
    $smarty->compile_dir    = ROOT_PATH . 'temp/compiled';
}

    if ((DEBUG_MODE & 2) == 2)
    {
        $smarty->direct_output = true;
        $smarty->force_compile = true;
    }
    else
    {
        $smarty->direct_output = false;
        $smarty->force_compile = false;
    }

    $smarty->assign('lang', $_LANG);
    $smarty->assign('ecs_charset', EC_CHARSET);
if(($ua == '' || preg_match($uachar, $ua))&& !strpos(strtolower($_SERVER['REQUEST_URI']),'wap'))
{
    if (!empty($_CFG['stylename']))
    {
$smarty->assign('ecs_css_path', 'themesmobile/' . $_CFG['template'] . '/style_' . $_CFG['stylename'] . '.css');
}
else
{
 $smarty->assign('ecs_css_path', 'themesmobile/' . $_CFG['template'] . '/style.css');
}
}else
{
if (!empty($_CFG['stylename']))
{
         $smarty->assign('ecs_css_path', 'themesmobile/' . $_CFG['template'] . '/style_' . $_CFG['stylename'] . '.css');
    }
    else
    {
         $smarty->assign('ecs_css_path', 'themesmobile/' . $_CFG['template'] . '/style.css');
    }

}
}

// 抽奖汇总
$week = local_date("w");
if($week == 1){        // 暂时写死周二五抽奖
    // 获取上次抽奖信息
    $is_insert = 0;
    $time_end = strtotime(date("y-m-d"));
    $sql = "SELECT * FROM " . $ecs->table('award_account') . " order by id desc limit 0,1";
    $data = $db->GetRow($sql);

    if($data){
        if($data['time_end'] != strtotime(date("y-m-d"))){
            $time_begin = $data['time_end'];
            $is_insert = 1;
        }
    }else{
        $time_begin = 0;
        $is_insert = 1;
    }
    if($is_insert){

        // 计算奖池累计
        $sql = " SELECT sum(og.goods_price * og.goods_number) as all_points FROM " . 
                $ecs->table('order_goods') . " AS og, ".
                $ecs->table('order_info'). " AS oi ".
                "WHERE oi.order_id = og.order_id AND oi.pay_status = 2 AND oi.pay_time > $time_begin AND oi.pay_time < $time_end AND og.extension_code = 'package_buy'";
        $all_points = $db->getOne($sql);

        // 计算人数加权值
        $sql = "SELECT sum(vip_times) as member_all FROM " . $ecs->table('users') . " WHERE vip_award > 0";
        $member_all = $db->getOne($sql);
        $award_percent = 20;
        $sql = "INSERT INTO " . $ecs->table('award_account') . "(time_begin, time_end, all_points, award_percent, member_all, week) VALUES( '$time_begin', '$time_end', '$all_points', '$award_percent', '$member_all', '$week')";
        $db->query($sql);
        // $id = $db->insert_id();

        $award_config = array(
                array(
                        'title' => '一等奖',
                        'award_percent' => '30',
                        'num' => '1',
                        'randnum' => '5'
                    ),
                array(
                        'title' => '二等奖',
                        'award_percent' => '20',
                        'num' => '2',
                        'randnum' => '10'
                    ),
                array(
                        'title' => '三等奖',
                        'award_percent' => '10',
                        'num' => '3',
                        'randnum' => '15'
                    ),
                array(
                        'title' => '幸运奖',
                        'award_percent' => '10',
                        'num' => '30',
                        'randnum' => '20'
                    ),
                array(
                        'title' => '拆包奖',
                        'award_percent' => '30',
                        'num' => ($member_all - 36 > 0) ? ($member_all - 36) : 0,
                        'randnum' => '40'
                    )
            );
        $sql = "SELECT award_type FROM " . $ecs->table('weixin_act') . " WHERE aid = 3";
        $award_type = $db->getOne($sql);
        // 更新抽奖配置
        foreach ($award_config as $key => $value) {
            // echo $value['title'];
            $points = 0;
            if($award_type == 1){

                if($value['num'] > 0){
                    $points = ($all_points * $award_percent * $value['award_percent'])/($value['num'] * 100 * 100);
                }else{
                    $points = 1;
                }

            }else{

                if($member_all > 0){
                    $points = $all_points * $award_percent/($member_all * 100);
                }

            }
            $points = round($points);
            // make_record($points, $value['num'], $value['title'], $value['randnum']);
            $sql = "UPDATE " . $ecs->table('weixin_actlist') . " SET awardname = '$points',  num = '$value[num]', num2 = 0, randnum = '$value[randnum]' WHERE title = '$value[title]' AND aid = 3";
            $GLOBALS['db']->query($sql);
        }
    }
}
/*
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : "/moblie/";
$autoUrl = str_replace($_SERVER['REQUEST_URI'],"",$GLOBALS['ecs']->url());

@file_get_contents($autoUrl."/weixin/auto_do.php");
*/

?>