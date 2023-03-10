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
                    if(($match = parseZencartNo($v))!==false) {//?????????
                        $order_no_prefix = $match['orders_prefix'];
                        $zencart_order_no = $match['orders_id'];
                        $_where[] = array(
                                    '_logic' => 'AND',
                                    'o.orders_id' => $zencart_order_no,
                                    's.order_no_prefix' => $order_no_prefix,
                                );
                    } else {//??????
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
            if('????????????'==I('receiving_status')){
                $having = 'sum(quantity_received)>=products_quantity';
            }elseif('????????????'==I('receiving_status')){
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
        
        $option_order_status = array('?????????'=>'?????????','?????????'=>'?????????','????????????'=>'????????????','?????????'=>'?????????');
        $where['order_status_remark'] = array('IN', array_keys($option_order_status));
        
        $order_by = 'o_p_r.date_process desc,op.site_id desc,op.orders_id desc,op.orders_products_id asc';
        
        return array($where, $join, $page_data, $order_by, $having);
    }

    public function indexAction(){
        list($where, $join, $page_data, $order_by, $having) = $this->_init_where_join();
        //??????
        $sql = M('orders_products')->alias('op')->join($join)->where($where)->field('o.site_id,o.orders_id,products_quantity,quantity_received')->group('op.site_id,op.orders_products_id')->having($having)->select(false);
        $rs = M()->query('select count(distinct site_id,orders_id) as num from '.$sql.' as t');
        $orders_num = $rs[0]['num'];//????????????
        $this->assign('orders_num', $orders_num);
        
        $page = I('page', 1);
        $num  = 100;//?????????????????????
        $sql = M('orders_products')->alias('op')->join($join)->where($where)->field('op.site_id,op.orders_products_id,products_quantity,quantity_received')->group('op.site_id,op.orders_products_id')->having($having)->select(false);
        $rs = M()->query('select count(distinct site_id,orders_products_id) as num from '.$sql.' as t');
        $count = $rs[0]['num'];//??????????????????  
        
        $products = M('orders_products')->alias('op')->join($join)->where($where)->page($page, $num)->order($order_by)->field('distinct op.*,o.*,s.*,o_r.*,o_p_r.*,ops.*,quantity_received')->group('op.site_id,op.orders_products_id')->having($having)->select();

        foreach($products as $k=>$v){
            $v['products_image'] = $this->_getProductImage($v['site_id'], $v['orders_products_id']);
            $v['products_image'] = $this->_cache_images($v['products_image'], 100, 80);
            $v['products_attributes'] = M('orders_products_attributes')->where(array('site_id'=>$v['site_id'], 'orders_products_id'=>$v['orders_products_id']))->select();
            
            $v['received_status'] = M('orders_products_remark_received')->where(array('orders_products_remark_id'=>$v['orders_products_remark_id']))->order('date_received desc')->select();//????????????
            
            $products[$k] = $v;
        }
        
        $supplier = M('orders_products_supplier')->select();
        $option_supplier = array();
        foreach($supplier as $entry){
            $option_supplier[$entry['supplier_id']] = $entry['supplier_name'];
        }
        $option_supplier['-1'] = '??????????????????';
        
        $options = explode('|',C('system_area'));
        foreach($options as $value){
            $option_system_area[$value] = $value;
        }
        $this->assign('option_system_area', $option_system_area);
        $system_depart_array = M('PromotionDepartment')->order('department_id')->getField('department_id,department_name',true);
        foreach($system_depart_array as $department_id => $department_name){
            $option_system_depart[$department_id] = $department_name;
        }     
        $option_logistics_status = array('00'=>'?????????', '1'=>'?????????');
        $option_receiving_status = array('?????????'=>'?????????', '????????????'=>'????????????', '????????????'=>'????????????');
        $option_cost_counted     = array('00'=>'???', '1'=>'???');
        
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
    *????????????
    */
    private function _resize($file, $new_file, $new_width, $quality=80, $max_size=51200){
        if(!file_exists($file))
            return false;

        $file_size = filesize($file);
        if($file_size>$max_size){
            $path = dirname($new_file);
            if(!file_exists($path)){//????????????
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
            
            $this->assign('message_success', '????????????!');
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
        $option_supplier['-1'] = '??????????????????'; 
        
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
    * ?????????????????????
    */
    public function importAction(){
        if (UPLOAD_ERR_OK != $_FILES['file']['error']) {
            $this->error('??????????????????!?????????:' . $_FILES['file']['error']);
        }
        $ext = pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
        if($ext!='xls')
            $this->error('???????????????xls??????');
        $file = $_FILES['file']['tmp_name'];
        Vendor('PHPExcel.PHPExcel');
        $php_excel_reader = new \PHPExcel_Reader_Excel5();
        if (!$php_excel_reader->canRead($file)) {
            $this->error('???????????????????????????!');
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
        if(!isset($fileds['?????????']) || !isset($fileds['????????????'])) 
            $this->error('??????????????????????????????????????????????????????????????????!');
            
        $row = 2;
        do {
            $next = true;
            $genghuohao       = $currentSheet->getCell($fileds['?????????'] . $row)->getFormattedValue();
            $purchase_price   = $currentSheet->getCell($fileds['????????????'] . $row)->getFormattedValue();
            $cost_counted     = $currentSheet->getCell($fileds['???????????????(???/???)'] . $row)->getFormattedValue();
            $logistics_status = $currentSheet->getCell($fileds['????????????'] . $row)->getFormattedValue();
            $supplier_name      = $currentSheet->getCell($fileds['?????????'] . $row)->getFormattedValue();
            $quantity_received  = $currentSheet->getCell($fileds['????????????'] . $row)->getFormattedValue();
            $date_received      = $currentSheet->getCell($fileds['????????????'] . $row)->getFormattedValue();
            $date_received = date('Y-m-d',strtotime($date_received));

            if(empty($genghuohao))
                $next = false;
            else{    
                list($site_id, $orders_products_id) = explode('-', $genghuohao);
                $check_supplier = M('orders_products_supplier')->where(array('supplier_name'=>$supplier_name))->field('supplier_id')->find();
                if($check_supplier)
                    $supplier_id = $check_supplier['supplier_id'];
                else{//???????????????
                    M('orders_products_supplier')->add(array('supplier_name'=>$supplier_name, 'orders_products_categories_ids'=>'[{}]'));
                    $supplier_id = M('orders_products_supplier')->getLastInsID();
                }                
                $data = array(
                    'purchase_price'=>$purchase_price,
                    //'receiving_status'=>$receiving_status,
                    'cost_counted'=>($cost_counted=='???'?1:0),
                    //'quantity_received1'=>$quantity_received1,
                    //'quantity_received2'=>$quantity_received2,
                    //'quantity_received3'=>$quantity_received3,
                    'supplier_id'=>$supplier_id,
                );
                M('orders_products_remark')->where(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id))->save($data);
                $data = array(
                    'logistics_status'=>($logistics_status=='???'?1:0)
                );
                $check_orders_products_remark = M('orders_products_remark')->where(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id))->find();
                if($check_orders_products_remark){
                    M('orders_remark')->where(array('site_id'=>$site_id, 'orders_id'=>$check_orders_products_remark['orders_id']))->save($data);
                    if(!empty($date_received) && $date_received!='1970-01-01'){
                        $check_orders_products_remark_received = M('orders_products_remark_received')->where(array('orders_products_remark_id'=>$check_orders_products_remark['orders_products_remark_id'], 'date_received'=>$date_received))->find();
                        if($check_orders_products_remark_received){
                            M('orders_products_remark_received')->where(array('orders_products_remark_id'=>$check_orders_products_remark['orders_products_remark_id'], 'date_received'=>$date_received))->save(array('quantity_received'=>$quantity_received, 'cost_counted'=>($cost_counted=='???'?1:0)));
                        }else{
                            M('orders_products_remark_received')->add(array('orders_products_remark_id'=>$check_orders_products_remark['orders_products_remark_id'], 'date_received'=>$date_received,'quantity_received'=>$quantity_received, 'cost_counted'=>($cost_counted=='???'?1:0)));
                        }
                    }                    
                }
            }
            $row++;
        } while ($next);


        $this->success('?????????'.($row-3).'???????????????!');
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
                );//??????????????????
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
        $this->success('????????????'.$success_num.'?????????!', U('Order/Purchase/index'));
    }
    
    /*
    * ????????????????????????
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
    *????????????
    */
    public function archivingAction(){

        if(I('archiving_type')=='logistics_status'){
            $orders = I('orders');
            if(empty($orders)){
                $this->error('????????????????????????!');
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
                $this->error('????????????????????????!');
            }  
            if(I('archiving_value')=='????????????'){
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
            }elseif(I('archiving_value')=='?????????'){
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
                $this->error('??????????????????????????????!');
            }  
            foreach($received as $entry){
                list($orders_products_remark_id, $date) = explode('|', $entry);
                M('orders_products_remark_received')->where(array('orders_products_remark_id'=>$orders_products_remark_id, 'date_received'=>$date))->save(array('cost_counted'=>I('archiving_value')));
            }
        }

        $this->success('????????????!');
    }
    
    /*
    *??????excel??????
    */
    private function _export_excel($products, $fileName){
        layout(false);
        vendor('PHPExcel.PHPExcel');
        //??????????????????????????????
        $field_array = array(
                    'A' => array('title' => '?????????',   'width' => 20,  'key' => 'orders_products_number'),
                    'B' => array('title' => '????????????', 'width' => 15,  'key' => 'image'),
                    'C' => array('title' => '??????',     'width' => 10,  'key' => 'products_quantity'),
                    'D' => array('title' => '????????????', 'width' => 80, 'key' => 'products_detail'),
                    'E' => array('title' => 'SKU',      'width' => 15, 'key' => 'products_model'),
                    'F' => array('title' => '??????',     'width' => 10, 'key' => 'size'),
                    'G' => array('title' => '????????????',  'width' => 10, 'key' => 'final_price'),
                    'H' => array('title' => '?????????',   'width' => 20,  'key' => 'order_number'),
                    'I' => array('title' => '????????????', 'width' => 10,  'key' => 'date_purchased'),
                    'J' => array('title' => '?????????',   'width' => 10,  'key' => 'supplier_name'),
                    'K' => array('title' => '????????????', 'width' => 10,  'key' => 'date_process'),
                    'L' => array('title' => '????????????', 'width' => 10,  'key' => 'quantity_process'),
                    'M' => array('title' => '????????????', 'width' => 10,  'key' => 'purchase_price'),
                    
                    'N' => array('title' => "????????????", 'width' => 10,  'key' => 'quantity_received'),
                    'O' => array('title' => "????????????", 'width' => 15,  'key' => 'date_received'),
                    'P' => array('title' => "???????????????(???/???)", 'width' => 20,  'key' => 'cost_counted'),
                    'Q' => array('title' => '????????????', 'width' => 10,  'key' => 'logistics_status'),//????????????
                    'R' => array('title' => '????????????', 'width' => 10,  'key' => 'department_name'),//????????????
                );
        $PHPExcel = new \PHPExcel();
        $currentSheet = $PHPExcel->getActiveSheet();
        $currentSheet->setTitle('?????????');
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
        $order_remark     = array();//????????????
        $finance_remark   = array();//????????????
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
                    $img->setPath($src);//??????????????????
                    $img->setWidth(100);//??????????????????
                    $img->setHeight(100);//??????????????????
                    $img->setOffsetX(1);//??????????????????????????????X?????????
                    $img->setOffsetY(1);//??????????????????????????????Y?????????
                    $img->setRotation(1);//??????????????????
                    $img->getShadow()->setVisible(true);
                    $img->getShadow()->setDirection(50);
                    $img->setCoordinates($k . $row);//??????????????????????????????
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
                                $objPayable = $objRichText->createTextRun( substr($product['products_detail'], $pos, $len));//???????????????
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
                        
                        $objPayable = $objRichText->createTextRun("??????:".$product['remark_product']);
                        $objPayable->getFont()->setBold(true);
                        $objPayable->getFont()->setColor( new \PHPExcel_Style_Color( \PHPExcel_Style_Color::COLOR_RED ) );   
                        $objPayable->getFont()->setSize(12);
                        $currentSheet->getCell($k . $row)->setValue($objRichText);
                    }else{
                        $detail = $product[$k_info['key']].(empty($product['remark_product'])?"":"\n??????:".$product['remark_product']);
                        $currentSheet->setCellValue($k . $row, $detail);
                        if(!empty($product['remark_product']))
                            $currentSheet->getStyle($k . $row)->applyFromArray(array('font'=>array('color'=>array('rgb' => 'FF0000'))));
                    }
                }elseif($k_info['key']=='cost_counted'||$k_info['key']=='logistics_status'){
                    $value = ($product[$k_info['key']]==1?'???':'???');
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
            $currentSheet->setTitle('????????????');
            $field_array = array(
                'A' => array('title' => '?????????',   'width' => 20,  'key' => 'order_number'),
                'B' => array('title' => '????????????', 'width' => 10,  'key' => 'date_purchased'),
                'C' => array('title' => '????????????', 'width' => 10,  'key' => 'order_remark'),
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
            $currentSheet->setTitle('????????????');
            $field_array = array(
                'A' => array('title' => '?????????',   'width' => 20,  'key' => 'order_number'),
                'B' => array('title' => '????????????', 'width' => 10,  'key' => 'date_purchased'),
                'C' => array('title' => '????????????', 'width' => 10,  'key' => 'finance_remark'),
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
            'A' => array('title' => '????????????', 'key' => 'logistics_remark'),
            'B' => array('title' => '?????????', 'key' => 'order_number'),
            'C' => array('title' => 'SKU', 'key' => ''),
            'D' => array('title' => '??????', 'key' => 'qty'),
            'E' => array('title' => '??????', 'key' => ''),
            'F' => array('title' => '??????', 'key' => 'delivery_name'),
            'G' => array('title' => '??????', 'key' => 'delivery_street_address'),
            'H' => array('title' => '??????', 'key' => 'delivery_city'),
            'I' => array('title' => '???', 'key' => 'delivery_state'),
            'J' => array('title' => '??????', 'key' => 'delivery_postcode'),
            'K' => array('title' => '??????', 'key' => 'delivery_country'),
            'L' => array('title' => '??????', 'key' => 'customers_telephone'),
            'M' => array('title' => '????????????', 'key' => 'payment_method_zh'),
            'N' => array('title' => '????????????', 'key' => ''),
            'O' => array('title' => '????????????', 'key' => ''),
            'P' => array('title' => '????????????', 'key' => ''),
            'Q' => array('title' => '????????????', 'key' => 'shipping_method_zh'),
            'R' => array('title' => '??????', 'key' => 'order_remark')
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
                            'westernunion' => '??????',
                            'moneygram' => '?????????',
                            'moneytransfers' => 'TW',
                            'mycheckout' => '?????????',
                            'tpo' => '?????????',
                            'mycheckout2f3d' => '?????????',
                            'mycheckout3f' => '?????????',
                            'rxhpay_inline' => '?????????',
                            'rxhpay' => '?????????',
                            'zdcheckout3f' => '??????',
                            'zdcheckout2f3d' => '??????',
                            'cp_pay' => 'MoneyBrace',
                            'paycloak' => '??????',
                            'security_alipay' => '?????????',
                            'security_pingpong' => 'pingpong',
                            'pingpong' => 'pingpong',
                            'pingpong2f' => 'pingpong',
                        ));
            switch ($order_info['shipping_module_code']) {
                case 'faster':
                case 'zones':
                case '????????????':
                    $order_info['shipping_method_zh'] ='?????? faster';
                    break;
                default:
                    $order_info['shipping_method_zh'] ='?????? standard';
            }

            //?????????????????????????????????????????????
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
        $fileName = "??????????????????" . date('YmdHis', time());
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
    *??????????????????
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
            $num  = 100;//?????????????????????
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
                
        $fileName = "??????????????????_" . date('YmdHis', time());
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
			$fileName = "???????????????_" . date('YmdHis', time());
			$this->_export_delivery($delivery, $fileName);
		}
    }
    
    public function importLogisticsRemarkAction(){
        if (UPLOAD_ERR_OK != $_FILES['file']['error']) {
            $this->error('??????????????????!?????????:' . $_FILES['file']['error']);
        }
        $ext = pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
        if($ext!='xls')
            $this->error('???????????????xls??????');
        $file = $_FILES['file']['tmp_name'];
        Vendor('PHPExcel.PHPExcel');
        $php_excel_reader = new \PHPExcel_Reader_Excel5();
        if (!$php_excel_reader->canRead($file)) {
            $this->error('???????????????????????????!');
        }
        $PHPExcel = $php_excel_reader->load($file);
        $currentSheet = $PHPExcel->getSheet(0);

        $fileds = array(
            'A' => array('title' => '????????????',   'key' => 'logistics_remark',              'required' => false),
            'B' => array('title' => '?????????',     'key' => 'order_no',                      'required' => true),
        );
        //?????????????????? start
        $ok = true;
        foreach ($fileds as $col => $v) {
            $title = $currentSheet->getCell($col . '1')->getFormattedValue();
            $title = trim($title);
            if (strpos($title, $v['title']) === false) {
                $this->error('????????????????????????['.$v['title'].']??????!');
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
                if(($match = parseZencartNo($data['order_no']))!==false) {//zencart???
                    $order_no_prefix = $match['orders_prefix'];
                    $zencart_order_no = $match['orders_id'];
                    //????????????ID
                    $check_site = M('site')->where(array('order_no_prefix'=>$order_no_prefix,'status'=>1))->find();
                    if($check_site){
                        M('orders_remark')->where(array('orders_id'=>$zencart_order_no,'site_id'=>$check_site['site_id']))->save(array('logistics_remark'=>$data['logistics_remark']));       
                        $num++;
                    }
                }else{//?????????
                    M('orders_remark')->where(array('order_no'=>$data['order_no']))->save(array('logistics_remark'=>$data['logistics_remark']));
                    $num++;
                }
            }
            
            if($next) $row++;
        } while ($next);

        $this->success('????????????'.$num.'?????????');
    }
    
    private function _getProductImage($site_id, $orders_producst_id) {
        $row = D('orders_products')->where(array('site_id'=>$site_id,'orders_products_id'=>$orders_producst_id))->field('products_image')->find();
        if(empty($row['products_image'])) {
            return DIR_WS_UPLOADS . 'no-image.gif';
        } else {
            if(file_exists(DIR_FS_ROOT.ltrim($row['products_image'], '/'))){//ckfind??????
                return ltrim($row['products_image'], '/');
            }
            $small_image = rtrim(dirname($row['products_image']), '/').'/small/'.basename($row['products_image']);
            if(file_exists(DIR_FS_PRODUCT_IMAGE.$small_image)) {
                //??????
                return str_replace(DIR_FS_ROOT, '', DIR_FS_PRODUCT_IMAGE).$small_image;
            } elseif(file_exists(DIR_FS_PRODUCT_IMAGE.$row['products_image'])) {
                //?????????
                return str_replace(DIR_FS_ROOT, '', DIR_FS_PRODUCT_IMAGE) . $row['products_image'];
            } elseif(file_exists(DIR_FS_PRODUCT_IMAGE.'saas/'.$row['products_image'])) {
                //?????????
                return str_replace(DIR_FS_ROOT, '', DIR_FS_PRODUCT_IMAGE.'saas/'.$row['products_image']);
            } else {
                $link = M('Site')->where(array('site_id'=>$site_id))->getField('img_url') . $row['products_image'];
                $path = parse_url($link, PHP_URL_PATH);
                $cache_images = DIR_FS_PRODUCT_IMAGE . 'cache' . $path;
                if(file_exists($cache_images)) {
                    return str_replace(DIR_FS_ROOT, '', $cache_images);
                } else {
                    $state = @file_get_contents($link,0,null,0,1);//?????????????????????????????????
                    if($state) {
                        $cache_dir = dirname($cache_images);
                        if(!file_exists($cache_dir)) makeDir($cache_dir);
                        ob_start();//????????????
                        readfile($link);//??????????????????
                        $img = ob_get_contents();//?????????????????????
                        ob_end_clean();//?????????????????????
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
	 *??????????????????
	 */
	private function _export_delivery($delivery, $fileName){
		layout(false);
		vendor('PHPExcel.PHPExcel');
		//??????????????????????????????
		$field_array = array(
			'A' => array('title' => '?????????', 'width' => 25, 'key' => 'order_no'),
			'B' => array('title' => '????????????', 'width' => 15, 'key' => 'delivery_date'),
			'C' => array('title' => '????????????', 'width' => 10, 'key' => 'delivery_type'),
			'D' => array('title' => '?????????', 'width' => 30, 'key' => 'delivery_forward_no'),
			'E' => array('title' => '????????????', 'width' => 30, 'key' => 'delivery_tracking_no'),
			'F' => array('title' => '??????', 'width' => 10, 'key' => 'delivery_weight'),
			'G' => array('title' => '????????????', 'width' => 10, 'key' => 'delivery_quanlity')
		);
		$PHPExcel = new \PHPExcel();
		$currentSheet = $PHPExcel->getActiveSheet();
		$currentSheet->setTitle('???????????????');
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