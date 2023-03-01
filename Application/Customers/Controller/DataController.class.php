<?php
namespace Customers\Controller;

use Think\Controller;
use Customers\Model\CustomersModel;

class DataController extends Controller {

    private $num_package = 1000; //每个包的客户数

    public function downAction($site_id) {
        $page = I('page', 1);
        $where = array();
        Vendor('phpRPC.phprpc_client');
        $interface_url = $this->getInterfaceUrl($site_id);
        $client = new \PHPRPC_Client($interface_url . '?m=Server&c=Customers');
        $result = $client->down($site_id, $where, $page, $this->num_package);
        if (is_object($result) && get_class($result) == 'PHPRPC_Error') {
            $this->ajaxReturn(array('status' => 0, 'error' => $result->toString()), 'JSON');
        }
        $data = uncompress_decode($result);
//        var_dump($data);
        if (is_array($data)) {
            $customer = new CustomersModel();
            foreach ($data as $_data) {
                $customer->relation(array('customers_basket', 'address_book'))->add($_data, array(), true);
            }
            $this->ajaxReturn(array('status' => 1), 'JSON');
        } else {
            $this->ajaxReturn(array('status' => 0, 'error' => '无法识别下载的数据!'), 'JSON');
        }
    }

    public function down2Action($site_id, $date) {
        $page = I('get.page', 0);
        $site_row = D('site')->field('site_index,new_saas')->find($site_id);
        $token = $site_row['new_saas'] == 1 ? C('umieshop_token') : C('storeforfast_token');
        $url = $site_row['site_index'].'/api_v1_customers/get?Token='.$token.'&lastDate='.$date;
        if($page)
            $url = $url.'&Page='.$page;
        $sign = md5(parse_url($url, PHP_URL_HOST).$token.'api_v1_customers/get');
        $url .= '&Sign='.$sign;

        $timestamp_today   = strtotime(date('Y-m-d'));//今天的日期
        $timestamp_7days   = $timestamp_today-7*60*60*24;//最近7天日期
        $timestamp_request = strtotime($date);//请求的日期
        if($timestamp_request<=$timestamp_today && $timestamp_request>=$timestamp_7days){
            $cache_expired = 60*60;//最近7天缓存1小时
        }else{
            $cache_expired = 60*60*24;//非当天缓存24小时
        }
        if(!file_exists(DIR_FS_TEMP . 'json/')) mkdir (DIR_FS_TEMP . 'json/');
        if(!file_exists(DIR_FS_TEMP . 'json/'.$date.'/')) mkdir (DIR_FS_TEMP . 'json/'.$date.'/');
        $cache_file = DIR_FS_TEMP . 'json/'.$date.'/'. md5($url).'.json';

        $data = array();
        if(file_exists($cache_file)){
            $cache_filemtime = filemtime($cache_file);
            if(time()-$cache_filemtime<$cache_expired){//缓存未过期
                $json_string = file_get_contents($cache_file);
                $data = json_decode($json_string, true);
            }
        }

        if(empty($data)){
            vendor('Request.Requests');
            \Requests::register_autoloader();
            $response = \Requests::get($url, array(), array('timeout'=>60, 'verify'=>false));
            $data = json_decode($response->body, true);
            if(is_array($data) && sizeof($data) && $data['code']=='0000'){
                file_put_contents($cache_file, $response->body);
            }
        }

        if(is_array($data) && isset($data['code'])){
            $customer = new CustomersModel();
            if($data['code']=='0000'){
                foreach ($data['data'] as $v){
                    $cdata = array(
                        'site_id' => $site_id,
                        'customers_id' => $v['customer_id'],
                        'customers_firstname' => $v['firstname'],
                        'customers_lastname' => $v['lastname'],
                        'customers_email_address' => $v['email'],
                        'customers_info_date_account_created' => $v['registered_time']
                    );
                    foreach ($v['address'] as $a){
                        $cdata['address_book'][] = array(
                            'site_id' => $site_id,
                            'address_book_id' => $a['address_id'],
                            'customers_id' => $v['customer_id'],
                            'entry_country' => $a['country'],
                            'entry_state' => $a['region'],
                            'entry_firstname' => $a['shopping_firstname'],
                            'entry_lastname' => $a['shopping_lastname'],
                            'entry_city' => $a['city'],
                            'entry_street_address' => $a['address'],
                            'entry_postcode' => $a['postcode'],
                            'entry_phone' => $a['telephone']
                        );
                    }
                    $customer->relation(array('address_book'))->add($cdata, array(), true);
                }
                if($data['total_page'] > $data['page']){
                    $data['page']++;
                }else{
                    $data['page'] = 0;
                }
                $this->ajaxReturn(array('status' => 1, 'page'=>$data['page']), 'JSON');
            }else{
                $this->ajaxReturn(array('status' => 0, 'error'=>$data['msg']), 'JSON');
            }
        }else{
            $this->ajaxReturn(array('status' => 0, 'error'=>'接口请求出错'), 'JSON');
        }
    }

    public function cartAction($site_id, $date){
        $site_row = D('site')->field('site_index,new_saas')->find($site_id);
        $token = $site_row['new_saas'] == 1 ? C('umieshop_token') : C('storeforfast_token');
        $url = rtrim($site_row['site_index'], ' /').'/api_v1_customers/getCheckoutCart';

        $sign = md5(parse_url($url, PHP_URL_HOST).$token.'api_v1_customers/getCheckoutCart');
        $sign = strtoupper($sign);
        $post_data = array(
            'Token'=>$token,
            'StartDate'=>date('Y-m-d 00:00:00', strtotime($date)),
            'EndDate'=>date('Y-m-d 23:59:59', strtotime($date)),
            'Sign'=>$sign
        );
        $timestamp_today   = strtotime(date('Y-m-d'));//今天的日期
        $timestamp_7days   = $timestamp_today-7*60*60*24;//最近7天日期
        $timestamp_request = strtotime($date);//请求的日期
        if($timestamp_request<=$timestamp_today && $timestamp_request>=$timestamp_7days){
            $cache_expired = 60*60;//最近7天缓存1小时
        }else{
            $cache_expired = 60*60*24;//非最近7天缓存12小时
        }
        if(!file_exists(DIR_FS_TEMP . 'json/')) mkdir (DIR_FS_TEMP . 'json/');
        if(!file_exists(DIR_FS_TEMP . 'json/'.$date.'/')) mkdir (DIR_FS_TEMP . 'json/'.$date.'/');
        $cache_file = DIR_FS_TEMP . 'json/'.$date.'/'. md5($url).'.json';
        $data = array();
        
        if(file_exists($cache_file)){
            $cache_filemtime = filemtime($cache_file);
            if(time()-$cache_filemtime<$cache_expired){//缓存未过期
                $json_string = file_get_contents($cache_file);
                $data = json_decode($json_string, true);
            }
        }
        if(empty($data)){
            vendor('Request.Requests');       
            \Requests::register_autoloader();
            $response = \Requests::post($url, array(), $post_data, array('timeout'=>60, 'verify'=>false));
            $data = json_decode($response->body, true);
            if(is_array($data) && sizeof($data) && $data['code']=='0000'){
                file_put_contents($cache_file, $response->body);
            }
        }
        if(is_array($data) && isset($data['code']) && $data['code']=='0000'){
            $max_customers_basket_id = M('customers_basket')->max('customers_basket_id');
            if(empty($max_customers_basket_id))
                $max_customers_basket_id = 1;
            else
                $max_customers_basket_id += 1;
            foreach ($data['data'] as $entry){
                $email = $entry['email'];
                $check_customer = M('customers')->where(array('customers_email_address'=>$email, 'site_id'=>$site_id))->find();
                $customers_id = 0;
                if(empty($check_customer)){
                    continue;
                }
                $cart_content = $entry['product_list'];
                   M('customers_basket')->where(array('customers_id'=>$customers_id, 'site_id'=>$site_id))->save(array('status'=>0));//从购物车下架
                foreach($cart_content as $cart_entry){
                    $customers_basket_attributes = json_encode($cart_entry['options']);
                    $products_id = md5($cart_entry['sku'].$customers_basket_attributes);                    
                    $check_cart = M('customers_basket')->where(array('customers_id'=>$customers_id, 'site_id'=>$site_id, 'products_id'=>$products_id))->find();
                    $cart_data = array(
                        'site_id'=>$site_id,
                        'customers_id'=>$customers_id,
                        'products_id'=>$products_id,  
                        'products_sku'=>$cart_entry['sku'],
                        'customers_basket_quantity'=>$cart_entry['qty'],
                        'customers_basket_attributes'=>$customers_basket_attributes,
                        'products_name'=>$cart_entry['name'],
                        'status'=>1,
                    );
                    if($check_cart){
                         M('customers_basket')->where(array('customers_id'=>$customers_id, 'site_id'=>$site_id, 'products_id'=>$products_id))->save($cart_data);              
                    }else{
                        $cart_data['customers_basket_id'] = $max_customers_basket_id;
                        M('customers_basket')->add($cart_data);
                        $max_customers_basket_id++;
                    }
                }
            }
            $this->ajaxReturn(array('status' => 1), 'JSON');
        }else{
            $this->ajaxReturn(array('status' => 0, 'error'=>$data['msg']), 'JSON');
        }
    }
    
    private function _upload($data_upload, $site_id){
        $data_string = encode_compress($data_upload);
        $upload_file = DIR_FS_TEMP . time() . '_update_customers.txt';
        $f = fopen($upload_file, 'w');
        fwrite($f, $data_string);
        fclose($f);

        if(version_compare(PHP_VERSION, '5.5.0') >= 0){
            $data = array(
                'file' => new \CURLFile($upload_file),
                'site_id'=>$site_id,
                'status'=>(int)$status,
            );
        }else{
            $data = array(
                'file' => '@'.$upload_file,
                'site_id'=>$site_id,
                'status'=>(int)$status,
            );
        }
        
        vendor('Request.Requests');
        
        \Requests::register_autoloader();
        $interface_url = $this->getInterfaceUrl($site_id);
        $response = \Requests::post($interface_url . '?m=Server&c=CustomerUpdate', array(), $data, array('timeout'=>60));

        $result = json_decode($response->body, true);
        return is_array($result)?$result:false;
    }
    
    public function uploadAction(){
        $customers = I('customers');
        $data_upload = array();
        foreach($customers as $entry){
            list($site_id, $customer_id) = explode('-', $entry);
            $row = M('customers')->where(array('site_id'=>$site_id, 'customers_id'=>$customer_id))->find();
            $address = M('address_book')->where(array('site_id'=>$site_id, 'customers_id'=>$customer_id))->select();
            $row['address_book'] = $address;
            $data_upload[] = $row;
        }
        $result = $this->_upload($data_upload, I('site_id'));
        if($result===false)
            $result = array('status'=>0, 'error'=>'上传超时');
        $this->ajaxReturn($result, 'JSON');
    }
    
    public function upload2Action(){
        $customer = new CustomersModel();
        $where = array();
        $fields = array('s.site_name,c.*');
        $page_data = array();
        if (is_array(I('site_id')) && sizeof(I('site_id'))) {
            $where['c.site_id'] = array('IN', I('site_id'));
            $page_data['site_id'] = implode(',', I('site_id'));
        }elseif(I('site_id')!=''){
            $site_ids = explode(',', I('site_id'));
            $where['c.site_id'] = array('IN', $site_ids);
            $page_data['site_id'] = I('site_id');
        }
        if (I('customer_email')!='') {
            $where['c.customers_email_address'] = I('customer_email');
            $page_data['customer_email'] = I('customer_email');
        }
        if (I('type', '')!='') {
            $where['s.type'] = I('type');
            $page_data['type'] = I('type');
        }
        if (I('register_time_start') !== '' && I('register_time_end') !== '') {
            $time_start = date('Y-m-d 0:0:0', strtotime(I('register_time_start')));
            $time_end   = date('Y-m-d 23:59:59', strtotime(I('register_time_end')));
            $where['customers_info_date_account_created'] = array('between', array($time_start, $time_end));
            $page_data['register_time_start'] = I('register_time_start');
            $page_data['register_time_end'] = I('register_time_end');            
        }else{
            $time_start = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d")-3,   date("Y")) );
            $time_end   = date('Y-m-d');
            $where['customers_info_date_account_created'] = array('between', array($time_start.' 0:0:0', $time_end.' 23:59:59'));
            $page_data['register_time_start'] = $time_start;
            $page_data['register_time_end']   = $time_end;            
        }
        $page = I('page', 1);//当前页码
        $num  = 100;//每页显示订单数    
        
        
        if(I('order_status')==='null'){//无购物记录
            $fields[] = 'o.orders_id';
            $join = array('__SITE__ s ON s.site_id=c.site_id','LEFT JOIN __ORDERS__ o ON o.customers_email_address=c.customers_email_address');
            $list = $customer->relation(array('address_book'))->alias('c')->field($fields)->join($join)->where($where)->group('c.customers_email_address')->having('SUM(IF(o.orders_id IS NULL, 0, 1))=0')->page($page, $num)->order('customers_info_date_of_last_logon desc,customers_info_date_account_created desc,customers_info_number_of_logons desc')->select();      
            $sql  = $customer->alias('c')->field($fields)->join($join)->where($where)->group('c.customers_email_address')->having('SUM(IF(o.orders_id IS NULL, 0, 1))=0')->select(false);   
            $count = $customer->db()->query('select count(*) as num from ('.$sql.') as t');
            $count = $count[0]['num'];      
        }elseif(I('order_status')==='1' || I('order_status')==='2'){//至少成功*单
            $join = array('__SITE__ s ON s.site_id=c.site_id','__ORDERS__ o ON o.customers_email_address=c.customers_email_address', '__ORDERS_REMARK__ o_r ON o.orders_id=o_r.orders_id AND o.site_id=o_r.site_id');
            $s = I('order_status')-1;
            $succss_status = array('待订货', '已发货', '已确认付款', '待发货', '已订货', '部分发货');
            $list = $customer->relation(array('address_book'))->alias('c')->field($fields)->join($join)->where($where)->group('c.customers_email_address')->having('SUM(IF(o_r.order_status_remark IN (\''.implode('\',\'', $succss_status).'\'), 1, 0))>'.$s)->page($page, $num)->order('customers_info_date_of_last_logon desc,customers_info_date_account_created desc,customers_info_number_of_logons desc')->select();      
            $sql  = $customer->relation(false)->alias('c')->field($fields)->join($join)->where($where)->group('c.customers_email_address')->having('SUM(IF(o_r.order_status_remark IN (\''.implode('\',\'', $succss_status).'\'), 1, 0))>'.$s)->select(false);
            $count = $customer->db()->query('select count(*) as num from ('.$sql.') as t');
            $count = $count[0]['num'];
        }elseif(I('order_status')==='-1'){//只有失败记录的客户
            $join = array('__SITE__ s ON s.site_id=c.site_id','__ORDERS__ o ON o.customers_email_address=c.customers_email_address', '__ORDERS_REMARK__ o_r ON o.orders_id=o_r.orders_id AND o.site_id=o_r.site_id');
            $not_failure_status = array('待订货', '付款确认中', '已确认付款', '待发货', '已订货', '部分发货', '已发货',  '订单取消', '老订单');
            $list = $customer->relation(array('address_book'))->alias('c')->field($fields)->join($join)->where($where)->group('c.customers_email_address')->having('SUM(IF(o_r.order_status_remark IN (\''.implode('\',\'', $not_failure_status).'\'), 1, 0))=0')->page($page, $num)->order('customers_info_date_of_last_logon desc,customers_info_date_account_created desc,customers_info_number_of_logons desc')->select();      
            $sql  = $customer->relation(false)->alias('c')->field($fields)->join($join)->where($where)->group('c.customers_email_address')->having('SUM(IF(o_r.order_status_remark IN (\''.implode('\',\'', $not_failure_status).'\'), 1, 0))=0')->select(false);
            $count = $customer->db()->query('select count(*) as num from ('.$sql.') as t');
            $count = $count[0]['num'];
        }else{
            $list = $customer->relation(array('address_book'))->alias('c')->field($fields)->join(array('__SITE__ s ON s.site_id=c.site_id'))->where($where)->order('customers_info_date_of_last_logon desc,customers_info_date_account_created desc,customers_info_number_of_logons desc')->page($page, $num)->select();
            $count = $customer->alias('c')->field($fields)->join(array('__SITE__ s ON s.site_id=c.site_id'))->where($where)->count();   
        }      
       
        $limit = $num;
        
        if(I('action')=='count'){
            $num_page = ceil($count/$limit);
            $page_data['page'] = 1;
            if($num_page==0)
                $tip = '当前筛选条件没有可上传的客户数据!';
            else{
                $tip = '当前筛选条件共找到'.$count.'个客户记录!系统将分'.$num_page.'批次上传,每批上传'.$limit.'个';
            }
            $this->ajaxReturn(array('tip'=>$tip, 'num_page'=>$num_page), 'JSON');
        }elseif(I('page', false)){
            $num_page = ceil($count/$limit);   
            if($page<=$num_page){
                
                $result = $this->_upload($list, I('upload_site_id'));
                if($result===false){
                    $success = false;
                    $tip = '上传超时';
                }else{
                    if($result['status']){
                        $success = true;
                        $tip = '第'.I('page').'批客户上传成功!';
                        foreach($result['result'] as $entry){
                            if(isset($entry['error'])){
                                $success = false;
                                $tip = '第'.I('page').'批客户上传失败(可能有部分客户上传不成功)!';
                                break;
                            }
                        }
                    } else {
                        $success = false;
                        $tip = '第'.I('page').'批客户上传失败!';
                    }
                }
                $this->ajaxReturn(array('tip'=>$tip, 'cur_page'=>I('page'), 'success'=>$success), 'JSON');
            }else{
                $this->ajaxReturn(array('tip'=>'页码错误', 'cur_page'=>''), 'JSON');
            }            
        }
    }    
    
    
    public function getPackageAction($site_id) {
        Vendor('phpRPC.phprpc_client');
        $interface_url = $this->getInterfaceUrl($site_id);
        $client = new \PHPRPC_Client($interface_url . '?m=Server&c=Customers');
        $total = $client->count($site_id);
        if (is_object($total) && get_class($total) == 'PHPRPC_Error') {
            $this->ajaxReturn(array('status' => 0, 'error' => $total->toString()), 'JSON');
        }
        $num_page = ceil($total / $this->num_package);
        $num_sys = D('customers')->where(array('site_id' => $site_id))->count();
        $page_down = ceil(($total - $num_sys) / $this->num_package);
        if ($page_down == 0) {
            $string_page = $num_page;
        } else {
            for ($i = 0; $i < $page_down; $i++) {
                $string_page .= ',' . ($num_page - $i);
            }
            $string_page = substr($string_page, 1);
        }
//    $this->ajaxReturn(array('status'=>1, 'num_page'=>$num_page, 'total'=>$total), 'JSON');
        $this->ajaxReturn(array('status' => 1, 'num_page' => $num_page, 'total' => $total, 'total_sys' => $num_sys, 'page_down' => $string_page), 'JSON');
    }

    private function getInterfaceUrl($site_id) {
        $site_row = D('site')->field('site_interface')->find($site_id);

        return $site_row['site_interface'];
    }

}
