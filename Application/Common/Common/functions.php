<?php

function showCurrency($money) {
    if ($money == 0) {
        return 0;
    } else {
        return '&yen;' . $money;
    }
}

function showTaobaoNo($taobao_no) {
    if (empty($taobao_no)) {
        return '';
    } else {
        return '<a href="http://buyer.trade.taobao.com/trade/detail/trade_item_detail.htm?bizOrderId=' . $taobao_no . '" target="_blank">' . $taobao_no . '</a>';
    }
}

function showExpressNo($express_no, $link = false) {
    if (empty($express_no)) {
        return '';
    } else {
        if ($link == true) {
            return U('Order/ExpressDelivery/query', array('no' => $express_no));
        } else {
            return '<a href="' . U('Order/ExpressDelivery/query', array('no' => $express_no)) . '" target="_blank">' . $express_no . '</a>';
        }
    }
}

function getProductLink($domain, $products_id) {
    return $domain . '/index.php?main_page=product_info&products_id=' . (int) $products_id;
}

function generate_order_no() {
    $orders_remark = D('orders_remark');
    $r = $orders_remark->where(array('order_no' => array('like', date('ymd') . '%')))
            ->order('order_no desc')
            ->field('order_no')
            ->find();
    if ($r == null) {
        return date('ymd') . '01';
    } else {
        $no = preg_replace_callback('/\d{2}$/', function ($m) {
            return sprintf('%02s', $m[0] + 1);
        }, $r['order_no']);
        return $no;
    }
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 4;

    // 密匙
    $key = md5($key ? $key : '124567');

    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) :
            substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
    //解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
            sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        // 验证数据有效性，请看未加密明文的格式
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
                substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

function encode_compress($data) {
    $json_data = json_encode($data);
    $gz_data = gzcompress($json_data);

    return $gz_data;
}

function uncompress_decode($string) {
    $json_string = gzuncompress($string);
    $data = json_decode($json_string, true);

    return $data;
}

function html_compress($html) {
    $html = preg_replace('~[\n\r]~', '', $html);
    $html = preg_replace('~\s+~', ' ', $html);
    return $html;
}

function makeDir($dirName, $rights=0777){
    $dirs = explode('/', $dirName);
    $dir='';
    foreach ($dirs as $part) {
        $dir.=$part.'/';
        if (!is_dir($dir) && strlen($dir)>0)
            mkdir($dir, $rights);
    }
    return true;
}
//解析订单号
function parseZencartNo($no){
    if (!preg_match('~-~', $no) && preg_match('~([a-zA-Z\d]+)([a-zA-Z])(\d+)$~', $no, $match)) {//单号解析
        $order_no_prefix = $match[1].$match[2];
        $zencart_order_no = $match[3];

        return array('orders_prefix'=>$order_no_prefix, 'orders_id'=>$zencart_order_no);
    }else
        return false;
}

function hasComment($site_id, $order_id){
    $row = D('orders_status_history')->where(array('site_id'=>$site_id, 'orders_id'=>$order_id))->order('date_added ASC')->field('comments')->find();

    if(empty($row['comments'])){
        return false;
    }else{
        return $row['comments'];
    }
}

//清空目录,包括子文件夹
function delDirAndFile($path, $level=0) {
    $handle = opendir($path);
    if ($handle) {
        while (false !== ( $item = readdir($handle) )) {
            if ($item != "." && $item != ".."){
                $level++;
                is_dir("$path/$item") ? delDirAndFile("$path/$item", $level) : unlink("$path/$item");
            }
        }
        closedir($handle);
        if ($level>0)
            return rmdir($path);
    }
}

function risk_order($order_info){
    
    $where = array(
        '_complex'=>array(
            '_logic'=>'OR',
            'risk_type&risk_value'=>array(0, $order_info['customers_email_address'],'_multi'=>true),
            '_complex'=>array(
                '_logic'=>'OR',
                'risk_type&risk_value'=>array(1, $order_info['customers_telephone'],'_multi'=>true),   
                '_complex'=>array(
                    '_logic'=>'OR',
                    'risk_type&risk_value'=>array(2, array('in', array($order_info['delivery_name'],$order_info['billing_name'],$order_info['customers_name'])),'_multi'=>true),   
                )
            )
        )
    );
    $check_rule = D('risk_rule')->where($where)->find();
    return $check_rule==false?false:true;
}