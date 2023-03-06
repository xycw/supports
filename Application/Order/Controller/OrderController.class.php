<?php
namespace Order\Controller;
use Aws\Iam\IamClient;
use Common\Controller\CommonController;
use Order\Model\OrderModel;
use Order\Model\OrderRemarkModel;
use Site\Model\SiteModel;
use Order\Model\OrderDeliveryModel;
class OrderController extends CommonController {
    public function OrderListMenuAction() {
        layout(false);
        //一定要关闭全局布局,否则如果其它模块调用时会可能可能会陷入无限循环
        $this->display(T('Order@Order/OrderListMenu'));
        layout(true);
    }
    public function SearchFormAction() {
        layout(false);
        //一定要关闭全局布局,否则如果其它模块调用时会可能可能会陷入无限循环
        $this->display(T('Order@Order/SearchForm'));
        layout(true);
    }
    public function listAction() {

        $order = new OrderModel();
        $where = array();
        $page_data = array();
        $join = array('LEFT JOIN __SITE__ s ON s.site_id=o.site_id');
        $join[] = 'JOIN __ORDERS_REMARK__ o_r ON o_r.site_id=o.site_id AND o_r.orders_id=o.orders_id';


        if (I('site_id') != '') {
            $params_site_id = I('site_id');
            $params_site_id = explode('_', $params_site_id);
            $where['o.site_id'] = array('IN', $params_site_id);
            $page_data['site_id'] = I('site_id');
            $this->assign('site_id_select', $params_site_id);
        }
        if (I('site_name') != '') {
            $where['s.site_name'] = I('site_name');
            $page_data['site_name'] = I('site_name');
        }
        if (I('user_id') != '') {
            $join[] = 'JOIN __USERS_TO_SITE__ u2s ON u2s.site_id=s.site_id';
            $where['u2s.user_id'] = I('user_id');
            $page_data['user_id'] = I('user_id');

            $this->assign('user_id_selected', I('user_id'));
        }
        if(I('is_received')!== '0') {

            //未签收，不加时间限制
            if (I('order_time_start') !== '' && I('order_time_end') !== '') {
                $order_time_start = date('Y-m-d 0:0:0', strtotime(I('order_time_start')));
                $order_time_end   = date('Y-m-d 23:59:59', strtotime(I('order_time_end')));
                $where['date_purchased'] = array('between', array($order_time_start, $order_time_end));
                $page_data['order_time_start'] = I('order_time_start');
                $page_data['order_time_end'] = I('order_time_end');
            } else {
                $order_time_start = mktime(0, 0, 0, date("m"), date("d")-1, date("Y"));
                $order_time_end   = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
                $_GET['order_time_start'] = $page_data['order_time_start'] = date('Y-m-d', $order_time_start);
                $_GET['order_time_end'] = $page_data['order_time_end'] = date('Y-m-d', $order_time_end);
                $where['date_purchased'] = array('between', array(date('Y-m-d H;i:s', $order_time_start), date('Y-m-d H;i:s', $order_time_end)));
            }
        }
        if (I('delivery_date_start') !== '' && I('delivery_date_end') !== '') {
            $url = U('Order/ExpressDelivery/list', array('site_id'=>I('site_id'), 'delivery_date_start'=>I('delivery_date_start'), 'delivery_date_end'=>I('delivery_date_end'), 'send_status'=>I('send_status'), 'page_num'=>I('page_num')), true, true);
            redirect($url);
        }
        if (I('last_email_template') != '') {
            $where['last_email'] = array('neq',I('last_email_template'));
            $page_data['last_email_template'] = I('last_email_template');
            $this->assign('last_email_template_selected', I('last_email_template'));
        }            
        if (I('payment_method') !== '') {
            if(I('payment_method') == 'Cash:moneytransfers') {
                $where['payment_module_code'] = array('in', array('moneytransfers','custom'));
            } elseif(urldecode(I('payment_method')) == 'Credit Card:security_pingpong') {
                $where['payment_module_code'] = array('in', array('security_pingpong','pingpong2f'));
            } elseif(urldecode(I('payment_method')) == 'Credit Card:xborderpay') {
                $where['payment_module_code'] = 'xborderpay';
            } elseif(urldecode(I('payment_method')) == 'Credit Card:AWX') {
                $where['payment_module_code'] = array('in', array('security_airwallex','Airwallex','awx'));
            } elseif(urldecode(I('payment_method')) == 'Credit Card:pacypay') {
                $where['payment_module_code'] = array('in', array('security_pacypay','Pacypay'));
            } elseif(urldecode(I('payment_method')) == 'Credit Card:gateway') {
                $where['payment_module_code'] = array('in', array('security_gateway','Gateway'));
            } elseif(urldecode(I('payment_method')) == 'Credit Card:cardpay') {
                $where['payment_module_code'] = 'Cardpay';
            }elseif(urldecode(I('payment_method')) == 'Credit Card:deepsea') {
                $where['payment_module_code'] = array('in', array('security_deep_sea','deepsea','Deep Sea'));
            } else {
                $where['payment_module_code'] = array('not in', array('moneytransfers','custom','security_pingpong','pingpong2f','xborderpay','security_airwallex','Airwallex','awx','security_pacypay','Pacypay','security_gateway','Gateway','Cardpay','security_deep_sea','deepsea','Deep Sea'));
            }
            $page_data['payment_method'] = I('payment_method');
            $this->assign('payment_method_selected', I('payment_method'));
        }
        if (I('customer_email') != '') {
            $where['customers_email_address'] = I('customer_email');
            $page_data['customer_email'] = I('customer_email');
        }
        if (I('customer_name') != '') {
            $where['customers_name'] = I('customer_name');
            $page_data['customer_name'] = I('customer_name');
        }
        if (I('ip_address') != '') {
            $where[] =$arrayName = array(
                                'o.ip_address' => array('like', '%' . I('ip_address') . '%'),
                            );
            $page_data['ip_address'] = I('ip_address');
        }
        if (I('customers_telephone') != '') {
            $where['customers_telephone'] = I('customers_telephone');
            $page_data['customers_telephone'] = I('customers_telephone');
        }
        if (I('zencart_order_no') != '') {
            if(($match = parseZencartNo(I('zencart_order_no')))!==false) {
                $order_no_prefix = $match['orders_prefix'];
                $zencart_order_no = $match['orders_id'];
                $where['o.orders_id'] = $zencart_order_no;
                $where['order_no_prefix'] = $order_no_prefix;
            } else {
                $order_no_prefix = '';
                $zencart_order_no = I('zencart_order_no');
                $where['o_r.order_no'] = I('zencart_order_no');
            }
            $page_data['zencart_order_no'] = I('zencart_order_no');
        }
        if (I('delivery_no') != '') {
            $join[] = 'JOIN __ORDERS_DELIVERY__ o_d ON o_d.site_id=o.site_id AND o_d.orders_id=o.orders_id';
            $where[] = array(
                                '_complex' => array(
                                    '_logic' => 'OR',
                                    'o_d.delivery_forward_no' => array('like', '%' . I('delivery_no') . '%'),
                                    'o_d.delivery_tracking_no' => array('like', '%' . I('delivery_no') . '%'),
                                )
                            );
            $page_data['delivery_no'] = I('delivery_no');
        }
        $where_jd = array(
                    '_complex' => array(
                        '_logic' => 'AND',
                        'is_rush_order' => 1,
                        'order_status_remark' => array('not in', array('已发货', '订单取消')),
                    )
                );
        if (I('is_rush') == '1') {
            $where[] = $where_jd;
        }
        if (I('is_received') === '0') {
            $join[] = 'LEFT JOIN __ORDERS_DELIVERY__ o_d ON o_d.site_id=o.site_id AND o_d.orders_id=o.orders_id';
            $where['o_d.delivery_status'] = array('not in', array('已签收'));
        }
        if (I('zencart_orders_status') != '') {
            $where['orders_status_name']     = I('zencart_orders_status');
            $page_data['zencart_orders_status'] = I('zencart_orders_status');
            $this->assign('zencart_orders_status_selected', I('zencart_orders_status'));
        }
        if (I('customer_feedback') !== '') {
            $where['customer_feedback'] = I('customer_feedback');
            $page_data['customer_feedback'] = I('customer_feedback');
            $this->assign('customer_feedback', I('customer_feedback'));
        }
        $where_daichuli = array(array('exp', 'IS NULL'), array('eq', '待处理'), array('eq', ''), 'OR');
        $where_tuikuan = array('in', array('全额退款', '拒付'));
        $where_is_paid = array(array('eq', '已确认付款'),array('eq', '待订货'), array('eq', '已订货'), array('eq', '待发货'), array('eq', '部分发货'), array('eq', '已发货'), 'OR');
        if (I('rp_no') != '' || I('order_status_remark') != '' || I('customer_feedback') != '') {
            if (I('rp_no') != '') {
                $where['rp_no'] = I('rp_no');
                $page_data['rp_no'] = I('rp_no');
            }
            if (I('order_status_remark') != '') {
                $order_status_remark = explode('_', I('order_status_remark'));
                if(in_array('待处理', $order_status_remark)) {
                    if(sizeof($order_status_remark)==1) {
                        $where['order_status_remark'] = $where_daichuli;
                    } else {
                        $where[] = array('_complex' => array(
                                                    '_logic' => 'OR',
                                                    'order_status_remark' => $where_daichuli,
                                                    'order_status_remark' => array('IN', $order_status_remark)
                                                ));
                    }
                } else {
                    $where['order_status_remark'] = array('IN', $order_status_remark);
                }
                $page_data['order_status_remark'] = I('order_status_remark');
                $this->assign('order_status_remark_select', $order_status_remark);
            }
            if (I('customer_feedback') != '') {
                if (I('customer_feedback') == '退款+拒付') {
                    $where['customer_feedback'] = $where_tuikuan;
                }
            }
        } elseif (I('is_paid') == 1) {
            $where['order_status_remark'] = $where_is_paid;
            $page_data['is_paid'] = 1;
        }
        if (I('taobao_no') != '' || I('products_name') != '') {
            $join[] = '__ORDERS_PRODUCTS__ op ON op.site_id=o.site_id AND op.orders_id=o.orders_id';
            $join[] = '__ORDERS_PRODUCTS_REMARK__ opr ON opr.site_id=op.site_id AND opr.orders_products_id=op.orders_products_id';
            if (I('taobao_no') != '') {
                $where['taobao_no'] = I('taobao_no');
                $page_data['taobao_no'] = I('taobao_no');
            }
            if (I('products_name') != '') {
                $where[] = array(
                                    '_complex' => array(
                                        '_logic' => 'OR',
                                        'products_name' => array('like', '%' . I('products_name') . '%'),
                                        'products_model' => array('like', '%' . I('products_name') . '%'),
                                    )
                                );
                $page_data['products_name'] = I('products_name');
            }
        }
        if(!isset($_GET['is_sale']) || $_GET['is_sale']=="") {
            $_GET['is_sale']=-1;
        }
        if($_GET['is_sale']!=-1) {
            $where['s.is_sale']=$_GET['is_sale'];
            $page_data['is_sale'] = $_GET['is_sale'];
        }
        $order_status_press_distinct = '';
        if (I('order_status_press') == '支付失败需催款') {
            $relation[] = 'delivery';
            $order_status_press_where = array('order_status_remark' => array(array('exp', 'IS NOT NULL'), array('not in', array('待处理', '', '付款失败or未付款', '付款确认中', '订单取消', '拒付')), 'AND'));
            if(!empty($where['date_purchased'])) $order_status_press_where['date_purchased'] = $where['date_purchased'];
            $customers_email_address_arr = $order->alias('o')->relation($relation)->join($join)->distinct(true)->where($order_status_press_where)->getField('customers_email_address',true);
            if(count($customers_email_address_arr) > 0) $where['customers_email_address'] = isset($where['customers_email_address']) && !empty($where['customers_email_address']) ? array(array('eq', $where['customers_email_address']),array('not in', $customers_email_address_arr), 'AND') : array('not in', $customers_email_address_arr);
            $order_status_press_remark = array(array('exp', 'IS NULL'), array('in', array('待处理', '', '付款失败or未付款', '付款确认中')), 'OR');
            $where['order_status_remark'] = isset($where['order_status_remark']) && !empty($where['order_status_remark']) ? array($where['order_status_remark'], $order_status_press_remark, 'AND') : $order_status_press_remark;
            $page_data['order_status_press'] = I('order_status_press');
        }
        if (in_array(I('send_status'), array(1,2))) {
            if(I('send_status') == 1){
                $where['o_r.send_status'] = 1;
            }else{
                $where['o_r.send_status'] = array('neq',1);
            }
            $page_data['send_status'] = I('send_status');
            $this->assign('send_status_selected', I('send_status'));
        }
        $page = I('page', 1);
        //当前页码
        if (isset($_GET['page_num'])) {
            $num = I('page_num');
            //每页显示订单数
            $page_data['page_num'] = $num;
        } else
                    $num = 300;
        if (I('order_status_press') == '支付失败需催款') {
            $sub_query = $order->alias('o')->relation(false)->join($join)->where($where)->field(array('distinct o.`orders_id`', 'o.site_id'))->group('o.customers_email_address')->select(false);
            $count = M()->table($sub_query . ' a')->count();
        }else{
            $count = $order->alias('o')->relation(false)->join($join)->where($where)->field(array('distinct o.`orders_id`', 'o.site_id'))->count();
        }
        if (I('order_by', '') != '') {
            $order_by = '';
            list($filed, $sort) = explode('_', I('order_by', ''));
            switch ($filed) {
                case 'delivery':
                                    $order_by = 'date_expected_supplier_send';
                break;
                case 'wedding':
                                    $order_by = 'date_require';
                break;
                default :
                                    $order_by = 'o.date_purchased';
                break;
            }
            if ($sort == 'asc') {
                $order_by .= ' asc';
            } else {
                $order_by .= ' desc';
            }
            $this->assign('order_by_field', $filed);
        } elseif (I('is_paid') == 1) {
            $order_by = 'field(order_status_remark,\'待订货\', \'已订货\', \'待发货\', \'部分发货\', \'已发货\'),o.date_purchased desc,o.site_id asc';
        } elseif((I('is_received') === '0')) {
            $order_by = 'o_r.date_send desc,o.site_id asc';
            $this->assign('order_by_field', '');
        } else {
            $this->assign('order_by_field', '');
            $order_by = 'o.date_purchased desc,o.site_id asc';
        }
        $relation = array('order_remark', 'site');
        if (I('is_received') === '0' || I('order_status_remark')=='已发货' || I('order_status_remark')=='部分发货'|| I('is_paid')=='1' ||
                (I('delivery_date_start') !== '' && I('delivery_date_end') !== '' || I('order_status_remark') == '')
                ) {
            $relation[] = 'delivery';
        }
        $last=I('last_modify',1);
        if($last_modify==1) {
            $last_modify=",last_modify desc";
        } else {
            $last_modify=",last_modify asc";
        }
        $order_by.=$last_modify;
        $page_data['last_modify'] =$last;
        $sql = D('orders_products')->alias('p')->where(array('p.site_id'=>array('exp','=o.site_id'), 'p.orders_id'=>array('exp','=o.orders_id')))->field('sum(products_quantity)')->select(false);
        
        if(I('get.parchase_table')){
            $join[] = '__ORDERS_PRODUCTS__ op ON op.site_id=o.site_id AND op.orders_id=o.orders_id';
            $join[] = '__ORDERS_PRODUCTS_REMARK__ opr ON opr.site_id=op.site_id AND opr.orders_id=op.orders_id AND opr.orders_products_id=op.orders_products_id';
            $sql_categories_name = M('orders_products_categories')->alias('opc')->where(array('opc.categories_id'=>array('exp', '=opr.categories_id')))->field('categories_name')->select(false);
            $sql_supplier_name = M('orders_products_supplier')->alias('ops')->where(array('ops.supplier_id'=>array('exp', '=opr.supplier_id')))->field('supplier_name')->select(false);
            $list = $order->alias('o')->relation(false)->join($join)->where($where)->field('s.order_no_prefix,o.orders_id,o_r.order_no,'.$sql_categories_name.' as categories_name,'.$sql_supplier_name.' as supplier_name,products_quantity,products_name,date_purchased,date_process,quantity_process,is_customized')->order($order_by)->select();
            
            $this->_export_purchase_table($list);
        }
        
        if (I('order_status_remark') == '部分发货') {
            $join[] = 'LEFT JOIN __ORDERS_PRODUCTS_REMARK__ opr ON opr.site_id=o.site_id AND opr.orders_id=o.orders_id AND opr.out_of_stock=1';
            $list = $order->alias('o')->relation($relation)->join($join)->where($where)->order($order_by)->field(array('distinct o.`orders_id`', 'o.*',$sql.' as num_products,opr.out_of_stock'))->page($page, $num)->select();
        }elseif (I('order_status_press') == '支付失败需催款') {
            $list = $order->alias('o')->relation($relation)->join($join)->where($where)->order($order_by)->field(array('distinct o.`orders_id`', 'o.*',$sql.' as num_products'))->group('o.customers_email_address')->page($page, $num)->select();
        }else{
            $list = $order->alias('o')->relation($relation)->join($join)->where($where)->order($order_by)->field(array('distinct o.`orders_id`', 'o.*',$sql.' as num_products'))->page($page, $num)->select();
            if(I('order_status_remark') == '待订货'){
                foreach($list as $k=>$v){
                    $orders_products_remark_count = M('orders_products_remark')->where(array('site_id'=>$v['site_id'], 'orders_id'=>$v['orders_id']))->field('item_status,count(*) as num')->group('item_status')->select();
                    $v['orders_products_remark_count'] = $orders_products_remark_count;
                    $list[$k] = $v;
                }
            }
        }
        // echo '<pre />';print_r($list);exit;
        //各状态订单数量统计
        $num_where_array = array(
                    'num_dcl'=>array('order_status_remark' => $where_daichuli),
                    'num_fksb'=>array('order_status_remark' => '付款失败or未付款'),
                    'num_fkqrz'=>array('order_status_remark' => '付款确认中'),
                    'num_yqrfk'=>array('order_status_remark' => '已确认付款'),
                    'num_ddh'=>array('order_status_remark' => '待订货'),
                    'num_ydh'=>array('order_status_remark' => '已订货'),
                    'num_dfh'=>array('order_status_remark' => '待发货'),
                    'num_bffh'=>array('order_status_remark' => '部分发货'),
                    'num_yfh'=>array('order_status_remark' => '已发货'),
                    'num_is_paid'=>array('order_status_remark'=>array('in', array('已确认付款','待订货','已订货','待发货','部分发货','已发货'))),
                    'num_jd'=>$where_jd,
                    'num_jf'=>array('customer_feedback' => '拒付'),
                );
        $order_remark_model = new OrderRemarkModel();
        foreach($num_where_array as $k=>$entry_where) {
            $num_status = $order_remark_model->where($entry_where)->count();
            $this->assign($k, $num_status);
        }
        $orders_delivery_model = new OrderDeliveryModel();
        $where_wqs = array('delivery_status' => array('not in', array('已签收')));
        $num_wqs = $orders_delivery_model->where($where_wqs)->count();
        //未签收
        $this->assign('num_wqs', $num_wqs);
        $model_site = new SiteModel();
        $options_site_name = array();
        $data_site = $model_site->where(array('status' => 1))->order('site_id asc')->select();
        if ($data_site) {
            foreach ($data_site as $row) {
                $options_site_name[$row['type']][$row['site_id']] = $row['site_name'];
            }
        }
        $this->assign('options_site_name', $options_site_name);
        $users = D('users_to_site')->alias('u2s')->join('__USERS__ u ON u.user_id=u2s.user_id')->field(array('distinct u.`user_id`', 'u.chinese_name'))->select();
        $options_users = array();
        foreach($users as $entry) {
            $options_users[$entry['user_id']] = $entry['chinese_name'];
        }
        //邮件模板
        $email_templates = M('email_template')->where(array('email_template_status'=>1))->order('email_template_name asc')->select();
        $options_email_templates = array();
        foreach($email_templates as $entry){
            $options_email_templates[$entry['email_template_id']] = $entry['email_template_name'];
        }
        $this->assign('options_email_templates', $options_email_templates);
        
        $this->assign('users', $options_users);
        $this->assign('data_shipping_status', C('shipping_status'));
        $this->assign('payment_methods', C('payment_methods'));
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('page_data', $page_data);
        $this->assign('num', $num);
        $this->assign('count', $count);
        $this->assign('page_num_data', array(1=>1,25=>25,50=>50,100=>100, 200=>200,300=>300, 500=>500));
        $this->assign('page_num_selected', $num);
        $this->assign('zencart_orders_status', C('order_status'));
        $this->assign('order_status_remark', C('order_status_remark'));
        $this->assign('data_is_send_from_manufacturer', C('data_is_send_from_manufacturer'));
        $this->assign('data_customer_feedback', C('customer_feedback'));
        $this->assign('options_send_status', array(1=>'是',2=>'否'));
        $this->display();
    }
    
    private function _export_purchase_table($products){
        layout(false);
        vendor('PHPExcel.PHPExcel');
        $field_array = array(
                    'A' => array('title' => '订单号',   'width' => 20,  'key' => 'orders_number'),
                    'B' => array('title' => '产品类别', 'width' => 30,  'key' => 'categories_name'),
                    'C' => array('title' => '订单数量', 'width' => 10,  'key' => 'products_quantity'),
                    'D' => array('title' => '产品名称', 'width' => 100, 'key' => 'products_name'),
                    'E' => array('title' => '订单日期', 'width' => 10,  'key' => 'date_purchased'),
                    'F' => array('title' => '供应商',   'width' => 10,  'key' => 'supplier_name'),
                    'G' => array('title' => '订货日期', 'width' => 10,  'key' => 'date_process'),
                    'H' => array('title' => '是否定制', 'width' => 10,  'key' => 'is_customized'),
                    'I' => array('title' => '订货数量', 'width' => 10,  'key' => 'quantity_process'),
                );
        $PHPExcel = new \PHPExcel();
        $currentSheet = $PHPExcel->getActiveSheet();
        $row = 1;
        foreach ($field_array as $k => $k_info) {
            $currentSheet->setCellValue($k . $row, $k_info['title']);
            $currentSheet->getColumnDimension($k)->setWidth($k_info['width']);
        }
        $row++;

        foreach($products as $index=>$product){
            foreach ($field_array as $k => $k_info) {
                if(empty($k_info['key'])) continue;
                if($k_info['key']=='orders_number'){
                    if(empty($product['order_no'])){
                        $order_no = $product['order_no_prefix'].$product['orders_id'];
                    }else{
                        $order_no = $product['order_no'];
                    }
                    $product[$k_info['key']] = $order_no;
                }elseif($k_info['key']=='date_purchased'){
                    $product[$k_info['key']] = date('Y-m-d', strtotime($product[$k_info['key']]));
                }
                $currentSheet->setCellValue($k . $row, $product[$k_info['key']]);

                $currentSheet->getStyle($k . $row)->getAlignment()->setShrinkToFit(true);
                
            }
               
            $currentSheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }
        $currentSheet->getStyle('A1:L' . $row)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                ),
                            )
                        )
                );
        $fileName = 'purchase_table_'.date('Ymdhis').'.xls';
        $objWriter = new \PHPExcel_Writer_Excel5($PHPExcel);
        $objWriter->save(DIR_FS_TEMP . $fileName);

        $link = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/'.str_replace(DIR_FS_ROOT, '', DIR_FS_TEMP).$fileName;
        redirect($link, 10,'系统将在10秒后跳转到.你也可以直接点击些链接   <a href="'.$link.'">点我下载</a>');
    }    
    
    private function _getProductImage($site_id, $orders_producst_id) {
        $row = D('orders_products')->where(array('site_id'=>$site_id,'orders_products_id'=>$orders_producst_id))->field('products_image')->find();
        if(empty($row['products_image'])) {
            return DIR_WS_UPLOADS . 'no-image.gif';
        } else {
            if(file_exists(DIR_FS_ROOT.ltrim($row['products_image'], '/'))){//ckfind上传
                return ltrim($row['products_image'], '/');
            }
            $small_image = rtrim(dirname($row['products_image']), '/').'/small/'.basename($row['products_image']);
            if(file_exists(DIR_FS_PRODUCT_IMAGE.$small_image)) {
                //小图
                return str_replace(DIR_FS_ROOT, '', DIR_FS_PRODUCT_IMAGE).$small_image;
            } elseif(file_exists(DIR_FS_PRODUCT_IMAGE.$row['products_image'])) {
                //原始图
                return str_replace(DIR_FS_ROOT, '', DIR_FS_PRODUCT_IMAGE) . $row['products_image'];
            } elseif(file_exists(DIR_FS_PRODUCT_IMAGE.'saas/'.$row['products_image'])) {
                //原始图
                return str_replace(DIR_FS_ROOT, '', DIR_FS_PRODUCT_IMAGE.'saas/'.$row['products_image']);
            } else {
                $link = M('Site')->where(array('site_id'=>$site_id))->getField('img_url') . $row['products_image'];
                $path = parse_url($link, PHP_URL_PATH);
                $cache_images = DIR_FS_PRODUCT_IMAGE . 'cache' . $path;
                if(file_exists($cache_images)) {
                    return str_replace(DIR_FS_ROOT, '', $cache_images);
                } else {
                    $state = @file_get_contents($link,0,null,0,1);//获取网络资源的字符内容
                    if($state) {
                        $cache_dir = dirname($cache_images);
                        if(!file_exists($cache_dir)) makeDir($cache_dir);
                        ob_start();//打开输出
                        readfile($link);//输出图片文件
                        $img = ob_get_contents();//得到浏览器输出
                        ob_end_clean();//清除输出并关闭
                        $f = fopen($cache_images, 'wb');
                        fwrite($f, $img);
                        fclose($f);
                        return str_replace(DIR_FS_ROOT, '', $cache_images);
                    } else {
                        return DIR_WS_UPLOADS . 'no-image.gif';
                    }
                }
            }
        }
    }
    //for view and edit
    //订单详情并赋值
    public function _order_detail($site_id, $order_id,$remark = false) {
        $order = new OrderModel();
        $where = array('site_id' => $site_id, 'orders_id' => $order_id);
        $order_info = $order->where($where)->relation(true)->find();
        if($remark) $order_info['all_products_remove'] = true;
        foreach ($order_info['product'] as $k => $product) {
            if($remark){
                if(!empty($product['orders_products_remark'])){
                    $product['orders_products_remark']['supplier_name'] = M('OrdersProductsSupplier')->where(array('supplier_id' => $product['orders_products_remark']['supplier_id']))->getField('supplier_name');
                    if($order_info['all_products_remove'] && $product['orders_products_remark']['remove'] == 0) $order_info['all_products_remove'] = false;
                }else{
                    if($order_info['all_products_remove']) $order_info['all_products_remove'] = false;
                }
            }
            $product['products_image'] = $this->_getProductImage($site_id, $product['orders_products_id']);
            $product['images'] = $this->getOrderProductImages($site_id, $product['orders_products_id']);
            $order_info['product'][$k] = $product;
        }

        $attachment = $this->getOrderAttachment($site_id, $order_id);
        $this->assign('attachemnt', $attachment);
        $this->assign('order_info', $order_info);
        $this->assign('is_rush_order', $order_info['is_rush_order']);
        $this->assign('order_status_remark_checked', $order_info['order_status_remark']);
        $this->assign('shipping_status', $order_info['shipping_status']);
        $this->assign('customer_feedback_checked', $order_info['customer_feedback']);        
    }
    public function viewAction($site_id, $order_id) {
        $this->_order_detail($site_id, $order_id);
        $this->assign('action', 'view');
        $email_history = array();
        if (!empty($order_info['email_logs'])) {
            $email_history = json_decode($order_info['email_logs'], true);
        }
        $this->assign('email_history', $email_history);
        if (IS_AJAX) {
            layout(false);
            $this->display('ajax_view');
        } else {
            $this->display();
        }
    }
    public function editAction($site_id, $order_id) {
        /* $order = new OrderModel();
        $where = array('site_id' => $site_id, 'orders_id' => $order_id);
        //判断物流是不是处理过此订单，处理过业务就不能进行编辑了
        $edit = true;
        $products_remark = M('orders_products_remark')->where($where)->field('item_status')->select();
        foreach($products_remark as $products_remark_entry){
            if($products_remark_entry['item_status']!='待订货(待处理)'){
                $edit = false;
                break;
            }
        }
        if($edit==false)
            $this->error('物流已处理此订单，业务无法进行编辑！'); */
        if (IS_POST) {
            $upload = new \Think\Upload();
            $upload->maxSize = 10485760;
            //  设置附件上传大小
            $upload->hash = false;
            $upload->replace = true;
            $upload->rootPath = DIR_FS_ORDER_PRODUCT;
            $upload->savePath = $site_id . '-' . $order_id . '/';
            //  设置附件上传目录
            $upload->subName = '';
            //附件路径
            if (isset($_FILES['attachment'])) {
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg', 'xls', 'xlsx', 'txt', 'pdf','doc','docx');
                //  设置附件上传类型
                foreach ($_FILES['attachment']['error'] as $k => $v) {
                    if ($v == UPLOAD_ERR_OK) {
                        $upload->saveName = '';
                        $upload_attachment = array(
                                                    'name' => iconv("UTF-8", "gb2312", $_FILES['attachment']['name'][$k]),
                                                    'type' => $_FILES['attachment']['type'][$k],
                                                    'tmp_name' => $_FILES['attachment']['tmp_name'][$k],
                                                    'error' => $_FILES['attachment']['error'][$k],
                                                    'size' => $_FILES['attachment']['size'][$k],
                                                );
                        $upload_info = $upload->upload(array('attachment' => $upload_attachment));
                        if (!$upload_info) {
                            //  上传错误提示错误信息
                            $this->error($upload->getError());
                        }
                    }
                }
            }
            $order_remark_model = D('orders_remark');
            $new_delivery_address = array(
                'delivery_name'=>I('delivery_name'),  
                'delivery_company'=>I('delivery_company'),
                'delivery_street_address'=>I('delivery_street_address'),
                'delivery_suburb'=>I('delivery_suburb'),
                'delivery_city'=>I('delivery_city'),
                'delivery_postcode'=>I('delivery_postcode'),
                'delivery_state'=>I('delivery_state'),
                'delivery_country'=>I('delivery_country'),
                'customers_telephone'=>I('customers_telephone'),
            );
            $order_remark = array(
                            'site_id' => $site_id,
                            'orders_id' => $order_id,

                            'order_pay' => I('order_pay'),
                            'order_pay_rmb' => I('order_pay_rmb'),
                            'express_cost' => I('express_cost'),
                            'shipping_cost' => I('shipping_cost'),
                            'other_cost' => I('other_cost'),
                            'other_cost_remark' => I('other_cost_remark'),
                            'rp_no' => I('rp_no'),
                            'express_no' => I('express_no'),
                            'shipping_no' => I('shipping_no'),
                            'order_remark' => I('order_remark'),
                            'order_status_remark' => I('order_status_remark'),
                            'customer_feedback' => I('customer_feedback'),
                            'date_require' => I('date_require'),
                            'date_expected_supplier_send' => I('date_expected_supplier_send'),
                            'date_send' => I('date_send'),
                            'sms_payment_notice' => I('sms_payment_notice'),
                            'manufacturers_id' => I('manufacturers_id'),
                            'is_send_from_manufacturer' => I('is_send_from_manufacturer'),
                            'shipping_status' => I('shipping_status'),
                            'is_rush_order' => I('is_rush_order'),
                            'new_delivery_address'=>json_encode($new_delivery_address),
                            'last_operator'=>session(C('USER_INFO').'.user_id'), 
                            'last_modify'=> date('Y-m-d H:i:s')                            
                        );
            if (I('orders_remark_id') == '') {
                $method = 'add';
            } else {
                $method = 'save';
                $order_remark['orders_remark_id'] = I('orders_remark_id');
            }
            $order_remark_model->$method($order_remark);
            if(empty(I('order_status_remark')) || I('order_status_remark') == '待处理') $this->logs($site_id . ' ' . $order_id . ' edit ' . I('order_status_remark'));
            $orders_products_remark = I('post.orders_products_remark');
            foreach($orders_products_remark['remove'] as $k=>$remove){
                $data = array();
                $where = array();
                $data['remove'] = I('all_products_remove') == 1 ? 1 : $remove;
                $data['remark'] = $orders_products_remark['remark'][$k];                
                if(strpos($k, '-')){
                    list($site_id, $orders_products_id) = explode('-', $k);
                    $data['site_id'] = $site_id;
                    $data['orders_id'] = $order_id;
                    $data['orders_products_id'] = $orders_products_id;
                    M('orders_products_remark')->add($data);
                }else{
                    $orders_products_remark_id = $k;
                    $where['orders_products_remark_id'] = $orders_products_remark_id;
                    M('orders_products_remark')->where($where)->save($data);
                }
            }
            $error = array();
            $new_product = I('post.new_product');
            foreach($new_product['model'] as $k=>$model){
                $qty = $new_product['qty'][$k];
                $product_name  = $new_product['product_name'][$k];
                $product_image = $new_product['product_image'][$k];
    
                if((empty($model) && (empty($product_name) || empty($product_image))) || empty($qty)) continue;
                
                if(empty($model)){
                    $check_product = array(
                        'product_name'=>$product_name,  
                        'product_images'=>$product_image,
                    );
                }else{
                    $check_product = M('products')->alias('p')->join('__PRODUCTS_DETAIL__ pd ON pd.product_id=p.product_id')->where(array('p.product_model'=>$model, 'pd.language_code'=>'en'))->find();    
                }
                
                if($check_product){
                    if(isset($max_orders_products_id)){
                        $max_orders_products_id++;
                    }else{
                        $max_orders_products_id = M('orders_products')->where(array('site_id'=>$site_id))->MAX('orders_products_id');
                        $max_orders_products_id = $max_orders_products_id+1;                        
                    }
                    $data = array(
                        'orders_products_id'=>$max_orders_products_id,
                        'site_id' => $site_id,
                        'orders_id' => $order_id,
                        'products_id'=>0,
                        'products_model'=>$model,
                        'products_name'=>$check_product['product_name'],
                        'products_quantity'=>$qty,
                        'products_image'=>$check_product['product_images'],
                        'add_from_sys'=>1
                    );
                    M('orders_products')->add($data);
                    M('orders_products_remark')->add(array('site_id'=>$site_id, 'orders_id'=>$order_id, 'orders_products_id'=>$max_orders_products_id,'remove'=>0, 'remark'=>$new_product['memo'][$k]));#memo 备注
                    foreach($new_product['attr'][$k]['option_name'] as $kk=>$option_name){
                        $option_value = $new_product['attr'][$k]['option_value'][$kk];
                        if(empty($option_name) || empty($option_value)) continue;
                        if(isset($max_orders_products_attributes_id)){
                            $max_orders_products_attributes_id++;
                        }else{
                            $max_orders_products_attributes_id = M('orders_products_attributes')->where(array('site_id'=>$site_id))->MAX('orders_products_attributes_id');
                            $max_orders_products_attributes_id = $max_orders_products_attributes_id+1;                            
                        }
                        $data = array(
                            'orders_products_attributes_id'=>$max_orders_products_attributes_id,
                            'orders_products_id'=>$max_orders_products_id,
                            'site_id' => $site_id,
                            'orders_id' => $order_id,
                            'products_options'=>$option_name,
                            'products_options_values'=>$option_value,
                        );
                        M('orders_products_attributes')->add($data);
                    }
                }else{
                    $error[] = 'sku:'.$model.'不存在产品库中,添加失败!';
                }
            }
           
            $post_delivery_id = I('post.delivery_id');
            $post_delivery_type = I('post.delivery_type');
            $post_delivery_quanlity = I('post.delivery_quanlity');
            $post_delivery_gift_quanlity = I('post.delivery_gift_quanlity');
            $post_delivery_weight = I('post.delivery_weight');
            $post_delivery_tracking_no = I('post.delivery_tracking_no');
            $post_delivery_remark = I('post.delivery_remark');
            $post_delivery_date = I('post.delivery_date');
            $post_del_delivery = I('post.del_delivery', array());
            $post_delivery_status = I('post.delivery_status', array());
            $post_delivery_forward_no = I('post.delivery_forward_no', array());
            foreach ($post_delivery_type as $k => $delivery_type) {
                if (empty($post_delivery_id[$k]) == false && in_array($post_delivery_id[$k], $post_del_delivery)) {
                    D('orders_delivery')->delete(array('where' => array('orders_delivery_id' => $post_delivery_id[$k])));
                    continue;
                }
                $data_delivery = array();
                if (empty($delivery_type) == false && empty($post_delivery_tracking_no[$k]) == false) {
                    $data_delivery = array(
                                            'site_id' => $site_id,
                                            'orders_id' => $order_id,
                                            'delivery_type' => $delivery_type,
                                            'delivery_quanlity' => $post_delivery_quanlity[$k],
                                            'delivery_gift_quanlity' => $post_delivery_gift_quanlity[$k],
                                            'delivery_weight' => $post_delivery_weight[$k],
                                            'delivery_tracking_no' => $post_delivery_tracking_no[$k],
                                            'delivery_forward_no' => $post_delivery_forward_no[$k],
                                            'delivery_date' => $post_delivery_date[$k],
                                            'delivery_remark' => $post_delivery_remark[$k],
                                            'delivery_status' => $post_delivery_status[$k],
                                        );
                    if (empty($post_delivery_id[$k]) == false) {
                        $data_delivery['orders_delivery_id'] = $post_delivery_id[$k];
                        D('orders_delivery')->save($data_delivery);
                    } else {
                        $data_delivery['add_time'] = date('Y-m-d H:i:s');
                        D('orders_delivery')->add($data_delivery);
                    }
                }
            }
            if(sizeof($error))
                $this->success(implode('<br>', $error), 'view/site_id/' . $site_id . '/order_id/' . $order_id);
            else
                $this->success('编辑成功', 'view/site_id/' . $site_id . '/order_id/' . $order_id);
        }
        $this->_order_detail($site_id, $order_id,true);
        if (empty($order_info['rp_no']) && empty($order_info['history']) == false &&
                        preg_match('~Transaction ID:(\d+)~', $order_info['history'][1]['comments'], $match)) {
            $order_info['rp_no'] = $match[1];
        }
        $this->assign('options_rush_order', array('0' => '非急单', '1' => '急单'));
        $this->assign('data_shipping_status', C('shipping_status'));
        $this->assign('customer_feedback', C('customer_feedback'));
        $this->assign('order_status_remark', C('order_status_remark'));
        $this->assign('data_delivery_type', C('delivery_type'));
        $this->assign('action', 'edit');
        $this->display('view');
    }
    public function orderHistoryAction($site_id, $order_id) {
        if (IS_POST) {
            $data = array(
                            'orders_id' => $order_id,
                            'orders_status_id' => I('orders_status_id'),
                            'comments' => I('comments'),
                            'customer_notified' => I('customer_notified', 0)
                        );
            //var_dump($site_id);exit;
            $resutlt = A('Order/Data')->updateOrderStatus($site_id, $data);
            $this->ajaxReturn(1);
        }
        $order_status = A('Order/Data')->getOrderStatus($site_id);
        $select_status = array();
        foreach ($order_status as $k => $v) {
            $select_status[$v['orders_status_id']] = $v['orders_status_name'];
        }
        $this->ajaxReturn($select_status, 'JSON');
    }
    public function delOrderFileAction($link) {
        $filename_path = ltrim(str_replace(__ROOT__, '', $link), '/');
        if (file_exists($filename_path)) {
            if (preg_match('#^' . DIR_WS_UPLOADS . '#', $filename_path)) {
                unlink($filename_path);
                echo 'success';
            } else {
                echo '无法删除此文件!';
            }
        } else {
            echo '删除的文件不存在!';
        }
    }
    /*
     * 追单
     */
    public function zhuidanAction($site_id, $order_id) {
        layout(false);
        $order = new OrderModel();
        $where = array('site_id' => $site_id, 'orders_id' => $order_id);
        $order_info = $order->where($where)->relation(true)->find();
        if ($order_info['payment_module_code'] == 'wire' || $order_info['payment_module_code'] == 'westernunion' || $order_info['payment_module_code'] == 'moneygram') {
            $content = 'We will process your order after you have paid it.' . "\n\n";
            if ($order_info['payment_module_code'] == 'wire') {
                $content .= "Bellow is our bank payment information:\n";
                $content .= "<b>Please remark your name in the remittance details .</b>\n"
                                        . "<b>Doesn't remark any order information in the remittance details ,otherwise your remittance maybe blocked .</b>\n\n ";
                $content .= "Company Address:No.2 Fushou Road, Ganzhou City, Jiangxi Province, China\n";
                $content .= "Account Name:Yimao Xie\n";
                $content .= "Account Number(IBAN):6013826502004692715\n";
                $content .= "Receiver Telephone:+86 15970984560\n";
                $content .= "Bank Name:Bank of China Ganzhou Branch\n";
                $content .= "Bank Address:No.72 Wenqing Road, Ganzhou City, Jiangxi Province, China\n";
                $content .= "BIC(Swift Code):BKCHCNBJ550\n\n";
            } else {
                $content .= "Bellow is our " . $order_info['payment_module_code'] . " payment information:\n\n";
                $content .= "First Name:Yimao\n";
                $content .= "Last Name:Xie\n";
                $content .= "Address:Dijinghaoyuan\n";
                $content .= "Zip Code:341000\n";
                $content .= "City:ganzhou\n";
                $content .= "Country:China\n";
                $content .= "Phone:+86 15970984560\n\n";
            }
        } else {
            $content = 'You can change another card to pay again or contact your creidt card company for the order.';
        }
        /*      
          if (preg_match('~failure~', $order_info['orders_status_name'])) {
          $content = 'You can change another card to pay again or contact your creidt card company for the order.';
          }else{
          $content = 'We will process your order after you have paid it.';
          }
         */
        $this->assign('content', $content);
        $this->assign('order_info', $order_info);
        $email_text = $this->fetch('zhuidan_text');
        $email_html = $this->fetch('zhuidan_html');
        $subject = 'Order Number ' . $order_id . ' Checkout Notice';
        $result = $this->send_email($order_info['customers_email_address'], $order_info['customers_name'], $subject, $email_text, $email_html);
        if ($result !== true) {
            $result = $this->send_email2($order_info['customers_email_address'], $order_info['customers_name'], $subject, $email_text, $email_html);
        }
        if ($result === true) {
            $model_order_remark = D('orders_remark');
            $data['num_zhuidan'] = array('exp', 'num_zhuidan+1');
            $model_order_remark->where(array('site_id' => $site_id, 'orders_id' => $order_id))->save($data);
            $this->success('追单邮件发送成功!');
        } else {
            $this->error('追单邮件发送失败(Error:' . $result . ')!');
        }
    }
    public function fahuotongzhiAction($site_id, $order_id) {
        layout(false);
        $order = new OrderModel();
        $where = array('site_id' => $site_id, 'orders_id' => $order_id);
        $order_info = $order->where($where)->relation(true)->find();
        $this->assign('order_info', $order_info);
        $subject = 'Order Number ' . $order_info['orders_id'] . ' Shipping Notice';
        $email_text = $this->fetch('fahuotongzhi_text');
        $email_html = $this->fetch('fahuotongzhi_html');
        $result = $this->send_email($order_info['customers_email_address'], $order_info['customers_name'], $subject, $email_text, $email_html);
        if ($result !== true) {
            $result = $this->send_email2($order_info['customers_email_address'], $order_info['customers_name'], $subject, $email_text, $email_html);
        }
        if ($result === true) {
            $model_order_remark = D('orders_remark');
            $data = array();
            $data['num_fahuotongzhi'] = array('exp', 'num_fahuotongzhi+1');
            $model_order_remark->where(array('site_id' => $site_id, 'orders_id' => $order_id))->save($data);
            $this->success('发货通知邮件发送成功!');
        } else {
            $this->error('发货通知邮件发送失败!' . $result);
        }
    }
    /*
     * 导出word格式订单
     */
    public function order_docAction($site_id, $order_id) {
        layout(false);
        $site_info = D('site')->where(array('site_id'=>$site_id))->field('order_no_prefix,type,system_cms')->find();
        if($site_info['type']==1 && $site_info['system_cms'] != 'easyshop')
                    $order_no = $site_info['order_no_prefix'].$order_id; else {
            $row = M('orders_remark')->where(array('site_id'=>$site_id, 'orders_id'=>$order_id))->field('order_no')->find();
            $order_no = $row['order_no'];
        }
        $order_file = $this->_get_order_dir($site_id, $order_id).$order_no.'.docx';
        //echo $order_file;die;
        //var_dump(file_exists($order_file));die;
        if(true || file_exists($order_file)==false) {
            $PHPWord = $this->_order_doc($site_id, $order_id);
            $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($PHPWord['obj'], 'Word2007');
            $xmlWriter->save($order_file);
        }
        if(file_exists($order_file)==false) {
            $this->error('生成WORD订单失败!');
        }
        $link = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/'.str_replace(DIR_FS_ROOT, '', $order_file).'?'.time();
        redirect($link);
        exit;
    }
    private function _get_order_dir($site_id, $order_id) {
        $dir = DIR_FS_ORDER_PRODUCT . $site_id .'/'. floor($order_id/5000).'/'.$order_id.'/';
        if(file_exists($dir)===false) makeDir($dir);
        return $dir;
    }
    private function _order_doc($site_id, $order_id) {
        $order = new OrderModel();
        $where = array('site_id' => $site_id, 'orders_id' => $order_id);
        $order_info = $order->where($where)->relation(array('order_remark', 'site', 'product'))->find();

        $order_no = empty($order_info['order_no']) ? $order_info['order_no_prefix'] . $order_info['orders_id'] : $order_info['order_no'];
        $quanlity_total = 0;
        foreach ($order_info['product'] as $entry) {
            $quanlity_total += $entry['products_quantity'];
        }
        Vendor('PhpOffice.PhpOffice_Autoloader');
        $PHPWord = new \PhpOffice\PhpWord\PhpWord();
        //56.69291338582492=10mm
        $sectionStyle = array(
                    'orientation' => \PhpOffice\PhpWord\Style\Section::ORIENTATION_PORTRAIT,
                    'marginTop' => 56,
                    'marginBottom' => 56,
                    'marginLeft' => 56,
                    'marginRight' => 56,
                );
        $section = $PHPWord->addSection($sectionStyle);
        // Add footer
        $header = $section->addHeader();
        $total_page = ceil(sizeof($order_info['product'])/10);
        $header->addPreserveText('订单号：'.$order_no.'第{PAGE}页，共'.$total_page.'页', array('align' => 'center'));
        //$header->addText('订单号：'.$order_info['order_no_prefix'] . $order_info['orders_id'].'第{PAGE}页，共'.$total_page.'页');
        $table = $section->addTable(array('alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER));
        $table->getStyle()->setBorderSize(0);
        $table->addRow(567);
        $cell = $table->addCell(5320);
        $cell->addText('备注:'.$order_info['order_remark'], array('bold' => true, 'color' => 'ff0000'), array('align' => 'left'));
        $cell->getStyle()->setVAlign(\PhpOffice\PhpWord\Style\Cell::VALIGN_CENTER);
        $cell = $table->addCell(5320);
        //$cell->addText($order_info['site_name'], array('bold' => true), array('align' => 'right'));
        $cell->addText('', array('bold' => true), array('align' => 'right'));
        $cell->getStyle()->setVAlign(\PhpOffice\PhpWord\Style\Cell::VALIGN_CENTER);
        $pStyle = array('spacing' => 50);
        $fStyle = array();
        $table->addRow();
        $cell = $table->addCell(5320, array('vAlign' => 'top'));
        $cell->addTextBreak(1);
        $cell->addText('送货地址', $fStyle, $pStyle);
        $textrun = $cell->addTextRun();
        $textrun->addText($order_info['customers_name'] . '   ');
        $textrun->addText($order_no . '    ', array('color' => 'ff0000'));
        $textrun->addText(date('Y-m-d'), $fStyle, $pStyle);
        $cell->addText($order_info['delivery_name'], $fStyle, $pStyle);
        $cell->addText($order_info['delivery_street_address'].(empty($order_info['delivery_suburb'])?'':','.$order_info['delivery_suburb']) . ', ' . $order_info['delivery_city'] . ', ' . $order_info['delivery_state'] . ' ' . $order_info['delivery_postcode'] . ', ' . $order_info['delivery_country'], $fStyle, $pStyle);
        $cell = $table->addCell(5320, array('vAlign' => 'top'));
        $cell->addTextBreak(1);
        $cell->addText('订单号:' . $order_no, $fStyle, $pStyle);
        $cell->addText('订单总额:   ' . round($order_info['order_total'] * $order_info['currency_value'], 2) . $order_info['currency'], $fStyle, $pStyle);
        $cell->addText('订单日期:', $fStyle, $pStyle);
        $order_info['payment_method'] = str_replace('&nbsp;', '', $order_info['payment_method']);
        $order_info['payment_method'] = strip_tags($order_info['payment_method']);
        $cell->addText('支付方式:   ' . ($order_info['payment_method']=='inline'?'Credit Cards':$order_info['payment_method']), $fStyle, $pStyle);
        $textrun = $cell->addTextRun();
        $textrun->addText('总件数: ', $fStyle, $pStyle);
        $textrun->addText($quanlity_total, array('color' => 'ff0000', 'bold' => true, 'size' => 18));
        $textrun->addText(' 件          货运方式:', $fStyle, $pStyle);
        if ($order_info['shipping_module_code'] == 'zones' || $order_info['shipping_module_code'] == 'faster' || $order_info['shipping_module_code']=='固定运费') {
            $string_delivery_type = '快速faster';
        } else {
            $string_delivery_type = '标准standard';
        }
        $textrun->addText($string_delivery_type, array('color' => 'ff0000', 'bold' => true, 'size' => 18));
        $cell->addText('制单人:', $fStyle, $pStyle);
        $table->addRow();
        $cell = $table->addCell(5320, array('vAlign' => 'top'));
        $sub_table_left = $cell->addTable();
        $cell = $table->addCell(5320, array('vAlign' => 'top'));
        $sub_table_right = $cell->addTable();
        $i = 0;

        foreach ($order_info['product'] as $entry) {
            if($i!=0&&$i%10==0) {
                $section = $PHPWord->addSection($sectionStyle);
                $cur_page = ceil($i%10)+1;
                //$section->addHeader()->addText('订单号：'.$order_info['order_no_prefix'] . $order_info['orders_id'].'第{PAGE}页，共'.$total_page.'页');
                $table = $section->addTable(array('alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER));
                $table->getStyle()->setBorderSize(0);
                $table->addRow();
                $cell = $table->addCell(5320, array('vAlign' => 'top'));
                $sub_table_left = $cell->addTable();
                $cell = $table->addCell(5320, array('vAlign' => 'top'));
                $sub_table_right = $cell->addTable();
            }
            if ($i % 2 == 0) {
                $sub_table = $sub_table_left;
            } else {
                $sub_table = $sub_table_right;
            }
            $sub_table->addRow();
            $cell = $sub_table->addCell(2240);

            try {
                $entry['products_image'] = $this->_getProductImage($site_id, $entry['orders_products_id']);
                $cell->addImage(trim($entry['products_image']), array('width' => 141, 'height' => 141, 'align' => 'center'));
            }
            catch (InvalidImageException $e) {
                $cell->addText('Invalid image');
            }
            $cell = $sub_table->addCell(600, array('vAlign' => 'center'));
            $textrun = $cell->addTextRun();
            $textrun->addText($entry['products_quantity'], array('bold' => true, 'size' => 25));
            $textrun->addText('x');
            $cell = $sub_table->addCell(2480);
            $cell->addTextBreak(1);
            $textrun = $cell->addTextRun();
            $entry['products_name'] = str_replace('&', ' ', $entry['products_name']);
            // &字符会引起word打不开
            $products_keyword = array('women', 'Nike', 'Youth', 'Game', 'Elite', 'Limited', 'Toddler', 'Men', 'Kid');
            $products_keyword_pos = array();
            foreach ($products_keyword as $keyword) {
                $products_name = $entry['products_name'];
                do {
                    $pos = stripos($products_name, $keyword);
                    if ($pos !== false) {
                        $products_keyword_pos[$pos] = $keyword;
                        $products_name = substr($products_name, ($pos + 1));
                    }
                }
                while ($pos !== false);
            }
            if (sizeof($products_keyword_pos) && $entry['orders_products_remark']['remove']==0) {
                $l = strlen($entry['products_name']);
                for ($pos = 0; $pos < $l;) {
                    if (isset($products_keyword_pos[$pos])) {
                        $len = strlen($products_keyword_pos[$pos]);
                        $textrun->addText(substr($entry['products_name'], $pos, $len), array('size' => 10, 'bold' => true, 'color' => 'ff0000', 'italic' => true));
                        //关键词加粗
                        $pos += $len;
                    } else {
                        $textrun->addText(substr($entry['products_name'], $pos, 1), array('size' => 10));
                        $pos++;
                    }
                }
            } else {
                if($entry['orders_products_remark']['remove']==1){
                    $textrun->addText($entry['products_name'], array('size' => 10, 'color' => 'ffffff', 'bgcolor' => 'ff0000', 'strikethrough'=>true));
                }else
                    $textrun->addText($entry['products_name'], array('size' => 10));
            }
            if (!empty($entry['attribute'])) {
                foreach ($entry['attribute'] as $attribute) {
                    $textrun = $cell->addTextRun();
                    $textrun->addText($attribute['products_options'] . ':');
                    $textrun->addText($attribute['products_options_values'], array('bold' => true, 'size' => 10));
                }
            }
            $textrun = $cell->addTextRun();
            $textrun->addText('SKU:');
            $textrun->addText($entry['products_model'], array('bold' => true));
            $textrun = $cell->addTextRun();
            $textrun->addText('备注:');
            if($entry['orders_products_remark']['remove']==1){
                $textrun->addText('取消此项目', array('color' => 'ff0000', 'bold' => true));
            }elseif($entry['orders_products_remark']['remark']){
                $textrun->addText($entry['orders_products_remark']['remark'], array('color' => 'ff0000'));
            }
            $i++;
        }
        $section->addTextBreak(1);
        $section->addText('发货记录:');
        return array('obj' => $PHPWord, 'filename' =>  $order_no . '.docx');
    }
    /*
     * 生成尚品婚纱订单
     */
    function sporderAction($site_id, $order_id) {
        layout(false);
        $order = new OrderModel();
        $where = array('site_id' => $site_id, 'orders_id' => $order_id);
        $order_info = $order->where($where)->relation(array('order_remark', 'product'))->find();
        Vendor('PHPExcel.PHPExcel');
        $tpl_xls = APP_PATH . '../Public/tpl_xls/sp-order-tpl.xls';
        foreach ($order_info['product'] as $k => $product) {
            $php_excel_reader = new \PHPExcel_Reader_Excel5();
            if (!$php_excel_reader->canRead($tpl_xls)) {
                $this->error('无法读取上海纵驰国际快递的excel模板!');
            }
            $PHPExcel = $php_excel_reader->load($tpl_xls);
            $currentSheet = $PHPExcel->getSheet(0);
            $currentSheet->setCellValueExplicit('B2', date('Y-m-d'));
            if ($order_info['date_expected_supplier_send'] == '0000-00-00') {
                $timestamp = mktime(0, 0, 0, date("m"), date("d") + 15, date("Y"));
            } else {
                $timestamp = strtotime($order_info['date_expected_supplier_send']) - 86400 * 7;
            }
            $date_expected_supplier_send = date('Y-m-d', $timestamp);
            $currentSheet->setCellValueExplicit('B3', $date_expected_supplier_send);
            $currentSheet->setCellValueExplicit('E1', $order_info['order_no']);
            $currentSheet->setCellValueExplicit('I1', $product['taobao_no']);
            $attribute_zh = $this->_translate_attr($product['attribute']);
            foreach ($attribute_zh as $attr) {
                switch (strtolower($attr['products_options'])) {
                    case 'color':
                                        case 'farbe':
                                            $currentSheet->setCellValueExplicit('B12', $attr['products_options_value_zh']);
                    case 'bust(cm)':
                                        case 'bust':
                                        case 'brustumfang':
                                            $currentSheet->setCellValueExplicit('B5', $attr['products_options_value_zh']);
                    break;
                    case 'waist(cm)':
                                        case 'waist':
                                        case 'taille':
                                            $currentSheet->setCellValueExplicit('B6', $attr['products_options_value_zh']);
                    break;
                    case 'hips(cm)':
                                        case 'hips':
                                        case 'hüftumfang':
                                            $currentSheet->setCellValueExplicit('B7', $attr['products_options_value_zh']);
                    break;
                    case 'hollow to floor(cm)':
                                        case 'hollow to floor':
                                        case 'länge vom schlüsselbein zum bode':
                                            $currentSheet->setCellValueExplicit('B8', $attr['products_options_value_zh']);
                    break;
                }
            }
            $currentSheet->setCellValueExplicit('B13', $order_info['order_remark']);
            $products_images = $this->getOrderProductImages($site_id, $product['orders_products_id']);
            array_unshift($products_images, DIR_WS_UPLOADS . $product['product_image']);
            foreach ($products_images as $entry) {
                $img = new \PHPExcel_Worksheet_Drawing();
                $img->setPath($entry);
                //写入图片路径
                $img->setWidth(175);
                //写入图片宽度
                $img->setHeight(270);
                //写入图片高度
                $img->setOffsetX(1);
                //写入图片在指定格中的X坐标值
                $img->setOffsetY(1);
                //写入图片在指定格中的Y坐标值
                $img->setRotation(1);
                //设置旋转角度
                $img->getShadow()->setVisible(true);
                //
                $img->getShadow()->setDirection(50);
                //
                $img->setCoordinates('C2');
                //设置图片所在表格位置
                $img->setWorksheet($currentSheet);
                //把图片写到当前的表格中
            }
            $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
            $objWriter->save(DIR_WS_UPLOADS . 'Order/' . $site_id . '-' . $order_id . '/' . iconv("UTF-8", "gb2312", '订单' . $order_info['order_no'] . '-' . $k . '-fortune.xls'));
        }
        $this->success('尚品婚纱订单生成成功！');
    }
    public function brand_rakingAction() {
        $model_orders_remark = D('orders_remark');
        $where = array('manufacturers_name' => array('neq', ''), 'order_status_remark' => array('in', '待收货,已发货'));
        if (I('country', '') !== '') {
            $where['customers_country'] = I('country');
        }
        $brand_raking = $model_orders_remark->alias('o_r')
                        ->field(array('manufacturers_name', 'count(manufacturers_name) as num'))
                        ->join('JOIN __ORDERS__ o ON o.site_id=o_r.site_id AND o_r.orders_id=o.orders_id')
                        ->join('JOIN __ORDERS_PRODUCTS__ op ON op.site_id=o.site_id AND op.orders_id=o.orders_id')
                        ->where($where)
                        ->select(array('group' => 'manufacturers_name', 'order' => 'num desc'));
        $this->assign('brand_raking', $brand_raking);
        $this->display();
    }
    public function statisticsAction() {
        $order = new OrderModel();
        $join = array('__ORDERS_REMARK__ o_r ON o_r.site_id=o.site_id AND o_r.orders_id=o.orders_id');
        $where = array('order_status_remark' => array('in', array('已订货', '待订货', '', '已发货')));
        $month_start = date('Y-m-1 0:0:0', strtotime(I('month_start', date('Y-m'))));
        $month_end = date('Y-m-t 23:59:59', strtotime(I('month_end', date('Y-m'))));
        $where['date_purchased'] = array('between', array($month_start, $month_end));
        if (I('country', '') !== '') {
            $where['customers_country'] = I('country');
        }
        if (I('site_name', '') !== '') {
            $join[] = 'LEFT JOIN __SITE__ s ON s.site_id=o.site_id';
            $where['site_name'] = I('site_name');
        }
        $view_type = I('view_type', 'd');
        $field = array('date_purchased', 'count(*) as num');
        switch ($view_type) {
            case 'd':
                            $field[] = 'DATE_FORMAT(date_purchased, \'%Y-%m-%e\') as date';
            break;
            case 'w':
                            $field[] = 'DATE_FORMAT(date_purchased, \'%w\') as date';
            break;
            case 'm':
                            $field[] = 'DATE_FORMAT(date_purchased, \'%Y-%m\') as date';
            break;
            case 'c':
                            $field[] = 'customers_country as date';
            break;
        }
        $order->field($field);
        $order->relation(false)
                        ->alias('o')
                        ->join($join)
                        ->where($where)
                        ->group('date')
                        ->order(array('date asc'));
        //      echo $order->buildSql();exit;
        $data = $order->select();
        $this->assign('month_start', date('Y-m', strtotime($month_start)));
        $this->assign('month_end', date('Y-m', strtotime($month_end)));
        $this->assign('data', $data);
        $this->assign('view_type_select', array('d' => '日视图', 'w' => '周视图', 'm' => '月视图', 'c' => '国家视图'));
        $this->assign('view_type', $view_type);
        $this->display();
    }
    //邮件批量发送
    public function multi_emailAction() {
        layout(false);
        $site_to_orders = I('site_to_orders', array());
        if(empty($site_to_orders)) {
            echo '请勾选对应的订单';
            exit;
        }
        $order_list = array();
        $order = new OrderModel();
        foreach($site_to_orders as $entry) {
            list($site_id, $orders_id) = explode('-', $entry);
            $order_info = $order->where(array('site_id'=>$site_id, 'orders_id'=>$orders_id))->relation(array('order_remark','site'))->find();
            $order_info['email_templates'] = $this->_order_email_template($order_info);
            $order_list[] = $order_info;
        }
        $list_email_template = M('email_template')->where(array('email_template_status' => 1))->select();
        $option_email_template = array();
        foreach($list_email_template as $entry){
            $option_email_template[$entry['email_template_id']] = $entry['email_template_name'];
        }
        $this->assign('option_email_template', $option_email_template);
        $this->assign('order_list', $order_list);
        $this->display();
    }
    //获取订单邮件模板
    private function _order_email_template($order_info) {
        $order_info['username'] = session(C('USER_INFO').'.username');
        $email_templates = D('email_template')->where(array('email_template_status' => 1))->select();
        $is_sale=M("Site")->where('site_id='.$order_info['site_id'])->getField('is_sale');
        $options_email_templates = array();
        foreach ($email_templates as $entry) {
            if (empty($entry['condition'])) {
                $options_email_templates[$entry['email_template_id']] = $entry['email_template_title'];
            } else {
                $condition = json_decode($entry['condition'], true);
                if (isset($condition['order_status_remark']) && in_array($order_info['order_status_remark'], $condition['order_status_remark'])) {
                    $match = true;
                    //                    var_dump($condition);exit;
                    if (isset($condition['condition']) && is_array($condition['condition'])) {
                        $match = true;
                        foreach ($condition['condition'] as $k => $v) {
                            if($v=='*') continue;
                            if(is_string($v)) {
                                if (!isset($order_info[$k]) || $order_info[$k] != $v) {
                                    $match = false;
                                    break;
                                }
                            } elseif(is_array($v)) {
                                if (!isset($order_info[$k]) || !in_array($order_info[$k] ,$v)) {
                                    $match = false;
                                    break;
                                }
                            }
                        }
                    }
                } else
                                    $match = false;
                if ($match)
                                    $options_email_templates[$entry['email_template_id']] = $entry['email_template_name'];
            }
        }
        return $options_email_templates;
    }
    /*
     * 邮件回复模板
     */
    public function emailAction($site_id, $order_id) {
        $order = new OrderModel();
        $where = array('site_id' => $site_id, 'orders_id' => $order_id);
        $order_info = $order->where($where)->relation(true)->find();
        //  echo '<pre />';print_r($order_info);exit;
        $orders_delivery = D('orders_delivery')->where(array('orders_id'=>$order_id))->select();
        $order_info['delivery_status'] = '未签收';
        if($orders_delivery) {
            $is_received = true;
            foreach($orders_delivery as $entry) {
                if($entry['delivery_status']!='已签收') {
                    $is_received = false;
                    break;
                }
            }
            if($is_received)
                            $order_info['delivery_status'] = '已签收';
        }
        $this->assign('order_info', $order_info);
        $email_templates = D('email_template')->where(array('email_template_status' => 1))->select();
        $options_email_templates=$tmp_email_templates = $this->_order_email_template($order_info);
        $email_history = array();
        if (!empty($order_info['email_logs'])) {
            $email_history = json_decode($order_info['email_logs'], true);
        }
        $this->assign('email_history', $email_history);
        $this->assign('options_email_templates', $options_email_templates);
        if (IS_POST && I('post.action', '') == 'send') {
            if(I('email_templates_id')) {
                $template = $this->_parse_email_template(I('email_templates_id'), $site_id, $order_id);
                $email_title = $template['email_template_title'];
                $email_content = $template['email_template_content'];
                $system_depart = M("Site")->where('site_id='.$site_id)->getField('system_depart');
                if($system_depart == 21) $email_content = str_replace(' jersey ', ' bags ', str_replace(' jerseys ', ' bags ', $email_content));
            } else {
                $email_title = I('email_title', '');
                $email_content = I('email_content', '', 'html_compress');
            }
            $email_text = $email_html = $email_content;
            $result = false;
            $email_data = array();
            if (!empty($order_info['email_data'])) {
                $email_data = json_decode($order_info['email_data'], true);
            }
            if (sizeof($email_data) == 0)
                            $this->error('你还没有配置网站的用于发送邮件的邮箱信息，无法发送!');
            shuffle($email_data);
            foreach ($email_data as $emai_info) {
                $result = $this->send_email($order_info['customers_email_address'], $order_info['customers_name'], $email_title, $email_text, $email_html, $emai_info['address'], $emai_info['password'], $emai_info['smtp'], $emai_info['port']);
                if ($result !== true) $result = $this->send_email2($order_info['customers_email_address'], $order_info['customers_name'], $email_title, $email_text, $email_html, $emai_info['address'], $emai_info['password'], $emai_info['smtp'], $emai_info['port']);
                if ($result === true) break;
            }
            //        var_dump($result);exit;
            if ($result === true) {
                $model_order_remark = D('orders_remark');
                if (empty($order_info['email_logs'])) {
                    $email_logs = array();
                } else {
                    $email_logs = json_decode($order_info['email_logs'], true);
                }
                
                $email_templates_id = I('email_templates', '0');
                
                if(!$email_templates_id)
                    $email_templates_id = I('email_templates_id', '0');
                if ($email_templates_id != '0' && isset($options_email_templates[$email_templates_id])) {
                    if (!isset($email_logs[$order_info['order_status_remark']]))
                        $email_logs[$order_info['order_status_remark']] = array();
                    $last_email = $email_templates_id;    
                    $email_logs[$order_info['order_status_remark']][] = array(
                                            'email_template_name' => $options_email_templates[$email_templates_id],
                                            'time' => date('Y-m-d H:i:s'),
                                        );
                } else {
                    if (!isset($email_logs['无模板']))
                                            $email_logs[$order_info['无模板']] = array();
                    $email_logs['无模板'][] = array(
                                            'email_template_name' => $email_title,
                                            'time' => date('Y-m-d H:i:s')
                                        );
                    $last_email = 0;    
                }
                $model_order_remark->where(array('site_id' => $site_id, 'orders_id' => $order_id))->save(array('email_logs' => json_encode($email_logs),'send_status'=>1, 'last_email'=>$last_email));
                $this->success('邮件发送成功!');
            } else {
                $model_order_remark = D('orders_remark');
                $model_order_remark->where(array('site_id' => $site_id, 'orders_id' => $order_id))->save(array('send_status'=>2));
                $this->error('邮件发送失败(Error:' . $result . ')!');
            }
        }
        if (IS_AJAX) {
            if (I('action', '') == 'get_template') {
                $data = array(
                                    'email_template_title' => '',
                                    'email_template_content' => ''
                                );
                $email_templates_id = I('email_templates_id', '0');
                $email_data = $this->_parse_email_template($email_templates_id, $site_id, $order_id);
                $data = array(
                                    'email_template_title' => $email_data['email_template_title'],
                                    'email_template_content' => $email_data['email_template_content']
                                );
                $this->ajaxReturn($data);
            }
        }
        if (IS_AJAX) {
            layout(false);
        }
        $this->display();
    }
    private function _parse_email_template($email_templates_id, $site_id, $order_id) {
        $order = new OrderModel();
        $where = array('site_id' => $site_id, 'orders_id' => $order_id);
        $order_info = $order->where($where)->relation(true)->find();
        $order_info['order_total']=round($order_info['order_total'] * $order_info['currency_value'],2);
        // switch ($order_info['payment_module_code']) {
        //     case 'moneytransfers':
        //         $self_discount = 0.08;
        //         break;
        //     case 'wire':
        //         $self_discount = 0.10;
        //         break;
        //     case 'westernunion':
        //         $self_discount = 0.10;
        //         break;
        //     case 'moneygram':
        //         $self_discount = 0.10;
        //         break;
        //     default:
        //         $self_discount = 0;
        //         break;
        // }
        $order_info['order_total_dicount'] = round($order_info['order_total'] * (1-0.08) ,2);
        //根据接口进行折扣
        //$order_info['customer_service_name'] = session(C('USER_INFO') . '.username'); //客服名称
        //客服邮件模板变量调用
        $users_mail_template_params = D('users')->where(array('user_id'=>session(C('USER_INFO') . '.user_id')))->getField('mail_template_params');
        if(!empty($users_mail_template_params)){
            $mail_template_params = json_decode($users_mail_template_params, true);
            foreach($mail_template_params as $params_entry){
                $order_info[$params_entry['key']] = $params_entry['value'];
            }
        }
        if(!isset($order_info['customer_service_email'])){
            $email_data = json_decode($order_info['email_data'], true);
            $order_info['customer_service_email'] = isset($email_data[0]['address'])?$email_data[0]['address']:'';
        }
        
        $delivery_address = $order_info['delivery_name'] . '<br>' . $order_info['delivery_street_address'] . '<br>';
        if (empty($order_info['delivery_suburb']) == false)
                    $delivery_address .= $order_info['delivery_suburb'] . '<br>';
        $delivery_address .= $order_info['delivery_city'] . ', ' . $order_info['delivery_state'] . '<br>      ' . $order_info['delivery_postcode'] . '<br>' . $order_info['delivery_country'];
        $order_info['customer_delivery_address'] = $delivery_address;
        $order_table = '<table border="1" cellspacing="0" cellpadding="2" width="800px"><tr style="background:#fff;"><th>IMG</th><th>Title&attribute</th><th>Qty.</th><th>Model</th><th>Unit</th><th>Total</th></tr>';
        foreach ($order_info['product'] as $product) {
            if($product['orders_products_remark']['remove']) continue;
            $order_table .= '<tr>';
            $products_image = $this->_getProductImage($site_id, $product['orders_products_id']);

            $order_table .= '<td style="width:110px;"><img src="http://' . $_SERVER['HTTP_HOST'].'/'.$products_image . '" width="100px"></td>';
            $order_table .= '<td style="width:300px;">' . $product['products_name'];
            if (!empty($product['attribute'])) {
                $order_table .= '<ul>';
                foreach ($product['attribute'] as $attribute) {
                    $order_table .= '<li>' . $attribute['products_options'] . ':' . $attribute['products_options_values'] . '</li>';
                }
                $order_table .= '</ul>';
            }
            $order_table .= '</td>';
            $order_table .= '<td>' . $product['products_quantity'] . '</td>';
            $order_table .= '<td>' . $product['products_model'] . '</td>';
            $order_table .= '<td>' . ($order_info['type'] == 10 ? round($product['final_price'], 2) : round($product['final_price'] * $order_info['currency_value'], 2)) . $order_info['currency'] . '</td>';
            $order_table .= '<td>' . ($order_info['type'] == 10 ? round($product['final_price'], 2)*$product['products_quantity'] : round($product['final_price'] * $order_info['currency_value'], 2)*$product['products_quantity']) . $order_info['currency'] . '</td>';
            $order_table .= '</tr>';
        }
        if (empty($order_info['order_total_detail']) == false) {
            $order_total = json_decode($order_info['order_total_detail'], true);
            if (is_array($order_total)) {
                foreach ($order_total as $entry) {
                    $order_table .= '<tr>';
                    $order_table .= '<th colspan="5" style="text-align:right;">' . $entry['title'] . '</th>';
                    $order_table .= '<td style="text-align:right;">' . $entry['text'] . '</td>';
                    $order_table .= '</tr>';
                }
            }
        }
        $order_table .= '</table>';
        $order_info['order_table'] = $order_table;
        $row_delivery = D('orders_delivery')->where($where)->select();
        if (empty($row_delivery) == false) {
            $tracking_number = '';
            foreach ($row_delivery as $entry) {
                //$tracking_number .= $entry['delivery_type'] . ' ' . $entry['delivery_tracking_no'] . ',';
                //$tracking_number .= $entry['delivery_tracking_no'] . ' (' .$entry['delivery_type'] . ')'.',';
				$tracking_number .= '<br>' . $entry['delivery_tracking_no'] . ',';
            }
            if(!empty($tracking_number)) $tracking_number = $tracking_number . '<br>';
        } else
                $tracking_number = '';
        $order_info['tracking_number'] = $tracking_number;
        if(!empty($order_info['order_no'])){
            $order_info['order_no_prefix'] = '';
            $order_info['orders_id'] = $order_info['order_no'];
        }
        //模板解析
        $template = D('email_template')->where(array('email_template_id' => $email_templates_id))->find();
        if (preg_match_all('~{([\w][\w\d]+)}~', $template['email_template_title'], $match)) {
            foreach ($match[1] as $k => $v) {
                if (isset($order_info[$v])) {
                    $template['email_template_title'] = str_replace($match[0][$k], $order_info[$v], $template['email_template_title']);
                }
            }
        }

        if (preg_match_all('~{([\w][\w\d_]+)}~', $template['email_template_content'], $match)) {
            foreach ($match[1] as $k => $v) {
                if (isset($order_info[$v])) {
                    $template['email_template_content'] = str_replace($match[0][$k], $order_info[$v], $template['email_template_content']);
                }
            }
        }
        return $template;
    }
    public function changeStatusAction() {
        if (I('site_to_orders', '') == '')
                    $this->error('请勾选变更状态订单!');
        $site_to_orders = I('site_to_orders');
        $change_to_status = I('get.change_to_status');
        $status = C('order_status_remark');
        $status = array_keys($status);
        if (in_array($change_to_status, $status) == false) {
            $this->error('无效的订单状态');
        }
        $daichuli = false;
        if($change_to_status=='拒付'){
            $data = array('customer_feedback' => $change_to_status,'last_operator'=>session(C('USER_INFO').'.user_id'), 'last_modify'=> date('Y-m-d H:i:s'));
        }else{
            $data = array('order_status_remark' => $change_to_status,'last_operator'=>session(C('USER_INFO').'.user_id'), 'last_modify'=> date('Y-m-d H:i:s'));
            if(empty($change_to_status) || $change_to_status == '待处理') $daichuli = true;
        }
        foreach ($site_to_orders as $entry) {
            list($site_id, $order_id) = explode('-', $entry);
            $where = array('site_id' => $site_id, 'orders_id' => $order_id);
            D('orders_remark')->save($data, array('where' => $where));
            if($daichuli) $this->logs($site_id . ' ' . $order_id . ' changeStatus ' . $change_to_status);
        }
        $this->success('批量变更状态成功!');
    }
    /*
     * 翻译属性到中文
     */
    private function _translate_attr($attr_array) {
        $products_options_zh = array(
                    'color' => '颜色',
                    'farbe' => '颜色',
                    'bust(cm)' => '胸围(cm)',
                    'bust' => '胸围(cm)',
                    'brustumfang' => '胸围(cm)',
                    'waist(cm)' => '腰围(cm)',
                    'waist' => '腰围(cm)',
                    'taille' => '腰围(cm)',
                    'Tailleumfang' => '腰围(cm)',
                    'hips(cm)' => '臀围(cm)',
                    'hips' => '臀围(cm)',
                    'hüftumfang' => '臀围(cm)',
                    'hollow to floor(cm)' => '肩到地高度(cm)',
                    'hollow to floor' => '肩到地高度(cm)',
                    'länge vom Schlüsselbein zum Bode' => '肩到地高度(cm)',
                    'wedding Date' => '结婚日期',
                    'need Date' => '需要日期',
                    'comments' => '备注'
                );
        $products_options_value_zh = array(
                    'same as pic' => '图片色',
                    'selben wie Fotos' => '图片色',
                    'white' => '白色',
                    'weiß' => '白色',
                    'ivory' => '象牙白',
                    'elfenbein' => '象牙白',
                    'custom color' => '其它颜色',
                );
        foreach ($attr_array as $k => $attr) {
            $products_option = strtolower($attr['products_options']);
            $products_options_values = strtolower($attr['products_options_values']);
            if (isset($products_options_zh[$products_option])) {
                $attr['products_options_zh'] = $products_options_zh[$products_option];
            } else {
                $attr['products_options_zh'] = $attr['products_options'];
            }
            if (isset($products_options_value_zh[$products_options_values])) {
                $attr['products_options_value_zh'] = $products_options_value_zh[$products_options_values];
            } else {
                $attr['products_options_value_zh'] = $attr['products_options_values'];
            }
            $attr_array[$k] = $attr;
        }
        //      var_dump($attr_array);exit;
        return $attr_array;
    }
    private function getOrderProductImages($site_id, $orders_products_id) {
        $where = array('site_id' => $site_id, 'orders_products_id' => $orders_products_id);
        $orders_id = D('orders_products')->where($where)->field('orders_id')->find();
        $images = array();
        $product_image_dir = DIR_WS_UPLOADS . 'Order/' . $site_id . '-' . $orders_id['orders_id'] . '/' . $orders_products_id . '/';
        if (file_exists($product_image_dir)) {
            $d = dir($product_image_dir);
            while ($e = $d->read()) {
                if ($e == '.' || $e == '..' || $e == $product_image_filename)
                                    continue;
                $images[] = $product_image_dir . $e;
            }
            $d->close();
        }
        return $images;
    }
    private function getOrderAttachment($site_id, $order_id) {
        $attachment_path = DIR_WS_ORDER_PRODUCT . $site_id . '-' . $order_id . '/';
        $attachment = array();
        if (file_exists($attachment_path)) {
            $d = dir($attachment_path);
            while ($e = $d->read()) {
                if ($e == '.' || $e == '..' || is_dir($attachment_path . $e))
                                    continue;
                if (substr(PHP_OS, 0, 3) == 'WIN') {
                    $e = iconv('gb2312', 'UTF-8', $e);
                }
                $attachment[] = array('link' => __ROOT__ . '/' . $attachment_path . $e, 'text' => $e);
            }
            $d->close();
        }
        return $attachment;
    }
    public function export_orderAction() {
        if (I('site_to_orders', '') == '')
                    $this->error('请勾选要导出的订单!', 'list');
        //清理1小时后的临时文件
        $d = dir(DIR_FS_TEMP);
        while (false !== ($entry = $d->read())) {
            if($entry=='.' || $entry=='..') continue;
            $Diff = (time() - filectime(DIR_FS_TEMP . $entry))/60/60;
            if ($Diff > 3) unlink(DIR_FS_TEMP . $entry);
        }
        $d->close();
        Vendor('PhpOffice.PhpOffice_Autoloader');
        $files = array();
        $export_orders = I('site_to_orders', array());
        foreach ($export_orders as $entry) {
            list($site_id, $order_id) = explode('-', $entry);
            $site_info = D('site')->where(array('site_id'=>$site_id))->field('order_no_prefix,type,system_cms')->find();
            if($site_info['type']==1 && $site_info['system_cms'] != 'easyshop')
                            $order_no = $site_info['order_no_prefix'].$order_id; else {
                $row = M('orders_remark')->where(array('site_id'=>$site_id, 'orders_id'=>$order_id))->field('order_no')->find();
                $order_no = $row['order_no'];
            }
            $order_file = $this->_get_order_dir($site_id, $order_id).$order_no.'.docx';
            if(file_exists($order_file)==false) {
                $PHPWord = $this->_order_doc($site_id, $order_id);
                $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($PHPWord['obj'], 'Word2007');
                $objWriter->save($order_file);
            }
            $files[] = $order_file;
        }
        $ZipArchive = new \PhpOffice\PhpWord\Shared\ZipArchive();
        $zip_file = date('YmdHis') . '.zip';
        $ZipArchive->open(DIR_FS_TEMP . $zip_file, \PhpOffice\PhpWord\Shared\ZipArchive::CREATE);
        foreach ($files as $file) {
            $ZipArchive->addFile($file, basename($file));
        }
        $ZipArchive->close();
        $link = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/'.str_replace(DIR_FS_ROOT, '', DIR_FS_TEMP . $zip_file);
        redirect($link, 10,'系统将在10秒后跳转到.你也可以直接点击些链接   <a href="'.$link.'">点我下载</a>（此链接3小时内有效）');
        exit;
    }
    public function order_list_excelAction() {
        if (I('site_to_orders', '') == '')
                    $this->error('请勾选要导出的订单!', 'list');
        layout(false);
        vendor('PHPExcel.PHPExcel');
        $title_style = array(
                    'font' => array(
                        'bold' => true,
                        'color' => array(
                            'argb' => '00000000',
                        ),
                    ),
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );
        $field_style = array(
                    'font' => array(
                        'bold' => true,
                        'color' => array(
                            'argb' => '00000000',
                        ),
                    ),
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    ),
                );
        $field_array = array(
                    'A' => array('title' => '订单号次数', 'width' => 10, 'key' => ''),
                    'B' => array('title' => '邮箱次数', 'width' => 10, 'key' => ''),
                    'C' => array('title' => '序号', 'width' => 8, 'key' => ''),
                    'D' => array('title' => '下单日期', 'width' => 10, 'key' => 'date_purchased'),
                    'E' => array('title' => '订单归属', 'width' => 10, 'key' => 'system_area|system_depart'),
                    'F' => array('title' => '网站', 'width' => 10, 'key' => 'site_name'),
                    'G' => array('title' => '订单号', 'width' => 10, 'key' => 'order_no'),
                    'H' => array('title' => '客户邮箱', 'width' => 10, 'key' => 'customers_email_address'),
                    'I' => array('title' => '电话', 'width' => 10, 'key' => 'customers_telephone'),
                    'J' => array('title' => '客户名字', 'width' => 10, 'key' => 'customers_name'),
                    'K' => array('title' => '国家', 'width' => 10, 'key' => 'customers_country'),
                    //'L' => array('title' => '订单金额（$）', 'width' => 10, 'key' => 'order_total'),
        'L' => array('title' => '订单金额', 'width' => 10, 'key' => 'order_total'),
                    'M' => array('title' => '货币类型', 'width' => 10, 'key' => 'currency'),
                    'N' => array('title' => '客户级别', 'width' => 10, 'key' => ''),
                    'O' => array('title' => '客户类型', 'width' => 10, 'key' => 'customers_type'),
                    'P' => array('title' => '付款方式', 'width' => 10, 'key' => 'payment_module_code'),
                    'Q' => array('title' => '付款备注', 'width' => 10, 'key' => 'payment_status'),
                    'R' => array('title' => '订单备注', 'width' => 10, 'key' => 'remarks'),
        //            'Q'=>array('title'=>'订单产品数量', 'width'=>10, 'key'=>''),
        'S' => array('title' => '总件数', 'width' => 10, 'key' => 'products_num'),
                    'T' => array('title' => '跟进业务员', 'width' => 10, 'key' => 'customer_service_name'),
                    'U' => array('title' => '备注', 'width' => 10, 'key' => 'comments'),
                    'V' => array('title' => '发货方式', 'width' => 10, 'key' => 'shipping_method'),
                    'W' => array('title' => '州', 'width' => 10, 'key' => 'delivery_state'),
                    'X' => array('title' => '城市', 'width' => 10, 'key' => 'delivery_city'),
                    'Y' => array('title' => '签收时间', 'width' => 10, 'key' => ''),
                    'Z' => array('title' => '物流发货天数', 'width' => 10, 'key' => ''),
                    'AA' => array('title' => '客户签收天数', 'width' => 10, 'key' => ''),
                    'AB' => array('title' => '客户跟进反馈', 'width' => 10, 'key' => ''),
                    'AC' => array('title' => '客户反馈具体信息', 'width' => 10, 'key' => ''),
                    'AD' => array('title' => '随机ID', 'width' => 10, 'key' => 'a_rand_order_id'),
                );
        $PHPExcel = new \PHPExcel();
        $row = 1;
        foreach ($field_array as $k => $k_info) {
            $PHPExcel->getActiveSheet()->setCellValue($k . $row, $k_info['title']);
            $PHPExcel->getActiveSheet()->getColumnDimension($k)->setWidth($k_info['width']);
        }
        $row++;
        $export_orders = I('site_to_orders', array());
        $order = new OrderModel();
        $system_depart_array = M('PromotionDepartment')->getField('department_id,department_name',true);
        foreach ($export_orders as $entry) {
            list($site_id, $order_id) = explode('-', $entry);
            $where = array('site_id' => $site_id, 'orders_id' => $order_id);
            $order_info = $order->where($where)->relation(array('order_remark', 'site', 'product','history'))->find();
            $order_info['date_purchased'] = empty($order_info['date_purchased']) ? '' : date('Y/m/d',strtotime($order_info['date_purchased']));
            $order_info['system_depart'] = isset($system_depart_array[$order_info['system_depart']]) ? $system_depart_array[$order_info['system_depart']] : '';
            if(empty($order_info['order_no'])) $order_info['order_no'] = $order_info['order_no_prefix'] . $order_info['orders_id'];
            $order_info['products_num'] = 0;
            foreach($order_info['product'] as $product) {
                $order_info['products_num'] += $product['products_quantity'];
            }
            $order_info['order_total']=round($order_info['order_total'] * $order_info['currency_value'], 2);
            $order_info['customers_type'] = M('Orders')->alias('o')->join(array('__ORDERS_REMARK__ r ON o.site_id=r.site_id and o.orders_id=r.orders_id'))->where(array('o.customers_email_address' => $order_info['customers_email_address'],'o.date_purchased'=>array('lt',$order_info['date_purchased']),'r.order_status_remark'=>array('in',array('已确认付款','待订货', '已订货', '待发货', '部分发货', '已发货'))))->count() > 0 ? '老客户' : '新客户';
            $order_info['comments'] = ltrim($order_info['history'][0]['comments'],"=");
            if ($order_info['payment_module_code'] == 'fortune_pay') {
                foreach ($order_info['history'] as $entry_history) {
                    if (preg_match('~^Pay_success~', $entry_history['orders_status_name'])) {
                        $order_info['payment_module_code'] = 'cp_pay';
                        break;
                    } elseif (preg_match('~^Sucess~', $entry_history['orders_status_name'])) {
                        $order_info['payment_module_code'] = 'mycheckout';
                        break;
                    }
                }
            }
            $order_info['payment_module_code'] = strtr($order_info['payment_module_code'], array(
                            'westernunion' => '西联',
                            'moneygram' => '速汇金',
                            'moneytransfers' => 'TW',
                            'mycheckout' => '中外宝',
                            'tpo' => '中外宝',
                            'mycheckout2f3d' => '中外宝',
                            'mycheckout3f' => '中外宝',
                            'rxhpay_inline' => '融信汇',
                            'rxhpay' => '融信汇',
                            'zdcheckout3f' => '佐道',
                            'zdcheckout2f3d' => '佐道',
                            'cp_pay' => 'MoneyBrace',
                            'paycloak' => '贝宝',
                            'security_alipay' => '支付宝',
                            'security_pingpong' => 'pingpong',
                            'pingpong' => 'pingpong',
                            'pingpong2f' => 'pingpong',
                        ));
            $order_info['a_rand_order_id']=$order_info['a_rand_order_id'];
            foreach ($field_array as $k => $k_info) {
                if (empty($k_info['key'])) continue;
                if($k_info['key']=='payment_status') {
                    if(in_array($order_info['order_status_remark'],array('已确认付款','待订货', '已订货', '待发货', '部分发货', '已发货'))) {
                        $value = '支付成功('.$order_info['order_status_remark'].')';
                    } else {
                        $value = $order_info[$k_info['key']];
                    }
                    $PHPExcel->getActiveSheet()->setCellValue($k . $row, $value);
                } elseif (strpos($k_info['key'], '|') !== false) {
                    $keys = explode('|', $k_info['key']);
                    $value = '';
                    foreach ($keys as $key) {
                        $value .= $order_info[$key];
                    }
                    $PHPExcel->getActiveSheet()->setCellValue($k . $row, $value);
                } else {
                    $PHPExcel->getActiveSheet()->setCellValue($k . $row, $order_info[$k_info['key']]);
                }
            }
            $row++;
        }
        $PHPExcel->getActiveSheet()->getStyle('A1:AC' . $row)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                ),
                            )
                        )
                );
        $fileName = "业务管理表" . date('YmdHis', time());
        $objWriter = new \PHPExcel_Writer_Excel5($PHPExcel);
        
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        $encoded_filename = urlencode($fileName);
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '.xls"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName . '.xls"');
        } else {
            header('Content-Disposition: attachment; filename="' . $fileName . '.xls"');
        }
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
        
        //$objWriter->save('/home/supportcustomize/public_html/test.xls');
        
    }
    public function shipping_address_excelAction() {
        layout(false);
        if (I('site_to_orders', '') == '')
                    $this->error('请勾选要导出的订单!', 'list');
        vendor('PHPExcel.PHPExcel');
        $title_style = array(
                    'font' => array(
                        'bold' => true,
                        'color' => array(
                            'argb' => '00000000',
                        ),
                    ),
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );
        $field_style = array(
                    'font' => array(
                        'bold' => true,
                        'color' => array(
                            'argb' => '00000000',
                        ),
                    ),
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    ),
                );
        $PHPExcel = new \PHPExcel();
        $PHPExcel->getActiveSheet()->setCellValue('A1', '网站');
        $PHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('B1', '订单号');
        $PHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('C1', '商品SKU');
        $PHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('D1', '数量');
        $PHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(8);
        $PHPExcel->getActiveSheet()->setCellValue('E1', '邮箱');
        $PHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('F1', '收件人姓名');
        $PHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('G1', '收件人地址');
        $PHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('H1', '收件人城市');
        $PHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('I1', '收件人州');
        $PHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('J1', '收件人邮编');
        $PHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('K1', '收件人国家');
        $PHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('L1', '收件人电话');
        $PHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('M1', '付款方式');
        $PHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('N1', '发货地址');
        $PHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('O1', '业务类型');
        $PHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('P1', '增值服务');
        $PHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
        $PHPExcel->getActiveSheet()->setCellValue('Q1', '货运方式');
        $PHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
        for ($i = 65; $i <= 81; $i++) {
            $col_name = chr($i);
            $PHPExcel->getActiveSheet()->getStyle($col_name . '1')->applyFromArray($title_style);
        }
        $files = array();
        $export_orders = I('site_to_orders', array());
        $order = new OrderModel();
        $k = 1;
        foreach ($export_orders as $entry) {
            list($site_id, $order_id) = explode('-', $entry);
            $entry = $order->relation(array('product','history','site','order_remark'))
                                ->where(array('site_id' => $site_id, 'orders_id' => $order_id))
                                ->find();
            $PHPExcel->getActiveSheet()->getRowDimension($k)->setRowHeight(30);
            $k++;
            //判断收货地址与账单地址是否一致
            if ($entry['delivery_name'] != $entry['billing_name'] || $entry['delivery_company'] != $entry['billing_company'] ||
                                $entry['delivery_street_address'] != $entry['billing_street_address'] || $entry['delivery_suburb'] != $entry['billing_suburb'] ||
                                $entry['delivery_city'] != $entry['billing_city'] || $entry['delivery_postcode'] != $entry['billing_postcode'] ||
                                $entry['delivery_state'] != $entry['billing_state'] || $entry['delivery_country'] != $entry['billing_country']) {
                $field_style['font']['color']['argb'] = '00ff0000';
            } else {
                $field_style['font']['color']['argb'] = '00000000';
            }
            $PHPExcel->getActiveSheet()->getRowDimension($k)->setRowHeight(30);
            //$PHPExcel->getActiveSheet()->setCellValue('A' . ($k), $entry['site_name']);
            $PHPExcel->getActiveSheet()->setCellValue('A' . ($k), '');
            $order_no = empty($entry['order_no']) ? $entry['order_no_prefix'] . $entry['orders_id'] : $entry['order_no'];
            //  var_dump($entry['order_no']);exit;
            $PHPExcel->getActiveSheet()->setCellValue('B' . ($k), $order_no);
            $PHPExcel->getActiveSheet()->setCellValue('C' . ($k), $order_no);
            $qty = 0;
            foreach ($entry['product'] as $entry_product) {
                $qty += $entry_product['products_quantity'];
            }
            $PHPExcel->getActiveSheet()->setCellValue('D' . ($k), $qty);
            //        $PHPExcel->getActiveSheet()->setCellValue('E' . ($k), $entry['customers_email_address']);
            $PHPExcel->getActiveSheet()->setCellValue('E' . ($k), '');
            $PHPExcel->getActiveSheet()->setCellValue('F' . ($k), $entry['delivery_name']);
            $PHPExcel->getActiveSheet()->setCellValue('G' . ($k), $entry['delivery_street_address'].' '.$entry['delivery_suburb']);
            $PHPExcel->getActiveSheet()->setCellValue('H' . ($k), $entry['delivery_city']);
            $PHPExcel->getActiveSheet()->setCellValue('I' . ($k), $entry['delivery_state']);
            $PHPExcel->getActiveSheet()->setCellValue('J' . ($k), ' '.$entry['delivery_postcode']);
            $PHPExcel->getActiveSheet()->setCellValue('K' . ($k), $entry['delivery_country']);
            $PHPExcel->getActiveSheet()->setCellValue('L' . ($k), ' '.$entry['customers_telephone']);
            if ($entry['payment_module_code'] == 'fortune_pay') {
                foreach ($entry['history'] as $_entry) {
                    if (preg_match('~^Pay_success~', $_entry['orders_status_name'])) {
                        $payment_method_zh = 'MoneyBrace';
                        break;
                    } elseif (preg_match('~^Sucess~', $_entry['orders_status_name'])) {
                        $payment_method_zh = '中外宝';
                        break;
                    }
                }
            }
            $payment_method_zh = strtr($entry['payment_module_code'], array(
                            'westernunion' => '西联',
                            'moneygram' => '速汇金',
                            'moneytransfers' => 'TW',
                            'mycheckout' => '中外宝',
                            'tpo' => '中外宝',
                            'mycheckout2f3d' => '中外宝',
                            'mycheckout3f' => '中外宝',
                            'rxhpay_inline' => '融信汇',
                            'rxhpay' => '融信汇',
                            'zdcheckout3f' => '佐道',
                            'zdcheckout2f3d' => '佐道',
                            'cp_pay' => 'MoneyBrace',
                            'paycloak' => '贝宝',
                            'security_alipay' => '支付宝',
                            'security_pingpong' => 'pingpong',
                            'pingpong' => 'pingpong',
                            'pingpong2f' => 'pingpong',
                        ));
            $PHPExcel->getActiveSheet()->setCellValue('M' . ($k), $payment_method_zh);
            $PHPExcel->getActiveSheet()->setCellValue('N' . ($k), '');
            $PHPExcel->getActiveSheet()->setCellValue('O' . ($k), '');
            $PHPExcel->getActiveSheet()->setCellValue('P' . ($k), '');
            //$huoyun_method=($entry['shipping_module_code']=='faster') ? "快速&nbsp;&nbsp;".$entry['shipping_module_code']:'标准&nbsp;&nbsp;';
            switch ($entry['shipping_module_code']) {
                case 'faster':
                case 'zone':
                case '固定运费':
                    $huoyun_method='快速 faster';
                    break;
                
                default:
                    $huoyun_method='标准 standard';
                    break;
            }
           
           // $entry['shipping_module_code']= str_replace("faster","快速货运",$entry['shipping_module_code']);
           // $entry['shipping_module_code']= str_replace("zone","快速货运",$entry['shipping_module_code']);
           // $entry['shipping_module_code']= str_replace("固定运费","快速货运",$entry['shipping_module_code']);

            $PHPExcel->getActiveSheet()->setCellValue('Q' . ($k), $huoyun_method);
            for ($i = 65; $i <= 81; $i++) {
                $col_name = chr($i);
                $PHPExcel->getActiveSheet()->getStyle($col_name . $k)->applyFromArray($field_style)->getAlignment()->setShrinkToFit(true);
            }
        }
        $PHPExcel->getActiveSheet()->getStyle('A1:Q' . $k)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                ),
                            )
                        )
                );
        $fileName = "e邮宝(To物流部)" . date('YmdHis', time());
        $objWriter = new \PHPExcel_Writer_Excel5($PHPExcel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        $encoded_filename = urlencode($fileName);
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '.xls"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName . '.xls"');
        } else {
            header('Content-Disposition: attachment; filename="' . $fileName . '.xls"');
        }
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }
    private function _delivery_excel_import_step1() {
        if (UPLOAD_ERR_OK == $_FILES['file']['error']) {
            $tmp_name = $_FILES['file']['tmp_name'];
            Vendor('PHPExcel.PHPExcel');
            $php_excel_reader = new \PHPExcel_Reader_Excel5();
            if (!$php_excel_reader->canRead($tmp_name)) {
                $this->error('无法解析上传的表格!');
            }
            $PHPExcel = $php_excel_reader->load($tmp_name);
            $currentSheet = $PHPExcel->getSheet(0);
            $fileds = array(
                            'A' => array('title' => '订单号', 'key' => 'order_no', 'required' => true),
                            'B' => array('title' => '发货日期', 'key' => 'delivery_date', 'required' => true),
                            'C' => array('title' => '货运方式', 'key' => 'delivery_type', 'required' => true),
                            'D' => array('title' => '转单号', 'key' => 'delivery_forward_no', 'required' => false),
                            'E' => array('title' => '货运单号', 'key' => 'delivery_tracking_no', 'required' => true),
                            'F' => array('title' => '重量', 'key' => 'delivery_weight', 'required' => true),
                            'G' => array('title' => '订单产品数', 'key' => 'delivery_quanlity', 'required' => true),
                            'H' => array('title' => '赠品数量', 'key' => 'delivery_gift_quanlity', 'required' => false),
                            'I' => array('title' => '其它备注', 'key' => 'delivery_remark', 'required' => false),
                        );
            //表格格式验证 start
            $ok = true;
            foreach ($fileds as $col => $v) {
                $title = $currentSheet->getCell($col . '1')->getValue();
                $title = trim($title);
                if (strpos($title, $v['title']) === false) {
                    $ok = false;
                }
            }
            if ($ok === false) {
                $this->error('你上传的表格格式不正确!');
            }
            //表格格式验证 end
            $row = 2;
            $data = array();
            do {
                $next = true;
                foreach ($fileds as $col => $v) {
                    $value = $currentSheet->getCell($col . $row)->getValue();
                    $value = trim($value);
                    if ($v['required'] == true && empty($value)) {
                        $next = false;
                        break;
                    } else {
                        $data[$row][$v['key']] = $value;
                    }
                }
                if ($next)
                                    $row++;
            }
            while ($next);
            if (sizeof($data[$row]) != sizeof($fileds)) {
                //去掉最后行不完整的记录
                unset($data[$row]);
            }
            $order = new OrderModel();
            foreach ($data as $k => $v) {
                if (false!==($zencart_no = parseZencartNo($v['order_no'])) || preg_match('~-~', $v['order_no'])) {
                    //单号解析
                    if(preg_match('~-~', $v['order_no'])) {
                        $check_order_no = M('orders_remark')->where(array('order_no'=>$v['order_no']))->find();
                        //平台订单号
                        if(!empty($check_order_no)) {
                            $order_no_prefix  = '';
                            $orders_id        = $check_order_no['orders_id'];
                            $data[$k]['order_no_prefix'] = $order_no_prefix;
                            $data[$k]['orders_id'] = $check_order_no['orders_id'];
                            $where = array(
                                                            'orders_id' => $orders_id,
                                                            'o.site_id' => $check_order_no['site_id'],
                                                        );
                            $join = array('LEFT JOIN __SITE__ s ON s.site_id=o.site_id');
                            $row = $order->alias('o')->relation(array('product'))->join($join)->where($where)->find();
                        } else
                                                    $row = array();
                    } else {
                        $order_no_prefix = $zencart_no['orders_prefix'];
                        $zencart_order_no = $zencart_no['orders_id'];
                        $data[$k]['order_no_prefix'] = $order_no_prefix;
                        $data[$k]['orders_id'] = $zencart_order_no;
                        $where = array(
                                                    'order_no_prefix' => $order_no_prefix,
                                                    'orders_id' => $zencart_order_no
                                                );
                        $join = array('LEFT JOIN __SITE__ s ON s.site_id=o.site_id');
                        $row = $order->alias('o')->relation(array('product'))->join($join)->where($where)->find();
                    }
                    if (empty($row)) {
                        $data[$k]['error'] = '没有在系统中找到对应的订单记录';
                    } else {
                        $data[$k]['site_id'] = $row['site_id'];
                        $data[$k]['orders_id'] = $row['orders_id'];
                        $order_products_quanlity = 0;
                        foreach ($row['product'] as $entry) {
                            $order_products_quanlity += $entry['products_quantity'];
                        }
                        //已发货产品数量 大于 订单产品数 状态变为 已发货
                        //已发货产品数量=本次发货数量+数据库中已发货数量
                        $delivery_quanlity = (int) $v['delivery_quanlity'];
                        //本次发货数
                        $delivery_quanlity_history = M('orders_delivery')->alias('o')
                                                        ->join($join)
                                                        ->where(array('order_no_prefix' => $order_no_prefix, 'orders_id' => $zencart_order_no, 'delivery_tracking_no' => array('neq', $v['delivery_tracking_no'])))
                                                        ->sum('delivery_quanlity');
                        if (empty($delivery_quanlity_history) == false) {
                            $delivery_quanlity += $delivery_quanlity_history;
                        }
                        if ($delivery_quanlity >= $order_products_quanlity)
                                                    $data[$k]['order_status_remark'] = '已发货'; else
                                                    $data[$k]['order_status_remark'] = '部分发货';
                    }
                } else {
                    $data[$k]['error'] = '单号有问题';
                }
            }
            $this->assign('delivery_data', $data);
            $this->assign('order_status_remark', C('order_status_remark'));
            $this->display('delivery_excel_import_confirmation');
        } else {
            $this->error('表格上传失败!错误码:' . $_FILES['file']['error']);
        }
    }
    private function _delivery_excel_import_step2() {
        $post_data = I('post.');
        $success = 0;
        $error = 0;
        foreach ($post_data['site_id'] as $k => $site_id) {
            if ($post_data['error'][$k] == '') {
                $error++;
                continue;
            }
            $data_delivery = array(
                            'site_id' => $site_id,
                            'orders_id' => $post_data['orders_id'][$k],
                            'delivery_type' => $post_data['delivery_type'][$k],
                            'delivery_quanlity' => $post_data['delivery_quanlity'][$k],
                            'delivery_gift_quanlity' => $post_data['delivery_gift_quanlity'][$k],
                            'delivery_weight' => $post_data['delivery_weight'][$k],
                            'delivery_forward_no' => $post_data['delivery_forward_no'][$k],
                            'delivery_tracking_no' => $post_data['delivery_tracking_no'][$k],
                            'delivery_date' => $post_data['delivery_date'][$k],
                            'delivery_remark' => $post_data['delivery_remark'][$k],
                        );
            $where = array('site_id' => $site_id, 'orders_id' => $post_data['orders_id'][$k], 'delivery_tracking_no' => $post_data['delivery_tracking_no'][$k]);
            $row = D('orders_delivery')->where($where)->find();
            if (empty($row)) {
                $data_delivery['add_time'] = date('Y-m-d H:i:s');
                D('orders_delivery')->add($data_delivery);
            } else {
                D('orders_delivery')->where(array('orders_delivery_id' => $row['orders_delivery_id']))->save($data_delivery);
            }
            D('orders_remark')->where(array('site_id' => $site_id, 'orders_id' => $post_data['orders_id'][$k]))
                        ->save(array('order_status_remark' => $post_data['order_status_remark'][$k], 'date_send'=>$post_data['delivery_date'][$k]));
            if(empty($post_data['order_status_remark'][$k]) || $post_data['order_status_remark'][$k] == '待处理') $this->logs($site_id . ' ' . $post_data['orders_id'][$k] . ' _delivery_excel_import_step2 ' . $post_data['order_status_remark'][$k]);
            $success++;
        }
        return array('success' => $success, 'error' => $error);
    }
    public function delivery_excel_importAction() {
        if (IS_AJAX)
                    layout(false);
        if (IS_POST) {
            if (I('action') == 'upload') {
                $this->_delivery_excel_import_step1();
            } elseif (I('action') == 'confirm') {
                $r = $this->_delivery_excel_import_step2();
                $msg = '导入完成!';
                $msg .= '<br>成功' . $r['success'] . '条数据!';
                $msg .= '<br>失败' . $r['error'] . '条数据!';
                $this->success($msg, U('Order/Order/list'));
            }
        } else
                    $this->display('delivery_excel_import_form');
    }
    private function _payment_confirmation_upload() {
        if (UPLOAD_ERR_OK != $_FILES['file']['error']) {
            $this->error('表格上传失败!错误码:' . $_FILES['file']['error']);
        }
        //判断文件类型stripe表或其它表
        $ext = pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
        if($ext=='xls'){
            $this->_payment_confirmation_excel ($_FILES['file']['tmp_name']);
        }elseif($ext=='xlsx'){
            $this->_payment_confirmation_excels ($_FILES['file']['tmp_name']);
        }elseif($ext=='csv'){
            $this->_payment_confirmation_csv ($_FILES['file']['tmp_name']);
        }else{
             $this->error('未知文件格式!');
        }
    }
    private function _payment_confirmation_excel($file) {
        Vendor('PHPExcel.PHPExcel');
        $php_excel_reader = new \PHPExcel_Reader_Excel5();
        if (!$php_excel_reader->canRead($file)) {
            $this->error('无法解析上传的表格!');
        }
        $PHPExcel = $php_excel_reader->load($file);
        $currentSheet = $PHPExcel->getSheet(0);
        $fileds = array(
                    'A' => array('title' => '交易流水号', 'key' => 'rp_no', 'required' => false),
                    'B' => array('title' => '网站', 'key' => 'site_name', 'required' => true),
                    'C' => array('title' => '订单号', 'key' => 'order_no', 'required' => true),
                    'D' => array('title' => '金额', 'key' => 'amount', 'required' => false),
                    'E' => array('title' => '货币', 'key' => 'currency', 'required' => false),
                    'F' => array('title' => '接口', 'key' => 'payment_code', 'required' => false),
                    'G' => array('title' => '支付状态', 'key' => 'payment_status', 'required' => true),
                    'H' => array('title' => '下单时间', 'key' => 'date_purchased', 'required' => false),
                    'I' => array('title' => '支付时间', 'key' => 'date_paid', 'required' => false),
                    'J' => array('title' => '备注', 'key' => 'paid_remark', 'required' => false),
                );
        //表格格式验证 start
        $ok = true;
        foreach ($fileds as $col => $v) {
            $title = $currentSheet->getCell($col . '1')->getValue();
            $title = trim($title);
            if (strpos($title, $v['title']) === false) {
                $ok = false;
            }
        }
        if ($ok === false) {
            $this->error('你上传的表格格式不正确!');
        }
        //表格格式验证 end
        $row = 2;
        $data = array();
        do {
            $next = true;
            foreach ($fileds as $col => $v) {
                $value = $currentSheet->getCell($col . $row)->getFormattedValue();
                $value = trim($value);
                if ($v['required'] == true && empty($value)) {
                    $next = false;
                    break;
                } else {
                    $data[$row][$v['key']] = $value;
                }
            }
            $data[$row]['line'] = $row;
            if ($next)
                            $row++;
        }
        while ($next);
        //echo '<pre/>';echo sizeof($data);
        if (sizeof($data[$row]) != sizeof($fileds)) {
            //去掉最后行不完整的记录
            unset($data[$row]);
        }
        //echo '<pre/>';echo sizeof($data);die;
        $order = new OrderModel();
        $has_error = false;
        foreach ($data as $k => $v) {
            if (false!==($zencart_no = parseZencartNo($v['order_no'])) || preg_match('~-~', $v['order_no'])) {
                //单号解析
                if(preg_match('~-~', $v['order_no'])) {
                    $v['order_no'] = trim($v['order_no']);
                    $check_order_no = M('orders_remark')->where(array('order_no'=>$v['order_no']))->find();
                    //平台订单号
                    if(!empty($check_order_no)) {
                        $order_no_prefix  = '';
                        $orders_id        = $check_order_no['orders_id'];
                        $data[$k]['order_no_prefix'] = '';
                        $data[$k]['orders_id']       = $orders_id;
                        $where = array(
                                                    'o.`orders_id`' => $check_order_no['orders_id'],
                                                    'o.site_id'     => $check_order_no['site_id'],
                                                );
                        $join = array('LEFT JOIN __SITE__ s ON s.site_id=o.site_id', '__ORDERS_REMARK__ o_r ON o_r.site_id=o.site_id and o_r.orders_id=o.orders_id');
                        $row = $order->alias('o')->relation(false)->join($join)->where($where)->field(array('o.*', 'o_r.order_status_remark'))->find();
                    } else
                                            $row = array();
                } else {
                    $order_no_prefix = $zencart_no['orders_prefix'];
                    $zencart_order_no = $zencart_no['orders_id'];
                    $data[$k]['order_no_prefix'] = $order_no_prefix;
                    $data[$k]['orders_id'] = $zencart_order_no;
                    $where = array(
                                            'order_no_prefix' => $order_no_prefix,
                                            'o.`orders_id`' => $zencart_order_no
                                        );
                    $join = array('LEFT JOIN __SITE__ s ON s.site_id=o.site_id', '__ORDERS_REMARK__ o_r ON o_r.site_id=o.site_id and o_r.orders_id=o.orders_id');
                    $row = $order->alias('o')->relation(false)->join($join)->where($where)->field(array('o.*', 'o_r.order_status_remark'))->find();
                }
                if (empty($row)) {
                    $data[$k]['error'] = '没有在系统中找到对应的订单记录';
                    $has_error = true;
                } else {
                    $data[$k]['site_id'] = $row['site_id'];
                    $data[$k]['orders_id'] = $row['orders_id'];
                    $data[$k]['cur_order_status_remark'] = $row['order_status_remark'];
                    //当前状态
                    if ($row['order_status_remark'] == '付款确认中' ||
                                                $row['order_status_remark'] == '付款失败or未付款' ||
                                                $row['order_status_remark'] == '待处理') {
                        switch ($v['payment_status']) {
                            case '交易成功':
                                                            $data[$k]['to_order_status_remark'] = '已确认付款';
                            break;
                            default :
                                                            $data[$k]['to_order_status_remark'] = '付款失败or未付款';
                            break;
                        }
                    } else {
                        $data[$k]['to_order_status_remark'] = $row['order_status_remark'];
                    }
                }
            } else {
                $data[$k]['error'] = '单号有问题';
                $has_error = true;
            }
        }
        $this->assign('has_error', $has_error);
        $this->assign('data', $data);
        $this->assign('order_status_remark', C('order_status_remark'));
        $this->display('payment_confirmation_form');
    }
    private function _payment_confirmation_excels($file) {
        Vendor('PHPExcel.PHPExcel');
        $php_excel_reader = new \PHPExcel_Reader_Excel2007();
        if (!$php_excel_reader->canRead($file)) $this->error('无法解析上传的表格!');
        $PHPExcel = $php_excel_reader->load($file);
        $currentSheet = $PHPExcel->getSheet(0);
        $fileds = array(
            'A' => array('title' => '系统订单号', 'key' => 'rp_no', 'required' => true),
            'B' => array('title' => '网站订单号', 'key' => 'order_no', 'required' => false),
            'C' => array('title' => '部门', 'key' => 'bumen', 'required' => false),
            'D' => array('title' => '网址', 'key' => 'site_name', 'required' => true),
            'E' => array('title' => 'MCC', 'key' => 'mcc', 'required' => false),
            'F' => array('title' => '账单标识', 'key' => 'payment_code', 'required' => false),
            'G' => array('title' => '币种', 'key' => 'currency', 'required' => false),
            'H' => array('title' => '金额', 'key' => 'amount', 'required' => false),
            'I' => array('title' => '卡掩码', 'key' => 'yanma', 'required' => false),
            'J' => array('title' => '卡种', 'key' => 'kazhong', 'required' => false),
            'K' => array('title' => '收件人', 'key' => 'shoujianren', 'required' => false),
            'L' => array('title' => '邮箱', 'key' => 'youxiang', 'required' => false),
            'M' => array('title' => '电话', 'key' => 'dianhua', 'required' => false),
            'N' => array('title' => '国家', 'key' => 'guojia', 'required' => false),
            'O' => array('title' => '详细地址', 'key' => 'xiangxidizhi', 'required' => false),
            'P' => array('title' => 'IP', 'key' => 'ip', 'required' => false),
            'Q' => array('title' => '类型', 'key' => 'leixing', 'required' => false),
            'R' => array('title' => '状态', 'key' => 'zhuangtai', 'required' => true),
            'S' => array('title' => '备注', 'key' => 'payment_status', 'required' => false),
            'T' => array('title' => '是否3D', 'key' => 'shifou3d', 'required' => false),
            'U' => array('title' => '日期', 'key' => 'date_purchased', 'required' => false)
        );
        //表格格式验证 start
        $ok = true;
        foreach ($fileds as $col => $v) {
            $title = $currentSheet->getCell($col . '1')->getValue();
            $title = trim($title);
            if (strpos($title, $v['title']) === false) {
                $ok = false;
            }
        }
        if ($ok === false) {
            $this->error('你上传的表格格式不正确!');
        }
        //表格格式验证 end
        $row = 2;
        $data = array();
        do {
            $next = true;
            foreach ($fileds as $col => $v) {
                $value = $currentSheet->getCell($col . $row)->getFormattedValue();
                $value = trim($value);
                if ($v['required'] == true && empty($value)) {
                    $next = false;
                    break;
                } else {
                    $data[$row][$v['key']] = $value;
                }
            }
            $data[$row]['line'] = $row;
            if ($next)
                $row++;
        }
        while ($next);
        //echo '<pre/>';echo sizeof($data);
        if (sizeof($data[$row]) != sizeof($fileds)) {
            //去掉最后行不完整的记录
            unset($data[$row]);
        }
        //echo '<pre/>';echo sizeof($data);die;
        $order = new OrderModel();
        $has_error = false;
        foreach ($data as $k => $v) {
            if (false!==($zencart_no = parseZencartNo($v['order_no'])) || preg_match('~-~', $v['order_no'])) {
                //单号解析
                if(preg_match('~-~', $v['order_no'])) {
                    $v['order_no'] = trim($v['order_no']);
                    $check_order_no = M('orders_remark')->where(array('order_no'=>$v['order_no']))->find();
                    //平台订单号
                    if(!empty($check_order_no)) {
                        $order_no_prefix  = '';
                        $orders_id        = $check_order_no['orders_id'];
                        $data[$k]['order_no_prefix'] = '';
                        $data[$k]['orders_id']       = $orders_id;
                        $where = array(
                            'o.`orders_id`' => $check_order_no['orders_id'],
                            'o.site_id'     => $check_order_no['site_id'],
                        );
                        $join = array('LEFT JOIN __SITE__ s ON s.site_id=o.site_id', '__ORDERS_REMARK__ o_r ON o_r.site_id=o.site_id and o_r.orders_id=o.orders_id');
                        $row = $order->alias('o')->relation(false)->join($join)->where($where)->field(array('o.*', 'o_r.order_status_remark'))->find();
                    } else
                        $row = array();
                } else {
                    $order_no_prefix = $zencart_no['orders_prefix'];
                    $zencart_order_no = $zencart_no['orders_id'];
                    $data[$k]['order_no_prefix'] = $order_no_prefix;
                    $data[$k]['orders_id'] = $zencart_order_no;
                    $where = array(
                        'order_no_prefix' => $order_no_prefix,
                        'o.`orders_id`' => $zencart_order_no
                    );
                    $join = array('LEFT JOIN __SITE__ s ON s.site_id=o.site_id', '__ORDERS_REMARK__ o_r ON o_r.site_id=o.site_id and o_r.orders_id=o.orders_id');
                    $row = $order->alias('o')->relation(false)->join($join)->where($where)->field(array('o.*', 'o_r.order_status_remark'))->find();
                }
                if (empty($row)) {
                    $data[$k]['error'] = '没有在系统中找到对应的订单记录';
                    $has_error = true;
                } else {
                    $data[$k]['site_id'] = $row['site_id'];
                    $data[$k]['orders_id'] = $row['orders_id'];
                    $data[$k]['cur_order_status_remark'] = $row['order_status_remark'];
                    //当前状态
                    if ($row['order_status_remark'] == '付款确认中' ||
                        $row['order_status_remark'] == '付款失败or未付款' ||
                        $row['order_status_remark'] == '待处理') {
                            switch ($v['zhuangtai']) {
                                case 'Approved':
                                    $data[$k]['to_order_status_remark'] = '已确认付款';
                                    break;
                                default :
                                    $data[$k]['to_order_status_remark'] = '付款失败or未付款';
                                    break;
                            }
                        } else {
                            $data[$k]['to_order_status_remark'] = $row['order_status_remark'];
                        }
                    $data[$k]['payment_code'] = explode('-',$v['payment_code'])[0];
                    if(empty($data[$k]['payment_code'])) $data[$k]['payment_code'] = $row['payment_module_code'];
                    $data[$k]['date_paid'] = $v['date_purchased'];
                }
            } else {
                $data[$k]['error'] = '单号有问题';
                $has_error = true;
            }
        }
        $this->assign('excels', 1);
        $this->assign('has_error', $has_error);
        $this->assign('data', $data);
        $this->assign('order_status_remark', C('order_status_remark'));
        $this->display('payment_confirmation_form');
    }
    private function _payment_confirmation_csv($file) {
        $fileds = array('status'=>-1, 'rand_order_id'=>-1);
        if (($handle = fopen($file, "r")) !== FALSE) {
            if (($header_data = fgetcsv($handle)) !== FALSE) {
                $n = sizeof($header_data);
                for ($c=0; $c < $n; $c++) {
                    $header_data[$c] = strtolower($header_data[$c]);
                    $fileds[$header_data[$c]] = $c;
                }
            }
            if($fileds['status']==-1 || $fileds['rand_order_id']==-1) {
                $this->error('你上传的表格格式不正确!');
            }
            $data = array();
            $row = 2;
            while((($v = fgetcsv($handle)) !== FALSE)) {
                $data[$row]['currency'] = $v[$fileds['currency']];
                $data[$row]['amount'] = $v[$fileds['amount']];
                $data[$row]['payment_code'] = 'stripe';
                $data[$row]['date_purchased'] = $v[$fileds['created (utc)']];
                $data[$row]['date_paid'] = $v[$fileds['created (utc)']];
                $data[$row]['paid_remark'] = $v[$fileds['description']];
                $data[$row]['payment_status'] = strtolower($v[$fileds['status']]);
                $data[$row]['rand_order_id'] = strtolower($v[$fileds['rand_order_id']]);
                $row++;
            }
            fclose($handle);
            $order = new OrderModel();
            $has_error = false;
            foreach ($data as $k => $v) {
                if(empty($v['rand_order_id'])) {
                    $row = array();
                } else {
                    $where = array(
                                            'a_rand_order_id' => $v['rand_order_id'],
                                        );
                    $join = array('LEFT JOIN __SITE__ s ON s.site_id=o.site_id', '__ORDERS_REMARK__ o_r ON o_r.site_id=o.site_id and o_r.orders_id=o.orders_id');
                    $row = $order->alias('o')->relation(false)->join($join)->where($where)->field(array('o.*', 'o_r.order_status_remark', 's.site_name','s.order_no_prefix'))->find();
                }
                if (empty($row)) {
                    $data[$k]['error'] = '没有在系统中找到对应的订单记录';
                    $data[$k]['line']      = $k;
                    $has_error = true;
                } else {
                    $data[$k]['site_id']   = $row['site_id'];
                    $data[$k]['orders_id'] = $row['orders_id'];
                    $data[$k]['site_name'] = $row['site_name'];
                    $data[$k]['line']      = $k;
                    $data[$k]['order_no']  = $row['order_no_prefix'].$row['orders_id'];
                    $data[$k]['cur_order_status_remark'] = $row['order_status_remark'];
                    //当前状态
                    if ($row['order_status_remark'] == '付款确认中' ||
                                                $row['order_status_remark'] == '付款失败or未付款' ||
                                                $row['order_status_remark'] == '待处理') {
                        switch ($v['payment_status']) {
                            case 'paid':
                                                            $data[$k]['to_order_status_remark'] = '已确认付款';
                            break;
                            default :
                                                            $data[$k]['to_order_status_remark'] = '付款失败or未付款';
                            break;
                        }
                    } else {
                        $data[$k]['to_order_status_remark'] = $row['order_status_remark'];
                    }
                }
            }
            $this->assign('has_error', $has_error);
            $this->assign('data', $data);
            $this->assign('order_status_remark', C('order_status_remark'));
            $this->display('payment_confirmation_form');
        } else {
            $this->error('无法读取上传的文件!');
        }
    }
    public function payment_confirmationAction($action) {
        if ($action == 'upload') {
            $this->_payment_confirmation_upload();
        } elseif ($action == 'confirm') {
            $post_data = I('post.');
            //echo '<pre/>';print_r(I('post.'));die;
            $success = 0;
            $error = 0;
            $date_paid_arr = array();
            foreach ($post_data['site_id'] as $k => $site_id) {
                if ($post_data['error'][$k] == 1) {
                    $error++;
                    continue;
                }
                $data_order_remark = array(
                                    'order_status_remark' => $post_data['order_status_remark'][$k],
                                    'payment_status' => $post_data['payment_status'][$k],
                                    'last_operator'=>session(C('USER_INFO').'.user_id'), 
                                    'last_modify'=> date('Y-m-d H:i:s')
                );
                $where = array('site_id' => $site_id, 'orders_id' => $post_data['orders_id'][$k]);
                if($post_data['excels'] == 1){
                    $site_id_orders_id = $site_id . '-' . $post_data['orders_id'][$k];
                    if(!isset($date_paid_arr[$site_id_orders_id]) || $post_data['date_paid'][$k] > $date_paid_arr[$site_id_orders_id]){
                        $data_orders = array(
                            'payment_method' => $post_data['payment_code'][$k],
                            'payment_module_code' => $post_data['payment_code'][$k]
                        );
                        D('orders')->where($where)->save($data_orders);
                        $date_paid_arr[$site_id_orders_id] = $post_data['date_paid'][$k];
                    }
                }
                D('orders_remark')->where($where)->save($data_order_remark);
                if(empty($post_data['order_status_remark'][$k]) || $post_data['order_status_remark'][$k] == '待处理') $this->logs($site_id . ' ' . $post_data['orders_id'][$k] . ' payment_confirmation ' . $post_data['order_status_remark'][$k]);
                $success++;
            }
            $msg = '导入完成!';
            $msg .= '<br>成功' . $success . '条数据!';
            $msg .= '<br>失败' . $error . '条数据!';
            $this->success($msg, U('Order/Order/list/order_status_remark/付款确认中'));
        }
    }
    public function clear_docAction() {
        delDirAndFile(DIR_FS_ORDER_PRODUCT,0);
        delDirAndFile(DIR_FS_TEMP.'json',0);
        delDirAndFile(RUNTIME_PATH,0);
        $this->success('清理完毕！');
        //$this->ajaxReturn(array('success'=>true));
    }
    //编辑器上传图片
    public function uploadUrlAction() {
        $upload=new \Think\Upload();
        $CKEditorFuncNum=$_GET['CKEditorFuncNum'];
        if(!$info=$upload->upload()) {
            $error=$upload->getError();
            echo "<script>window.parent.CKEDITOR.tools.callFunction(".$CKEditorFuncNum.",'',$error);</script>";
        } else {
            $savename='http://'.$_SERVER['SERVER_NAME'].'/Uploads/'.$info['upload']['savepath'].$info['upload']['savename'];
            echo "<script>window.parent.CKEDITOR.tools.callFunction(".$CKEditorFuncNum.", '".$savename."', '上传成功');</script>";
        }
    }
    function ipQueryAction() {
        vendor('Request.Requests');
        \Requests::register_autoloader();
        $ip = I('get.ip');
        $response = \Requests::get('http://api.db-ip.com/v2/d7974b0e243fcacf4914b8ecf4a568578d083d72/'.$ip);
        if($response->status_code==200) {
            $data = json_decode($response->body, true);
            if(isset($data['error'])) {
                $this->ajaxReturn(array('status'=>false, 'msg'=>$data['error']), 'json');
            } else {
                D('orders')->where(array('ip_address'=>array('like', '%'.$ip.'%')))->save(array('ip_info'=>$response->body));
                $this->ajaxReturn($data, 'json');
            }
        } else {
            $this->ajaxReturn(array('status'=>false, 'msg'=>'接口查询超时!'), 'json');
        }
        echo $response->body;
        exit;
    }
    public function table_handleAction() {
        if (UPLOAD_ERR_OK != $_FILES['file']['error']) {
            $this->error('表格上传失败!错误码:' . $_FILES['file']['error']);
        }
        $ext = pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
        if($ext!='xls')
                    $this->error('文件必须是xls格式');
        //判断融信汇，中外宝1，中外宝2
        $file = $_FILES['file']['tmp_name'];
        Vendor('PHPExcel.PHPExcel');
        $php_excel_reader = new \PHPExcel_Reader_Excel5();
        if (!$php_excel_reader->canRead($file)) {
            $this->error('无法解析上传的表格!');
        }
        $PHPExcel = $php_excel_reader->load($file);
        $currentSheet = $PHPExcel->getSheet(0);
        if($currentSheet->getCell('A2')->getValue()=='序号') {
            $table_type = 'rxh';
            $row = 2;
        } elseif($currentSheet->getCell('J1')->getValue()=='账号名') {
            $table_type = 'zwb2';
            $row = 1;
        } elseif($currentSheet->getCell('B1')->getValue()=='商户订单号') {
            $table_type = 'zwb1';
            $row = 1;
        } else {
            $this->error('无法识别上传的表格类型!');
        }
        $fields = array();
        for ($i=65;$i<=90;$i++) {
            $col   = chr($i);
            $title = $currentSheet->getCell($col.$row)->getValue();
            if(empty($title)) break;
            $fields[$title] = $col;
        }
        if(isset($fields['商户订单号'])) {
            $row++;
            while($order_no = $currentSheet->getCell($fields['商户订单号'].$row)->getValue()) {
                $where = array();
                //单号解析
                if(preg_match('~([a-zA-Z\d]+)([a-zA-Z])(\d+)~', $order_no, $match)) {
                    //独立站单号
                    $order_no_prefix = $match[1].$match[2];
                    $zencart_order_no = $match[3];
                    $where['s.order_no_prefix'] = $order_no_prefix;
                    $where['o.orders_id']       = $zencart_order_no;
                } elseif(preg_match('~-~', $order_no)) {
                    //平台单号
                    $where['o_r.order_no'] = $order_no;
                } elseif(preg_match('~^\d+$~', $order_no)) {
                    //独立站单号，无前缀
                    $where['o.orders_id'] = $order_no;
                }
                /*
                if(isset($fields['平台订单号'])){
                    $val = $currentSheet->getCell($fields['平台订单号'].$row)->getValue();
                    $currentSheet->setCellValueExplicit($fields['平台订单号'].$row, $val,\PHPExcel_Cell_DataType::TYPE_STRING);
                }elseif(isset($fields['系统订单号'])){
                    $currentSheet->getStyle($fields['系统订单号'].$row)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_GENERAL);
                }
                */
                if(isset($fields['网址']))
                                    $site_domain = $currentSheet->getCell($fields['网址'].$row)->getValue(); else
                                    $site_domain = $currentSheet->getCell($fields['交易网址'].$row)->getValue();
                $site_domain = preg_replace('~www.~i', '', $site_domain);
                $where['s.site_index'] = array('like', '%'.$site_domain.'%');
                $where['s.status']     = 1;
                $join = array('LEFT JOIN __SITE__ s ON s.site_id=o.site_id', '__ORDERS_REMARK__ o_r ON o_r.site_id=o.site_id AND o_r.orders_id=o.site_id');
                $order = M('orders')->alias('o')->join($join)->where($where)->field('o.site_id,o.orders_id')->find();
                if(!empty($order)) {
                    $delivery = M('orders_delivery')->where(array('site_id'=>$order['site_id'],'orders_id'=>$order['orders_id']))->order('delivery_date asc','orders_delivery_id asc')->select();
                    if(!empty($delivery)) {
                        if($table_type=='rxh') {
                            $delivery_type_filed = '快递公司';
                            $delivery_no_filed   = '跟踪单号';
                        } elseif($table_type=='zwb1' || $table_type=='zwb2') {
                            $delivery_type_filed = '运单类型';
                            $delivery_no_filed   = '运单号';
                        }
                        $currentSheet->getCell($fields[$delivery_type_filed].$row)->setValue('');
                        $currentSheet->getCell($fields[$delivery_no_filed].$row)->setValue('');
                        foreach ($delivery as $entry) {
                            $delivery_tracking_no = empty($entry['delivery_forward_no'])?$entry['delivery_tracking_no']:$entry['delivery_forward_no'];
                            $delivery_type        = $entry['delivery_type'];
                            $cell_value_delivery_type = $currentSheet->getCell($fields[$delivery_type_filed].$row)->getValue();
                            if(empty($cell_value_delivery_type))
                                                            $currentSheet->getCell($fields[$delivery_type_filed].$row)->setValue($delivery_type);
                            $cell_value_delivery_no   = $currentSheet->getCell($fields[$delivery_no_filed].$row)->getValue();
                            if(empty($cell_value_delivery_no))
                                                            $cell_value_delivery_no = $delivery_tracking_no; else
                                                            $cell_value_delivery_no .= ','.$delivery_tracking_no;
                            $currentSheet->getCell($fields[$delivery_no_filed].$row)->setValue($cell_value_delivery_no);
                        }
                        $row++;
                    } else {
                        $currentSheet->removeRow($row);
                    }
                } else {
                    $currentSheet->removeRow($row);
                }
            }
            ;
        }
        if($table_type=='zwb2') {
            $currentSheet->removeColumn('J', 1);
            $currentSheet->removeColumn('C', 5);
        }
        $currentSheet->setTitle('delivery');
        $fileName = $table_type.'-'.date('YmdHis', time());
        $objWriter = new \PHPExcel_Writer_Excel5($PHPExcel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        $encoded_filename = urlencode($fileName);
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '.xls"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName . '.xls"');
        } else {
            header('Content-Disposition: attachment; filename="' . $fileName . '.xls"');
        }
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }

    /*
     * 邮件通知客户
     */
    private function send_email($email, $email_name, $email_subject, $email_txt, $email_html, $smtp_email, $smtp_pwd, $smtp_host, $smtp_port) {
        vendor('phpMailer.PHPMailerAutoload');
        $mail = new \PHPMailer;
        $mail->IsHTML(true);
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host     = $smtp_host;                       // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $smtp_email;                 // SMTP username
        $mail->Password = $smtp_pwd;                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $smtp_port;                                    // TCP port to connect to
        $mail->Timeout = 120;
        $mail->From = $smtp_email;
        $mail->FromName = $smtp_email;
        $mail->addAddress($email, $email_name);     // Add a recipient
        $mail->CharSet  = 'utf-8';
        $mail->Encoding = '8bit';
        $mail->Subject  = $email_subject;
        $mail->MsgHTML($email_html);
        
        if (!$mail->send()) {
            return 'failure(' . $mail->ErrorInfo . ')';
        } else {
            return true;
        }
    }

    private function send_email2($email, $email_name, $email_subject, $email_txt, $email_html, $smtp_email, $smtp_pwd, $smtp_host, $smtp_port) {
        $send_mail_api = array(
            'http://support.customize.company/api-sendmail/sendmail.php',
        );
        $post_data = array(
            'smtp_email'=>$smtp_email,
            'smtp_pwd'      => $smtp_pwd,
            'smtp_host'     => $smtp_host,
            'smtp_port'     => $smtp_port,
            'to_address'    => $email,
            'email_subject' => $email_subject,
            'email_html'    => $email_html,
            'email_text'    => $email_txt,
            'to_name'       => $email_name,
            'email_reply_to_address'=>$smtp_email,
            'email_reply_to_name'=>$smtp_email
            
        );
        $success = false;
        $msg = array();
        foreach($send_mail_api as $entry){
            $r = $this->_curl_post($entry, $post_data);
            if($r=='success'){
                $success = true;
                break;
            }else{
                $msg[] = 'API:'.$entry.' Error:'.$r;
            }
        }
        
        if($success){
            return true;
        }else{
            return implode("\n<br>", $msg);
        }
    }

    private function _curl_post($url, array $post = NULL, array $options = array()) {
        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_POSTFIELDS => http_build_query($post)
        );
        
        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return $error;
        } else {
            curl_close($ch);
            return $result;
        }
    }

    private function logs($msg) {
        $logs_dir = dirname(dirname(__FILE__)) . '/logs/';
        if (!is_dir($logs_dir)) mkdir($logs_dir,0777,true);
        $logs_file = $logs_dir . 'daichuli.txt';
        $f = fopen($logs_file, 'a');
        fwrite($f, date('Y-m-d H:i:s') . ' ' . session(C('USER_INFO').'.user_id') . ' ' . $msg . "\n");
        fclose($f);
    }

	//获取物流包裹中的产品
	public function getDeliveryProductsAction(){
		$orders_delivery_id = I('orders_delivery_id',0);
		$delivery = M('OrdersDelivery')->field('delivery_type,delivery_quanlity,delivery_weight')->where(array('orders_delivery_id' => $orders_delivery_id))->find();
		if(empty($delivery)){
			$this->ajaxReturn(array('status' => 0, 'msg' => '未查询到发货记录！'));
		}else{
			$products = M('OrdersProductsRemark')->alias('opr')->join(array('LEFT JOIN __ORDERS_PRODUCTS__ op ON op.site_id=opr.site_id AND op.orders_products_id=opr.orders_products_id'))->where(array('opr.orders_delivery_id' => array(array('eq', $orders_delivery_id), array('like', $orders_delivery_id . ',%'), array('like', '%,' . $orders_delivery_id), array('like', '%,' . $orders_delivery_id . ',%'), 'OR')))->field('op.site_id,op.orders_products_id,op.products_model,op.products_name,op.products_quantity,op.products_image')->order('op.orders_products_id asc')->select();
			if(empty($products)){
				$this->ajaxReturn(array('status' => 0, 'msg' => '未查询到发货的产品！'));
			}else{
				$html = '<table class="table table-bordered">
							<tr>
								<td>货运方式</td>
								<td>' . $delivery['delivery_type'] . '</td>
							</tr>
							<tr>
								<td>发货件数</td>
								<td>' . $delivery['delivery_quanlity'] . '</td>
							</tr>
							<tr>
								<td>重量</td>
								<td>' . $delivery['delivery_weight'] . '</td>
							</tr>
							<tr>
								<td colspan="2">
									<table class="table table-bordered">
										<tr>
											<th>产品图片</th>
											<th>产品数量</th>
											<th>产品信息</th>
										<tr>';
				foreach($products as $v){
					$html .= '<tr>
								<td><img src="' . $this->_getProductImage($v['site_id'], $v['orders_products_id']) . '" width="100px" /></td>
								<td>' . $v['products_quantity'] . '</td>
								<td>
									' . $v['products_name'];
					$products_attributes = M('orders_products_attributes')->where(array('site_id'=>$v['site_id'], 'orders_products_id'=>$v['orders_products_id']))->select();
					if (!empty($products_attributes)) {
						$html .= '<br>';
						$attribute_info = array();
						foreach ($products_attributes as $attribute) {
							$attribute_info[] =  $attribute['products_options'] . ':' . $attribute['products_options_values'];
						}
						$html .= implode('<br>', $attribute_info);
					}
					$html .= '<br>
								SKU:' . $v['products_model'] . '
								</td>
							<tr>';
				}
				$html .= '<table>
						</td>
					</tr>';
				$this->ajaxReturn(array('status' => 1, 'html' => $html));
			}
		}
	}

	//获取没货的产品
	public function getOutOfStockProductsAction(){
		$site_orders_id = I('site_orders_id','0-0');
		list($site_id,$orders_id) = explode('-',$site_orders_id);
		$products = M('OrdersProductsRemark')->alias('opr')->join(array('LEFT JOIN __ORDERS_PRODUCTS__ op ON op.site_id=opr.site_id AND op.orders_products_id=opr.orders_products_id'))->where(array('opr.site_id' => $site_id,'opr.orders_id' => $orders_id,'opr.out_of_stock' => 1))->field('op.site_id,op.orders_products_id,op.products_model,op.products_name,op.products_quantity,op.products_image')->order('op.orders_products_id asc')->select();
		if(empty($products)){
			$this->ajaxReturn(array('status' => 0, 'msg' => '未查询到没货的产品！'));
		}else{
			$html = '<table class="table table-bordered">
						<tr>
							<th>产品图片</th>
							<th>产品数量</th>
							<th>产品信息</th>
						<tr>';
			foreach($products as $v){
				$html .= '<tr>
							<td><img src="' . $this->_getProductImage($v['site_id'], $v['orders_products_id']) . '" width="100px" /></td>
							<td>' . $v['products_quantity'] . '</td>
							<td>
								' . $v['products_name'];
				$products_attributes = M('orders_products_attributes')->where(array('site_id'=>$v['site_id'], 'orders_products_id'=>$v['orders_products_id']))->select();
				if (!empty($products_attributes)) {
					$html .= '<br>';
					$attribute_info = array();
					foreach ($products_attributes as $attribute) {
						$attribute_info[] =  $attribute['products_options'] . ':' . $attribute['products_options_values'];
					}
					$html .= implode('<br>', $attribute_info);
				}
				$html .= '<br>
							SKU:' . $v['products_model'] . '
							</td>
						<tr>';
			}
			$html .= '<table>';
			$this->ajaxReturn(array('status' => 1, 'html' => $html));
		}
	}
}