<?php

namespace Order\Controller;
use Common\Controller\CommonController;
use Order\Model\OrderModel;

class FinanceController extends CommonController {
    
    private function _init_where_join(){
        $having = '';
        $join = array();
        $join[] = 'LEFT JOIN __SITE__ s ON s.site_id=op.site_id';
        $join[] = '__ORDERS__ o ON o.site_id=op.site_id AND o.orders_id=op.orders_id';
        $join[] = '__ORDERS_REMARK__ o_r ON o_r.site_id=op.site_id AND o_r.orders_id=op.orders_id';
        $join[] = '__ORDERS_PRODUCTS_REMARK__ o_p_r ON o_p_r.site_id=op.site_id AND o_p_r.orders_products_id=op.orders_products_id';
        $join[] = 'LEFT JOIN __ORDERS_PRODUCTS_REMARK_RECEIVED__ o_p_r_r ON o_p_r_r.orders_products_remark_id=o_p_r.orders_products_remark_id';
        $join[] = 'LEFT JOIN __ORDERS_PRODUCTS_SUPPLIER__ ops ON ops.supplier_id=o_p_r.supplier_id';
        $join[] = 'LEFT JOIN __PROMOTION_DEPARTMENT__ p_d ON p_d.department_id=s.system_depart';
         
        $where = array();
        $page_data = array();

        if(I('supplier_id')){
            $supplier_id = explode('_', I('supplier_id'));
            if(in_array('-1', $supplier_id)){
                if(sizeof($supplier_id)>1){
                        $where[] = array('_complex' => array(
                                                    '_logic' => 'OR',
                                                    'ops.supplier_id' => array('exp', 'is null'),
                                     				'_complex'=>array(
                                                            'ops.supplier_id' => array('IN', $supplier_id),
                            				            )
                                    ));
                }else
                    $where['ops.supplier_id'] = array('exp', 'is null');
            }else{
                $where['ops.supplier_id'] = array('IN', $supplier_id);
            }
            
            $page_data['supplier_id']   = I('supplier_id');
            $this->assign('option_supplier_id_selected', I('supplier_id'));
        }        
        
        if(I('order_number')){
            if(strpos(I('order_number'), "\n"))
                $order_number = explode("\n", I('order_number'));
            else
                $order_number = explode(",", I('order_number'));

            $_where = array('_logic' => 'OR');
            foreach($order_number as $k=>$v){
                $v = trim($v);
                if(empty($v)){
                    unset($order_number[$k]);
                }else{
                    $order_number[$k] = $v;
                    if(($match = parseZencartNo($v))!==false) {//独立站
                        $order_no_prefix = $match['orders_prefix'];
                        $zencart_order_no = $match['orders_id'];
                        $_where[] = array(
                                    '_logic' => 'AND',
                                    'o.orders_id' => $zencart_order_no,
                                    's.order_no_prefix' => $order_no_prefix,
                                );
                    } else {//平台
                        $zencart_order_no = $v;
                        $_where[] = array(
                                    'o_r.order_no' => $zencart_order_no
                                );
                    }                    
                }
            }
            if(sizeof($_where)>1)
                $where['_complex'] = $_where;
            $page_data['order_number'] = implode(',', $order_number);
            $this->assign('order_number', I('order_number'));
        }
        if(I('purchase_date_start') && I('purchase_date_end')){
            $page_data['purchase_date_start'] = I('purchase_date_start');
            $page_data['purchase_date_end']   = I('purchase_date_end');
            $where['o.date_purchased'] = array('between', array(I('purchase_date_start'), I('purchase_date_end')));
        }
        if(I('date_process_start') && I('date_process_end')){
            $page_data['date_process_start'] = I('date_process_start');
            $page_data['date_process_end']   = I('date_process_end');
            $where['o_p_r.date_process'] = array('between', array(I('date_process_start'), I('date_process_end')));
        }else{
            $date_process_end = date('Y-m-d');
            $date_process_start = date('Y-m-d', strtotime($date_process_end)-2*24*3600);
            $page_data['date_process_start'] = $date_process_start;
            $page_data['date_process_end']   = $date_process_end;
            $_GET['date_process_start'] = $date_process_start;
            $_GET['date_process_end'] = $date_process_end;
            $where['o_p_r.date_process'] = array('between', array($date_process_start, $date_process_end));            
        }        
        if(I('system_area')){
            $where['s.system_area'] = I('system_area');
            $page_data['system_area']   = I('system_area');
            $this->assign('option_system_area_selected', I('system_area'));
        }
        if(I('system_depart')){
            $where['s.system_depart'] = I('system_depart');
            $page_data['system_depart']   = I('system_depart');
            $this->assign('option_system_depart_selected', I('system_depart'));
        }
        if(I('logistics_status')!==''){
            $where['o_r.logistics_status'] = I('logistics_status');
            $page_data['logistics_status']   = I('logistics_status');
            $this->assign('option_logistics_status_selected', I('logistics_status'));
        }
        
        if(I('receiving_status')!==''){
            if('完全收货'==I('receiving_status')){
                $having = 'sum(quantity_received)>=products_quantity';
            }elseif('部分收货'==I('receiving_status')){
                $having = 'sum(quantity_received)<products_quantity sum(quantity_received)>0';
            }else{
                $having = 'sum(quantity_received)=0 OR quantity_received IS NULL';
                //$where['o_p_r_r.quantity_received'] = array('exp', 'IS NULL');
            }
            $this->assign('option_receiving_status_selected', I('receiving_status'));
        }
        if(I('cost_counted')!==''){
            $where['o_p_r.cost_counted'] = I('cost_counted');
            $page_data['cost_counted']   = I('cost_counted');
            $this->assign('option_cost_counted_selected', I('cost_counted'));
        }
        if(I('date_received_start') && I('date_received_end')){
            $page_data['date_received_start'] = I('date_received_start');
            $page_data['date_received_end']   = I('date_received_end');
            $where['o_p_r_r.date_received'] = array('between', array(I('date_received_start'), I('date_received_end')));
        }
        
        $option_order_status = array('待订货'=>'待订货','已订货'=>'已订货','部分发货'=>'部分发货','已发货'=>'已发货');
        $where['order_status_remark'] = array('IN', array_keys($option_order_status));
        
        $order_by = 'o_p_r.date_process desc,op.site_id desc,op.orders_id desc,op.orders_products_id asc';
        
        return array($where, $join, $page_data, $order_by, $having);
    }

    public function indexAction(){
        list($where, $join, $page_data, $order_by, $having) = $this->_init_where_join();
        //统计
        $sql = M('orders_products')->alias('op')->join($join)->where($where)->field('o.site_id,o.orders_id,products_quantity,quantity_received')->group('op.site_id,op.orders_products_id')->having($having)->select(false);
        $rs = M()->query('select count(distinct site_id,orders_id) as num from '.$sql.' as t');
        $orders_num = $rs[0]['num'];//订单总数
        $this->assign('orders_num', $orders_num);
        
        $page = I('page', 1);
        $num  = 100;//第页显示记录数
        $sql = M('orders_products')->alias('op')->join($join)->where($where)->field('op.site_id,op.orders_products_id,products_quantity,quantity_received')->group('op.site_id,op.orders_products_id')->having($having)->select(false);
        $rs = M()->query('select count(distinct site_id,orders_products_id) as num from '.$sql.' as t');
        $count = $rs[0]['num'];//产品项目总数  
        
        $products = M('orders_products')->alias('op')->join($join)->where($where)->page($page, $num)->order($order_by)->field('distinct op.*,o.*,s.*,o_r.*,o_p_r.*,ops.*,quantity_received')->group('op.site_id,op.orders_products_id')->having($having)->select();

        foreach($products as $k=>$v){
            $v['products_image'] = $this->_getProductImage($v['site_id'], $v['orders_products_id']);
            $v['products_image'] = $this->_cache_images($v['products_image'], 100, 80);
            $v['products_attributes'] = M('orders_products_attributes')->where(array('site_id'=>$v['site_id'], 'orders_products_id'=>$v['orders_products_id']))->select();
            
            $v['received_status'] = M('orders_products_remark_received')->where(array('orders_products_remark_id'=>$v['orders_products_remark_id']))->order('date_received desc')->select();//收货情况
            
            $products[$k] = $v;
        }
        
        $supplier = M('orders_products_supplier')->select();
        $option_supplier = array();
        foreach($supplier as $entry){
            $option_supplier[$entry['supplier_id']] = $entry['supplier_name'];
        }
        $option_supplier['-1'] = '未确定供应商';
        
        $options = explode('|',C('system_area'));
        foreach($options as $value){
            $option_system_area[$value] = $value;
        }
        $this->assign('option_system_area', $option_system_area);
        $system_depart_array = M('PromotionDepartment')->order('department_id')->getField('department_id,department_name',true);
        foreach($system_depart_array as $department_id => $department_name){
            $option_system_depart[$department_id] = $department_name;
        }     
        $option_logistics_status = array('00'=>'未完单', '1'=>'已完单');
        $option_receiving_status = array('未收货'=>'未收货', '部分收货'=>'部分收货', '完全收货'=>'完全收货');
        $option_cost_counted     = array('00'=>'否', '1'=>'是');
        
        $this->assign('option_receiving_status', $option_receiving_status);
        $this->assign('option_cost_counted', $option_cost_counted);
        $this->assign('option_logistics_status', $option_logistics_status);
        
        $this->assign('option_system_depart', $option_system_depart);
        $this->assign('option_supplier', $option_supplier);
        $this->assign('products', $products);
        $this->assign('page', $page);
        $this->assign('count', $count);
        $this->assign('num', $num);
        $this->assign('page_data', $page_data);        
        $this->display();
    }
    

    /*
    *压缩图片
    */
    private function _resize($file, $new_file, $new_width, $quality=80, $max_size=51200){
        if(!file_exists($file))
            return false;

        $file_size = filesize($file);
        if($file_size>$max_size){
            $path = dirname($new_file);
            if(!file_exists($path)){//创建目录
        		do {
        			$dir = $path;
        			while (!is_dir($dir)) {
        				$basedir = dirname($dir);
        				if ($basedir == '/' || is_dir($basedir))
        					mkdir($dir,0777);
        				else
        					$dir=$basedir;
        			}
        		} while ($dir != $path);
            }            
        	list($src_w, $src_h) = getimagesize($file);
        		
        	if($src_w > $new_width){
        		$r = $new_width/$src_w;
        		$new_height = $src_h*$r;
        	}else{
        		$new_width  = $src_w;
        		$new_height = $src_h;
        	}
        	
            $type = pathinfo($file, PATHINFO_EXTENSION);
            $type = strtolower($type);
            
            $allowedTypes = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
            if (!in_array($type, $allowedTypes)) {
                return false;
            }
            switch ($type) {
                case 'gif' :
                    $im_src = imagecreatefromgif($file);
                break;
                case 'jpg' :
                case 'jpeg' :
                    $im_src = imagecreatefromjpeg($file);
                break;
                case 'png' :
                    $im_src = imagecreatefrompng($file);
                break;
                case 'bmp' :
                    $im_src = imagecreatefrombmp($file);
                break;
            }           
        	$im_dst = imagecreatetruecolor ($new_width, $new_height);
        	$white  = imagecolorallocate($im_dst, 255, 255, 255);
        	imagefill($im_dst, 0, 0, $white);
        	imagecopyresized ( $im_dst , $im_src ,  0,  0, 0 , 0 , $new_width , $new_height , $src_w , $src_h );
            imagejpeg($im_dst, $new_file, $quality);
        	imagedestroy($im_dst);
        	imagedestroy($im_src);
        	return $new_file;
        }else{
            return false;
        }
    }
    
    public function itemEditAction($site_id, $orders_products_id){
        if(IS_POST){
            $data = array(
                'supplier_id'       => I('supplier_id'),
                'date_process'      => I('date_process'),
                'purchase_price'    => I('purchase_price'),
                'quantity_process'  => I('quantity_process'),
            );
            M('orders_products_remark')->where(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id))->save($data);
            
            M('orders_remark')->where(array('site_id'=>$site_id, 'orders_id'=>I('orders_id')))->save(array('finance_remark'=>I('finance_remark')));
            
            $this->assign('message_success', '保存成功!');
        }
        $join = array();
        $join[] = '__ORDERS_PRODUCTS__ op ON o_p_r.site_id=op.site_id AND o_p_r.orders_products_id=op.orders_products_id';
        $join[] = '__ORDERS_REMARK__ o_r ON o_r.site_id=o_p_r.site_id AND o_r.orders_id=o_p_r.orders_id';
        $join[] = 'LEFT JOIN __SITE__ s ON s.site_id=o_p_r.site_id';
        $row = M('orders_products_remark')->alias('o_p_r')->join($join)->field('o_p_r.*,op.*,o_r.finance_remark,o_r.order_no,s.order_no_prefix')->where(array('o_p_r.site_id'=>$site_id, 'o_p_r.orders_products_id'=>$orders_products_id))->find();

        $products_image = $this->_getProductImage($site_id, $orders_products_id);
        $row['products_image'] = $this->_cache_images($products_image, 100, 80);
        $row['products_attributes'] = M('orders_products_attributes')->where(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id))->select();
        
        $supplier = M('orders_products_supplier')->select();
        $option_supplier = array();
        foreach($supplier as $entry){
            $option_supplier[$entry['supplier_id']] = $entry['supplier_id'].'-'.$entry['supplier_name'];
        }
        $option_supplier['-1'] = '未确定供应商'; 
        
        $this->assign('supplier_id_selected', $row['supplier_id']);
        $this->assign('option_supplier', $option_supplier);
        $this->assign('data', $row);
        $this->display();
    }
    
    private function _getCellValue($sheet, $header_fields, $field_name, $row){
        if(isset($header_fields[$field_name])){
            return $sheet->getCell($header_fields[$field_name].$row)->getFormattedValue();
        }else{
            return false;
        }
    }
    
    /*
    * 订货表回单导入
    */
    public function importAction(){
        if (UPLOAD_ERR_OK != $_FILES['file']['error']) {
            $this->error('表格上传失败!错误码:' . $_FILES['file']['error']);
        }
        $ext = pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
        if($ext!='xls')
            $this->error('文件必须是xls格式');
        $file = $_FILES['file']['tmp_name'];
        Vendor('PHPExcel.PHPExcel');
        $php_excel_reader = new \PHPExcel_Reader_Excel5();
        if (!$php_excel_reader->canRead($file)) {
            $this->error('无法解析上传的表格!');
        }
        $PHPExcel = $php_excel_reader->load($file);
        $currentSheet = $PHPExcel->getSheet(0);


        $fileds = array();
        for($i=65;$i<=90;$i++){
            $col = chr($i);
            $title = $currentSheet->getCell($col . '1')->getFormattedValue();
            if(empty($title)) break;
            $fileds[$title] = $col;
        }
        if(!isset($fileds['跟货号']) || !isset($fileds['订货单价'])) 
            $this->error('你上传的表格缺少字段“跟货号”或“订货单价”!');
            
        $row = 2;
        do {
            $next = true;
            $genghuohao       = $currentSheet->getCell($fileds['跟货号'] . $row)->getFormattedValue();
            $purchase_price   = $currentSheet->getCell($fileds['订货单价'] . $row)->getFormattedValue();
            $cost_counted     = $currentSheet->getCell($fileds['已记录成本(是/否)'] . $row)->getFormattedValue();
            $logistics_status = $currentSheet->getCell($fileds['是否完单'] . $row)->getFormattedValue();
            $supplier_name      = $currentSheet->getCell($fileds['供应商'] . $row)->getFormattedValue();
            $quantity_received  = $currentSheet->getCell($fileds['收货数量'] . $row)->getFormattedValue();
            $date_received      = $currentSheet->getCell($fileds['收货日期'] . $row)->getFormattedValue();
            $date_received = date('Y-m-d',strtotime($date_received));

            if(empty($genghuohao))
                $next = false;
            else{    
                list($site_id, $orders_products_id) = explode('-', $genghuohao);
                $check_supplier = M('orders_products_supplier')->where(array('supplier_name'=>$supplier_name))->field('supplier_id')->find();
                if($check_supplier)
                    $supplier_id = $check_supplier['supplier_id'];
                else{//新增供应商
                    M('orders_products_supplier')->add(array('supplier_name'=>$supplier_name, 'orders_products_categories_ids'=>'[{}]'));
                    $supplier_id = M('orders_products_supplier')->getLastInsID();
                }                
                $data = array(
                    'purchase_price'=>$purchase_price,
                    //'receiving_status'=>$receiving_status,
                    'cost_counted'=>($cost_counted=='是'?1:0),
                    //'quantity_received1'=>$quantity_received1,
                    //'quantity_received2'=>$quantity_received2,
                    //'quantity_received3'=>$quantity_received3,
                    'supplier_id'=>$supplier_id,
                );
                M('orders_products_remark')->where(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id))->save($data);
                $data = array(
                    'logistics_status'=>($logistics_status=='是'?1:0)
                );
                $check_orders_products_remark = M('orders_products_remark')->where(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id))->find();
                if($check_orders_products_remark){
                    M('orders_remark')->where(array('site_id'=>$site_id, 'orders_id'=>$check_orders_products_remark['orders_id']))->save($data);
                    if(!empty($date_received) && $date_received!='1970-01-01'){
                        $check_orders_products_remark_received = M('orders_products_remark_received')->where(array('orders_products_remark_id'=>$check_orders_products_remark['orders_products_remark_id'], 'date_received'=>$date_received))->find();
                        if($check_orders_products_remark_received){
                            M('orders_products_remark_received')->where(array('orders_products_remark_id'=>$check_orders_products_remark['orders_products_remark_id'], 'date_received'=>$date_received))->save(array('quantity_received'=>$quantity_received, 'cost_counted'=>($cost_counted=='是'?1:0)));
                        }else{
                            M('orders_products_remark_received')->add(array('orders_products_remark_id'=>$check_orders_products_remark['orders_products_remark_id'], 'date_received'=>$date_received,'quantity_received'=>$quantity_received, 'cost_counted'=>($cost_counted=='是'?1:0)));
                        }
                    }                    
                }
            }
            $row++;
        } while ($next);


        $this->success('已更新'.($row-3).'条订货价格!');
    }
    
    public function importConfirmAction(){
        
        $date_process = I('post.date_process');
        $supplier_id = I('post.supplier_id');
        $categories_id = I('post.categories_id');
        $is_customized = I('post.is_customized');
        $quantity_process = I('post.quantity_process');
        $times_process = I('post.times_process');
        $purchase_price = I('post.purchase_price');
        
        $success_num = 0;

        foreach($date_process as $orders_products_remark_id=>$date){
            if(empty($date) || empty($quantity_process[$orders_products_remark_id]) || empty($supplier_id[$orders_products_remark_id])) continue;
            
            if($times_process[$orders_products_remark_id]==1){
                $data = array(
                    'date_process'=>$date,
                    'supplier_id'=>$supplier_id[$orders_products_remark_id],
                    'categories_id'=>$categories_id[$orders_products_remark_id],
                    'is_customized'=>$is_customized[$orders_products_remark_id],
                    'quantity_process'=>$quantity_process[$orders_products_remark_id],
                    'purchase_price'=>$purchase_price[$orders_products_remark_id],
                );
                $data['detail_process'] = json_encode(array(1=>$data));
            }else{
                $detail_process = M('orders_products_remark')->where(array('orders_products_remark_id'=>$orders_products_remark_id))->field('detail_process')->find();
                $detail_process = json_decode($detail_process['detail_process'], true);
                for($i=$times_process[$orders_products_remark_id];$i<=sizeof($detail_process);$i++){
                    unset($detail_process[$i]);
                }
                
                $detail_process[$i] = array(
                    'date_process'=>$date,
                    'supplier_id'=>$supplier_id[$orders_products_remark_id],
                    'is_customized'=>$is_customized[$orders_products_remark_id],
                    'quantity_process'=>$quantity_process[$orders_products_remark_id],
                    'purchase_price'=>$purchase_price[$orders_products_remark_id],
                );//本次订货情况
                $quantity = 0;
                foreach($detail_process as $entry){
                    $quantity += $entry['quantity_process'];
                }
                $data = array(
                    'date_process'=>$date,
                    'supplier_id'=>$supplier_id[$orders_products_remark_id],
                    'categories_id'=>$categories_id[$orders_products_remark_id],
                    'is_customized'=>$is_customized[$orders_products_remark_id],
                    'quantity_process'=>$quantity,
                    'purchase_price'=>$purchase_price[$orders_products_remark_id],
                    'detail_process'=>json_encode($detail_process)
                );                
            }
            $success_num++;
            M('orders_products_remark')->where(array('orders_products_remark_id'=>$orders_products_remark_id))->save($data);
        }
        $this->success('成功导入'.$success_num.'条数据!', U('Order/Purchase/index'));
    }
    
    /*
    * 获取压缩后的图片
    */
    private function _cache_images($src, $width, $quality){
        $new_file = DIR_FS_TEMP.'cache/'.preg_replace('~(.+)(\.\w+)$~i', '$1_w'.$width.'_q'.$quality.'.jpg', $src);
        if(!file_exists($new_file)){
            if($this->_resize(DIR_FS_ROOT.$src, $new_file, $width, $quality)){
                $cache_file = str_replace(DIR_FS_ROOT, '', $new_file);
                return $cache_file;
            }
        }else{
            $cache_file = str_replace(DIR_FS_ROOT, '', $new_file);
            return $cache_file;
        }  
        return $src;
    }
    
    /*
    *订单标记
    */
    public function archivingAction(){

        if(I('archiving_type')=='logistics_status'){
            $orders = I('orders');
            if(empty($orders)){
                $this->error('请勾选相应的订单!');
            }            
            $data['logistics_status'] = I('archiving_value');
            foreach($orders as $entry){
                list($site_id, $orders_id) = explode('-', $entry);
                $where = array(
                    'site_id'=>$site_id,    
                    'orders_id'=>$orders_id,
                );
                M('orders_remark')->where($where)->save($data);
            }            
        }elseif(I('archiving_type')=='receiving_status'){
            $order_products = I('order_products');
            if(empty($order_products)){
                $this->error('请勾选相应的产品!');
            }  
            if(I('archiving_value')=='完全收货'){
                foreach($order_products as $entry){
                    list($site_id, $orders_products_id) = explode('-', $entry);
                    $where = array(
                        'site_id'=>$site_id,    
                        'orders_products_id'=>$orders_products_id,
                    );
                    $orders_products_row = M('orders_products')->field('products_quantity')->where($where)->find();
                    if($orders_products_row){
                        $order_qty = $orders_products_row['products_quantity'];
                        
                        $check_received_qty = M('orders_products_remark')->alias('opr')->join('LEFT JOIN __ORDERS_PRODUCTS_REMARK_RECEIVED__ oprr ON oprr.orders_products_remark_id=opr.orders_products_remark_id')->where($where)->field('sum(quantity_received) as total_received,opr.orders_products_remark_id')->find();   
                        if(!$check_received_qty['total_received']){
                            $quantity_received = 0;
                        }else{
                            $quantity_received = $check_received_qty['total_received'];
                        }
                        
                        if($check_received_qty && $order_qty>$quantity_received){
                            $quantity_received = $order_qty-$quantity_received;
                            $date_received     = date('Y-m-d');
                            M('orders_products_remark_received')->add(array('quantity_received'=>$quantity_received, 'date_received'=>$date_received, 'orders_products_remark_id'=>$check_received_qty['orders_products_remark_id']));
                        }
                    }
                } 
            }elseif(I('archiving_value')=='未收货'){
                foreach($order_products as $entry){
                    list($site_id, $orders_products_id) = explode('-', $entry);
                    $where = array(
                        'site_id'=>$site_id,    
                        'orders_products_id'=>$orders_products_id,
                    );
                    $check_orders_products_remark = M('orders_products_remark')->where($where)->field('orders_products_remark_id')->find();
                    if($check_orders_products_remark){
                        M('orders_products_remark_received')->where(array('orders_products_remark_id'=>$check_orders_products_remark['orders_products_remark_id']))->delete();
                    }
                } 
            }
        }elseif(I('archiving_type')=='cost_counted'){
            $received = I('received');
            if(empty($received)){
                $this->error('请勾选相应的收货记录!');
            }  
            foreach($received as $entry){
                list($orders_products_remark_id, $date) = explode('|', $entry);
                M('orders_products_remark_received')->where(array('orders_products_remark_id'=>$orders_products_remark_id, 'date_received'=>$date))->save(array('cost_counted'=>I('archiving_value')));
            }
        }

        $this->success('操作完成!');
    }
    
    /*
    *导出excel格式
    */
    private function _export_excel($products, $fileName){
        layout(false);
        vendor('PHPExcel.PHPExcel');
        //导出的字段中不能乱改
        $field_array = array(
                    'A' => array('title' => '跟货号',   'width' => 20,  'key' => 'orders_products_number'),
                    'B' => array('title' => '产品图片', 'width' => 15,  'key' => 'image'),
                    'C' => array('title' => '数量',     'width' => 10,  'key' => 'products_quantity'),
                    'D' => array('title' => '产品信息', 'width' => 80, 'key' => 'products_detail'),
                    'E' => array('title' => 'SKU',      'width' => 15, 'key' => 'products_model'),
                    'F' => array('title' => '尺码',     'width' => 10, 'key' => 'size'),
                    'G' => array('title' => '产品单价',  'width' => 10, 'key' => 'final_price'),
                    'H' => array('title' => '订单号',   'width' => 20,  'key' => 'order_number'),
                    'I' => array('title' => '订单日期', 'width' => 10,  'key' => 'date_purchased'),
                    'J' => array('title' => '供应商',   'width' => 10,  'key' => 'supplier_name'),
                    'K' => array('title' => '订货日期', 'width' => 10,  'key' => 'date_process'),
                    'L' => array('title' => '订货数量', 'width' => 10,  'key' => 'quantity_process'),
                    'M' => array('title' => '订货单价', 'width' => 10,  'key' => 'purchase_price'),
                    
                    'N' => array('title' => "收货数量", 'width' => 10,  'key' => 'quantity_received'),
                    'O' => array('title' => "收货日期", 'width' => 15,  'key' => 'date_received'),
                    'P' => array('title' => "已记录成本(是/否)", 'width' => 20,  'key' => 'cost_counted'),
                    'Q' => array('title' => '是否完单', 'width' => 10,  'key' => 'logistics_status'),//整个单子
                    'R' => array('title' => '所属部门', 'width' => 10,  'key' => 'department_name'),//整个单子
                );
        $PHPExcel = new \PHPExcel();
        $currentSheet = $PHPExcel->getActiveSheet();
        $currentSheet->setTitle('成本表');
        $row = 1;
        foreach ($field_array as $k => $k_info) {
            $currentSheet->setCellValue($k . $row, $k_info['title']);
            $currentSheet->getColumnDimension($k)->setWidth($k_info['width']);
        }
        $row++;
        
        $pre_order_number = '';
        $i = 0;
        $merge_rows_start = 0;
        $merge_rows_end   = 0;
        $order_remark     = array();//业务备注
        $finance_remark   = array();//财务备注
        foreach($products as $index=>$product){
            $order_number = $product['order_number'];
            if($pre_order_number!=$order_number){
                if(!empty($product['order_remark'])){
                    $currentSheet->setCellValue('K' . $row, $product['order_remark']);
                }
                if($i%2==0){
                    $bk_color = 'ffffff';
                }else{
                    $bk_color = 'e6e6e6';
                }
                $i++;
                $pre_order_number = $order_number;
                
            }
            if(!empty($product['order_remark']) && !isset($order_remark[$order_number])){
                $order_remark[$order_number] = array(
                    'order_number'      => $order_number,
                    'date_purchased'    => $product['date_purchased'],
                    'order_remark'      => $product['order_remark'],
                );
            }    
            if(!empty($product['finance_remark']) && !isset($finance_remark[$order_number])){
                $finance_remark[$order_number] = array(
                    'order_number'      => $order_number,
                    'date_purchased'    => $product['date_purchased'],
                    'finance_remark'    => $product['finance_remark'],
                );
            }                
            foreach ($field_array as $k => $k_info) {
                if(empty($k_info['key'])) continue;
                
                if($k_info['key']=='image'){
                    $src = $product['products_image'];
                    $img = new \PHPExcel_Worksheet_Drawing();
                    $img->setPath($src);//写入图片路径
                    $img->setWidth(100);//写入图片宽度
                    $img->setHeight(100);//写入图片高度
                    $img->setOffsetX(1);//写入图片在指定格中的X坐标值
                    $img->setOffsetY(1);//写入图片在指定格中的Y坐标值
                    $img->setRotation(1);//设置旋转角度
                    $img->getShadow()->setVisible(true);
                    $img->getShadow()->setDirection(50);
                    $img->setCoordinates($k . $row);//设置图片所在表格位置
                    $img->setWorksheet($currentSheet); 
                }elseif($k_info['key']=='products_detail'){
                    $products_keyword = array('women', 'Nike', 'Youth', 'Game', 'Elite', 'Limited', 'Toddler', 'Men', 'Kid');
                    $products_keyword_pos = array();
                    foreach ($products_keyword as $keyword) {
                        $products_name = $product['products_detail'];
                        do {
                            $pos = stripos($products_name, $keyword);
                            if ($pos !== false) {
                                $products_keyword_pos[$pos] = $keyword;
                                $products_name = substr($products_name, ($pos + 1));
                            }
                        }
                        while ($pos !== false);
                    }
                    if (false && sizeof($products_keyword_pos)) {
                        $l = strlen($product['products_detail']);
                        $objRichText = new \PHPExcel_RichText();
                        for ($pos = 0; $pos < $l;) {
                            if (isset($products_keyword_pos[$pos])) {
                                $len = strlen($products_keyword_pos[$pos]);
                                $objPayable = $objRichText->createTextRun( substr($product['products_detail'], $pos, $len));//关键词加粗
                                $objPayable->getFont()->setBold(true);
                                $objPayable->getFont()->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_RED ) );
                                $pos += $len;
                            } else {
                                $objRichText->createText(substr($product['products_detail'], $pos, 1));
                                $pos++;
                            }
                        }
                        $currentSheet->getCell($k . $row)->setValue($objRichText);
                    }
                    
                    if(false && !empty($product['remark_product'])){
                        $objRichText = new \PHPExcel_RichText();
                        $objRichText->createText($product['products_detail']);
                        
                        $objPayable = $objRichText->createTextRun("备注:".$product['remark_product']);
                        $objPayable->getFont()->setBold(true);
                        $objPayable->getFont()->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_RED ) );   
                        $objPayable->getFont()->setSize(12);
                        $currentSheet->getCell($k . $row)->setValue($objRichText);
                    }else{
                        $detail = $product[$k_info['key']].(empty($product['remark_product'])?"":"\n备注:".$product['remark_product']);
                        $currentSheet->setCellValue($k . $row, $detail);
                        if(!empty($product['remark_product']))
                            $currentSheet->getStyle($k . $row)->applyFromArray(array('font'=>array('color'=>array('rgb' => 'FF0000'))));
                    }
                }elseif($k_info['key']=='cost_counted'||$k_info['key']=='logistics_status'){
                    $value = ($product[$k_info['key']]==1?'是':'否');
                    $currentSheet->setCellValue($k . $row, $value);
                }else{
                    if('quantity_received'==$k_info['key'] && $product[$k_info['key']]==0)
                        $product[$k_info['key']] = "";
                    elseif('date_received'==$k_info['key'] && $product[$k_info['key']]=="1970-01-01")
                        $product[$k_info['key']] = "";
                        
                    $currentSheet->setCellValue($k . $row, $product[$k_info['key']]);
                }
                $currentSheet->getStyle($k . $row)->getAlignment()->setWrapText(true);
                
            }
            
            $currentSheet->getStyle('A'.$row.':P' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($bk_color);
            
            $currentSheet->getRowDimension($row)->setRowHeight(100);
            $row++;
        }
        $last_col = array_key_last($field_array);
        $currentSheet->getStyle('A1:'.$last_col . $row)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                ),
                            )
                        )
                );
        if(sizeof($order_remark)){
            $PHPExcel->createSheet();
            $PHPExcel->setActiveSheetIndex(1);
            $currentSheet = $PHPExcel->getActiveSheet();
            $currentSheet->setTitle('业务备注');
            $field_array = array(
                'A' => array('title' => '订单号',   'width' => 20,  'key' => 'order_number'),
                'B' => array('title' => '订单日期', 'width' => 10,  'key' => 'date_purchased'),
                'C' => array('title' => '业务备注', 'width' => 10,  'key' => 'order_remark'),
            );
            $row = 1;
            foreach ($field_array as $k => $k_info) {
                $currentSheet->setCellValue($k . $row, $k_info['title']);
                $currentSheet->getColumnDimension($k)->setWidth($k_info['width']);
            }
            $row++;            
            foreach($order_remark as $entry){
                foreach ($field_array as $k => $k_info) {
                    $currentSheet->setCellValue($k . $row, $entry[$k_info['key']]);
                }
                $currentSheet->getRowDimension($row)->setRowHeight(15);
                $row++;
                
            }
            $currentSheet->getStyle('A1:C' . $row)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                ),
                            )
                        )
                );
        }
        
        if(sizeof($finance_remark)){
            $PHPExcel->createSheet();
            $PHPExcel->setActiveSheetIndex(2);
            $currentSheet = $PHPExcel->getActiveSheet();
            $currentSheet->setTitle('财务备注');
            $field_array = array(
                'A' => array('title' => '订单号',   'width' => 20,  'key' => 'order_number'),
                'B' => array('title' => '订单日期', 'width' => 10,  'key' => 'date_purchased'),
                'C' => array('title' => '财务备注', 'width' => 10,  'key' => 'finance_remark'),
            );
            $row = 1;
            foreach ($field_array as $k => $k_info) {
                $currentSheet->setCellValue($k . $row, $k_info['title']);
                $currentSheet->getColumnDimension($k)->setWidth($k_info['width']);
            }
            $row++;            
            foreach($finance_remark as $entry){
                foreach ($field_array as $k => $k_info) {
                    $currentSheet->setCellValue($k . $row, $entry[$k_info['key']]);
                }
                $currentSheet->getRowDimension($row)->setRowHeight(15);
                $row++;
                
            }
            $currentSheet->getStyle('A1:C' . $row)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                ),
                            )
                        )
                );
        }
        
        
        $objWriter = new \PHPExcel_Writer_Excel5($PHPExcel);
        $objWriter->save(DIR_FS_TEMP . $fileName .'.xls');

        $zip = new \ZipArchive;
        $zip_file = DIR_FS_TEMP . date('ymdhis') .'.zip';
        $zip->open($zip_file, \ZIPARCHIVE::CREATE);
        $zip->addFile(DIR_FS_TEMP . $fileName .'.xls', $fileName .'.xls'); 
        $zip->close();
        $link = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/'.str_replace(DIR_FS_ROOT, '', $zip_file);
        echo $link;
        exit();
    }
    
    private function _export_address($products){
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
        $active_sheet    = $PHPExcel->getActiveSheet();
        $fileds = array(
            'A' => array('title' => '物流备注', 'key' => 'logistics_remark'),
            'B' => array('title' => '订单号', 'key' => 'order_number'),
            'C' => array('title' => 'SKU', 'key' => ''),
            'D' => array('title' => '数量', 'key' => 'qty'),
            'E' => array('title' => '邮箱', 'key' => ''),
            'F' => array('title' => '姓名', 'key' => 'delivery_name'),
            'G' => array('title' => '地址', 'key' => 'delivery_street_address'),
            'H' => array('title' => '城市', 'key' => 'delivery_city'),
            'I' => array('title' => '州', 'key' => 'delivery_state'),
            'J' => array('title' => '邮编', 'key' => 'delivery_postcode'),
            'K' => array('title' => '国家', 'key' => 'delivery_country'),
            'L' => array('title' => '电话', 'key' => 'customers_telephone'),
            'M' => array('title' => '付款方式', 'key' => 'payment_method_zh'),
            'N' => array('title' => '发货地址', 'key' => ''),
            'O' => array('title' => '业务类型', 'key' => ''),
            'P' => array('title' => '增值服务', 'key' => ''),
            'Q' => array('title' => '货运方式', 'key' => 'shipping_method_zh'),
            'R' => array('title' => '备注', 'key' => 'order_remark')
        );
        foreach ($fileds as $k => $k_info) {
            $active_sheet->setCellValue($k . '1', $k_info['title']);
            $active_sheet->getColumnDimension($k)->setWidth(15);
        }
        $order = new OrderModel();
        $k = 1;
        $row = 2;
        foreach ($products as $products_entry) {
            $site_id  = $products_entry['site_id'];
            $order_id = $products_entry['orders_id'];

            $order_info = $order->relation(array('product','site','order_remark'))->where(array('site_id' => $site_id, 'orders_id' => $order_id))->find();
            if(!empty($order_info['order_no'])){
                $order_info['order_number'] = $order_info['order_no'];
            }else{
                $order_info['order_number'] = $order_info['order_no_prefix'] . $order_info['orders_id'];
            }

            $order_info['qty'] = 0;
            foreach ($order_info['product'] as $product) {
                if($product['orders_products_remark']['remove']==1) continue;
                $order_info['qty'] += $product['products_quantity'];
            }
            $order_info['delivery_street_address'] = $order_info['delivery_street_address'].' '.$order_info['delivery_suburb'];
            $order_info['payment_method_zh'] = strtr(
                            $entry['payment_module_code'], array(
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
            switch ($order_info['shipping_module_code']) {
                case 'faster':
                case 'zones':
                case '固定运费':
                    $order_info['shipping_method_zh'] ='快速 faster';
                    break;
                default:
                    $order_info['shipping_method_zh'] ='标准 standard';
            }

            //判断收货地址与账单地址是否一致
            if ($order_info['order_remark']) {
                $field_style['font']['color']['argb'] = '00ff0000';
            } else {
                $field_style['font']['color']['argb'] = '00000000';
            }
            
            foreach ($fileds as $col => $k_info) {
                if(empty($k_info['key'])) continue;
                $active_sheet->setCellValue($col . $row, $order_info[$k_info['key']]);
                $active_sheet->getStyle($col . $row)->applyFromArray($field_style)->getAlignment()->setShrinkToFit(true);
            }
            $active_sheet->getRowDimension($row)->setRowHeight(30);
            $row++;
        }
        $PHPExcel->getActiveSheet()->getStyle('A1:R' . $row)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                ),
                            )
                        )
                );
        $fileName = "发货地址列表" . date('YmdHis', time());
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
    
    private function _get_order_dir($site_id, $order_id) {
        $dir = DIR_FS_ORDER_PRODUCT . $site_id .'/'. floor($order_id/5000).'/'.$order_id.'/';
        if(file_exists($dir)===false) makeDir($dir);
        return $dir;
    }
 
    private function  _export_order($products){
        Vendor('PhpOffice.PhpOffice_Autoloader');
        $files = array();
        foreach ($products as $products_entry) {
            $site_id  = $products_entry['site_id'];
            $order_id = $products_entry['orders_id'];
            $site_info = D('site')->where(array('site_id'=>$site_id))->field('order_no_prefix,type,system_cms')->find();
            if($site_info['type']==1 && $site_info['system_cms'] != 'easyshop')
                $order_no = $site_info['order_no_prefix'].$order_id; 
            else {
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
        if(sizeof($files)==0) return false;
        $ZipArchive = new \PhpOffice\PhpWord\Shared\ZipArchive();
        $zip_file = date('YmdHis').mt_rand(1000, 9999) . '.zip';
        $ZipArchive->open(DIR_FS_TEMP . $zip_file, \PhpOffice\PhpWord\Shared\ZipArchive::CREATE);
        foreach ($files as $file) {
            $ZipArchive->addFile($file, basename($file));
        }
        $ZipArchive->close();
        $link = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/'.str_replace(DIR_FS_ROOT, '', DIR_FS_TEMP . $zip_file);
        return $link;
    }
    
    /*
    *获取物流备注
    */
    public function remarkAction(){
        if(I('get.orders_products_id')){
            $table = 'orders_products_remark';
            $where = array('site_id'=>I('get.site_id'), 'orders_products_id'=>I('get.orders_products_id'));
            $check_row = M($table)->where($where)->find();
            if(!$check_row){
                $row = M('orders_products')->where($where)->find();
                $where['orders_id'] = $row['orders_id'];
                M('orders_products_remark')->add($where);
            }
        }else{
            $site_id = I('get.site_id');
            $orders_id = I('get.orders_id');            
            $table = 'orders_remark';
            $where = array('site_id'=>$site_id, 'orders_id'=>$orders_id);
        }
        $field = I('get.field');

        if(empty($field) && !in_array($field, array('remark', 'logistics_remark'))){
            $this->ajaxReturn(array('success'=>false), 'JSON');
        }
        if(IS_GET){
            $row = M($table)->where($where)->field($field)->find();
            $this->ajaxReturn($row, 'JSON');
        }elseif(IS_POST){
            $value = I('value', '');
            $row = M($table)->where($where)->save(array($field=>$value));
            $this->ajaxReturn(array('success'=>true), 'JSON');
        }
    }
    
    public function exportAction(){
        list($where, $join, $page_data, $order_by) = $this->_init_where_join();
        
        if(I('get.type', 'word')=='excel'){
            $page = I('get.page', 1);
            $num  = I('get.num', 1000);
            $where['remove'] = 0;
            $products = M('orders_products')->alias('op')->join($join)->where($where)->page($page, $num)->order($order_by)->select();
            foreach($products as $k=>$v){
                $v['products_image'] = $this->_getProductImage($v['site_id'], $v['orders_products_id']);
                $v['products_image'] = DIR_FS_ROOT.$this->_cache_images($v['products_image'], 80, 70);
                
                $v['products_attributes'] = M('orders_products_attributes')->where(array('site_id'=>$v['site_id'], 'orders_products_id'=>$v['orders_products_id']))->select();
    
                $v['products_detail'] = $v['products_name']."\n";
                $v['size']            = "";
                if($v['products_attributes']){
                    foreach($v['products_attributes'] as $attributes){
                        if(preg_match('~size~i', $attributes['products_options'])){
                            $v['size'] = $attributes['products_options_values'];            
                        }
                        $v['products_detail'] .= $attributes['products_options'].':'.$attributes['products_options_values']."\n";
                    }
                }
                $v['orders_products_number'] = $v['site_id'].'-'.$v['orders_products_id'];        
                $v['order_number'] = empty($v['order_no']) ? $v['order_no_prefix'] . $v['orders_id'] : $v['order_no'];
                $v['date_purchased'] = date('Y-m-d', strtotime($v['date_purchased']));
                $products[$k] = $v;
            }
        }elseif(I('get.type')=='order'){
            $page = I('get.page', 1);
            $num  = 100;//第页显示记录数
            $sql = M('orders_products')->alias('op')->join($join)->where($where)->field('distinct o.site_id,o.orders_id')->select(false);
            $rs = M()->query('select count(*) as num from '.$sql.' as t');
            $count = $rs[0]['num'];
            $products = M('orders_products')->alias('op')->join($join)->where($where)->field('distinct o.site_id,o.orders_id')->page($page, $num)->order('date_purchased desc,o.orders_id desc')->select();
		}elseif(I('get.type') == 'delivery'){
			$page = I('get.page', 1);
			$num  = 10000;
			$join[] = '__ORDERS_DELIVERY__ o_d ON o_d.site_id=o.site_id AND o_d.orders_id=o.orders_id';
			$delivery = M('orders_products')->alias('op')->join($join)->field('distinct o_d.orders_delivery_id,o_r.order_no,s.order_no_prefix,o.orders_id,o_d.delivery_date,o_d.delivery_type,o_d.delivery_forward_no,o_d.delivery_tracking_no,o_d.delivery_weight,o_d.delivery_quanlity')->where($where)->page($page, $num)->order($order_by)->select();
        }else{
            $products = M('orders_products')->alias('op')->join($join)->where($where)->field('distinct o.site_id,o.orders_id')->order('date_purchased desc,o.orders_id desc')->select();
        }   
                
        $fileName = "订货表成本表_" . date('YmdHis', time());
        if(I('get.type', 'word')=='excel')    
            $this->_export_excel($products, $fileName);
        elseif('address'==I('get.type', 'word')){
            $this->_export_address($products, $fileName);
        }elseif('order'==I('get.type', 'order')){
            $link = $this->_export_order($products);
            $page = $page;
            $total_page = ceil($count/$num);
            $this->ajaxReturn(array('link'=>$link, 'page'=>$page, 'total_page'=>$total_page), 'JSON');
		}elseif(I('get.type') == 'delivery'){
			$fileName = "订单物流表_" . date('YmdHis', time());
			$this->_export_delivery($delivery, $fileName);
		}
    }
    
    public function importLogisticsRemarkAction(){
        if (UPLOAD_ERR_OK != $_FILES['file']['error']) {
            $this->error('表格上传失败!错误码:' . $_FILES['file']['error']);
        }
        $ext = pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
        if($ext!='xls')
            $this->error('文件必须是xls格式');
        $file = $_FILES['file']['tmp_name'];
        Vendor('PHPExcel.PHPExcel');
        $php_excel_reader = new \PHPExcel_Reader_Excel5();
        if (!$php_excel_reader->canRead($file)) {
            $this->error('无法解析上传的表格!');
        }
        $PHPExcel = $php_excel_reader->load($file);
        $currentSheet = $PHPExcel->getSheet(0);

        $fileds = array(
            'A' => array('title' => '物流备注',   'key' => 'logistics_remark',              'required' => false),
            'B' => array('title' => '订单号',     'key' => 'order_no',                      'required' => true),
        );
        //表格格式验证 start
        $ok = true;
        foreach ($fileds as $col => $v) {
            $title = $currentSheet->getCell($col . '1')->getFormattedValue();
            $title = trim($title);
            if (strpos($title, $v['title']) === false) {
                $this->error('你上传的表格字段['.$v['title'].']缺失!');
            }
        }
        $num = 0;
        $row = 2;
        do {
            $next = true;
            $data = array();
            foreach ($fileds as $col => $v) {
                $value = $currentSheet->getCell($col . $row)->getFormattedValue();
                $value = trim($value);
                if($v['required'] && $value==''){
                    $next = false;
                    break;
                }
                $data[$v['key']] = $value;
            }

            if(isset($data['order_no']) && isset($data['logistics_remark'])){
                if(($match = parseZencartNo($data['order_no']))!==false) {//zencart站
                    $order_no_prefix = $match['orders_prefix'];
                    $zencart_order_no = $match['orders_id'];
                    //查找网站ID
                    $check_site = M('site')->where(array('order_no_prefix'=>$order_no_prefix,'status'=>1))->find();
                    if($check_site){
                        M('orders_remark')->where(array('orders_id'=>$zencart_order_no,'site_id'=>$check_site['site_id']))->save(array('logistics_remark'=>$data['logistics_remark']));       
                        $num++;
                    }
                }else{//商城站
                    M('orders_remark')->where(array('order_no'=>$data['order_no']))->save(array('logistics_remark'=>$data['logistics_remark']));
                    $num++;
                }
            }
            
            if($next) $row++;
        } while ($next);

        $this->success('成功导入'.$num.'条数据');
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

	/*
	 *导出订单物流
	 */
	private function _export_delivery($delivery, $fileName){
		layout(false);
		vendor('PHPExcel.PHPExcel');
		//导出的字段中不能乱改
		$field_array = array(
			'A' => array('title' => '订单号', 'width' => 25, 'key' => 'order_no'),
			'B' => array('title' => '发货日期', 'width' => 15, 'key' => 'delivery_date'),
			'C' => array('title' => '货运方式', 'width' => 10, 'key' => 'delivery_type'),
			'D' => array('title' => '转单号', 'width' => 30, 'key' => 'delivery_forward_no'),
			'E' => array('title' => '货运单号', 'width' => 30, 'key' => 'delivery_tracking_no'),
			'F' => array('title' => '重量', 'width' => 10, 'key' => 'delivery_weight'),
			'G' => array('title' => '产品数量', 'width' => 10, 'key' => 'delivery_quanlity')
		);
		$PHPExcel = new \PHPExcel();
		$currentSheet = $PHPExcel->getActiveSheet();
		$currentSheet->setTitle('订单物流表');
		$row = 1;
		foreach ($field_array as $k => $k_info) {
			$currentSheet->setCellValue($k . $row, $k_info['title']);
			$currentSheet->getColumnDimension($k)->setWidth($k_info['width']);
		}
		$row++;
		foreach($delivery as $index=>$v){
			if(empty($v['order_no'])) $v['order_no'] = $v['order_no_prefix'] . $v['orders_id'];
			foreach ($field_array as $k => $k_info) {
				$currentSheet->setCellValue($k . $row, $v[$k_info['key']]);
				$currentSheet->getStyle($k . $row)->getAlignment()->setWrapText(true);
			}
			$row++;
		}
		$objWriter = new \PHPExcel_Writer_Excel5($PHPExcel);
		$objWriter->save(DIR_FS_TEMP . $fileName . '.xls');
		$zip = new \ZipArchive;
		$zip_file = DIR_FS_TEMP . date('ymdhis') . '.zip';
		$zip->open($zip_file, \ZIPARCHIVE::CREATE);
		$zip->addFile(DIR_FS_TEMP . $fileName . '.xls', $fileName . '.xls');
		$zip->close();
		$link = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/'.str_replace(DIR_FS_ROOT, '', $zip_file);
		echo $link;
		exit();
	}
}