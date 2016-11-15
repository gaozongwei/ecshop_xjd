<?php

$cache_id = sprintf('%X', crc32('supplier_category_app-'.$_REQUEST['suppId']));
if (!$smarty->is_cached('supplier_category_app.lbi', $cache_id))
{
	$smarty->assign('shopname',      $GLOBALS['_CFG']['shop_name']); 
	$smarty->assign('shoplogo',      $GLOBALS['_CFG']['shop_logo']); 
	$smarty->assign('categories',      get_categories_tree_supplier()); // 分类树
    assign_dynamic('supplier_search_app');
}
$result['result']	= $smarty->fetch('supplier_category_app.lbi', $cache_id);
die($json->encode($result));

?>
