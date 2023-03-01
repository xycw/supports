<?php
namespace Order\Controller;
use Common\Controller\CommonController;
use Order\Model\OrderRemarkModel;
use Order\Model\OrderDeliveryModel;

vendor('zencartManagement.zencartManagementAutoload');

class ExpressDeliveryController extends CommonController {

    public function listAction(){
        $page = I('page', 1); //当前页码
        $page_data = array('is_received'=>0);
        if (isset($_GET['page_num']) && $_GET['page_num'] > 0) {
            $num = I('page_num'); //每页显示订单数
            $page_data['page_num'] = $num;
        } else
            $num = 300;
        
        $join = array('LEFT JOIN __SITE__ s ON s.site_id=o_d.site_id');
        $join[] = 'JOIN __ORDERS__ o ON o.site_id=o_d.site_id AND o.orders_id=o_d.orders_id';
        $join[] = 'JOIN __ORDERS_REMARK__ o_r ON o_r.site_id=o.site_id AND o_r.orders_id=o.orders_id';
        $join[] = 'LEFT JOIN __USERS__ u ON u.user_id=o_r.last_operator';
        
        $where = array('o_d.delivery_status'=>array('not in', array('已签收')));
        if (I('site_id') != '') {
            $params_site_id = I('site_id');
            $params_site_id = explode('_', $params_site_id);
            $where['p.site_id'] = array('IN', $params_site_id);
            $page_data['site_id'] = I('site_id');
            $this->assign('site_id_select', $params_site_id);
        }
        if (I('delivery_date_start') !== '' && I('delivery_date_end') !== '') {
            $where['delivery_date'] = array('between', array(I('delivery_date_start'), I('delivery_date_end')));
            $page_data['delivery_date_start'] = I('delivery_date_start');
            $page_data['delivery_date_end'] = I('delivery_date_end');
        }else{
            $delivery_date_start = date('Y-m-d');
            $delivery_date_end = date('Y-m-d');
            $_GET['delivery_date_start'] = $delivery_date_start;
            $_GET['delivery_date_end'] = $delivery_date_end;
            $where['delivery_date'] = array('between', array($delivery_date_start, $delivery_date_end));
            $page_data['delivery_date_start'] = $delivery_date_start;
            $page_data['delivery_date_end'] = $delivery_date_end;            
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
        $orders_delivery = new OrderDeliveryModel();
        $sql  = D('orders_products')->alias('p')->where(array('p.site_id'=>array('exp','=o.site_id'), 'p.orders_id'=>array('exp','=o.orders_id')))->field('sum(products_quantity)')->select(false);
        $count=$orders_delivery->alias('o_d')->join($join)->field(array('o.*','s.*','o_r.*',$sql.' as num_products','o_d.*', 'u.*'))->where($where)->count();
        $list = $orders_delivery->alias('o_d')->join($join)->field(array('o.*','s.*','o_r.*',$sql.' as num_products','o_d.*', 'u.*'))->where($where)->order('delivery_date desc,orders_delivery_id desc')->page($page, $num)->select();       
        
        $this->assign('list', $list);
        $this->assign('count', $count);
       //各状态订单数量统计
        $num_where_array = array(
            'num_dcl'=>array('order_status_remark' => array(array('exp', 'IS NULL'), array('eq', '待处理'), array('eq', ''), 'OR')),
            'num_fksb'=>array('order_status_remark' => '付款失败or未付款'),
            'num_fkqrz'=>array('order_status_remark' => '付款确认中'),
            'num_yqrfk'=>array('order_status_remark' => '已确认付款'),
            'num_ddh'=>array('order_status_remark' => '待订货'),
            'num_ydh'=>array('order_status_remark' => '已订货'),
            'num_dfh'=>array('order_status_remark' => '待发货'),
            'num_bffh'=>array('order_status_remark' => '部分发货'),
            'num_yfh'=>array('order_status_remark' => '已发货'),
            'num_is_paid'=>array('order_status_remark'=>array('in', array('已确认付款','待订货','已订货','待发货','部分发货','已发货'))),
            'num_jd'=>array(
                '_complex' => array(
                    '_logic' => 'AND',
                    'is_rush_order' => 1,
                    'order_status_remark' => array('not in', array('已发货', '订单取消')),
                )
            ),
        );
        $order_remark_model = new OrderRemarkModel();
        foreach($num_where_array as $k=>$entry_where){
            $num_status = $order_remark_model->where($entry_where)->count();
            $this->assign($k, $num_status);
        }        
        $where_wqs = array('delivery_status' => array('not in', array('已签收')));
        $num_wqs = $orders_delivery->where($where_wqs)->count();//未签收
        $this->assign('num_wqs', $num_wqs);
        $this->assign('zencart_orders_status', C('order_status'));
        $this->assign('order_status_remark', C('order_status_remark'));
        $this->assign('data_is_send_from_manufacturer', C('data_is_send_from_manufacturer'));
        $this->assign('data_customer_feedback', C('customer_feedback'));
        $this->assign('payment_methods', C('payment_methods'));
        
        $users = D('users_to_site')->alias('u2s')->join('__USERS__ u ON u.user_id=u2s.user_id')->field(array('distinct u.`user_id`', 'u.chinese_name'))->select();
        $options_users = array();
        foreach($users as $entry){
            $options_users[$entry['user_id']] = $entry['chinese_name'];
        }
        $this->assign('users', $options_users);       

        $options_site_name = array();
        $data_site = D('site')->where(array('status' => 1))->order('site_id asc')->select();
        if ($data_site) {
            foreach ($data_site as $row) {
                $options_site_name[$row['site_id']] = $row['site_name'];
            }
        }
        $this->assign('options_site_name', $options_site_name);
        $this->assign('data_shipping_status', C('shipping_status'));        
        $this->assign('page_num_data', array(1=>1,50=>50,100=>100, 200=>200,300=>300, 500=>500));
        $this->assign('page_num_selected', $num);
        $this->assign('num', $num);      
        $this->assign('page', $page);        $this->assign('page_data', $page_data);
        $this->assign('options_send_status', array(1=>'是',2=>'否'));
        $this->display();
    }
    
    public function exportAction(){
        $order_no = I('post.order_no');
        $where = array();
        $join  = array();
        if(false==empty($order_no)){
            $order_number = explode("\n", $order_no);
            $orders_id = array();
            
            foreach($order_number as $order_no){
                $_where = array();
                $order_no = trim($order_no);
                if(($match = parseZencartNo($order_no))!==false) {
                    $order_no_prefix = $match['orders_prefix'];
                    $zencart_order_no = $match['orders_id'];
                    $_where['o_r.orders_id'] = $zencart_order_no;
                    $_where['s.order_no_prefix'] = $order_no_prefix;                    
                } else {
                    $_where['o_r.order_no'] = $order_no;
                }
                $check_order = M('orders_remark')->alias('o_r')->join('__SITE__ s ON s.site_id=o_r.site_id')->where($_where)->field('o_r.orders_id,o_r.site_id')->find();
                if($check_order)
                    $orders_id[] = $check_order;
            }
            if(sizeof($orders_id)){
                $where[0] = array(
                    '_complex' => array(
                        '_logic'    => 'OR',
                    )
                );
                foreach($orders_id as $entry){
                    $_where = array(
                        '_complex' => array(
                            '_logic'    => 'AND',
                            'd.site_id'   => $entry['site_id'],
                            'd.orders_id' => $entry['orders_id']
                        )
                    );
                    $where[0]['_complex'][] = $_where;
                }
            }
        }else{
            $date_start     = I('post.date_send_start');
            $date_end  = I('post.date_send_end');
            if(empty($date_start) && empty($date_end)){
                $date_end = date('Y-m-d');
                $date_start = date('Y-m-d', strtotime('-7 day'));
            }elseif(empty($date_start)){
                $date_start = date('Y-m-d', (strtotime($date_end)-7*24*60*60));
            }else{
                $date_end = date('Y-m-d', (strtotime($date_start)+7*24*60*60));
            }
            $where = array('delivery_date'=>array('between', array($date_start, $date_end)));
        }
        $join[] = '__ORDERS__ o ON o.orders_id=d.orders_id AND o.site_id=d.site_id';
        $join[] = '__ORDERS_REMARK__ o_r ON o_r.orders_id=d.orders_id AND o_r.site_id=d.site_id';
        $join[] = 'LEFT JOIN __SITE__ s ON s.site_id=d.site_id';
        $sql = D('orders_products')->alias('p')->where(array('p.site_id'=>array('exp','=d.site_id'), 'p.orders_id'=>array('exp','=d.orders_id')))->field('sum(products_quantity)')->select(false);
        $list = D('orders_delivery')->alias('d')->join($join)->where($where)->field('d.*,s.order_no_prefix,o_r.order_no,'.$sql.' as num_products')->order('delivery_date desc,d.orders_delivery_id desc')->select();

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
                    'A' => array('title' => '订单号(包括前缀)', 'width' => 20, 'key' => 'order_number'),
                    'B' => array('title' => '发货日期', 'width' => 10, 'key' => 'delivery_date'),
                    'C' => array('title' => '货运方式', 'width' => 8, 'key' => 'delivery_type'),
                    'D' => array('title' => '转单号', 'width' => 10, 'key' => 'delivery_forward_no'),
                    'E' => array('title' => '货运单号', 'width' => 20, 'key' => 'delivery_tracking_no'),
                    'F' => array('title' => '重量(Kg)', 'width' => 10, 'key' => 'delivery_weight'),
                    'G' => array('title' => '订单产品数', 'width' => 10, 'key' => 'num_products'),
                    'H' => array('title' => '赠品数量(有就填)', 'width' => 10, 'key' => 'delivery_gift_quanlity'),
                    'I' => array('title' => '其它备注(有就填)', 'width' => 10, 'key' => 'delivery_remark')
                );
        $PHPExcel = new \PHPExcel();
        $row = 1;
        foreach ($field_array as $k => $k_info) {
            $PHPExcel->getActiveSheet()->setCellValue($k . $row, $k_info['title']);
            $PHPExcel->getActiveSheet()->getColumnDimension($k)->setWidth($k_info['width']);
        }
        $row++;
        foreach($list as $entry){
            $entry['order_number'] = empty($entry['order_no'])?$entry['order_no_prefix'].$entry['orders_id']:$entry['order_no'];
            foreach ($field_array as $k => $k_info) {
                if(isset($entry[$k_info['key']]))
                    $PHPExcel->getActiveSheet()->setCellValue($k . $row, $entry[$k_info['key']]);
            }
            $row++;
        }
        $PHPExcel->getActiveSheet()->getStyle('A1:I' . $row)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                ),
                            )
                        )
                );
        $fileName = "物流信息表" . date('YmdHis', time());
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
    
    public function queryAction($no) {
        $aikuaidi = new \aikuaidi();
        $result = $aikuaidi->htmlQuery($no);
        if ($result === false) {
            $result = '查询失败!';
        }
        $this->assign('express_no', $no);
        $this->assign('result', $result);
        $this->display();
    }

    public function logisticsAction($orders_delivery_id) {
        $where = array('orders_delivery_id' => $orders_delivery_id);
        $express_info = D('orders_delivery')->find(array('where' => $where));
        $shipping_no = $express_info['delivery_type'] . '-' . $express_info['delivery_tracking_no'];

        if ($express_info['delivery_status'] == '已签收' && empty($express_info['delivery_data']) == false) {
            $data_shipping = unserialize($express_info['delivery_data']);
            $content = '<table class="table table-striped">';
            $content .= '<tr><th>时间</th><th>物流信息</th></tr>';
            foreach ($data_shipping['data'] as $k => $entry) {
                $content .= '<tr' . ($k == 0 ? ' class="text-danger"' : '') . '><td>' . $entry['time'] . '</td><td>' . $entry['content'] . '</td></tr>';
            }
            $content .= '</table>';
        } else {
            $aikuaidi = new \aikuaidi();
            $data_shipping = $aikuaidi->arrayQuery($shipping_no);
            if ($data_shipping === false) {
                $content = '查询失败，请重试!';
            } elseif ($data_shipping['status'] == 0 || $data_shipping['status'] == 1) {
                $content = $aikuaidi->statusCode($data_shipping['status']);
            } else {
                //将物流数据保存到数据库中
                $data_serialize = serialize($data_shipping);
                $n = sizeof($data_shipping['data']) - 1;
                $data = array(
                    'delivery_status' => $aikuaidi->statusCode($data_shipping['status']),
                    'delivery_data' => $data_serialize,
                );
                if ($data_shipping['name'] == 'DHL快递' || 'UPS国际快递' == $data_shipping['name']) {
                    if (preg_match('~派送并签收~', $data_shipping['data'][0]['content'])) {
                        $data['shipping_status'] = '已签收';
                    } elseif (preg_match('~已递送~', $data_shipping['data'][0]['content'])) {
                        $data['shipping_status'] = '已签收';
                    }
                }
                D('orders_delivery')->save($data, array('where' => $where));

                $content = '<table class="table table-striped">';
                $content .= '<tr><th>时间</th><th>物流信息</th></tr>';
                foreach ($data_shipping['data'] as $k => $entry) {
                    $content .= '<tr' . ($k == 0 ? ' class="text-danger"' : '') . '><td>' . $entry['time'] . '</td><td>' . $entry['content'] . '</td></tr>';
                }
                $content .= '</table>';
            }
        }
        if (IS_AJAX)
            layout(false);
        $this->assign('express_no', $shipping_no);
        $this->assign('result', $content);
        $this->display('query');
    }

    public function changeStatusAction() {
        $orders_delivery_id = I('orders_delivery_id');
        $status = I('status');
        $where = array('orders_delivery_id' => $orders_delivery_id);
        $data = array(
            'delivery_status' => $status,
        );
        D('orders_delivery')->save($data, array('where' => $where));
        $this->ajaxReturn(array('status' => 1));
    }

}
