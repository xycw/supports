<?php
namespace Order\Controller;
use Common\Controller\CommonController;
use Order\Model\OrderModel;
class DataController extends CommonController {
    private $num_package = 50; //每个包的订单数
    public function orderDataAction($site_id, $order_id = 0) {
        $where = array();
        $site_info = D('site')->where(array('site_id'=>$site_id))->field('order_no_prefix,system_cms')->find();	
        if ($order_id != 0) {
            M('Orders_products')->where(array('site_id'=>$site_id,'orders_id'=>$order_id))->delete();
            $where['orders_id'] = $order_id;
            $order_file = $this->_get_order_dir($site_id, $order_id).$site_info['order_no_prefix'].$order_id.'.docx';			
            if(file_exists($order_file)) @unlink($order_file);
        }
        $page = I('get.page', 1);
        Vendor('phpRPC.phprpc_client');
        $interface_url = $this->getInterfaceUrl($site_id);
        $client = new \PHPRPC_Client($interface_url . '?m=Server&c=Order');
        $result = $client->order($site_id, $where, $page, $this->num_package);
        if (is_object($result) && get_class($result) == 'PHPRPC_Error') {
            $this->ajaxReturn(array('status' => 0, 'error'=>$result->toString()), 'JSON');
        }
        $data = uncompress_decode($result);
        if (is_array($data)) {
            $order = new OrderModel();
            $order_fields = $order->getDbFields();
            $order_fields[] = 'product';
            $order_fields[] = 'history';
            $order_fields[] = 'attribute';
            $model_orders_remark = D('orders_remark');
            foreach ($data as $_data) {
                if(isset($_data['product']) && is_array($_data['product'])){
                    foreach($_data['product'] as $k=>$product){
        				if(isset($product['select_pic'])){
        				    if(!empty($product['select_pic']))
        					    $_data['product'][$k]['products_image'] = preg_replace('~^images/~', '', $product['select_pic']);
        					unset($_data['product'][$k]['select_pic']);
        				}   
                    }
                }
                
                foreach($_data as $k=>$v){
                    if(!in_array($k, $order_fields)){
                        unset($_data[$k]);
                    }
                }
                
                $timestamp_date_purchased = strtotime($_data['date_purchased']) + ($site_info['system_cms'] == 'easyshop' ? 0 : 28800);
                $_data['date_purchased'] = date('Y-m-d H:i:s', $timestamp_date_purchased);
                $order->relation(array('history'))->add($_data, array(), true);
                
                /*此段代码先暂时开启，后面订货稳定了，在关闭 start */
                /*
                if(preg_match('~2020-12-31~', $_data['date_purchased']) || preg_match('~2020-12-30~', $_data['date_purchased'])){
                    M('orders_products')->where(array('site_id'=>$site_id, 'orders_id'=>$_data['orders_id']))->delete();
                    M('orders_products_attributes')->where(array('site_id'=>$site_id, 'orders_id'=>$_data['orders_id']))->delete();
                    M('orders_products_remark')->where(array('site_id'=>$site_id, 'orders_id'=>$_data['orders_id']))->delete();
                }
                */
                /*此段代码先暂时开启，后面订货稳定了，在关闭 end*/
                
                $check_orders_products = M('orders_products')->where(array('site_id'=>$site_id, 'orders_id'=>$_data['orders_id']))->select();
                if(empty($check_orders_products)){
                    foreach ($_data['product'] as $entry_product){
                        $max_orders_products_id = M('orders_products')->where(array('site_id'=>$site_id))->max('orders_products_id');
                        if(empty($max_orders_products_id))
                            $orders_products_id = 1;
                        else
                            $orders_products_id = $max_orders_products_id+1;
                        $entry_product['orders_products_id'] = $orders_products_id;
                        
                        M('orders_products')->add($entry_product);
                        //订单项目归档 start
                        $check_model = M('orders_products')->alias('op')->join(array('__ORDERS_PRODUCTS_REMARK__ r ON r.site_id=op.site_id AND r.orders_products_id=op.orders_products_id'))->field('r.categories_id')->where(array('products_model'=>$entry_product['products_model']))->order('r.orders_products_remark_id desc')->find();
                        if($check_model){//先从历史订单中查找分类
                            $orders_products_categories_id = $check_model['categories_id'];
                        }else{
                            $check_model = M('products')->where(array('product_model'=>$entry_product['products_model']))->find();
                            if($check_model)
                                $orders_products_categories_id = $check_model['orders_products_categories_id'];
                            else
                                $orders_products_categories_id = 0;
                        }
                        $check_orders_procuts_remark = M('orders_products_remark')->where(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id))->find();
                        if($check_orders_procuts_remark){
                            M('orders_products_remark')->where(array('orders_products_remark_id'=>$check_orders_procuts_remark['orders_products_remark_id']))->save(array('categories_id'=>$orders_products_categories_id));
                        }else{
                            M('orders_products_remark')->add(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id, 'orders_id'=>$_data['orders_id'], 'categories_id'=>$orders_products_categories_id));
                        }                           
                        //订单项目归档 end
                        
                        if(!empty($entry_product['attribute'])){
                            $max_orders_products_attributes = M('orders_products_attributes')->where(array('site_id'=>$site_id))->max('orders_products_attributes_id');
                            if(empty($max_orders_products_attributes))
                                $orders_products_attributes_id = 1;
                            else
                                $orders_products_attributes_id = $max_orders_products_attributes+1;
                            foreach ($entry_product['attribute'] as $entry_attribute){
                                $entry_attribute['orders_products_attributes_id'] = $orders_products_attributes_id;
                                $entry_attribute['orders_products_id'] = $orders_products_id;
                                M('orders_products_attributes')->add($entry_attribute);
                                $orders_products_attributes_id++;
                            }
                        }
                    }
                }    
                
                
                
                $row = $model_orders_remark->find(array('where'=>array('site_id'=>$site_id, 'orders_id'=>$_data['orders_id'])));
                $order_no = $site_info['system_cms'] == 'easyshop' ? $site_info['order_no_prefix'] . '-1' . str_pad($_data['orders_id'], 4, "0", 0) : '';
                if(empty($row)){
                    $auto_status_name = '待处理';
                    $status_switch = C('status_switch');
                    foreach($status_switch as $rule=>$name){
                        if(preg_match($rule, $_data['orders_status_name'])){
                            $auto_status_name = $name;
                            break;
                        }
                    }
                    $model_orders_remark->add(array(
                        'site_id' => $site_id,
                        'orders_id' => $_data['orders_id'],
                        'order_no' => $order_no,
                        'order_status_remark'=>$auto_status_name,
                        'last_modify'=>$_data['date_purchased'],
                    ));
                }elseif(empty($row['order_status_remark']) || $row['order_status_remark']=='待处理'){
                    $auto_status_name = '待处理';
                    $status_switch = C('status_switch');
                    foreach($status_switch as $rule=>$name){
                        if(preg_match($rule, $_data['orders_status_name'])){
                            $auto_status_name = $name;
                            break;
                        }
                    }
                    $model_orders_remark->save(array('order_status_remark'=>$auto_status_name),array('where'=>array(
                        'site_id' => $site_id,
                        'orders_id' => $_data['orders_id'],
                        'order_no' => $order_no
                    )));
                }
            }
            $this->ajaxReturn(array('status' => 1), 'JSON');
        } else {
            $this->ajaxReturn(array('status' => 0, 'error'=>'无法识别下载的数据!'), 'JSON');
        }
    }    
    private function _get_order_dir($site_id, $order_id){
        $dir = DIR_FS_ORDER_PRODUCT . $site_id .'/'. floor($order_id/5000).'/'.$order_id.'/';
        if(file_exists($dir)==false) makeDir($dir);
        return $dir;    
    }
    public function orderData2Action($site_id, $date){
        $page = I('get.page', 0);
        $site_row = D('site')->field('site_interface,new_saas')->find($site_id);
        $token = $site_row['new_saas'] == 1 ? C('umieshop_token') : C('storeforfast_token');
        $url = $site_row['site_interface'].'?Token='.$token.'&lastDate='.$date;
        if($page)
            $url = $url.'&Page='.$page;

        $sign = md5(parse_url($url, PHP_URL_HOST).$token.'api_v1_orders/get');
        $url .= '&Sign='.$sign;

        $timestamp_today   = strtotime(date('Y-m-d'));//今天的日期
        $timestamp_7days   = $timestamp_today-7*60*60*24;//最近7天日期
        $timestamp_request = strtotime($date);//请求的日期
        if($timestamp_request<=$timestamp_today && $timestamp_request>=$timestamp_7days){
            $cache_expired = 60*60;//最近7天缓存1小时
        }else{
            $cache_expired = 60*60*24;//非当天缓存12小时
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
            if($data['code']=='0000'){
                // var_dump($data);
                foreach ($data['data'] as $o){
                    $order_no = $o['order_number'];
             
                    $check_order_exist = M('orders_remark')->where(array('site_id'=>$site_id, 'order_no'=>$order_no))->find();

                    if(empty($check_order_exist)){
                        $max_orders_id = M('orders')->where(array('site_id'=>$site_id))->max('orders_id');
                        if(empty($max_orders_id))
                            $orders_id = 1;
                        else 
                            $orders_id = $max_orders_id+1;
                        
                    }else
                        $orders_id = $check_order_exist['orders_id'];
                    $order_status_name = array(0=>'支付失败',1=>'支付成功',2=>'未支付',4=>'部分退款',5=>'退款成功');

                    if($o['payment'] == 'deepsea'){
                        $payment_method_name_array = explode('-',$o['payment_method_name']);
                        if(isset($payment_method_name_array[1])) $o['payment'] = trim($payment_method_name_array[1]);
                    }
                    $amount = $o['amount']/$o['currency_value'];
                    $data_order = array(
                        'site_id'                   => $site_id,
                        'orders_id'                 => $orders_id,
                        'customers_id'              => $o['customer_id'],
                        'payment_module_code'       => $o['payment'],
                        'payment_method'            => $o['payment']=='mycheckout2f3d'?'Credit Cards':$o['payment'],
                        'customers_email_address'   => $o['email'],
                        'customers_telephone'       => $o['delivery_phone'],
                        'currency'                  => $o['currency'],
                        'currency_value'            => $o['currency_value'],
                        'order_total'               => $amount,
                        'customers_name'            =>$o['billing_name'],
                        'customers_country'         =>$o['billing_country'],
                        'customers_state'           =>$o['billing_state'],
                        'customers_city'            =>$o['billing_city'],
                        'customers_postcode'        =>$o['billing_postcode'],
                        'customers_street_address'  =>$o['billing_address'],
                        
                        'shipping_method'           =>$o['shipping_method_name'],
                        'shipping_module_code'      =>$o['shipping_method_name'],
                        
                        'billing_name'              =>$o['billing_name'],
                        'billing_country'           =>$o['billing_country'],
                        'billing_state'             =>$o['billing_state'],
                        'billing_city'              =>$o['billing_city'],
                        'billing_postcode'          =>$o['billing_postcode'],
                        'billing_street_address'    =>$o['billing_address'],
                        'delivery_name'             =>$o['delivery_name'],
                        'delivery_country'          =>$o['delivery_country'],
                        'delivery_state'            =>$o['delivery_state'],
                        'delivery_city'             =>$o['delivery_city'],
                        'delivery_postcode'         =>$o['delivery_postcode'],
                        'delivery_street_address'   =>$o['delivery_address'],
                        'date_purchased'            => $o['date_create'],
                        'last_modified'             => $o['date_modified'],
                        'ip_address'                => $o['ip'],
                        'orders_status'             => $o['status'],
                        'orders_status_name'	    => isset($order_status_name[$o['status']])?$order_status_name[$o['status']]:$o['status'],
                        'remarks'                   => $o['remarks'],
                    );
                    M('orders')->add($data_order, array(), true);
                    $data_order_remark = array(
                        'site_id'                   => $site_id,
                        'orders_id'                 => $orders_id, 
                        'order_no'                  => $order_no,
                    );
                    if(empty($check_order_exist)){
                        $data_order_remark['last_modify'] = $o['date_create'];
                        if($o['status']==1 || $o['payment']=='custom')
                            $data_order_remark['order_status_remark'] = '付款确认中';
                        elseif($o['status']==0 || $o['status']==2)
                            $data_order_remark['order_status_remark'] = '付款失败or未付款';
                            
                        M('orders_remark')->add($data_order_remark, array(), true);
                    }
                    
                    $subtotal = 0;
                    foreach ($o['product'] as $p){
                        $subtotal += $p['qty']*$p['price'];
                    }

                    $order_total = array();
                    $order_total[] = array(
                        'site_id'                   => $site_id,
                        'orders_id'                 => $orders_id,
                        'title'                     => 'Subtotal:',
                        'text'                      => $subtotal.$o['currency'],
                        'value'                     => ($subtotal/$o['currency_value']),
                        'class'                     => 'ot_sub_total',
                        'sort_order'                => 0
                    );
                    if($o['coupon_discount']>0){
                        $order_total[] = array(
                            'site_id'                   => $site_id,
                            'orders_id'                 => $orders_id,
                            'title'                     => 'Coupon Discount:',
                            'text'                      => '-'.number_format ($o['coupon_discount'], 2).$o['currency'],
                            'value'                     =>  ($o['coupon_discount']/$o['currency_value']),
                            'class'                     => 'ot_coupon_discount',
                            'sort_order'                => 10
                        );
                    }
                    if($o['payment_discount']>0){
                        $order_total[] = array(
                            'site_id'                   => $site_id,
                            'orders_id'                 => $orders_id,
                            'title'                     => 'Payment Discount:',
                            'text'                      => '-'.number_format ($o['payment_discount'], 2).$o['currency'],
                            'value'                     =>  $o['payment_discount'],
                            'class'                     => 'ot_payment_discount',
                            'sort_order'                => 20
                        );
                    }
                    if($o['fee']>0){
                        $order_total[] = array(
                            'site_id'                   => $site_id,
                            'orders_id'                 => $orders_id,
                            'title'                     => 'Shipping Cost:',
                            'text'                      => number_format ($o['fee'], 2).$o['currency'],
                            'value'                     =>  ($o['fee']/$o['currency_value']),
                            'class'                     => 'ot_fee',
                            'sort_order'                => 30
                        );
                    }   
                    
                    if($o['payment_fee']>0){
                        $order_total[] = array(
                            'site_id'                   => $site_id,
                            'orders_id'                 => $orders_id,
                            'title'                     => 'Payment Fee:',
                            'text'                      => number_format ($o['payment_fee'], 2).$o['currency'],
                            'value'                     =>  ($o['payment_fee']/$o['currency_value']),
                            'class'                     => 'ot_payment_fee',
                            'sort_order'                => 40
                        );
                    }      
                    if($o['insurance']>0){
                        $order_total[] = array(
                            'site_id'                   => $site_id,
                            'orders_id'                 => $orders_id,
                            'title'                     => 'Insurance Fee:',
                            'text'                      => number_format ($o['insurance'], 2).$o['currency'],
                            'value'                     =>  ($o['insurance']/$o['currency_value']),
                            'class'                     => 'ot_insurance',
                            'sort_order'                => 50
                        );
                    }                     
                    foreach($o['extra_info'] as $entry){
                        $sort_order = 60;
                        $order_total[] = array(
                            'site_id'                   => $site_id,
                            'orders_id'                 => $orders_id,
                            'title'                     => $entry['name'],
                            'text'                      => number_format ($entry['amount'], 2).$o['currency'],
                            'value'                     =>  ($entry['amount']/$o['currency_value']),
                            'class'                     => 'ot_'.preg_replace('~[^\w\W]~', '_', $entry['name']),
                            'sort_order'                => $sort_order
                        );
                        $sort_order += 10;
                    }
                        $order_total[] = array(
                            'site_id'                   => $site_id,
                            'orders_id'                 => $orders_id,
                            'title'                     => 'Total:',
                            'text'                      => number_format($o['amount'], 2).$o['currency'],
                            'value'                     =>  ($o['amount']/$o['currency_value']),
                            'class'                     => 'ot_total',
                            'sort_order'                => 9000
                        );                    
                    foreach($order_total as $entry){
                        $check_orders_total = M('orders_total')->where(array('site_id'=>$site_id, 'orders_id'=>$orders_id, 'class'=>$entry['class']))->find();
                        if(empty($check_orders_total)){
                            M('orders_total')->add($entry);
                        }
                    }
                    
                    $order_total_detail = json_encode($order_total);
                    M('orders')->where(array('site_id'=>$site_id, 'orders_id'=>$orders_id))->save(array('order_total_detail'=>$order_total_detail));
                
                /*此段代码先暂时开启，后面在关闭 start */
                /*
                if($date=='2020-12-31' || $date=='2020-12-30'){
                    M('orders_products')->where(array('site_id'=>$site_id, 'orders_id'=>$orders_id))->delete();
                    M('orders_products_attributes')->where(array('site_id'=>$site_id, 'orders_id'=>$orders_id))->delete();
                    M('orders_products_remark')->where(array('site_id'=>$site_id, 'orders_id'=>$orders_id))->delete();
                }
                */
                /*此段代码先暂时开启，在关闭 end*/                
                    $check_orders_products = M('orders_products')->where(array('site_id'=>$site_id, 'orders_id'=>$orders_id))->select();
                    if(empty($check_orders_products)){
                        $max_orders_products_id = M('orders_products')->where(array('site_id'=>$site_id))->max('orders_products_id');
                        if(empty($max_orders_products_id))
                            $orders_products_id = 1;
                        else
                            $orders_products_id = $max_orders_products_id+1;
                        
                        
                        $max_orders_products_attributes = M('orders_products_attributes')->where(array('site_id'=>$site_id))->max('orders_products_attributes_id');
                        if(empty($max_orders_products_attributes))
                            $orders_products_attributes_id = 1;
                        else
                            $orders_products_attributes_id = $max_orders_products_attributes+1;                    
                        foreach ($o['product'] as $p){
                            $data_products = array(
                                'site_id'                   => $site_id,
                                'orders_id'                 => $orders_id,
                                'orders_products_id'        => $orders_products_id,
                                'products_model'            => $p['sku'],
                                'products_name'             => $p['name'],
                                'products_image'            => $p['image'],
                                'products_quantity'         => $p['qty'],
                                'products_price'            => $p['price'],
                                'final_price'               => $p['price'],
                                'add_time'                  => $o['date_create'],
                            );

                            M('orders_products')->add($data_products);
                            
                            //订单项目归档 start
                            $check_model = M('orders_products')->alias('op')->join(array('__ORDERS_PRODUCTS_REMARK__ r ON r.site_id=op.site_id AND r.orders_products_id=op.orders_products_id'))->field('r.categories_id')->where(array('products_model'=>$p['sku']))->order('r.orders_products_remark_id desc')->find();
                            if($check_model){//先从历史订单中查找分类
                                $orders_products_categories_id = $check_model['categories_id'];
                            }else{                            
                                $check_model = M('products')->where(array('product_model'=>$p['sku']))->find();
                                if($check_model){
                                    $orders_products_categories_id = $check_model['orders_products_categories_id'];
                                }else{
                                    $orders_products_categories_id = 0;
                                }
                            }
                            $check_orders_procuts_remark = M('orders_products_remark')->where(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id))->find();
                            if($check_orders_procuts_remark){
                                M('orders_products_remark')->where(array('orders_products_remark_id'=>$check_orders_procuts_remark['orders_products_remark_id']))->save(array('categories_id'=>$orders_products_categories_id));
                            }else{
                                M('orders_products_remark')->add(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id, 'orders_id'=>$orders_id, 'categories_id'=>$orders_products_categories_id));
                            }                               
                            //订单项目归档 end
                        
                            if(!empty($p['attributes'])){
                                $attributes = explode(';', $p['attributes']);
                                foreach($attributes as $attr){
                                    list($option_name, $option_value) = explode(':', $attr);
                                    $data_attributes = array(
                                        'site_id'                   => $site_id,
                                        'orders_products_attributes_id' => $orders_products_attributes_id,
                                        'orders_id'                 => $orders_id,
                                        'orders_products_id'        => $orders_products_id,
                                        'products_options'          => $option_name,
                                        'products_options_values'   => $option_value,
                                    );
                                    M('orders_products_attributes')->add($data_attributes);
                                    $orders_products_attributes_id++;
                                }
                            }
                            $orders_products_id++;
                        }
                    }else{
                        /*
                        foreach ($check_orders_products as $p){
                            var_dump(array('site_id'=>$p['site_id'], 'orders_products_id'=>$p['orders_products_id']));exit;
                            $check_orders_procuts_remark = M('orders_products_remark')->where(array('site_id'=>$p['site_id'], 'orders_products_id'=>$p['orders_products_id']))->find();
                            if(empty($check_orders_procuts_remark)){
                                M('orders_products_remark')->add(array('site_id'=>$p['site_id'], 'orders_products_id'=>$p['orders_products_id'], 'orders_id'=>$p['orders_id'], 'item_status'=>'待订货(待处理)'));
                            }else
                                M('orders_products_remark')->where(array('site_id'=>$p['site_id'], 'orders_products_id'=>$p['orders_products_id']))->save(array('orders_id'=>$p['orders_id']));
                                
                        }
                        */
                    }
                }
                $this->ajaxReturn(array('status' => 1, 'page'=>$data['page']), 'JSON');
            }else{
                $this->ajaxReturn(array('status' => 0, 'error'=>$data['msg']), 'JSON');
            }
            
        }else{
            $this->ajaxReturn(array('status' => 0, 'error'=>'接口请求出错'), 'JSON');
        }
        
        
    }
    
    public function getOrderPackageAction($site_id) {
        Vendor('phpRPC.phprpc_client');
        $interface_url = $this->getInterfaceUrl($site_id);
        
        try{
            $client = new \PHPRPC_Client($interface_url . '?m=Server&c=Order');
            
            $total_order = $client->ordercount($site_id);
             
            if (is_object($total_order) && get_class($total_order) == 'PHPRPC_Error') {
                $this->ajaxReturn(array('status' => 0, 'error' => $total_order->toString().'xxx'), 'JSON');
            }
            $num_page = ceil($total_order / $this->num_package);
        } catch (PHPRPC_Error $e){
            $this->ajaxReturn(array('status' => 0, 'error' => $e->toString().'yyy'), 'JSON');
        }
        //查找系统中订单数,并计算应下载的页数
        $order_sys = D('order')->where(array('site_id'=>$site_id))->count();
        $page_down = ceil(($total_order-$order_sys)/$this->num_package);
        if($page_down==0){
            $string_page = $num_page;
        }else{
            for($i=0;$i<$page_down;$i++){
                $string_page .= ','.($num_page-$i);
            }
            $string_page = substr($string_page, 1);
        }
        
        $this->ajaxReturn(array('status' => 1, 'num_page' => $num_page, 'total' => $total_order, 'total_sys'=>$order_sys, 'page_down'=>$string_page), 'JSON');
    }
    public function getOrderStatus($site_id) {
        Vendor('phpRPC.phprpc_client');
        $interface_url = $this->getInterfaceUrl($site_id);
        $client = new \PHPRPC_Client($interface_url . '?m=Server&c=Order');
        $result = $client->getOrderStatus($site_id);
        return $result;
    }
    public function updateOrderStatus($site_id, $data) {
        Vendor('phpRPC.phprpc_client');
        $interface_url = $this->getInterfaceUrl($site_id);
        $client = new \PHPRPC_Client($interface_url . '?m=Server&c=Order');
        $result = $client->updateOrderStatus($data);
        return $result === true ? true : false;
    }
    private function getInterfaceUrl($site_id) {
        $site_row = D('site')->field('site_interface')->find($site_id);
        $site_index = rtrim($site_row['site_index'], '/cs2020pi/');
        return $site_row['site_interface'];
    }
}