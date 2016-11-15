<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ($_REQUEST['act'] == 'order_excel')
{
    // 载入入驻商
    $sql_supplier = "SELECT supplier_id, supplier_name FROM " . $GLOBALS['ecs']->table("supplier") . " WHERE status = '1' ORDER BY supplier_id";
    $res_supplier = $db->query($sql_supplier);
    while($row_supplier=$db->fetchRow($res_supplier))
    {
        $supplier_list .= "<option value='" . $row_supplier['supplier_id'] . "'>" . $row_supplier['supplier_name'] . "</option>";
    }
    $smarty->assign('supplier_list', $supplier_list);

    // 载入国家
    $smarty->assign('country_list', get_regions());
    $smarty->assign('ur_here', $_LANG['12_order_excel']);
    $smarty->display('excel.htm');
}
elseif($_REQUEST['act'] == 'excel')
{
    $filename='orderexcel';
    header("Content-type: application/vnd.ms-excel; charset=gbk");
    header("Content-Disposition: attachment; filename=$filename.xls");

    // 订单状态
    $order_status = intval($_REQUEST['order_status']);
    // 下单开始时间
    $start_time = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
    // 下单结束时间
    $end_time = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);
    // 起始订单号
    $order_sn1 = $_REQUEST['order_sn1'];
    // 终了订单号
    $order_sn2 = $_REQUEST['order_sn2'];
    // 国家
    $country = empty($_REQUEST['country']) ? 0 : intval($_REQUEST['country']);
    // 省
    $province = empty($_REQUEST['province']) ? 0 : intval($_REQUEST['province']);
    // 市
    $city = empty($_REQUEST['city']) ? 0 : intval($_REQUEST['city']);
    // 区
    $district = empty($_REQUEST['district']) ? 0 : intval($_REQUEST['district']);
    // 店铺
    $shop_id = $_REQUEST['shop_id'];
    // 入驻商
    $suppliers_id = $_REQUEST['suppliers_id'];

//    $where = 'WHERE o.supplier_id=0 ';
    $where = 'WHERE 1 ';

    if($order_status >= 0)
    {
        $where .= " AND o.order_status = '$order_status' ";
    }

    if($start_time != '' && $end_time != '')
    {
        $where .= " AND o.add_time >= '$start_time' AND o.add_time <= '$end_time' ";
    }

    if($order_sn1 != '' && $order_sn2 != '')
    {
        $where .= " AND o.order_sn >= '$order_sn1' AND o.order_sn <= '$order_sn2' ";
    }

    if ($country > 0)
    {
        $where .= " AND o.country = $country ";
    }

    if ($province > 0)
    {
        $where .= " AND o.province = $province ";
    }

    if ($city > 0)
    {
        $where .= " AND o.city = $city ";
    }

    if ($district > 0)
    {
        $where .= " AND o.district = $district ";
    }

    if ($shop_id == 0)
    {
        // 自营
        $where .= 'AND o.supplier_id = 0 ';
    }
    else if ($shop_id > 0)
    {
        if ($suppliers_id < 0)
        {
            // 所有入驻商
            $where .= 'AND o.supplier_id <> 0 ';
        }
        else
        {
            // 选择入驻商
            $where .= 'AND o.supplier_id = ' . $suppliers_id;
        }
    }

//    $sql="select o.order_sn,o.consignee,o.address,o.tel,o.add_time,o.shipping_name,o.pay_name,g.goods_name,g.goods_attr,g.goods_number,g.goods_sn,g.market_price,g.goods_price ,g.goods_number,g.goods_price*g.goods_number as money,u.user_name from  ". $GLOBALS['ecs']->table('order_info').
//    " as o left join " . $GLOBALS['ecs']->table('users')." as u on o.user_id=u.user_id "."left join  ". $GLOBALS['ecs']->table('order_goods')." as g on o.order_id=g.order_id  $where ";
    $sql = "SELECT "
        . "o.order_sn, " // 订单号
        . "o.is_pickup, " // 订单类型
        . "o.add_time, " // 下单时间
        . "o.froms, " // 订单来源
        . "o.order_status, " // 订单状态
        . "o.consignee, " // 收货人姓名
        . "o.address, " // 收货人地址
        . "o.supplier_id, " // 商家
        . "o.tel, " // 收货人电话
        . "o.mobile, " // 收货人手机
        . "o.pay_name, " // 支付方式
        . "o.shipping_name, " // 配送方式
        . "g.goods_name, " // 商品名称
        . "g.goods_sn, " // 商品货号
        . "g.goods_price, " // 商品价格
        . "g.goods_number, " // 购买数量
        . "g.goods_attr, " // 商品属性
        . "g.goods_price * g.goods_number money, " // 价格小计
        . "u.user_name, " // 用户名
        . "b.brand_name, "
        . "s.supplier_name " // 店铺名
        . "FROM " . $GLOBALS['ecs']->table('order_info')
        . " AS o LEFT JOIN " . $GLOBALS['ecs']->table('users')
        . " AS u ON o.user_id = u.user_id "
        . " LEFT JOIN  " . $GLOBALS['ecs']->table('order_goods')
        . " AS g ON o.order_id = g.order_id "
        . " LEFT JOIN " . $GLOBALS['ecs']->table('goods')
        . " AS go ON g.goods_id = go.goods_id "
        . " LEFT JOIN " . $GLOBALS['ecs']->table('brand')
        . " AS b ON go.brand_id = b.brand_id "
        . " LEFT JOIN " . $GLOBALS['ecs']->table('supplier')
        . "AS s ON s.supplier_id = o.supplier_id "
        . $where ;

    $res=$db->getAll($sql);
    $list = array();
    foreach($res as $key => $rows)
    {
        // 订单状态
        if ($rows['order_status'] == 0)
        {
            $list[$key]['order_status'] = '未确认';
        }
        else if ($rows['order_status'] == 1)
        {
            $list[$key]['order_status'] = '已确认';
        }
        else if ($rows['order_status'] == 2)
        {
            $list[$key]['order_status'] = '已取消';
        }
        else if ($rows['order_status'] == 3)
        {
            $list[$key]['order_status'] = '无效';
        }
        else if ($rows['order_status'] == 4)
        {
            $list[$key]['order_status'] = '退货';
        }
        else if ($rows['order_status'] == 6)
        {
            $list[$key]['order_status'] = '部分发货';
        }
        else if ($rows['order_status'] == 100)
        {
            $list[$key]['order_status'] = '待付款';
        }
        else if ($rows['order_status'] == 101)
        {
            $list[$key]['order_status'] = '待发货';
        }
        else if ($rows['order_status'] == 102)
        {
            $list[$key]['order_status'] = '已完成';
        }
        else
        {
            $$list[$key]['order_status'] = '';
        }

        /* 取得区域名 */
        $sql = "SELECT concat('', '', IFNULL(p.region_name, ''), " .
            "'', IFNULL(t.region_name, ''), '', IFNULL(d.region_name, '')) AS region " .
            "FROM " . $ecs->table('order_info') . " AS o " .
            "LEFT JOIN " . $ecs->table('region') . " AS c ON o.country = c.region_id " .
            "LEFT JOIN " . $ecs->table('region') . " AS p ON o.province = p.region_id " .
            "LEFT JOIN " . $ecs->table('region') . " AS t ON o.city = t.region_id " .
            "LEFT JOIN " . $ecs->table('region') . " AS d ON o.district = d.region_id " .
            "WHERE o.order_sn = {$rows['order_sn']}";
        $address = $db->getOne($sql) . ' ' . $rows['address'];

        $list[$key]['order_sn'] = $rows['order_sn'];
        $list[$key]['is_pickup'] = $rows['is_pickup'];
        $list[$key]['add_time'] = local_date('y-m-d H:i', $rows['add_time']);
        $list[$key]['froms'] = $rows['froms'];
        $list[$key]['consignee'] = $rows['consignee'];
        $list[$key]['address'] = $address;
        $list[$key]['supplier_id'] = $rows['supplier_id'];
        $list[$key]['tel'] = empty($rows['mobile']) ? $rows['tel'] : $rows['mobile'];
        $list[$key]['pay_name'] = $rows['pay_name'];
        $list[$key]['shipping_name'] = $rows['shipping_name'];
        $list[$key]['goods_name'] = $rows['goods_name'];
        $list[$key]['goods_sn'] = $rows['goods_sn'];
        $list[$key]['goods_price'] = $rows['goods_price'];
        $list[$key]['goods_number'] = $rows['goods_number'];
        $list[$key]['goods_attr'] = $rows['goods_attr'];
        $list[$key]['money'] = $rows['money'];
        $list[$key]['user_name'] = $rows['user_name'];
        $list[$key]['brand_name'] = $rows['brand_name'];
        $list[$key]['supplier_name'] = $rows['supplier_name'];
    }

    foreach($list as $key => $val)
    {
//        $data .= "<table border='1'>";
//        $data .= "<tr><td colspan='2'>订单号：".$val['order_sn']."</td><td>用户名：".$val['user_name']."</td><td colspan='2'>收货人：".$val['consignee']."</td><td colspan='2'>联系电话：".$val['tel']."</td></tr>";
//        $data .= "<tr><td colspan='5'>送货地址：".$val['address']."</td><td colspan='2'>下单时间：".$val['add_time']."</td></tr>";
//        $data .= "<tr bgcolor='#999999'><th>序号</th><th>货号</th><th>商品名称</th><th>市场价</th><th>本店价</th><th>购买数量</th><th>小计</th></tr>";
//        $data .= "<tr><th>1</th><th>".$val['goods_sn']."</th><th>".$val['goods_name']."</th><th>".$val['market_price']."</th><th>".$val['goods_price']."</th><th>".$val['goods_number']."</th><th>".$val['money']."</th></tr>";
//        $data .= "</table>";
//        $data .= "<br>";

        // 序号计数用
        $count++;
        if ($val['order_sn'] != $last_order_sn)
        {
            $count = 1;
            $data .= "</table>";
            $data .= "<br>";
            $data .= "<table border='1'>";
            $data .= "<tr>"
                . "<td colspan='2'>订单号：" . $val['order_sn'] . "</td>"
                . "<td>订单类型：" . ($val['is_pickup'] == 1 ? '自提订单' : '一般订单') . "</td>"
                . "<td>订单来源：" . $val['froms'] . "</td>"
                . "<td>订单状态：" . $val['order_status'] . "</td>"
                . "<td>收货人：".$val['consignee'] . "</td>"
                . "<td colspan='2'>联系电话：" . $val['tel'] . "</td>"
                . "</tr>";

            $data .= "<tr>"
                . "<td colspan='2'>送货地址：" . $val['address'] . "</td>"
                . "<td colspan='1'>支付方式：" . $val['pay_name'] . "</td>"
                . "<td colspan='1'>配送方式：" . $val['shipping_name'] . "</td>"
                . "<td colspan='2'>商家：" . ($val['supplier_id'] == 0 ? '平台自营' : $val['supplier_name']) . "</td>"
                . "<td colspan='2'>下单时间：" . $val['add_time'] . "</td>"
                . "</tr>";

            $data .= "<tr>"
                . "<th bgcolor='#999999'>序号</th>"
                . "<th bgcolor='#999999'>货号</th>"
                . "<th bgcolor='#999999'>商品名称</th>"
                . "<th bgcolor='#999999'>品牌</th>"
                . "<th bgcolor='#999999'>属性</th>"
                . "<th bgcolor='#999999'>价格</th>"
                . "<th bgcolor='#999999'>购买数量</th>"
                . "<th bgcolor='#999999'>小计</th>"
                . "</tr>";

            $data .= "<tr><th>$count</th><th>" . $val['goods_sn']
                . "</th><th>" . $val['goods_name']
                . "</th><th>" . $val['brand_name']
                . "</th><th>" . $val['goods_attr']
                . "</th><th>" . $val['goods_price']
                . "</th><th>" . $val['goods_number']
                . "</th><th>" . $val['money']
                . "</th></tr>";
        }
        else
        {
            $data .= "<tr><th>$count</th><th>" . $val['goods_sn']
                . "</th><th>" . $val['goods_name']
                . "</th><th>" . $val['brand_name']
                . "</th><th>" . $val['goods_attr']
                . "</th><th>" . $val['goods_price']
                . "</th><th>" . $val['goods_number']
                . "</th><th>" . $val['money']
                . "</th></tr>";
        }
        $last_order_sn = $val['order_sn'];
    }

    if (EC_CHARSET != 'gbk')
    {
        echo ecs_iconv(EC_CHARSET, 'gbk', $data) . "\t";
    }
    else
    {
        echo $data. "\t";
    }
}

?>