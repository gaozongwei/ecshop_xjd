<?php
header("Content-Type:text/html; charset=utf-8"); 


/** 
 * 中文分词处理方法 
 *+--------------------------------- 
 * @param stirng  $string 要处理的字符串 
 * @param boolers $sort=false 根据value进行倒序 
 * @param Numbers $top=0 返回指定数量，默认返回全部 
 *+--------------------------------- 
 * @return void 
 */  
function scws($text, $top = 5, $return_array = false, $sep = ',') {  
    include('./pscws4/pscws4.php');//去下面给的网址把pscws4下载下来  
    $cws = new pscws4('utf-8');  
    $cws -> set_charset('utf-8');  
    $cws -> set_dict('./pscws4/etc/dict.utf8.xdb');  
    $cws -> set_rule('./pscws4/etc/rules.utf8.ini');  
    //$cws->set_multi(3);  
    $cws -> set_ignore(true);  
    //$cws->set_debug(true);  
    //$cws->set_duality(true);  
    $cws -> send_text($text);  
    $ret = $cws -> get_tops($top, 'r,v,p');  
    $result = null;  
    foreach ($ret as $value) {  
        if (false === $return_array) {  
            $result .= $sep . $value['word'];  
        } else {  
            $result[] = $value['word'];  
        }  
    }  
    return false === $return_array ? substr($result, 1) : $result;  
}  
print_r(scws('青花夔龙纹香炉'));  









exit;


print_r(fci("仿小京东插件"));

function fci($text) {
	$text = iconv("UTF-8", "GBK//IGNORE", $text);
	$text = urlencode($text);
	$result = file_get_contents("http://wx.buqiu.com:1985/?w=".$text);
	$result = iconv("GBK", "UTF-8//IGNORE", $result);
	return explode(" ", $result);
}

exit;
//echo date('Y-m-d','1456934400');

$vyear = floor((strtotime('2016-02-03 00:00:00') - strtotime('2016-02-03 00:00:00'))/(86400*365));
echo $vyear;
?>