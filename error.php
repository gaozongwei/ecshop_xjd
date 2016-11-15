<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>error</title>
<link href="themes/68ecshopcom_360buy/css/error.css" rel="stylesheet" type="text/css">
</head>

<body>
<div class="error_con">
    <div class="error">
        <i></i>
        <p class="error_warning">哎呀...您访问的页面出现错误</p>
        <p class="error_back">
            <?php
            echo ' <a href="'.$_SERVER['HTTP_REFERER'].'">返回上一页</a>';
            echo ' <a href="index.php">返回网站首页</a>';
            ?>
        </p>
        <p>错误提醒：您可能输入了错误的信息或者做了错误操作！</p>
    </div>
</div>
</body>
</html>
