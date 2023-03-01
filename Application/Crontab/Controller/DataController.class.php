<?php
//执行链接：http://support.customize.company/index.php/Crontab/Data/orderData/key/e1ab935d69934be6b174faba1696e383
namespace Crontab\Controller;
use Think\Controller;
use Crontab\Model\OrderModel;
use Customers\Model\CustomersModel;
class DataController extends Controller {
    private $num_package = 1000; //每个包的订单数

    function __construct() {
        ini_set('max_execution_time','0');
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function orderDataAction($key = '',$first = 0) {
        if($key != md5('#http://support.customize.company#')){
            $this->logs("\nkey错误\n");
            exit();
        }
        $today = date("Y-m-d");
        $hour = date("H");
        $where = array('s.status' => 1,'s.site_id' => array('not in', array(1,983,950,951,986,987,992,993)));
        if($hour < 1){
            $where['s.type'] = 1;
            $where['c.order_execution_time'] = array('lt',date("Y-m-d H:i:s", strtotime("-8 hour")));
        }else{
            if($hour < 7 || $hour > 17){
                $where_order_execution_time = $today . ' 01:00:00';
            }else{
                $where_order_execution_time = date("Y-m-d H:00:00", strtotime("-1 hour"));
            }
            $where['s.type'] = array('in',array(1,10));
            $where[] = array(
                '_complex' => array(
                    '_logic' => 'OR',
                    'c.order_execution_time' =>array('exp', 'IS NULL'),
                    '_complex'=>array(
                        'c.order_execution_time' => array('lt',$where_order_execution_time)
                    )
                )
            );
        }
        $site_list = M('Site')->alias('s')->join(array('LEFT JOIN __CRONTAB__ c ON s.site_id=c.site_id'))->where($where)->order('c.order_execution_time')->getField('s.site_id,s.order_no_prefix,s.type,s.site_interface,s.system_cms,s.new_saas,c.order_execution_time',true);
        $order = new OrderModel();
        $order_fields = $order->getDbFields();
        $order_fields[] = 'product';
        $order_fields[] = 'history';
        $order_fields[] = 'attribute';
        $model_orders_remark = D('orders_remark');
        $model_orders_products = M('orders_products');
        $model_products = M('products');
        $model_orders_products_remark = M('orders_products_remark');
        $model_orders_products_attributes = M('orders_products_attributes');
        $domains_model = M('Domains');
        $crontab_model = M('Crontab');
        if($first == 1 && $hour == 8){
            $domains_model->where(array('status' => 1,'expire_date' => array('lt',date("Y-m-d",strtotime("+8 day")))))->save(array('status' => 2));
            $domains_model->where(array('status' => 2,'expire_date' => array('lt',$today)))->save(array('status' => 5));
            $domains_model->where(array('status' => 3,'expire_date' => array('lt',date("Y-m-d",strtotime("-10 day")))))->save(array('status' => 6));
            $domains_model->where(array('status' => 4,'expire_date' => array('lt',$today)))->save(array('status' => 6));
            $domains_model->where(array('status' => 5,'expire_date' => array('lt',date("Y-m-d",strtotime("-7 day")))))->save(array('status' => 6));
            $delete_site_id = $domains_model->where(array('site_id' => array('gt',0),'expire_date' => array('lt',$today),'status' => array('neq',3)))->getField('site_id',true);
            if(!empty($delete_site_id)){
                M('Site')->where(array('status' => array('neq',0),'site_id' => array('in',$delete_site_id)))->save(array('status' => 0));
                $crontab_model->where(array('site_id' => array('in',$delete_site_id)))->delete();
            }
        }elseif($first == 1 && $hour == 10){
            $expire_list = $domains_model->field('user_id,domain_name,expire_date')->where(array('status' => 2))->select();
            $users_model = M('Users');
            if(!empty($expire_list) && !in_array($today, array('2023-02-04','2023-02-05','2023-02-12','2023-02-18','2023-02-19','2023-02-26','2023-03-04','2023-03-05','2023-03-12','2023-03-18','2023-03-19','2023-03-26'))){
                $remind_date = $today;
                if(in_array($today, array('2023-03-03','2023-03-17'))){
                    $remind_date = date("Y-m-d",strtotime("+1 day"));
                }
                if(in_array($today, array('2023-02-17'))){
                    $remind_date = date("Y-m-d",strtotime("+2 day"));
                }
                $user_array = $sales_array = array();
                foreach ($expire_list as $v){
                    if(empty($v['user_id'])){
                        $user_array[1][] = $v;
                    }else{
                        $user_array[$v['user_id']][] = $v;
                        $sales_id = M('PromotionDepartmentMembers')->where(array('user_id' => $v['user_id']))->getField('sales_id');
                        if($v['expire_date'] <= $remind_date && $v['user_id'] != $sales_id) $sales_array[$sales_id][] = $v;
                    }
                }
                $send_user_id_array = array_keys($user_array);
                if(!in_array(1, $send_user_id_array)) $send_user_id_array[] = 1;
                if(!empty($sales_array)) $send_user_id_array = array_unique(array_merge($send_user_id_array,array_keys($sales_array)));
                $send_user_array = $users_model->where(array('user_id' => array('in',$send_user_id_array)))->getField('user_id,chinese_name,email',true);
                foreach ($user_array as $user_id => $val){
                    $user_info = isset($send_user_array[$user_id]) ? $send_user_array[$user_id] : $send_user_array[1];
                    if(empty($user_info['email'])){
                        $this->logs("\n" . $user_info['chinese_name'] . "的邮箱为空\n");
                    }else{
                        $content = array();
                        foreach ($val as $v){
                            $content[] = '域名：' . $v['domain_name'] . '将于' . $v['expire_date'] . '到期，';
                        }
                        $send_mail_result = send_mail($user_info['email'], $user_info['chinese_name'], '域名即将到期', implode('<br>',$content) . '请前往http://support.customize.company' . U('Domains/Domains/index',array('status'=>2)) . ' 确认是否续费！');
                        if($send_mail_result['status'] != 1) $this->logs("\n未能成功发送通知邮件给" . $user_info['chinese_name'] . "\n");
                    }
                }
                if(!empty($sales_array)){
                    foreach ($sales_array as $user_id => $val){
                        $user_info = isset($send_user_array[$user_id]) ? $send_user_array[$user_id] : $send_user_array[1];
                        if(empty($user_info['email'])){
                            $this->logs("\n" . $user_info['chinese_name'] . "的邮箱为空\n");
                        }else{
                            $content = array();
                            foreach ($val as $k => $v){
                                $content[] = '域名：' . $v['domain_name'] . '将于' . $v['expire_date'] . '到期，请联系' . (isset($send_user_array[$v['user_id']]) ? $send_user_array[$v['user_id']]['chinese_name'] : $send_user_array[1]['chinese_name']) . '确认是否续费！';
                            }
                            $send_mail_result = send_mail($user_info['email'], $user_info['chinese_name'], '域名即将到期', implode('<br>',$content));
                            if($send_mail_result['status'] != 1) $this->logs("\n未能成功发送通知邮件给" . $user_info['chinese_name'] . "\n");
                        }
                    }
                }
            }
            $ssl_expire_list = $domains_model->field('domain_name,ssl_expire_date')->where('`status`=1 AND `ssl_expire_date`<DATE_SUB(`expire_date`, INTERVAL 5 DAY) AND `ssl_expire_date`<DATE_ADD(CURDATE(),INTERVAL 4 DAY)')->select();
            if(!empty($ssl_expire_list)){
                if(isset($send_user_array)){
                    $super_administrator_info = $send_user_array[1];
                }else{
                    $super_administrator_info = $users_model->field('chinese_name,email')->where('user_id=1')->find();
                }
                if(empty($super_administrator_info['email'])){
                    $this->logs("\n" . $super_administrator_info['chinese_name'] . "的邮箱为空\n");
                }else{
                    $content = array();
                    foreach ($ssl_expire_list as $v){
                        $content[] = '域名：' . $v['domain_name'] . '的SSL将于' . $v['ssl_expire_date'] . '到期';
                    }
                    $send_mail_result = send_mail($super_administrator_info['email'], $super_administrator_info['chinese_name'], 'SSL即将到期', implode('，',$content)) . '请续费！';
                    if($send_mail_result['status'] != 1) $this->logs("\n未能成功发送通知邮件给" . $super_administrator_info['chinese_name'] . "\n");
                }
            }
            $this->logs("\n域名状态改变执行完成！\n");
        }
        $cache_expired = 60*60;//最近7天缓存1小时
        if(!file_exists(DIR_FS_TEMP . 'json/')) mkdir (DIR_FS_TEMP . 'json/');
        Vendor('phpRPC.phprpc_client');
        vendor('Request.Requests');
        \Requests::register_autoloader();
        $model_orders = M('orders');
        $model_orders_total = M('orders_total');
        $not_site_id = array();
        $Model = M();
        foreach ($site_list as $site_id => $site){
            if(!in_array($site_id, array(952,953))) $not_site_id[$site_id] = $site_id;
            if($site['type'] == 1){
                if(empty($site['order_execution_time']) || $site['order_execution_time'] == '0000-00-00 00:00:00'){
                    $start_time = date("Y-m-d H:i:s",time()-25*60*60);
                }else{
                    $start_time = date("Y-m-d H:i:s",strtotime($site['order_execution_time'])-3600);
                }
                $where = "date_purchased>='" . $start_time . "'";
                $client = new \PHPRPC_Client($site['site_interface'] . '?m=Server&c=Order');
                $result = $client->order($site_id, $where, 1, $this->num_package);
                if (is_object($result) && get_class($result) == 'PHPRPC_Error') {
                    $this->logs("\nsite_id："  . $site_id . " " . $result->toString() . "\n");
                }
                $data = uncompress_decode($result);
                if (is_array($data)) {
                    foreach ($data as $_data) {
                        $Model->startTrans();
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

                        $timestamp_date_purchased = strtotime($_data['date_purchased']) + ($site['system_cms'] == 'easyshop' ? 0 : 28800);
                        $_data['date_purchased'] = date('Y-m-d H:i:s', $timestamp_date_purchased);
                        $r = $order->relation(array('history'))->add($_data, array(), true);
                        if(!$r){
                            $Model->rollback();
                            $this->logs("\nsite_id："  . $order->getDbError() . " " . json_encode($_data) . "\n");
                            $this->logs("添加失败\n");
                            exit();
                        }

                        $check_orders_products = $model_orders_products->where(array('site_id'=>$site_id, 'orders_id'=>$_data['orders_id']))->select();
                        if(empty($check_orders_products)){
                            foreach ($_data['product'] as $entry_product){
                                $max_orders_products_id = $model_orders_products->where(array('site_id'=>$site_id))->max('orders_products_id');
                                if(empty($max_orders_products_id))
                                    $orders_products_id = 1;
                                else
                                    $orders_products_id = $max_orders_products_id+1;
                                $entry_product['orders_products_id'] = $orders_products_id;

                                $r = $model_orders_products->add($entry_product);
                                if(!$r){
                                    $Model->rollback();
                                    $this->logs("\nsite_id："  . $model_orders_products->getDbError() . " " . json_encode($entry_product) . "\n");
                                    $this->logs("添加失败\n");
                                    exit();
                                }
                                //订单项目归档 start
                                $check_model = $model_orders_products->alias('op')->join(array('__ORDERS_PRODUCTS_REMARK__ r ON r.site_id=op.site_id AND r.orders_products_id=op.orders_products_id'))->field('r.categories_id')->where(array('products_model'=>$entry_product['products_model']))->order('r.orders_products_remark_id desc')->find();
                                if($check_model){//先从历史订单中查找分类
                                    $orders_products_categories_id = $check_model['categories_id'];
                                }else{
                                    $check_model = $model_products->where(array('product_model'=>$entry_product['products_model']))->find();
                                    if($check_model)
                                        $orders_products_categories_id = $check_model['orders_products_categories_id'];
                                    else
                                        $orders_products_categories_id = 0;
                                }
                                $check_orders_procuts_remark = $model_orders_products_remark->where(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id))->find();
                                if($check_orders_procuts_remark){
                                    $model_orders_products_remark->where(array('orders_products_remark_id'=>$check_orders_procuts_remark['orders_products_remark_id']))->save(array('categories_id'=>$orders_products_categories_id));
                                }else{
                                    $r = $model_orders_products_remark->add(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id, 'orders_id'=>$_data['orders_id'], 'categories_id'=>$orders_products_categories_id));
                                    if(!$r){
                                        $Model->rollback();
                                        $this->logs("\nsite_id："  . $model_orders_products_remark->getDbError() . " " . json_encode(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id, 'orders_id'=>$_data['orders_id'], 'categories_id'=>$orders_products_categories_id)) . "\n");
                                        $this->logs("添加失败\n");
                                        exit();
                                    }
                                }
                                //订单项目归档 end

                                if(!empty($entry_product['attribute'])){
                                    $max_orders_products_attributes = $model_orders_products_attributes->where(array('site_id'=>$site_id))->max('orders_products_attributes_id');
                                    if(empty($max_orders_products_attributes))
                                        $orders_products_attributes_id = 1;
                                    else
                                        $orders_products_attributes_id = $max_orders_products_attributes+1;
                                    foreach ($entry_product['attribute'] as $entry_attribute){
                                        $entry_attribute['orders_products_attributes_id'] = $orders_products_attributes_id;
                                        $entry_attribute['orders_products_id'] = $orders_products_id;
                                        $r = $model_orders_products_attributes->add($entry_attribute);
                                        if(!$r){
                                            $Model->rollback();
                                            $this->logs("\nsite_id："  . $model_orders_products_attributes->getDbError() . " " . json_encode($entry_attribute) . "\n");
                                            $this->logs("添加失败\n");
                                            exit();
                                        }
                                        $orders_products_attributes_id++;
                                    }
                                }
                            }
                        }

                        $row = $model_orders_remark->find(array('where'=>array('site_id'=>$site_id, 'orders_id'=>$_data['orders_id'])));
                        $order_no = $site['system_cms'] == 'easyshop' ? $site['order_no_prefix'] . '-1' . str_pad($_data['orders_id'], 4, "0", 0) : '';
                        if(empty($row)){
                            $auto_status_name = '待处理';
                            $status_switch = C('status_switch');
                            foreach($status_switch as $rule=>$name){
                                if(preg_match($rule, $_data['orders_status_name'])){
                                    $auto_status_name = $name;
                                    break;
                                }
                            }
                            $r = $model_orders_remark->add(array(
                                'site_id' => $site_id,
                                'orders_id' => $_data['orders_id'],
                                'order_no' => $order_no,
                                'order_status_remark'=>$auto_status_name,
                                'last_modify'=>$_data['date_purchased'],
                            ));
                            if(!$r){
                                $Model->rollback();
                                $this->logs("\nsite_id："  . $model_orders_remark->getDbError() . " " . json_encode(array(
                                    'site_id' => $site_id,
                                    'orders_id' => $_data['orders_id'],
                                    'order_no' => $order_no,
                                    'order_status_remark'=>$auto_status_name,
                                    'last_modify'=>$_data['date_purchased'],
                                )) . "\n");
                                $this->logs("添加失败\n");
                                exit();
                            }
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
                        $Model->commit();
                    }
                    $this->logs("site_id："  . $site_id . " 下载完成！");
                    if(empty($site['order_execution_time']) && !$crontab_model->where(array('site_id' => $site_id))->find()){
                        $crontab_model->add(array('site_id' => $site_id,'order_execution_time'=>date('Y-m-d H:i:s')),array(),true);
                    }else{
                        $crontab_model->save(array('order_execution_time' => date('Y-m-d H:i:s')),array('where'=>array('site_id' => $site_id)));
                    }
                    unset($not_site_id[$site_id]);
                } elseif($data !== null) {
                    $this->logs("\nsite_id："  . $site_id . " 无法识别下载的数据！\n");
                }else{
                    $this->logs("\nsite_id："  . $site_id . " 下载出错！\n");
                }
            }elseif($site['type'] == 10){
                $page = 0;
                do{
                    $token = $site['new_saas'] == 1 ? C('umieshop_token') : C('storeforfast_token');
                    if(empty($site['order_execution_time']) || $site['order_execution_time'] == '0000-00-00 00:00:00'){
                        $date = date("Y-m-d",strtotime("-2 day"));
                    }else{
                        $date = date("Y-m-d",strtotime($site['order_execution_time'])-3600);
                    }
                    $url = $site['site_interface'].'?Token='.$token.'&lastDate='.$date;
                    if($page)
                        $url = $url.'&Page='.$page;

                    $sign = md5(parse_url($url, PHP_URL_HOST).$token.'api_v1_orders/get');
                    $url .= '&Sign='.$sign;

                    if(!file_exists(DIR_FS_TEMP . 'json/'.$date.'/')) mkdir (DIR_FS_TEMP . 'json/'.$date.'/');
                    $cache_file = DIR_FS_TEMP . 'json/'.$date.'/'. md5($url).'.json';
                    $data = array();
                    $filed = true;
                    if(file_exists($cache_file)){
                        $cache_filemtime = filemtime($cache_file);
                        if(time()-$cache_filemtime<$cache_expired){//缓存未过期
                            $json_string = file_get_contents($cache_file);
                            $data = json_decode($json_string, true);
                            $filed = false;
                        }
                    }
                    if(empty($data)){
                        /* if(isset($execution_time)){
                            $time_difference = $this->getMillisecond() - $execution_time;
                            if($time_difference < 6000){
                                sleep(ceil(6 - $time_difference/1000));
                            }
                        }
                        $execution_time = $this->getMillisecond(); */
                        $response = \Requests::get($url, array(), array('timeout'=>60, 'verify'=>false));
                        $data = json_decode($response->body, true);
                        if(is_array($data) && sizeof($data) && $data['code']=='0000'){
                            file_put_contents($cache_file, $response->body);
                        }
                    }
                    if(is_array($data) && isset($data['code'])){
                        if($data['code']=='0000'){
                            if($filed){
                                foreach ($data['data'] as $o){
                                    $Model->startTrans();
                                    $order_no = $o['order_number'];

                                    $check_order_exist = $model_orders_remark->where(array('site_id'=>$site_id, 'order_no'=>$order_no))->find();

                                    if(empty($check_order_exist)){
                                        $max_orders_id = $model_orders->where(array('site_id'=>$site_id))->max('orders_id');
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
                                        'orders_status_name'        => isset($order_status_name[$o['status']])?$order_status_name[$o['status']]:$o['status'],
                                        'remarks'                   => $o['remarks'],
                                    );
                                    $r = $model_orders->add($data_order, array(), true);
                                    if(!$r){
                                        $Model->rollback();
                                        $this->logs("\nsite_id："  . $model_orders->getDbError() . " " . json_encode($data_order) . "\n");
                                        $this->logs("添加失败\n");
                                        exit();
                                    }
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
                                            
                                            $r = $model_orders_remark->add($data_order_remark, array(), true);
                                            if(!$r){
                                                $Model->rollback();
                                                $this->logs("\nsite_id："  . $model_orders_remark->getDbError() . " " . json_encode($data_order_remark) . "\n");
                                                $this->logs("添加失败\n");
                                                exit();
                                            }
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
                                        $check_orders_total = $model_orders_total->where(array('site_id'=>$site_id, 'orders_id'=>$orders_id, 'class'=>$entry['class']))->find();
                                        if(empty($check_orders_total)){
                                            $r = $model_orders_total->add($entry);
                                            if(!$r){
                                                $Model->rollback();
                                                $this->logs("\nsite_id："  . $model_orders_total->getDbError() . " " . json_encode($entry) . "\n");
                                                $this->logs("添加失败\n");
                                                exit();
                                            }
                                        }
                                    }

                                    $order_total_detail = json_encode($order_total);
                                    $model_orders->where(array('site_id'=>$site_id, 'orders_id'=>$orders_id))->save(array('order_total_detail'=>$order_total_detail));

                                    $check_orders_products = $model_orders_products->where(array('site_id'=>$site_id, 'orders_id'=>$orders_id))->select();
                                    if(empty($check_orders_products)){
                                        $max_orders_products_id = $model_orders_products->where(array('site_id'=>$site_id))->max('orders_products_id');
                                        if(empty($max_orders_products_id))
                                            $orders_products_id = 1;
                                        else
                                            $orders_products_id = $max_orders_products_id+1;

                                       $max_orders_products_attributes = $model_orders_products_attributes->where(array('site_id'=>$site_id))->max('orders_products_attributes_id');
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

                                            $r = $model_orders_products->add($data_products);
                                            if(!$r){
                                                $Model->rollback();
                                                $this->logs("\nsite_id："  . $model_orders_products->getDbError() . " " . json_encode($data_products) . "\n");
                                                $this->logs("添加失败\n");
                                                exit();
                                            }

                                            //订单项目归档 start
                                            $check_model = $model_orders_products->alias('op')->join(array('__ORDERS_PRODUCTS_REMARK__ r ON r.site_id=op.site_id AND r.orders_products_id=op.orders_products_id'))->field('r.categories_id')->where(array('products_model'=>$p['sku']))->order('r.orders_products_remark_id desc')->find();
                                            if($check_model){//先从历史订单中查找分类
                                                $orders_products_categories_id = $check_model['categories_id'];
                                            }else{
                                                $check_model = $model_products->where(array('product_model'=>$p['sku']))->find();
                                                if($check_model){
                                                    $orders_products_categories_id = $check_model['orders_products_categories_id'];
                                                }else{
                                                    $orders_products_categories_id = 0;
                                                }
                                            }
                                            $check_orders_procuts_remark = $model_orders_products_remark->where(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id))->find();
                                            if($check_orders_procuts_remark){
                                                $model_orders_products_remark->where(array('orders_products_remark_id'=>$check_orders_procuts_remark['orders_products_remark_id']))->save(array('categories_id'=>$orders_products_categories_id));
                                            }else{
                                                $r = $model_orders_products_remark->add(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id, 'orders_id'=>$orders_id, 'categories_id'=>$orders_products_categories_id));
                                                if(!$r){
                                                    $Model->rollback();
                                                    $this->logs("\nsite_id："  . $model_orders_products_remark->getDbError() . " " . json_encode(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id, 'orders_id'=>$orders_id, 'categories_id'=>$orders_products_categories_id)) . "\n");
                                                    $this->logs("添加失败\n");
                                                    exit();
                                                }
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
                                                    $r = $model_orders_products_attributes->add($data_attributes);
                                                    if(!$r){
                                                        $Model->rollback();
                                                        $this->logs("\nsite_id："  . $model_orders_products_attributes->getDbError() . " " . json_encode($data_attributes) . "\n");
                                                        $this->logs("添加失败\n");
                                                        exit();
                                                    }
                                                    $orders_products_attributes_id++;
                                                }
                                            }
                                            $orders_products_id++;
                                        }
                                    }
                                    $Model->commit();
                                }
                            }
                            $this->logs("site_id："  . $site_id . " page："  . $page . " 下载完成！");
                            $page = $data['page'];
                            if($page == 0){
                                if(empty($site['order_execution_time']) && !$crontab_model->where(array('site_id' => $site_id))->find()){
                                    $crontab_model->add(array('site_id' => $site_id,'order_execution_time'=>date('Y-m-d H:i:s')),array(),true);
                                }else{
                                    $crontab_model->save(array('order_execution_time' => date('Y-m-d H:i:s')),array('where'=>array('site_id' => $site_id)));
                                }
                                unset($not_site_id[$site_id]);
                            }
                        }else{
                            $this->logs("\nsite_id："  . $site_id . " " . $data['msg'] . "\n");
                        }

                    }else{
                        $this->logs("\nsite_id："  . $site_id . " 接口请求出错！\n");
                    }
                }while($page > 0);
            }
        }
        if(count($not_site_id) > 0) $this->logs("\nsite_id："  . implode('、', $not_site_id) . " 未下载！\n");
        $this->logs("执行完成！");
        exit();
    }

    public function customerDataAction($key = '') {
        if($key != md5('#http://support.customize.company#')){
            $this->logs("\n客户 key错误\n");
            exit();
        }
        $where = array('s.status' => 1,'s.type' => 1, 's.site_id' => array('not in', array(1,983)));
        $where[] = array(
            '_complex' => array(
                '_logic' => 'OR',
                'c.customer_execution_time' =>array('exp', 'IS NULL'),
                '_complex'=>array(
                    'c.customer_execution_time' => array('lt',date("Y-m-d") . ' 00:00:00')
                )
            )
        );
        $site_list = M('Site')->alias('s')->join(array('LEFT JOIN __CRONTAB__ c ON s.site_id=c.site_id'))->where($where)->order('c.customer_execution_time')->getField('s.site_id,s.site_interface,c.customer_execution_time',true);
        $not_site_id = array();
        Vendor('phpRPC.phprpc_client');
        $crontab_model = M('Crontab');
        $customer_model = new CustomersModel();
        foreach ($site_list as $site_id => $site){
            if(!in_array($site_id, array(952,953))) $not_site_id[$site_id] = $site_id;
            if(empty($site['customer_execution_time']) || $site['customer_execution_time'] == '0000-00-00 00:00:00'){
                $start_time = date("Y-m-d H:i:s",time()-25*60*60);
            }else{
                $start_time = date("Y-m-d H:i:s",strtotime($site['customer_execution_time'])-3600);
            }
            $where = "date_added>='" . $start_time . "'";
            $client = new \PHPRPC_Client($site['site_interface'] . '?m=Server&c=Customers');
            $result = $client->down($site_id, $where, 1, $this->num_package);
            if (is_object($result) && get_class($result) == 'PHPRPC_Error') {
                $this->logs("\n客户 site_id："  . $site_id . " " . $result->toString() . "\n");
            }
            $data = uncompress_decode($result);
            if (is_array($data)) {
                foreach ($data as $_data) {
                    $customer_model->relation(array('address_book'))->add($_data, array(), true);
                }
                if(empty($site['customer_execution_time']) && !$crontab_model->where(array('site_id' => $site_id))->find()){
                    $crontab_model->add(array('site_id' => $site_id,'customer_execution_time'=>date('Y-m-d H:i:s')),array(),true);
                }else{
                    $crontab_model->save(array('customer_execution_time' => date('Y-m-d H:i:s')),array('where'=>array('site_id' => $site_id)));
                }
                unset($not_site_id[$site_id]);
                $this->logs("客户 site_id："  . $site_id . " 下载完成！");
            } elseif($data !== null) {
                $this->logs("\n客户 site_id："  . $site_id . " 无法识别下载的数据！\n");
            }else{
                $this->logs("\n客户 site_id："  . $site_id . " 下载出错！\n");
            }
        }
        if(count($not_site_id) > 0) $this->logs("\n客户 site_id："  . implode('、', $not_site_id) . " 未下载！\n");
        $this->logs("客户执行完成！");
    }

    public function searchKeywordDataAction($key = '') {
        if($key != md5('#http://support.customize.company#')){
            $this->logs("\n搜索统计 key错误\n");
            exit();
        }
        $where = array('s.status' => 1,'s.type' => 10);
        $where[] = array(
            '_complex' => array(
                '_logic' => 'OR',
                'c.search_execution_time' =>array('exp', 'IS NULL'),
                '_complex'=>array(
                    'c.search_execution_time' => array('lt',date("Y-m-d") . ' 00:00:00')
                )
            )
        );
        $site_list = M('Site')->alias('s')->join(array('LEFT JOIN __CRONTAB__ c ON s.site_id=c.site_id'))->where($where)->order('c.search_execution_time')->getField('s.site_id,s.site_index,s.new_saas,c.search_execution_time',true);
        $not_site_id = array();
        vendor('Request.Requests');
        \Requests::register_autoloader();
        $crontab_model = M('Crontab');
        $search_keyword_model = M('SearchKeyword');
        foreach ($site_list as $site_id => $site){
            $not_site_id[$site_id] = $site_id;
            $token = $site['new_saas'] == 1 ? C('umieshop_token') : C('storeforfast_token');;
            $url = $site['site_index'] . '/api_v1_count/getSearch?Token=' . $token;
            if(!empty($site['search_execution_time']) && $site['ip_execution_time'] != '0000-00-00 00:00:00') $url .= '&start_date=' . date("Y-m-d",strtotime($site['search_execution_time']));
            $sign = strtoupper(md5(parse_url($url, PHP_URL_HOST) . $token . 'api_v1_count/getSearch'));
            $url .= '&num=100000&Sign='.$sign;
            $response = \Requests::get($url, array(), array('timeout'=>60, 'verify'=>false));
            $data = json_decode($response->body, true);
            if(is_array($data) && isset($data['code'])){
                if($data['code']=='0000'){
                    $search_keyword_data = array();
                    foreach ($data['data'] as $v){
                        $search_keyword_data[] = array(
                            'site_id' => $site_id,
                            'keyword' => $v['search'],
                            'count' => $v['freq'],
                            'status' => $v['status'],
                            'date_added' => $v['date_added']
                        );
                    }
                    if(count($search_keyword_data) > 0) $search_keyword_model->addAll($search_keyword_data,array(),true);
                    $this->logs("搜索统计 site_id："  . $site_id . " 下载完成！");
                    if(empty($site['search_execution_time']) && !$crontab_model->where(array('site_id' => $site_id))->find()){
                        $crontab_model->add(array('site_id' => $site_id,'search_execution_time'=>date('Y-m-d H:i:s')),array(),true);
                    }else{
                        $crontab_model->save(array('search_execution_time' => date('Y-m-d H:i:s')),array('where'=>array('site_id' => $site_id)));
                    }
                    unset($not_site_id[$site_id]);
                }else{
                    $this->logs("\n搜索统计 site_id："  . $site_id . " " . $data['msg'] . "\n");
                }
            }else{
                $this->logs("\n搜索统计 site_id："  . $site_id . " 接口请求出错！\n");
            }
        }
        if(count($not_site_id) > 0) $this->logs("\n搜索统计 site_id："  . implode('、', $not_site_id) . " 未下载！\n");
        $this->logs("搜索统计 执行完成！");
    }

    public function ipAccessLogDataAction($key = '') {
        if($key != md5('#http://support.customize.company#')){
            $this->logs("\nIP统计 key错误\n");
            exit();
        }
        $where = array('s.status' => 1,'s.type' => 10);
        $where[] = array(
            '_complex' => array(
                '_logic' => 'OR',
                'c.ip_execution_time' =>array('exp', 'IS NULL'),
                '_complex'=>array(
                    'c.ip_execution_time' => array('lt',date("Y-m-d") . ' 00:00:00')
                )
            )
        );
        $site_list = M('Site')->alias('s')->join(array('LEFT JOIN __CRONTAB__ c ON s.site_id=c.site_id'))->where($where)->order('c.ip_execution_time')->getField('s.site_id,s.site_index,s.new_saas,c.ip_execution_time',true);
        $not_site_id = array();
        vendor('Request.Requests');
        \Requests::register_autoloader();
        $crontab_model = M('Crontab');
        $ip_access_log_model = M('IpAccessLog');
        foreach ($site_list as $site_id => $site){
            $not_site_id[$site_id] = $site_id;
            $token = $site['new_saas'] == 1 ? C('umieshop_token') : C('storeforfast_token');;
            $url = $site['site_index'] . '/api_v1_count/getIp?Token=' . $token;
            if(!empty($site['ip_execution_time']) && $site['ip_execution_time'] != '0000-00-00 00:00:00') $url .= '&start_date=' . date("Y-m-d",strtotime($site['ip_execution_time']));
            $sign = strtoupper(md5(parse_url($url, PHP_URL_HOST) . $token . 'api_v1_count/getIp'));
            $url .= '&num=100000&Sign='.$sign;
            $response = \Requests::get($url, array(), array('timeout'=>60, 'verify'=>false));
            $data = json_decode($response->body, true);
            if(is_array($data) && isset($data['code'])){
                if($data['code']=='0000'){
                    $ip_access_log_data = array();
                    foreach ($data['data'] as $v){
                        $ip_access_log_data[] = array(
                            'site_id' => $site_id,
                            'ip' => $v['ip'],
                            'http_referer' => $v['details_count']['http_referer'],
                            'http_access' => $v['details_count']['http_access'],
                            'total' => $v['details_count']['total'],
                            'date_added' => $v['date_added']
                        );
                    }
                    if(count($ip_access_log_data) > 0) $ip_access_log_model->addAll($ip_access_log_data,array(),true);
                    $this->logs("IP统计 site_id："  . $site_id . " 下载完成！");
                    if(empty($site['ip_execution_time']) && !$crontab_model->where(array('site_id' => $site_id))->find()){
                        $crontab_model->add(array('site_id' => $site_id,'ip_execution_time'=>date('Y-m-d H:i:s')),array(),true);
                    }else{
                        $crontab_model->save(array('ip_execution_time' => date('Y-m-d H:i:s')),array('where'=>array('site_id' => $site_id)));
                    }
                    unset($not_site_id[$site_id]);
                }else{
                    $this->logs("\nIP统计 site_id："  . $site_id . " " . $data['msg'] . "\n");
                }
            }else{
                $this->logs("\nIP统计 site_id："  . $site_id . " 接口请求出错！\n");
            }
        }
        if(count($not_site_id) > 0) $this->logs("\nIP统计 site_id："  . implode('、', $not_site_id) . " 未下载！\n");
        $this->logs("IP统计 执行完成！");
    }

    private function logs($msg) {
        $logs_dir = dirname(dirname(__FILE__)) . '/logs/';
        if (!is_dir($logs_dir)) mkdir($logs_dir,0777,true);
        $logs_file = $logs_dir . date('Ymd') . '.txt';
        $f = fopen($logs_file, 'a');
        fwrite($f, date('Y-m-d H:i:s') . ' ' . $msg . "\n");
        fclose($f);
    }

    private function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
}