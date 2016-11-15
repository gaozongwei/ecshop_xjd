<?php
/*
 *
 *
 *MySQL防止注入控制
 *
 *2014-8-18
 *
 *author kings 
 *
 *
 * e-mail:664550744@qq.com
 *
 *
 */
foreach ($_POST as $key => $value){
	
	if(!is_array($value)){
		inject_check($value);
	}
}
foreach ($_GET as $key => $value){
	
	if(!is_array($value)){
		inject_check($value);
	}
}

foreach ($_REQUEST  as $key => $value){
	if(!is_array($value)){
		inject_check($value);
	}
	
}
function inject_check($Sql_Str) {//自动过滤Sql的注入语句。
    $check=preg_match('/select|insert|update|delete|\'|\\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/i',$Sql_Str);
    if ($check) {
		$result['code']=0;
		$result['info']="您提交的信息包含非法字符！";
        print_r(json_encode($result));
        exit();
    }else{
        return $Sql_Str;
    }
}
?>