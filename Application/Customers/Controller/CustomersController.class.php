<?php
namespace Customers\Controller;
use Common\Controller\CommonController;
use Customers\Model\CustomersModel;
use Customers\Model\PromotionDepartMentModel;
use Order\Model\OrderModel;

class CustomersController extends CommonController {
    public function listAction(){
        $customer = new CustomersModel();
        $where = array();
        $fields = array('s.site_name,s.type,c.*');
        if (is_array(I('site_id')) && sizeof(I('site_id'))) {
            $where['c.site_id'] = array('IN', I('site_id'));
            $page_data['site_id'] = implode(',', I('site_id'));
            $this->assign('site_id_select', I('site_id'));
        }elseif(I('site_id')!=''){
            $site_ids = explode(',', I('site_id'));
            $where['c.site_id'] = array('IN', $site_ids);
            $page_data['site_id'] = I('site_id');
            $this->assign('site_id_select', $site_ids);
        }
        if (I('customer_email')!='') {
            $where['c.customers_email_address'] = I('customer_email');
            $page_data['customer_email'] = I('customer_email');
        }
        #网站类型
        if (I('sex', '')!='') {
            $where['s.type'] = I('sex');
            $page_data['type'] = I('sex');
            //$this->assign('site_type_select', I('type'));
        }
        #归属部门
        if (I('type', '')!='') {
            $where['p.department_id'] = I('type');
            $page_data['department_id'] = I('type');

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
            $_GET['register_time_start'] = $time_start;
            $_GET['register_time_end']   = $time_end;
            $where['customers_info_date_account_created'] = array('between', array($time_start.' 0:0:0', $time_end.' 23:59:59'));
            $page_data['register_time_start'] = I('register_time_start');
            $page_data['register_time_end'] = I('register_time_end');
        }
        $page = I('page', 1);//当前页码
        $num  = 50;//每页显示订单数    


        if (I('order_status') === 'null') {//无购物记录
            $fields[] = 'o.orders_id';
            $join = array('__SITE__ s ON s.site_id=c.site_id', 'LEFT JOIN __ORDERS__ o ON o.customers_email_address=c.customers_email_address');
            $list = $customer->relation(true)->alias('c')->field($fields)->join($join)->join(PromotionDepartMentModel::PROMOTION_DEPARTMENT, 'LEFT')->where($where)->group('c.customers_email_address')->having('SUM(IF(o.orders_id IS NULL, 0, 1))=0')->page($page, $num)->order('customers_info_date_of_last_logon desc,customers_info_date_account_created desc,customers_info_number_of_logons desc')->select();
            $sql = $customer->alias('c')->field($fields)->join($join)->join(PromotionDepartMentModel::PROMOTION_DEPARTMENT, 'LEFT')->where($where)->group('c.customers_email_address')->having('SUM(IF(o.orders_id IS NULL, 0, 1))=0')->select(false);
            $count = $customer->db()->query('select count(*) as num from (' . $sql . ') as t');
            $count = $count[0]['num'];

            $page_data['order_status'] = I('order_status');
            $this->assign('order_status_record_selected', I('order_status'));
        } elseif (I('order_status') === '1' || I('order_status') === '2') {//至少成功*单
            $join = array('__SITE__ s ON s.site_id=c.site_id', '__ORDERS__ o ON o.customers_email_address=c.customers_email_address', '__ORDERS_REMARK__ o_r ON o.orders_id=o_r.orders_id AND o.site_id=o_r.site_id');
            $s = I('order_status') - 1;
            $succss_status = array('待订货', '已发货', '已确认付款', '待发货', '已订货', '部分发货');
            $list = $customer->relation(true)->alias('c')->field($fields)->join($join)->join(PromotionDepartMentModel::PROMOTION_DEPARTMENT, 'LEFT')->where($where)->group('c.customers_email_address')->having('SUM(IF(o_r.order_status_remark IN (\'' . implode('\',\'', $succss_status) . '\'), 1, 0))>' . $s)->page($page, $num)->order('customers_info_date_of_last_logon desc,customers_info_date_account_created desc,customers_info_number_of_logons desc')->select();
            $sql = $customer->relation(true)->alias('c')->field($fields)->join($join)->join(PromotionDepartMentModel::PROMOTION_DEPARTMENT, 'LEFT')->where($where)->group('c.customers_email_address')->having('SUM(IF(o_r.order_status_remark IN (\'' . implode('\',\'', $succss_status) . '\'), 1, 0))>' . $s)->select(false);
            $count = $customer->db()->query('select count(*) as num from (' . $sql . ') as t');
            $count = $count[0]['num'];

            $page_data['order_status'] = I('order_status');
            $this->assign('order_status_record_selected', I('order_status'));
        } elseif (I('order_status') === '-1') {//只有失败记录的客户
            $join = array('__SITE__ s ON s.site_id=c.site_id', '__ORDERS__ o ON o.customers_email_address=c.customers_email_address', '__ORDERS_REMARK__ o_r ON o.orders_id=o_r.orders_id AND o.site_id=o_r.site_id');
            $not_failure_status = array('待订货', '付款确认中', '已确认付款', '待发货', '已订货', '部分发货', '已发货', '订单取消', '老订单');
            $list = $customer->relation(true)->alias('c')->field($fields)->join($join)->join(PromotionDepartMentModel::PROMOTION_DEPARTMENT, 'LEFT')->where($where)->group('c.customers_email_address')->having('SUM(IF(o_r.order_status_remark IN (\'' . implode('\',\'', $not_failure_status) . '\'), 1, 0))=0')->page($page, $num)->order('customers_info_date_of_last_logon desc,customers_info_date_account_created desc,customers_info_number_of_logons desc')->select();
            $sql = $customer->relation(true)->alias('c')->field($fields)->join($join)->join(PromotionDepartMentModel::PROMOTION_DEPARTMENT, 'LEFT')->where($where)->group('c.customers_email_address')->having('SUM(IF(o_r.order_status_remark IN (\'' . implode('\',\'', $not_failure_status) . '\'), 1, 0))=0')->select(false);
            $count = $customer->db()->query('select count(*) as num from (' . $sql . ') as t');
            $count = $count[0]['num'];

            $page_data['order_status'] = I('order_status');
            $this->assign('order_status_record_selected', I('order_status'));
        } else {

            $list = $customer->relation(true)->alias('c')->field($fields)->join('__SITE__ s ON s.site_id=c.site_id')->join(PromotionDepartMentModel::PROMOTION_DEPARTMENT, 'LEFT')->where($where)->order('customers_info_date_of_last_logon desc,customers_info_date_account_created desc,customers_info_number_of_logons desc')->page($page, $num)->select();
            $count = $customer->alias('c')->field($fields)->join('__SITE__ s ON s.site_id=c.site_id')->join(PromotionDepartMentModel::PROMOTION_DEPARTMENT, 'LEFT')->where($where)->count();
        }

        $order_model = new OrderModel();
        $join = array(
            'LEFT JOIN __ORDERS_REMARK__ o_r ON o_r.site_id=o.site_id AND o_r.orders_id=o.orders_id',
        );
        // if(I('no_order', '')==''){
            $where = array();
            foreach ($list as $k=>$customer){
                $where['customers_email_address'] = $customer['customers_email_address'];
                $result = $order_model->field('IFNULL(order_status_remark,\'待处理\') as order_status,count(*) as num')->alias('o')->relation(false)->join($join)->where($where)->group('order_status_remark')->select();
                $list[$k]['orders'] = $result;
            }
        // }

//
        $model_site = D('site');
        $options_site_name = array();
        $data_site = $model_site->where(array('status'=>1))->order('site_id asc')->select();
        if ($data_site){
            foreach ($data_site as $row){
                $options_site_name[$row['site_id']]='#'.$row['site_id'].'-'.$row['site_name'];
            }
        }

        #归属部门信息
        $options_department_info = array_column(D('promotion_department')->order('department_id asc')->select(),'department_name','department_id');
        //$options_site_type = array(1=>'独立站',10=>'商城站');
        $order_status_record = array('null'=>'无购物记录', '1'=>'至少成功一单', '2'=>'至少成功2单', '-1'=>'只有失败订单');
        $data_site = D('site')->where(array('status' => 1,'type'=>'1','is_sale'=>array('neq',0)))->order('is_sale asc,site_id asc')->select();
        $this->assign('sites', $data_site);
        $this->assign('order_status_record', $order_status_record);
        $this->assign('options_department_info', $options_department_info);
        $this->assign('options_site_name', $options_site_name);
        $this->assign('list',$list);
        $this->assign('page_data', $page_data);
        $this->assign('page', $page);
        $this->assign('num', $num);
        $this->assign('count', $count);
        $this->display();
	}
	
        public function exportAction(){
            $site_id = I('site_id');
            $join = array('LEFT JOIN __ADDRESS_BOOK__ a ON a.address_book_id=c.customers_default_address_id and a.site_id=c.site_id');
            $num  = 10000;//每个文件的记录条数
            
            $where = array();
            $customer = new CustomersModel();
            $fields = array('s.site_name,c.*');
            $where['c.site_id'] = $site_id;
            
            if (I('customer_email')!='') {
                $where['customers_email_address'] = I('customer_email');
            }
            if (I('type', '')!='') {
                $where['s.type'] = I('type');
            }
    
    
            if (I('register_time_start') !== '' && I('register_time_end') !== '') {
                $time_start = date('Y-m-d 0:0:0', strtotime(I('register_time_start')));
                $time_end   = date('Y-m-d 23:59:59', strtotime(I('register_time_end')));
            }else{
                $time_start = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d")-3,   date("Y")) );
                $time_end   = date('Y-m-d');
            }
            $where['customers_info_date_account_created'] = array('between', array($time_start, $time_end));            
            
            if(I('order_status')==='null'){//无购物记录
                $fields[] = 'o.orders_id';
                $join = array('__SITE__ s ON s.site_id=c.site_id','LEFT JOIN __ORDERS__ o ON o.customers_email_address=c.customers_email_address');
                $sql  = $customer->relation(false)->alias('c')->field($fields)->join($join)->where($where)->group('c.customers_email_address')->having('SUM(IF(o.orders_id IS NULL, 0, 1))=0')->select(false);   
                $count = $customer->db()->query('select count(*) as num from ('.$sql.') as t');
                $count = $count[0]['num'];      
            }elseif(I('order_status')==='1' || I('order_status')==='2'){//至少成功*单
                $join = array('__SITE__ s ON s.site_id=c.site_id','__ORDERS__ o ON o.customers_email_address=c.customers_email_address', '__ORDERS_REMARK__ o_r ON o.orders_id=o_r.orders_id AND o.site_id=o_r.site_id');
                $s = I('order_status')-1;
                $succss_status = array('待订货', '已发货', '已确认付款', '待发货', '已订货', '部分发货');
                $sql  = $customer->relation(false)->alias('c')->field($fields)->join($join)->where($where)->group('c.customers_email_address')->having('SUM(IF(o_r.order_status_remark IN (\''.implode('\',\'', $succss_status).'\'), 1, 0))>'.$s)->select(false);
                $count = $customer->db()->query('select count(*) as num from ('.$sql.') as t');
                $count = $count[0]['num'];
            }elseif(I('order_status')==='-1'){//只有失败记录的客户
                $join = array('__SITE__ s ON s.site_id=c.site_id','__ORDERS__ o ON o.customers_email_address=c.customers_email_address', '__ORDERS_REMARK__ o_r ON o.orders_id=o_r.orders_id AND o.site_id=o_r.site_id');
                $not_failure_status = array('待订货', '付款确认中', '已确认付款', '待发货', '已订货', '部分发货', '已发货',  '订单取消', '老订单');
                $sql  = $customer->relation(false)->alias('c')->field($fields)->join($join)->where($where)->group('c.customers_email_address')->having('SUM(IF(o_r.order_status_remark IN (\''.implode('\',\'', $not_failure_status).'\'), 1, 0))=0')->select(false);
                $count = $customer->db()->query('select count(*) as num from ('.$sql.') as t');
                $count = $count[0]['num'];
            }else{
                $count = $customer->relation(false)->alias('c')->field($fields)->join(array('__SITE__ s ON s.site_id=c.site_id'))->where($where)->count();   
            } 
            
            $total = $count;
            $page_num = ceil($total/$num);
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
                'A' => array('title' => 'firstname', 'width' => 15, 'key' => 'entry_firstname'),
                'B' => array('title' => 'lastname', 'width' => 15, 'key' => 'entry_lastname'),
                'C' => array('title' => 'email', 'width' => 15, 'key' => 'customers_email_address'),
                'D' => array('title' => 'telephone', 'width' => 15, 'key' => 'customers_telephone'),
                'E' => array('title' => 'street', 'width' => 25, 'key' => 'entry_street_address'),
                'F' => array('title' => 'postcode', 'width' => 10, 'key' => 'entry_postcode'),
                'G' => array('title' => 'city', 'width' => 15, 'key' => 'entry_city'),
                'H' => array('title' => 'state', 'width' => 15, 'key' => 'entry_state'),
                'I' => array('title' => 'country', 'width' => 15, 'key' => 'entry_country'),
                'J' => array('title' => 'register date', 'width' => 15, 'key' => 'customers_info_date_account_created'),
                'K' => array('title' => 'success orders', 'width' => 15, 'key' => 'success_orders'),
                'L' => array('title' => 'failure orders', 'width' => 15, 'key' => 'failure_orders'),
                'M' => array('title' => 'has order', 'width' => 15, 'key' => 'has_order'),
                'N' => array('title' => 'site', 'width' => 15, 'key' => 'site_name'),
                
            );   

            $file_to_zip = array();
            
            for($i=0;$i<$page_num;$i++){
                if(I('order_status')==='null'){//无购物记录
                    $fields[] = 'o.orders_id';
                    $join = array('__SITE__ s ON s.site_id=c.site_id','LEFT JOIN __ORDERS__ o ON o.customers_email_address=c.customers_email_address');
                    $list = $customer->relation(array('default_address'))->alias('c')->field($fields)->join($join)->where($where)->group('c.customers_email_address')->having('SUM(IF(o.orders_id IS NULL, 0, 1))=0')->order('customers_info_date_of_last_logon desc,customers_info_date_account_created desc,customers_info_number_of_logons desc')->page(($i+1), $num)->select();     
                }elseif(I('order_status')==='1' || I('order_status')==='2'){//至少成功*单
                    $join = array('__SITE__ s ON s.site_id=c.site_id','__ORDERS__ o ON o.customers_email_address=c.customers_email_address', '__ORDERS_REMARK__ o_r ON o.orders_id=o_r.orders_id AND o.site_id=o_r.site_id');
                    $s = I('order_status')-1;
                    $succss_status = array('待订货', '已发货', '已确认付款', '待发货', '已订货', '部分发货');
                    $list = $customer->relation(array('default_address'))->alias('c')->field($fields)->join($join)->where($where)->group('c.customers_email_address')->having('SUM(IF(o_r.order_status_remark IN (\''.implode('\',\'', $succss_status).'\'), 1, 0))>'.$s)->order('customers_info_date_of_last_logon desc,customers_info_date_account_created desc,customers_info_number_of_logons desc')->page(($i+1), $num)->select();
                }elseif(I('order_status')==='-1'){//只有失败记录的客户
                    $join = array('__SITE__ s ON s.site_id=c.site_id','__ORDERS__ o ON o.customers_email_address=c.customers_email_address', '__ORDERS_REMARK__ o_r ON o.orders_id=o_r.orders_id AND o.site_id=o_r.site_id');
                    $not_failure_status = array('待订货', '付款确认中', '已确认付款', '待发货', '已订货', '部分发货', '已发货',  '订单取消', '老订单');
                    $list = $customer->relation(array('default_address'))->alias('c')->field($fields)->join($join)->where($where)->group('c.customers_email_address')->having('SUM(IF(o_r.order_status_remark IN (\''.implode('\',\'', $not_failure_status).'\'), 1, 0))=0')->order('customers_info_date_of_last_logon desc,customers_info_date_account_created desc,customers_info_number_of_logons desc')->page(($i+1), $num)->select();
                }else{
                    $list = $customer->relation(array('default_address'))->alias('c')->field($fields)->join(array('__SITE__ s ON s.site_id=c.site_id'))->where($where)->order('customers_info_date_of_last_logon desc,customers_info_date_account_created desc,customers_info_number_of_logons desc')->page(($i+1), $num)->select();
                }                 
// var_dump($list);exit;
                if(!file_exists(DIR_FS_ORDER_PRODUCT . $site_id)) mkdir (DIR_FS_ORDER_PRODUCT . $site_id);
                $filename = DIR_FS_ORDER_PRODUCT . $site_id . '/'.$site_id.'_customers_'.($i*$num+1).'_'.($i*$num+sizeof($list));
                $filename  .= '_'.$time_start.'-'.$time_end;
                $filename .= 'v('.date('ymdhi').').xls';
                if(!file_exists($filename)){
                    $PHPExcel = new \PHPExcel();
                    $row = 1;
                    foreach ($field_array as $k => $k_info) {
                        $PHPExcel->getActiveSheet()->setCellValue($k . $row, $k_info['title']);
                        $PHPExcel->getActiveSheet()->getColumnDimension($k)->setWidth($k_info['width']);
                    }
                    $row = 2;
                    foreach ($list as $entry){
                        $where_is_paid = array(array('eq', '已确认付款'),array('eq', '待订货'), array('eq', '已订货'), array('eq', '待发货'), array('eq', '部分发货'), array('eq', '已发货'), 'OR');
                        $entry['success_orders'] = D('orders_remark')->alias('r')->join('__ORDERS__ o ON o.orders_id=r.orders_id AND o.site_id=r.site_id')->where(array('o.site_id'=>$site_id, 'customers_id'=>$entry['customers_id'], 'order_status_remark'=>$where_is_paid))->count();
                        $entry['failure_orders'] = D('orders_remark')->alias('r')->join('__ORDERS__ o ON o.orders_id=r.orders_id AND o.site_id=r.site_id')->where(array('o.site_id'=>$site_id, 'customers_id'=>$entry['customers_id'], 'order_status_remark' => '付款失败or未付款'))->count();
                        $entry['has_order']      = (($entry['success_orders'] || $entry['failure_orders'])?1:0);
                        foreach ($field_array as $k => $k_info) {
                            $PHPExcel->getActiveSheet()->setCellValue($k . $row, $entry[$k_info['key']]);
                        }
                        $row++;
                    }
                    $objWriter = new \PHPExcel_Writer_Excel5($PHPExcel);
                    $objWriter->save($filename);
                }
                $file_to_zip[] = $filename; 
            }
            $link = '';
            if(sizeof($file_to_zip)){
                Vendor('PhpOffice.PhpOffice_Autoloader');
                $ZipArchive = new \PhpOffice\PhpWord\Shared\ZipArchive();
                $zip_file = preg_replace('~[^a-z]~i', '_', $site['site_name']);
                if (I('register_time_start') !== '' && I('register_time_end') !== '') {
                    $time_start = date('Y_m_d', strtotime(I('register_time_start')));
                    $time_end   = date('Y_m_d', strtotime(I('register_time_end')));
                    $zip_file .= $time_start.'-'.$time_end;
                }
                $zip_file .= '('.date('YmdHis').').zip';
                $ZipArchive->open(DIR_FS_TEMP . $zip_file, \PhpOffice\PhpWord\Shared\ZipArchive::CREATE);
                foreach ($file_to_zip as $file) {
                    $ZipArchive->addFile($file, basename($file));
                }
                $ZipArchive->close();
                $link = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/'.str_replace(DIR_FS_ROOT, '', DIR_FS_TEMP . $zip_file);
                $this->ajaxReturn(array('success'=>($link!=''), 'url'=>$link));
            }else{
                $this->ajaxReturn(array('success'=>true, 'url'=>''));
            }
            
        }
        
}