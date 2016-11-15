<?php

/**
 * 订单详情
*/
ob_start();
	define('IN_ECS', true);

	require('../includes/init.php');
	include('../includes/cls_json.php');
	require('includes/safety_mysql.php');
	$json   = new JSON;


	$smarty->template_dir = ROOT_PATH . 'json/tpl';//app部分模板所在位置
	/*require('includes/safety_mysql.php');
	define('IN_ECS', true);
	require('../includes/init.php');*/
	$order_id = isset($_REQUEST['order_id'])  ? intval($_REQUEST['order_id']) : 0;
	$result=array();
	$sql="SELECT * FROM  ".$ecs->table('order_info')." WHERE order_id='$order_id' ";
	//print_r($sql);
	$res = $db -> getAll($sql);
	for($i=0;$i<count($res);$i++)
	{
		$res[$i]['add_time']=local_date($GLOBALS['_CFG']['time_format'], $res['add_time']);//下单时间
		$res[$i]['confirm_time']=local_date($GLOBALS['_CFG']['time_format'], $res['confirm_time']);//确定时间
		if($res[$i]['order_status']==0){
			$res[$i]['order_status']="未确认";
		}else if($res[$i]['order_status']==1){
			$res[$i]['order_status']="已确认";
		}else if($res[$i]['order_status']==2){
			$res[$i]['order_status']="已取消";
		}else if($res[$i]['order_status']==3){
			$res[$i]['order_status']="无效";
		}else if($res[$i]['order_status']==4){
			$res[$i]['order_status']="退货";
		}else if($res[$i]['order_status']==5){
			$res[$i]['order_status']="已分单";
		}
		$res[$i]['shipping_time']=local_date($GLOBALS['_CFG']['time_format'], $res['shipping_time']);//配送时间
		if($res[$i]['shipping_status']==0){
			$res[$i]['shipping_status']="未发货";
		}else if($res[$i]['shipping_status']==1){
			$res[$i]['shipping_status']="已发货";
		}else if($res[$i]['shipping_status']==2){
			$res[$i]['shipping_status']="已收货";
		}else if($res[$i]['shipping_status']==3){
			$res[$i]['shipping_status']="备货中";
		}else if($res[$i]['shipping_status']==5){
			$res[$i]['shipping_status']="配货中";
		}
		$res[$i]['pay_time']=local_date($GLOBALS['_CFG']['time_format'], $res['pay_time']);//支付时间
		
		if($res[$i]['pay_status']==0){
			$res[$i]['pay_status']="未付款";
		}else if($res[$i]['pay_status']==1){
			$res[$i]['pay_status']="付款中";
		}else if($res[$i]['pay_status']==2){
			$res[$i]['pay_status']="已付款";
		}
		$res[$i]['inv_complete_address'] = get_inv_complete_address($res[$i]);
		$aa = $res[$i]['order_id'];
		$sql ="SELECT g.goods_thumb,s.* FROM ".$ecs->table('order_goods')."as s,".$ecs->table('goods')." as g WHERE s.order_id='$aa' AND s.goods_id=g.goods_id";
		$res[$i]['xiang'] = $db ->getAll($sql);
		if($res[$i]['xiang'])
		{
			$count_money = $res[$i]['xiang'];
			for($j=0;$j<count($count_money);$j++)
			{
				$res[$i]['count_goods_amount'] += $count_money[$j]['goods_number'] * $count_money[$j]['goods_price'];
			}
		}
		
		$res[$i]['count_amount'] = ($res[$i]['count_goods_amount'] + $res[$i]['shipping_fee'] + $res[$i]['insure_fee'] + $res[$i]['pay_fee'] + $res[$i]['pack_fee'] + $res[$i]['card_fee'] + $res[$i]['tax']) - ($res[$i]['discount'] + $res[$i]['bonus'] + $res[$i]['integral_money']);
	
	}
	//$result['orderInfo']=$res;
	
	$sql ="SELECT g.goods_thumb,s.* FROM ".$ecs->table('order_goods')."as s,".$ecs->table('goods')." as g WHERE s.order_id='$order_id' AND s.goods_id=g.goods_id";
	$resa = $db -> getAll($sql);
	
	$smarty->assign('order_info',$res);
	$smarty->assign('order_goods',$resa);
	
	$result['result'] = $smarty->fetch('order_app.lib');
	//file_put_contents('./22.txt',$result);
	print_r(json_encode($result));
//发票地址
function get_inv_complete_address($order)
{	
    if($order['inv_type'] == 'normal_invoice')
    {
        $address = trim(get_inv_complete_region($order['order_id'],$order['inv_type']));
        if(empty($address))
        {
            return $order['address'];
        }
        else
        {
            return '['.$address.'] '.$order['address'];
        }
    }
    elseif($order['inv_type'] == 'vat_invoice')
    {
        $address = trim(get_inv_complete_region($order['order_id'],$order['inv_type']));
        if(empty($address))
        {
            return $order['inv_consignee_address'];
        }
        else
        {
            return '['.$address.'] '.$order['inv_consignee_address'];
        }
    }
    else
    {
        return '';
    }
}

function get_inv_complete_region($order_id,$inv_type)
{
    if(!empty($order_id))
    {
        if($inv_type == 'normal_invoice')
        {
            $sql = "SELECT concat(IFNULL(c.region_name, ''), '  ', IFNULL(p.region_name, ''), " .
                        "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
                    "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o " .
                        "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS c ON o.country = c.region_id " .
                        "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS p ON o.province = p.region_id " .
                        "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS t ON o.city = t.region_id " .
                        "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS d ON o.district = d.region_id " .
                    "WHERE o.order_id = '$order_id'";
            return $GLOBALS['db']->getOne($sql);
        }
        elseif($inv_type == 'vat_invoice')
        {
            $sql = "SELECT concat(IFNULL(p.region_name, ''), " .
                            "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
                        "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o " .
                            "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS p ON o.inv_consignee_province = p.region_id " .
                            "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS t ON o.inv_consignee_city = t.region_id " .
                            "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS d ON o.inv_consignee_district = d.region_id " .
                        "WHERE o.order_id = '$order_id'";
            return $GLOBALS['db']->getOne($sql);
        }
        else
        {
            return ' ';
        }
    }
    else
    {
        return ' ';
    }
}
ob_end_flush();
?>