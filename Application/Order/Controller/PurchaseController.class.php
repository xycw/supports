<?php

namespace Order\Controller;
use Common\Controller\CommonController;
use Order\Model\OrderModel;

class PurchaseController extends CommonController {

    public function indexAction(){
        $join = array();
        $join[] = 'LEFT JOIN __SITE__ s ON s.site_id=op.site_id';
        $join[] = '__ORDERS__ o ON o.site_id=op.site_id AND o.orders_id=op.orders_id';
        $join[] = '__ORDERS_REMARK__ o_r ON o_r.site_id=op.site_id AND o_r.orders_id=op.orders_id';
        $join[] = 'LEFT JOIN __ORDERS_PRODUCTS_REMARK__ o_p_r ON o_p_r.site_id=op.site_id AND o_p_r.orders_products_id=op.orders_products_id';
        $join[] = 'LEFT JOIN __ORDERS_PRODUCTS_CATEGORIES__ c ON c.categories_id=o_p_r.categories_id';
        $join[] = 'LEFT JOIN __ORDERS_PRODUCTS_SUPPLIER__ ops ON ops.supplier_id=o_p_r.supplier_id';
        
        $where = array();
        $page_data = array();
        if (I('user_id') != '') {
            $join[] = 'JOIN __USERS_TO_SITE__ u2s ON u2s.site_id=s.site_id';
            $where['u2s.user_id'] = I('user_id');
            $page_data['user_id'] = I('user_id');
            $this->assign('user_id_selected', I('user_id'));
        }      
        if (I('is_print') != '') {
            $where['o_p_r.is_print'] = I('is_print');
            $page_data['is_print'] = I('is_print');
            $this->assign('is_print_selected', I('is_print'));
        }             
        if(I('categories_id')){
            if(I('categories_id')=='-1') 
                $where['c.categories_id'] = array('exp', 'is null');
            else
                $where['c.categories_id'] = I('categories_id');
            $page_data['categories_id'] = I('categories_id');
            $this->assign('option_categories_selected', I('categories_id'));
        }
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
        if(I('last_motify_date_start') && I('last_motify_date_end')){
            $last_motify_date_start = I('last_motify_date_start');
            $last_motify_date_end   = I('last_motify_date_end');
        }else{
            $last_motify_date_end     = date('Y-m-d');//默认最近2天
            $last_motify_date_start   = date('Y-m-d', strtotime($last_motify_date_end)-24*3600);
        }
        if(I('sku')){
            if(strpos(I('sku'), "\n"))
                $sku_array = explode("\n", I('sku'));
            else
                $sku_array = explode(",", I('sku'));            
            foreach($sku_array as $k=>$v){
                $v = trim($v);
                if(empty($v)){
                    unset($sku_array[$k]);
                }else
                    $sku_array[$k] = $v;
            }
            $this->assign('sku', I('sku'));
            $page_data['sku'] = implode(',', sku);
            $where['op.products_model'] = array('in', $sku_array);
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
            $where['o.date_purchased'] = array('between', array(I('purchase_date_start').' 0:0:0', I('purchase_date_end').' 23:59:59'));
        }
        $where['o_r.last_modify'] = array('between', array($last_motify_date_start.' 0:0:0', $last_motify_date_end.' 23:59:59'));
        $page_data['last_motify_date_start'] = $last_motify_date_start;
        $page_data['last_motify_date_end']   = $last_motify_date_end;
        $this->assign('last_motify_date_start', $last_motify_date_start);
        $this->assign('last_motify_date_end', $last_motify_date_end);
        
        $option_order_status = array('待订货'=>'待订货','已订货'=>'已订货','部分发货'=>'部分发货');
        if(I('order_status') && in_array(I('order_status'), $option_order_status)){
            $option_order_status_selected = I('order_status');
            $where['order_status_remark'] = $option_order_status_selected;
            $page_data['order_status_remark'] = $option_order_status_selected;
            $this->assign('option_order_status_selected', $option_order_status_selected);
        }else{
            $where['order_status_remark'] = array('IN', array_keys($option_order_status));
        }
        
        $option_item_status = array('待订货(待处理)'=>'待订货(待处理)','订单定货中'=>'订单定货中','已处理'=>'已处理');
        if(I('item_status') && in_array(I('item_status'), $option_item_status)){
            $option_item_status_selected = I('item_status');
            $where['item_status'] = $option_item_status_selected;
            $page_data['item_status'] = $option_item_status_selected;
            $this->assign('option_item_status_selected', $option_item_status_selected);
        }
        
        if(I('archiving_type') && I('archiving_value', false)!==false){//订单标记
            $products = M('orders_products')->alias('op')->join($join)->where($where)->field('op.*')->order('o_p_r.is_print asc,o.date_purchased,op.site_id desc,op.orders_id desc,op.orders_products_id asc')->select();     
            $this->_archiving($products, I('archiving_type'), I('archiving_value'));
            $this->success('归档完毕!');
        } 
        
        //统计
        $sql = M('orders_products')->alias('op')->join($join)->where($where)->field('distinct o.site_id,o.orders_id')->select(false);
        $rs = M()->query('select count(*) as num from '.$sql.' as t');
        $orders_num = $rs[0]['num'];//订单总数
        $this->assign('orders_num', $orders_num);
        
        $page = I('page', 1);
        $num  = 100;//第页显示记录数
        $count = M('orders_products')->alias('op')->join($join)->where($where)->field('op.*,o.*,o_r.*,s.*,c.categories_name')->count();//产品项目总数
        $products = M('orders_products')->alias('op')->join($join)->where($where)->field('op.*,o.*,o_r.*,s.*,c.categories_name,ops.supplier_name,o_p_r.categories_id,o_p_r.orders_products_remark_id,
        o_p_r.supplier_id,o_p_r.remark as products_remark,o_p_r.remove,o_p_r.is_print,o_p_r.item_status,o_p_r.date_process,o_p_r.quantity_process,o_p_r.detail_process,o_p_r.is_customized,purchase_price')->page($page, $num)->order('o_p_r.is_print asc,o.date_purchased,op.site_id desc,op.orders_id desc,op.orders_products_id asc')->select();
        
        foreach($products as $k=>$v){
            $v['products_image'] = $this->_getProductImage($v['site_id'], $v['orders_products_id']);
            
            $new_file = DIR_FS_TEMP.'cache/'.preg_replace('~(.+)(\.\w+)$~i', '$1_w100.jpg', $v['products_image']);
            if(!file_exists($new_file)){
                if($this->_resize(DIR_FS_ROOT.$v['products_image'], $new_file, 100)){
                    $v['products_image'] = str_replace(DIR_FS_ROOT, '', $new_file);
                }
            }else{
                $v['products_image'] = str_replace(DIR_FS_ROOT, '', $new_file);
            }
            
            $v['products_attributes'] = M('orders_products_attributes')->where(array('site_id'=>$v['site_id'], 'orders_products_id'=>$v['orders_products_id']))->select();
            $products[$k] = $v;
        }
        
        $supplier = M('orders_products_supplier')->select();
        $option_supplier = array();
        foreach($supplier as $entry){
            $option_supplier[$entry['supplier_id']] = $entry['supplier_name'];
        }
        $option_supplier['-1'] = '未确定供应商';
        
        $categories = M('orders_products_categories')->select();
        $option_categories = array();
        foreach($categories as $entry){
            $option_categories[$entry['categories_id']] = $entry['categories_name'];
        }
        $option_categories[-1] = '未归类';
        $users = D('users_to_site')->alias('u2s')->join('__USERS__ u ON u.user_id=u2s.user_id')->field(array('distinct u.`user_id`', 'u.chinese_name'))->select();
        $options_users = array();
        foreach($users as $entry) {
            $options_users[$entry['user_id']] = $entry['chinese_name'];
        }
        $this->assign('option_is_print', array('0'=>'未打印', '1'=>'已打印'));
        $this->assign('users', $options_users);
        
        $this->assign('option_order_status', $option_order_status);
        $this->assign('option_supplier', $option_supplier);
        $this->assign('option_categories', $option_categories);
        $this->assign('option_item_status', $option_item_status);
        $this->assign('products', $products);
        $this->assign('page', $page);
        $this->assign('count', $count);
        $this->assign('num', $num);
        $this->assign('page_data', $page_data);        
        $this->display();
    }
    
    private function _archiving($products, $archiving_type, $archiving_value){
        $data = array();
        if($archiving_type=='category'){
            $data['categories_id'] = $archiving_value;
        }elseif($archiving_type=='print'){
            $data['is_print'] = $archiving_value;
        }elseif($archiving_type=='item_status'){
            $data['item_status'] = $archiving_value;
        }else{
            $data['supplier_id'] = $archiving_value;
        }

        foreach($products as $entry){
            $site_id = $entry['site_id'];
            $orders_products_id = $entry['orders_products_id'];

            $where = array(
                'site_id'=>$site_id,    
                'orders_products_id'=>$orders_products_id,
            );
            $data['site_id'] = $site_id;
            $data['orders_products_id'] = $orders_products_id;
            $check_remark = M('orders_products_remark')->where($where)->find();

            if($check_remark){
                $data['orders_id'] = $check_remark['orders_id'];
                M('orders_products_remark')->where($where)->save($data);
            }else{
                $row = M('orders_products')->where($where)->find();
                $data['orders_id'] = $row['orders_id'];
                M('orders_products_remark')->add($data);
            }
        }
    }
    
    public function autoArchivingAction(){
        $join = array();
        $join[] = 'LEFT JOIN __SITE__ s ON s.site_id=op.site_id';
        $join[] = '__ORDERS__ o ON o.site_id=op.site_id AND o.orders_id=op.orders_id';
        $join[] = '__ORDERS_REMARK__ o_r ON o_r.site_id=op.site_id AND o_r.orders_id=op.orders_id';
        $join[] = 'LEFT JOIN __ORDERS_PRODUCTS_REMARK__ o_p_r ON o_p_r.site_id=op.site_id AND o_p_r.orders_products_id=op.orders_products_id';
        $join[] = 'LEFT JOIN __ORDERS_PRODUCTS_CATEGORIES__ c ON c.categories_id=o_p_r.categories_id';       
        $join[] = 'LEFT JOIN __ORDERS_PRODUCTS_SUPPLIER__ ops ON ops.supplier_id=o_p_r.supplier_id';
        $where = array();
        if (I('user_id') != '') {
            $join[] = 'JOIN __USERS_TO_SITE__ u2s ON u2s.site_id=s.site_id';
            $where['u2s.user_id'] = I('user_id');
        }       
        if(I('categories_id')){
            if(I('categories_id')=='-1') 
                $where['c.categories_id'] = array('exp', 'is null');
            else
                $where['c.categories_id'] = I('categories_id');            
            $categories = M('orders_products_categories')->where(array('categories_id'=>I('categories_id')))->find();
        }
        
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
        }  
        
        if(I('last_motify_date_start') && I('last_motify_date_end')){
            $last_motify_date_start = I('last_motify_date_start');
            $last_motify_date_end   = I('last_motify_date_end');
        }else{
            $last_motify_date_end     = date('Y-m-d');//默认最近2天
            $last_motify_date_start   = date('Y-m-d', strtotime($last_motify_date_end)-24*3600);
        }
        if(I('sku')){
            if(strpos(I('sku'), "\n"))
                $sku_array = explode("\n", I('sku'));
            else
                $sku_array = explode(",", I('sku'));            
            foreach($sku_array as $k=>$v){
                $v = trim($v);
                if(empty($v)){
                    unset($sku_array[$k]);
                }else
                    $sku_array[$k] = $v;
            }
            $where['op.products_model'] = array('in', $sku_array);
        }
        $option_order_status = array('待订货'=>'待订货','已订货'=>'已订货','部分发货'=>'部分发货');
        if(I('order_status') && in_array(I('order_status'), $option_order_status)){
            $option_order_status_selected = I('order_status');
            $where['order_status_remark'] = $option_order_status_selected;
        }else{
            $where['order_status_remark'] = array('IN', array_keys($option_order_status));
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
        }
        if(I('purchase_date_start') && I('purchase_date_end')){
            $where['o.date_purchased'] = array('between', array(I('purchase_date_start').' 0:0:0', I('purchase_date_end').' 23:59:59'));
        }        
        $where['o_r.last_modify'] = array('between', array($last_motify_date_start.' 0:0:0', $last_motify_date_end.' 23:59:59'));
        $option_order_status = array('待订货'=>'待订货','已订货'=>'已订货','部分发货'=>'部分发货');
        if(I('order_status') && in_array(I('order_status'), $option_order_status)){
            $option_order_status_selected = I('order_status');
            $where['order_status_remark'] = $option_order_status_selected;
        }

        $products = M('orders_products')->alias('op')->join($join)->where($where)->field('op.*')->order('date_purchased desc,op.site_id desc,op.orders_id desc,op.orders_products_id asc')->select();
        foreach($products as $product){
            $check_orders_procuts_remark = M('orders_products_remark')->where(array('site_id'=>$product['site_id'], 'orders_products_id'=>$product['orders_products_id']))->find();//检测备注记录是否存在
            if($check_orders_procuts_remark)
                $check_model = M('orders_products')->alias('op')->join(array('__ORDERS_PRODUCTS_REMARK__ r ON r.site_id=op.site_id AND r.orders_products_id=op.orders_products_id'))->field('r.categories_id')->where(array('products_model'=>$product['sku'], 'orders_products_remark_id'=>array('neq', $check_orders_procuts_remark['orders_products_remark_id'])))->order('r.orders_products_remark_id desc')->find();
            else
                $check_model = M('orders_products')->alias('op')->join(array('__ORDERS_PRODUCTS_REMARK__ r ON r.site_id=op.site_id AND r.orders_products_id=op.orders_products_id'))->field('r.categories_id')->where(array('products_model'=>$product['sku']))->order('r.orders_products_remark_id desc')->find();
                
            if($check_model){//先从历史订单中查找分类
                $orders_products_categories_id = $check_model['categories_id'];
            }else{
                $check_model = M('products')->where(array('product_model'=>$product['products_model']))->find();
                if($check_model){//从产品库中查找
                    $orders_products_categories_id = $check_model['orders_products_categories_id'];
                }else{
                    $orders_products_categories_id = 0;
                }
            }

            if($check_orders_procuts_remark){
                M('orders_products_remark')->where(array('orders_products_remark_id'=>$check_orders_procuts_remark['orders_products_remark_id']))->save(array('categories_id'=>$orders_products_categories_id));
            }else{
                M('orders_products_remark')->add(array('site_id'=>$product['site_id'], 'orders_products_id'=>$product['orders_products_id'], 'orders_id'=>$product['orders_id'], 'categories_id'=>$orders_products_categories_id));
            }            
        }
        $this->success('订单归类完成!');
    }
    
    /*
    *压缩图片
    */
    private function _resize($file, $new_file, $new_width, $max_size=51200){
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
            $allowedTypes = array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'webp');
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
                case 'webp' :
                    $im_src = imagecreatefromwebp($file);
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
            imagejpeg($im_dst, $new_file, 80);
        	imagedestroy($im_dst);
        	imagedestroy($im_src);
        	return $new_file;
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

        $fileds = array(
            'A' => array('title' => '跟货号',   'key' => 'process_id',      'required' => true),
            'B' => array('title' => '产品类别', 'key' => 'categories_id',   'required' => false),
            'I' => array('title' => '供应商',   'key' => 'supplier_id',     'required' => false),
            'J' => array('title' => '订货日期', 'key' => 'date_process',    'required' => true),
            'K' => array('title' => '是否定制', 'key' => 'is_customized',    'required' => false),
            'L' => array('title' => '订货数量', 'key' => 'quantity_process',  'required' => true),
            'M' => array('title' => '订货单价', 'key' => 'purchase_price',    'required' => false),
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
        $row = 2;
        do {
            $next = true;
            foreach ($fileds as $col => $v) {
                $value = $currentSheet->getCell($col . $row)->getFormattedValue();
                $value = trim($value);
                if($v['required'] && $value==''){
                    $next = false;
                    break;
                }
                
                if($v['key']=='process_id'){
                    list($site_id, $orders_products_id) = explode('-', $value);
                    $order = M('orders_products')->alias('op')
                        ->join(array('__ORDERS_PRODUCTS_REMARK__ o_p_r ON o_p_r.site_id=op.site_id AND o_p_r.orders_products_id=op.orders_products_id', '__ORDERS__ o ON o.orders_id=op.orders_id AND o.site_id=op.site_id', '__ORDERS_REMARK__ o_r ON o_r.orders_id=o.orders_id AND o_r.site_id=o.site_id'))
                        ->where(array('op.site_id'=>$site_id, 'op.orders_products_id'=>$orders_products_id))
                        ->field('op.*,o_p_r.*,o.*,o_r.*')
                        ->find();
                    if($order){
                        $order['products_image'] = $this->_getProductImage($site_id, $orders_products_id);
                        $order['products_image'] = $this->_cache_images($order['products_image'], 100);
                        $order['products_attributes'] = M('orders_products_attributes')->where(array('site_id'=>$site_id, 'orders_products_id'=>$orders_products_id))->select();                        
                        $data[$row]['order'] = $order;
                    }else
                        $data[$row]['order'] = false;
                }elseif($v['key']=='supplier_id'){
                    if(empty($value)){
                        $value = 0;
                    }else{
                        $check_supplier = M('orders_products_supplier')->where(array('supplier_name'=>$value))->field('supplier_id')->find();
                        if($check_supplier)
                            $value = $check_supplier['supplier_id'];
                        else{//新增供应商
                            M('orders_products_supplier')->add(array('supplier_name'=>$value, 'orders_products_categories_ids'=>'[{}]'));
                            $value = M('orders_products_supplier')->getLastInsID();
                        }
                    }
                }elseif($v['key']=='categories_id'){
                    if(empty($value)){
                        $value = 0;
                    }else{
                        $check_categories = M('orders_products_categories')->where(array('categories_name'=>$value))->field('categories_id')->find();
                        if($check_categories)
                            $value = $check_categories['categories_id'];
                        else{
                            M('orders_products_categories')->add(array('categories_name'=>$value));
                            $value = M('orders_products_supplier')->getLastInsID();
                        }
                    }
                }elseif($v['key']=='is_customized'){
                    if(empty($value)){
                        $value = 0;
                    }else{
                        $value = (int)$value;
                    }
                }
                
                $data[$row][$v['key']] = $value;
            }
            $data[$row]['line'] = $row;
            if ($next) $row++;
        } while ($next);

        if (sizeof($data[$row]) != sizeof($fileds)) {//去掉最后行不完整的记录
            unset($data[$row]);
        }
        
        $supplier = M('orders_products_supplier')->order('supplier_id asc')->select();
        $this->assign('supplier', $supplier);
        $categories = M('orders_products_categories')->order('categories_id asc')->select();
        $this->assign('categories', $categories);        
        $this->assign('data', $data);
        $this->display();
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
    private function _cache_images($src, $width){
        $new_file = DIR_FS_TEMP.'cache/'.preg_replace('~(.+)(\.\w+)$~i', '$1_w'.$width.'.jpg', $src);
        if(!file_exists($new_file)){
            if($this->_resize(DIR_FS_ROOT.$src, $new_file, 100)){
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
    *产品归档,分类，供应商,标签等
    */
    public function archivingAction(){
        $orders_products = I('orders_products');
        if(empty($orders_products)){
            $this->error('请勾选相应的订单产品项目!');
        }
        if(I('archiving_type')=='category'){
            $data['categories_id'] = I('archiving_value');
        }elseif(I('archiving_type')=='print'){
            $data['is_print'] = I('archiving_value');
        }elseif(I('archiving_type')=='item_status'){
            $data['item_status'] = I('archiving_value');
        }else{
            $data['supplier_id'] = I('archiving_value');
        }

        foreach($orders_products as $entry){
            list($site_id, $orders_products_id) = explode('-', $entry);
            $where = array(
                'site_id'=>$site_id,    
                'orders_products_id'=>$orders_products_id,
            );
            $data['site_id'] = $site_id;
            $data['orders_products_id'] = $orders_products_id;
            $check_remark = M('orders_products_remark')->where($where)->find();

            if($check_remark){
                $data['orders_id'] = $check_remark['orders_id'];
                M('orders_products_remark')->where($where)->save($data);
            }else{
                $row = M('orders_products')->where($where)->find();
                $data['orders_id'] = $row['orders_id'];
                M('orders_products_remark')->add($data);
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
        $field_array = array(
                    'A' => array('title' => '跟货号',   'width' => 20,  'key' => 'orders_products_number'),
                    'B' => array('title' => '产品类别', 'width' => 30,  'key' => 'categories_name'),
                    'C' => array('title' => '产品图片', 'width' => 15,  'key' => 'image'),
                    'D' => array('title' => '数量',     'width' => 10,  'key' => 'products_quantity'),
                    'E' => array('title' => '产品信息', 'width' => 100, 'key' => 'products_detail'),
                    'F' => array('title' => '尺码',     'width' => 20,  'key' => 'products_size'),
                    'G' => array('title' => '订单号',   'width' => 20,  'key' => 'order_number'),
                    'H' => array('title' => '订单日期', 'width' => 10,  'key' => 'date_purchased'),
                    'I' => array('title' => '供应商',   'width' => 10,  'key' => 'supplier_name'),
                    'J' => array('title' => '订货日期', 'width' => 10,  'key' => 'date_process'),
                    'K' => array('title' => '是否定制', 'width' => 10,  'key' => 'is_customized'),
                    'L' => array('title' => '订货数量', 'width' => 10,  'key' => 'quantity_process'),
                    'M' => array('title' => '订货单价', 'width' => 10,  'key' => 'purchase_price'),
                    'N' => array('title' => 'SKU',      'width' => 20,  'key' => 'products_model'),
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
                }elseif('is_customized'==$k_info['key']){
                    $value = ($product[$k_info['key']]?'是':'否');
                    $currentSheet->setCellValue($k . $row, $value);
                }else{
                    $currentSheet->setCellValue($k . $row, $product[$k_info['key']]);
                }

                $currentSheet->getStyle($k . $row)->getAlignment()->setWrapText(true);
                
            }
            if($product['remove']==1){
                $currentSheet->getStyle('A'.$row.':N' . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('e1827f');
                $currentSheet->getStyle('A'.$row.':N' . $row)->getFont()->setStrikethrough (true);
            }            
            $currentSheet->getRowDimension($row)->setRowHeight(100);
            $row++;
        }
        $currentSheet->getStyle('A1:N' . $row)->applyFromArray(
                        array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                ),
                            )
                        )
                );
        
        $objWriter = new \PHPExcel_Writer_Excel5($PHPExcel);
        $objWriter->save(DIR_FS_TEMP . $fileName .'.xls');

        $zip = new \ZipArchive;
        $zip_file = DIR_FS_TEMP . date('ymdhis') .'.zip';
        $zip->open($zip_file, \ZIPARCHIVE::CREATE);
        $zip->addFile(DIR_FS_TEMP . $fileName .'.xls', $fileName .'.xls'); 
        $zip->close();
        $link = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/'.str_replace(DIR_FS_ROOT, '', $zip_file);
        redirect($link, 10,'系统将在10秒后跳转到.你也可以直接点击些链接   <a href="'.$link.'">点我下载</a>（此链接3小时内有效）');
    }
    
    private function _export_word($products, $fileName){
        Vendor('PhpOffice.PhpOffice_Autoloader');
        $PHPWord = new \PhpOffice\PhpWord\PhpWord();        
        $sectionStyle = array(
                    'orientation' => \PhpOffice\PhpWord\Style\Section::ORIENTATION_LANDSCAPE,
                    'marginTop' => 56,
                    'marginBottom' => 56,
                    'marginLeft' => 56,
                    'marginRight' => 56,
                );
        $section = $PHPWord->addSection($sectionStyle);
        $header = $section->addHeader();
        $total_page = ceil(sizeof($products)/7);
        $header->addPreserveText($fileName.'第{PAGE}页，共'.$total_page.'页', array('align' => 'center'));
        $table = $section->addTable(array('alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER));
        $table->getStyle()->setBorderSize(0);
        $table->addRow();
        $field_array = array(
                    'A' => array('title' => '跟货号',   'width' => 20,  'key' => 'orders_products_number'),
                    'B' => array('title' => '产品类别', 'width' => 30,  'key' => 'categories_name'),
                    'C' => array('title' => '产品图片', 'width' => 15,  'key' => 'image'),
                    'D' => array('title' => '产品信息', 'width' => 100, 'key' => 'products_detail'),
                    'E' => array('title' => '数量',     'width' => 10,  'key' => 'products_quantity'),
                    'F' => array('title' => '订单号',   'width' => 20,  'key' => 'order_number'),
                    'G' => array('title' => '订单日期', 'width' => 10,  'key' => 'date_purchased'),
                    'H' => array('title' => '供应商',   'width' => 10,  'key' => ''),
                    'I' => array('title' => '订货日期', 'width' => 10,  'key' => ''),
                    'J' => array('title' => '是否定制', 'width' => 10,  'key' => ''),
                    'K' => array('title' => '是否有货', 'width' => 10,  'key' => ''),
        );
        foreach ($field_array as $k => $k_info) {
            $cell = $table->addCell(5320);
            $cell->addText($k_info['title']);
            $cell->getStyle()->setVAlign(\PhpOffice\PhpWord\Style\Cell::VALIGN_CENTER);
        }
        $table->addRow();
        foreach($products as $product){
            foreach ($field_array as $k => $k_info) {
                $cell = $table->addCell(2240);
                if($k_info['key']=='image'){
                    try {
                        $cell->addImage(trim($product['products_image']), array('width' => 141, 'height' => 141, 'align' => 'center'));
                    }catch (InvalidImageException $e) {
                        $cell->addText('Invalid image');
                    }
                }elseif($k_info['key']=='products_detail'){
                    $product['products_name'] = str_replace('&', ' ', $product['products_name']);
                    // &字符会引起word打不开
                    $products_keyword = array('women', 'Nike', 'Youth', 'Game', 'Elite', 'Limited', 'Toddler', 'Men', 'Kid');
                    $products_keyword_pos = array();
                    foreach ($products_keyword as $keyword) {
                        $products_name = $product['products_name'];
                        do {
                            $pos = stripos($products_name, $keyword);
                            if ($pos !== false) {
                                $products_keyword_pos[$pos] = $keyword;
                                $products_name = substr($products_name, ($pos + 1));
                            }
                        }
                        while ($pos !== false);
                    }
                    $textrun = $cell->addTextRun();
                    if (sizeof($products_keyword_pos)) {
                        $l = strlen($product['products_name']);
                        for ($pos = 0; $pos < $l;) {
                            if (isset($products_keyword_pos[$pos])) {
                                $len = strlen($products_keyword_pos[$pos]);
                                $textrun->addText(substr($product['products_name'], $pos, $len), array('size' => 10, 'bold' => true, 'color' => 'ff0000', 'italic' => true));
                                //关键词加粗
                                $pos += $len;
                            } else {
                                $textrun->addText(substr($product['products_name'], $pos, 1), array('size' => 10));
                                $pos++;
                            }
                        }
                    } else {
                        $textrun->addText($product['products_name'], array('size' => 10));
                    }
                    if (!empty($product['products_attributes'])) {
                        foreach ($product['products_attributes'] as $attribute) {
                            $textrun = $cell->addTextRun();
                            $textrun->addText($attribute['products_options'] . ':');
                            $textrun->addText($attribute['products_options_values'], array('bold' => true, 'size' => 10));
                        }
                    }
                    $textrun = $cell->addTextRun();
                    $textrun->addText('SKU:');
                    $textrun->addText($product['products_model'], array('bold' => true));
                }else{
                    $cell->addText($product[$k_info['key']]);
                }
            }
            $table->addRow();
        }
        
        $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($PHPWord, 'Word2007');
        $xmlWriter->save(DIR_FS_TEMP . $fileName .'.docx');
        
        $ZipArchive = new \PhpOffice\PhpWord\Shared\ZipArchive();
        $zip_file = DIR_FS_TEMP.date('YmdHis') . '.zip';
        $ZipArchive->open($zip_file, \PhpOffice\PhpWord\Shared\ZipArchive::CREATE);
        $ZipArchive->addFile(DIR_FS_TEMP . $fileName .'.docx', $fileName .'.docx');
        $ZipArchive->close();        
        
        $link = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/'.str_replace(DIR_FS_ROOT, '', $zip_file).'?'.time();
        redirect($link);
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
            $order_info['order_number'] = empty($order_info['order_no']) ? $order_info['order_no_prefix'] . $order_info['orders_id'] : $order_info['order_no'];

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
    private function _order_doc($site_id, $order_id) {
        $order = new OrderModel();
        $where = array('site_id' => $site_id, 'orders_id' => $order_id);
        $order_info = $order->where($where)->relation(true)->find();
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
        vendor('barcode.autoload');
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($order_no, $generator::TYPE_CODE_128);
        $section->addImage($barcode, array('width' => 300, 'height' => 80, 'align' => 'center'));
        $table = $section->addTable(array('alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER));
        $table->getStyle()->setBorderSize(0);
        $table->addRow(567);
        $cell = $table->addCell(5320);
        $cell->addText('业务备注:'.$order_info['order_remark'], array('bold' => true, 'color' => 'ff0000'), array('align' => 'left'));
        $cell->getStyle()->setVAlign(\PhpOffice\PhpWord\Style\Cell::VALIGN_CENTER);
        $cell = $table->addCell(5320);
        
        $cell->addText('物流备注：'.$order_info['logistics_remark'], array('bold' => true, 'color' => 'ff0000'), array('align' => 'left'));
        
        //$cell->addText($order_info['site_name'], array('bold' => true), array('align' => 'right'));
        //$cell->addText('', array('bold' => true), array('align' => 'right'));
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
        //$cell->addText('订单总额:   ' . round($order_info['order_total'] * $order_info['currency_value'], 2) . $order_info['currency'], $fStyle, $pStyle);
        $cell->addText('订单日期:', $fStyle, $pStyle);
        $order_info['payment_method'] = str_replace('&nbsp;', '', $order_info['payment_method']);
        $order_info['payment_method'] = strip_tags($order_info['payment_method']);
        $cell->addText('支付方式:   ' . ($order_info['payment_method']=='inline'?'Credit Cards':$order_info['payment_method']), $fStyle, $pStyle);
        $textrun = $cell->addTextRun();
        $textrun->addText('总件数: ', $fStyle, $pStyle);
        $textrun->addText($quanlity_total, array('color' => 'ff0000', 'bold' => true, 'size' => 18));
        $textrun->addText(' 件          货运方式:', $fStyle, $pStyle);
        /*
        if ($order_info['shipping_module_code'] == 'zones' || $order_info['shipping_module_code'] == 'faster' || $order_info['shipping_module_code']=='固定运费') {
            $string_delivery_type = '快速faster';
        } else {
            $string_delivery_type = '标准standard';
        }*/
            switch ($order_info['shipping_module_code']) {
                case 'faster':
                case 'zones':
                case '固定运费':
                    $string_delivery_type = '快速 faster';
                    break;
                default:
                    $string_delivery_type = '标准 standard';
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
            $cell = $sub_table->addCell(5320, array('vAlign' => 'top'));
            $product_table = $cell->addTable();
            $product_table->addRow();

            $cell = $product_table->addCell(2240);
            try {
                $entry['products_image'] = $this->_getProductImage($site_id, $entry['orders_products_id']);
                $new_file = DIR_FS_TEMP.'cache/'.preg_replace('~(.+)(\.\w+)$~i', '$1_w200.jpg', $entry['products_image']);
                if(!file_exists($new_file)){
                    if($this->_resize(DIR_FS_ROOT.$entry['products_image'], $new_file, 200)){
                        $entry['products_image'] = $new_file;
                    }
                }else{
                    $entry['products_image'] = $new_file;
                }                  
                $cell->addImage(trim($entry['products_image']), array('width' => 141, 'height' => 141, 'align' => 'center'));
            }
            catch (InvalidImageException $e) {
                $cell->addText('Invalid image');
            }
       
            $cell = $product_table->addCell(600, array('vAlign' => 'center'));
            $textrun = $cell->addTextRun();
            $textrun->addText($entry['products_quantity'], array('bold' => true, 'size' => 25));
            $textrun->addText('x');
            $cell = $product_table->addCell(2480);
            
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
            //判断是否有货
            $supplier = M('orders_products_supplier')->where(array('supplier_id'=>$entry['orders_products_remark']['supplier_id']))->find();
            if($supplier['supplier_name']=='没货')
                $has_supplier = false;
            else
                $has_supplier = true;
            if (sizeof($products_keyword_pos)) {
                $l = strlen($entry['products_name']);
                for ($pos = 0; $pos < $l;) {
                    if (isset($products_keyword_pos[$pos])) {
                        $len = strlen($products_keyword_pos[$pos]);
                        if($entry['orders_products_remark']['remove']==1)
                            $textrun->addText(substr($entry['products_name'], $pos, $len), array('size' => 10, 'bold' => true, 'color' => 'ff0000', 'italic' => true, 'doubleStrikethrough'=>true));
                        elseif($has_supplier==false)
                            $textrun->addText(substr($entry['products_name'], $pos, $len), array('size' => 10, 'bold' => true, 'color' => 'ff0000', 'italic' => true, 'bgColor'=>'7CFC00'));
                        else
                            $textrun->addText(substr($entry['products_name'], $pos, $len), array('size' => 10, 'bold' => true, 'color' => 'ff0000', 'italic' => true));
                        //关键词加粗
                        $pos += $len;
                    } else {
                        if($entry['orders_products_remark']['remove']==1)
                            $textrun->addText(substr($entry['products_name'], $pos, 1), array('size' => 10, 'doubleStrikethrough'=>true));
                        elseif($has_supplier==false)
                            $textrun->addText(substr($entry['products_name'], $pos, 1), array('size' => 10, 'bgColor'=>'7CFC00'));                            
                        else
                            $textrun->addText(substr($entry['products_name'], $pos, 1), array('size' => 10));
                        $pos++;
                    }
                }
            } else {
                if($entry['orders_products_remark']['remove']==1)
                    $textrun->addText($entry['products_name'], array('size' => 10, 'doubleStrikethrough'=>true));
                elseif($has_supplier==false)
                    $textrun->addText($entry['products_name'], array('size' => 10, 'bgColor'=>'7CFC00'));
                else
                    $textrun->addText($entry['products_name'], array('size' => 10));
            }
            if (!empty($entry['attribute'])) {
                foreach ($entry['attribute'] as $attribute) {
                    $textrun = $cell->addTextRun();
                    if($has_supplier==false){
                        $textrun->addText($attribute['products_options'] . ':', array('bgColor'=>'7CFC00'));
                        $textrun->addText($attribute['products_options_values'], array('bold' => true, 'size' => 10, 'bgColor'=>'7CFC00'));                    
                    }else{
                        $textrun->addText($attribute['products_options'] . ':');
                        $textrun->addText($attribute['products_options_values'], array('bold' => true, 'size' => 10));
                    }
                }
            }
            $textrun = $cell->addTextRun();
            if($has_supplier==false){
                $textrun->addText('SKU:', array('bgColor'=>'7CFC00'));
                $textrun->addText($entry['products_model'], array('bold' => true, 'bgColor'=>'7CFC00'));                
            }else{
                $textrun->addText('SKU:');
                $textrun->addText($entry['products_model'], array('bold' => true));
            }
            if($entry['orders_products_remark']['remove']==1){
                $textrun = $cell->addTextRun();
                $textrun->addText('取消此项', array('size' => 10, 'bold' => true, 'color' => 'ff0000'));
            }
            $textrun = $cell->addTextRun();
            $textrun->addText('Note:', array('size' => 10, 'bold' => true, 'color' => 'ff0000'));
            if(!empty($entry['orders_products_remark']['remark'])){
                $textrun->addText($entry['orders_products_remark']['remark'], array('size' => 10, 'bold' => true, 'color' => 'ff0000', 'bgColor'=>'ffff00'));
            }            
            
            $detail_process = json_decode($entry['orders_products_remark']['detail_process'], true);            
            if(!empty($detail_process)){
                $sub_table->addRow();
                $cell = $sub_table->addCell(5320);
                $j = 1;
                foreach($detail_process as $detail_process_entry){
                    $textrun = $cell->addTextRun();
                    
                    $supplier = M('orders_products_supplier')->where(array('supplier_id'=>$detail_process_entry['supplier_id']))->find();
                    $detail_process_entry['date_process'] = date('m-d', strtotime($detail_process_entry['date_process']));
                    $textrun->addText($supplier['supplier_name'].'/'.$detail_process_entry['date_process'].'/'.$detail_process_entry['quantity_process'].($detail_process_entry['is_customized']?'/定制':''), array('size' => 14, 'bold' => true, 'color' => 'ff0000'));
                    $textrun->addText('   '.number_format($entry['orders_products_remark']['purchase_price'], 2), array('size' => 14, 'bold' => true, 'color' => '0000ff'));
                    $j++;
                }
            }
            $i++;
        }
        $section->addTextBreak(1);
        $section->addText('发货记录:');
        return array('obj' => $PHPWord, 'filename' =>  $order_no . '.docx');
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
    
    /*
    *导出文档发供应商确认是否有货
    */
    public function exportAction(){
        $join = array();
        $join[] = 'LEFT JOIN __SITE__ s ON s.site_id=op.site_id';
        $join[] = '__ORDERS__ o ON o.site_id=op.site_id AND o.orders_id=op.orders_id';
        $join[] = '__ORDERS_REMARK__ o_r ON o_r.site_id=op.site_id AND o_r.orders_id=op.orders_id';
        $join[] = 'LEFT JOIN __ORDERS_PRODUCTS_REMARK__ o_p_r ON o_p_r.site_id=op.site_id AND o_p_r.orders_products_id=op.orders_products_id';
        $join[] = 'LEFT JOIN __ORDERS_PRODUCTS_CATEGORIES__ c ON c.categories_id=o_p_r.categories_id';       
        $join[] = 'LEFT JOIN __ORDERS_PRODUCTS_SUPPLIER__ ops ON ops.supplier_id=o_p_r.supplier_id';
        $where = array();
        if (I('user_id') != '') {
            $join[] = 'JOIN __USERS_TO_SITE__ u2s ON u2s.site_id=s.site_id';
            $where['u2s.user_id'] = I('user_id');
        }       
        if(I('categories_id')){
            if(I('categories_id')=='-1') 
                $where['c.categories_id'] = array('exp', 'is null');
            else
                $where['c.categories_id'] = I('categories_id');
            $categories = M('orders_products_categories')->where(array('categories_id'=>I('categories_id')))->find();
            $fileName = $categories['categories_name']."供应商订货表_" . date('YmdHis', time());
        }else{
            $fileName = "供应商订货表_" . date('YmdHis', time());
        }
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
        }          
        if(I('item_status')){
            $where['item_status'] = I('item_status');
        }
        $option_order_status = array('待订货'=>'待订货','已订货'=>'已订货','部分发货'=>'部分发货');
        if(I('order_status') && in_array(I('order_status'), $option_order_status)){
            $option_order_status_selected = I('order_status');
            $where['order_status_remark'] = $option_order_status_selected;
        }else{
            $where['order_status_remark'] = array('IN', array_keys($option_order_status));
        }   
        if(I('last_motify_date_start') && I('last_motify_date_end')){
            $last_motify_date_start = I('last_motify_date_start');
            $last_motify_date_end   = I('last_motify_date_end');
        }else{
            $last_motify_date_end     = date('Y-m-d');//默认最近2天
            $last_motify_date_start   = date('Y-m-d', strtotime($last_motify_date_end)-24*3600);
        }        
        if(I('purchase_date_start') && I('purchase_date_end')){
            $where['o.date_purchased'] = array('between', array(I('purchase_date_start').' 0:0:0', I('purchase_date_end').' 23:59:59'));
        }
        if(I('sku')){
            if(strpos(I('sku'), "\n"))
                $sku_array = explode("\n", I('sku'));
            else
                $sku_array = explode(",", I('sku'));            
            foreach($sku_array as $k=>$v){
                $v = trim($v);
                if(empty($v)){
                    unset($sku_array[$k]);
                }else
                    $sku_array[$k] = $v;
            }
            $where['op.products_model'] = array('in', $sku_array);
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
        }
        $where['o_r.last_modify'] = array('between', array($last_motify_date_start.' 0:0:0', $last_motify_date_end.' 23:59:59'));
        $option_order_status = array('待订货'=>'待订货','已订货'=>'已订货','部分发货'=>'部分发货');
        if(I('order_status') && in_array(I('order_status'), $option_order_status)){
            $option_order_status_selected = I('order_status');
            $where['order_status_remark'] = $option_order_status_selected;
        }
        
        if(I('get.type', 'word')=='word' || I('get.type', 'word')=='excel'){
            $products = M('orders_products')->alias('op')->join($join)->where($where)->field('op.*,o.*,o_r.*,s.*,c.categories_name,o_p_r.remove,o_p_r.remark as remark_product,ops.supplier_name,o_p_r.*')->order('date_purchased desc,op.site_id desc,op.orders_id desc,op.orders_products_id asc')->select();
            foreach($products as $k=>$v){
                $v['products_image'] = $this->_getProductImage($v['site_id'], $v['orders_products_id']);
                $new_file = DIR_FS_TEMP.'cache/'.preg_replace('~(.+)(\.\w+)$~i', '$1_w200.jpg', $v['products_image']);
                
                if(!file_exists($new_file)){
                    if($this->_resize(DIR_FS_ROOT.$v['products_image'], $new_file, 200)){
                        $v['products_image'] = $new_file;
                    }
                }else
                    $v['products_image'] = $new_file;
                    
                $v['products_attributes'] = M('orders_products_attributes')->where(array('site_id'=>$v['site_id'], 'orders_products_id'=>$v['orders_products_id']))->select();
    
                $v['products_detail'] = $v['products_name']."\n";
                $products_size        = '';
                if($v['products_attributes']){
                    foreach($v['products_attributes'] as $attributes){
                        if(preg_match('~size~i', $attributes['products_options'])){//尺码
                            $products_size .= $attributes['products_options'].':'.$attributes['products_options_values']."\n";
                        }else
                            $v['products_detail'] .= $attributes['products_options'].':'.$attributes['products_options_values']."\n";
                    }
                }
                $v['products_size'] = trim($products_size, "\n");
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
        }else{
            $products = M('orders_products')->alias('op')->join($join)->where($where)->field('distinct o.site_id,o.orders_id')->order('date_purchased desc,o.orders_id desc')->select();
        }   
                


        if(I('get.type', 'word')=='word')
            $this->_export_word($products, $fileName);
        elseif(I('get.type', 'word')=='excel')    
            $this->_export_excel($products, $fileName);
        elseif('address'==I('get.type', 'word')){
            $this->_export_address($products, $fileName);
        }elseif('order'==I('get.type', 'order')){
            $link = $this->_export_order($products);
            $page = $page;
            $total_page = ceil($count/$num);
            $this->ajaxReturn(array('link'=>$link, 'page'=>$page, 'total_page'=>$total_page), 'JSON');
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
            'B' => array('title' => '订单号',     'key' => 'order_no',      'required' => true),
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

	//录入回单
	public function enterReceiptAction(){
		if (IS_POST) {
			$post_data = I('post.');
			if(empty($post_data['site_id']) || empty($post_data['orders_id']) || empty($post_data['delivery_type']) || empty($post_data['delivery_quanlity']) || empty($post_data['delivery_weight']) || empty($post_data['delivery_tracking_no']) || empty($post_data['delivery_date'])){
				$this->error('数据不完整');
			}
			$_SESSION['delivery_type'] = $post_data['delivery_type'];
			$data_delivery = array(
				'site_id' => $post_data['site_id'],
				'orders_id' => $post_data['orders_id'],
				'delivery_type' => $post_data['delivery_type'],
				'delivery_quanlity' => $post_data['delivery_quanlity'],
				'delivery_gift_quanlity' => $post_data['delivery_gift_quanlity'],
				'delivery_weight' => $post_data['delivery_weight'],
				'delivery_forward_no' => $post_data['delivery_forward_no'],
				'delivery_tracking_no' => $post_data['delivery_tracking_no'],
				'delivery_date' => $post_data['delivery_date'],
				'delivery_remark' => $post_data['delivery_remark']
			);
			$where = array('site_id' => $post_data['site_id'], 'orders_id' => $post_data['orders_id'], 'delivery_tracking_no' => $post_data['delivery_tracking_no']);
			$orders_delivery_id = D('orders_delivery')->where($where)->getField('orders_delivery_id');
			if (empty($orders_delivery_id)) {
				$data_delivery['add_time'] = date('Y-m-d H:i:s');
				$orders_delivery_id = D('orders_delivery')->add($data_delivery);
			} else {
				D('orders_delivery')->where(array('orders_delivery_id' => $orders_delivery_id))->save($data_delivery);
			}
			$orders_products_remark_model = M('OrdersProductsRemark');
			$orders_products_remark_received_model = M('OrdersProductsRemarkReceived');
			$orders_products_remark_list = $orders_products_remark_model->field('orders_products_remark_id,orders_products_id,quantity_process,cost_counted,orders_delivery_id,out_of_stock')->where(array('site_id' => $post_data['site_id'],'orders_id' => $post_data['orders_id']))->select();
			$logistics_status = 1;
			$out_of_stock_id = array();
			foreach ($orders_products_remark_list as $v){
				$save = array();
				$orders_delivery_id_arr = empty($v['orders_delivery_id']) ? array() : explode(',',$v['orders_delivery_id']);
				//标记包裹中的商品
				if(in_array($v['orders_products_id'], $post_data['orders_products_id'])){
					$check_orders_products_remark_received = $orders_products_remark_received_model->where(array('orders_products_remark_id' => $v['orders_products_remark_id'], 'date_received' => $post_data['delivery_date']))->find();
					if($check_orders_products_remark_received){
						$orders_products_remark_received_model->where(array('orders_products_remark_id' => $v['orders_products_remark_id'], 'date_received' => $post_data['delivery_date']))->save(array('quantity_received' => $v['quantity_process'], 'cost_counted' => 1));
					}else{
						$orders_products_remark_received_model->add(array('orders_products_remark_id' => $v['orders_products_remark_id'], 'date_received' => $post_data['delivery_date'],'quantity_received' => $v['quantity_process'], 'cost_counted' => 1));
					}
					if($v['cost_counted'] != 1) $save['cost_counted'] = 1;
					if(!in_array($orders_delivery_id, $orders_delivery_id_arr)){
						$orders_delivery_id_arr[] = $orders_delivery_id;
						$save['orders_delivery_id'] = implode(',', $orders_delivery_id_arr);
					}
					if($v['out_of_stock'] == 1) $save['out_of_stock'] = 0;
				}else{
					if(in_array($orders_delivery_id, $orders_delivery_id_arr)) $save['orders_delivery_id'] = implode(',',array_diff($orders_delivery_id_arr,array($orders_delivery_id)));
				}

				//标记没货的商品
				if(in_array($v['orders_products_id'], $post_data['out_of_stock'])){
					if($v['out_of_stock'] != 1){
						$save['out_of_stock'] = 1;
						$out_of_stock_id[] = $v['orders_products_id'];
					}
				}else{
					if($v['out_of_stock'] == 1) $save['out_of_stock'] = 0;
				}

				if(count($save) > 0) $orders_products_remark_model->where(array('orders_products_remark_id' => $v['orders_products_remark_id']))->save($save);
				if((isset($save['orders_delivery_id']) && empty($save['orders_delivery_id'])) || (!isset($save['orders_delivery_id']) && empty($v['orders_delivery_id'])) || (isset($save['out_of_stock']) && $save['out_of_stock'] == 1) || (!isset($save['out_of_stock']) && $v['out_of_stock'] == 1)) $logistics_status = 0;
			}
			if ($orders_products_remark_model->where(array('site_id' => $post_data['site_id'],'orders_id' => $post_data['orders_id'],'remove' => 0,'orders_delivery_id' => array(array('exp', 'IS NULL'), array('eq', ''), 'OR')))->count() > 0){
				$order_status_remark = '部分发货';
			}else{
				$order_status_remark = '已发货';
			}
			$order_remark_data = array('order_status_remark' => $order_status_remark, 'date_send' => $post_data['delivery_date'], 'logistics_status' => $logistics_status);
			$order_info = M('Orders')->alias('o')->join(array('__ORDERS_REMARK__ ore ON ore.site_id=o.site_id AND ore.orders_id=o.orders_id','__SITE__ s ON s.site_id=o.site_id'))->field('o.orders_id,o.customers_email_address,o.customers_name,ore.order_no,ore.email_logs,s.order_no_prefix,s.customer_service_name,s.site_index,s.email_data')
				->where(array('o.site_id' => $post_data['site_id'],'o.orders_id' => $post_data['orders_id']))->find();
			if(empty($order_info['order_no'])) $order_info['order_no'] = $order_info['order_no_prefix'] . $order_info['orders_id'];
			$email_data = array();
			if (!empty($order_info['email_data'])) $email_data = json_decode($order_info['email_data'], true);
			if (count($email_data) == 0) $this->error('网站没有配置用于发送邮件的邮箱信息，请通知业务员发送邮件给客户！', U('Order/Purchase/enterReceipt'),5);
			//发送发货提醒的邮件给客户
			$email_subject = 'Dear ' . $order_info['customers_name'] . ', Tracking number for your order ' . $order_info['order_no'];
			$delivery_tracking_no_arr = M('orders_delivery')->where(array('site_id' => $post_data['site_id'], 'orders_id' => $post_data['orders_id']))->getField('delivery_tracking_no',true);
			$email_text = '<p>Dear ' . $order_info['customers_name'] . ',</p>
				<p>This is ' . $order_info['customer_service_name'] . ' from <b>' . $order_info['site_index'] . '</b>,</p>
				<p>We have sent out package for you, here is the tracking number list: <b><br>' . implode(',<br>',$delivery_tracking_no_arr) . ',<br></b> Please check it 48 hours later by <a href="http://www.17track.net/en">http://www.17track.net/en</a> or <a href="https://www.usps.com/">https://www.usps.com/</a>, we will follow the shipping for you too.</p>
				<p>By the way, if you recommend our website to your friends buddy or any one, we will offer 5% of your friends\' order total as commission,  please email us your friends\' full name first!</p>
				<p>Best regards</p>
				<p>' . $order_info['customer_service_name'] . '</p>';
			$result = false;
			shuffle($email_data);
			foreach ($email_data as $email_info) {
				$result = $this->send_email($order_info['customers_email_address'], $order_info['customers_name'], $email_subject, $email_text, $email_text, $email_info['address'], $email_info['password'], $email_info['smtp'], $email_info['port']);
				if ($result !== true) $result = $this->send_email2($order_info['customers_email_address'], $order_info['customers_name'], $email_subject, $email_text, $email_text, $email_info['address'], $email_info['password'], $email_info['smtp'], $email_info['port']);
				if ($result === true) break;
			}
			$error = array();
			if($result !== true){
				$order_remark_data['send_status'] = 2;
				$error[] = '发货提醒邮件发送失败(Error:' . $result . ')!';
			}else{
				if (empty($order_info['email_logs'])) {
					$email_logs = array();
				} else {
					$email_logs = json_decode($order_info['email_logs'], true);
				}
				if(!isset($email_logs[$order_status_remark])) $email_logs[$order_status_remark] = array();
				$email_logs[$order_status_remark][] = array(
					'email_template_name' => '发货提醒:部分发货+已发货(完单)',
					'time' => date('Y-m-d H:i:s'),
				);
				$order_remark_data['email_logs'] = json_encode($email_logs);
				$order_remark_data['send_status'] = 1;
				$order_remark_data['last_email'] = 0;
			}
			M('OrdersRemark')->where(array('site_id' => $post_data['site_id'], 'orders_id' => $post_data['orders_id']))->save($order_remark_data);

			if(count($out_of_stock_id) > 0){
				//发送换货的邮件给客户
				$products_list = M('OrdersProducts')->field('orders_products_id,products_name,orders_id,products_quantity,products_model')->where(array('site_id' => $post_data['site_id'],'orders_products_id' => array('in',$out_of_stock_id)))->select();
				if(!empty($products_list)){
					$email_subject = 'These Items are out of stock now for ' . $order_info['order_no'];
					$email_text = '<p>Hello ' . $order_info['customers_name'] . '<p>
						<p>This is ' . $order_info['customer_service_name'] . ' from jersey website ' . $order_info['site_index'] . ',<p>
						<p>Thank you for your support on us, we are sorry to inform you that the following items are out of stock now, could you please pick the replacements then send to us? We will try our best to complete your order asap.<p>
						<p>Thanks for your cooperation!<p>';
					$email_text .= '<table border="1" cellspacing="0" cellpadding="2" width="800px"><tr style="background:#fff;"><th>IMG</th><th>Title&attribute</th><th>Qty.</th><th>Model</th></tr>';
					foreach ($products_list as $product) {
						$email_text .= '<tr>';
						$products_image = $this->_getProductImage($post_data['site_id'], $product['orders_products_id']);
						$email_text .= '<td style="width:110px;"><img src="http://' . $_SERVER['HTTP_HOST'].'/'.$products_image . '" width="100px"></td>';
						$email_text .= '<td style="width:300px;">' . $product['products_name'];
						$attributes_list = M('OrdersProductsAttributes')->field('products_options,products_options_values')->where(array('orders_id'=>$product['orders_id'],'orders_products_id'=>$product['orders_products_id']))->select();
						if (!empty($attributes_list)) {
							$email_text .= '<ul>';
							foreach ($attributes_list as $attribute) {
								$email_text .= '<li>' . $attribute['products_options'] . ':' . $attribute['products_options_values'] . '</li>';
							}
							$email_text .= '</ul>';
						}
						$email_text .= '</td>';
						$email_text .= '<td>' . $product['products_quantity'] . '</td>';
						$email_text .= '<td>' . $product['products_model'] . '</td>';
						$email_text .= '</tr>';
					}
					$email_text .= '</table>';
					$email_text .= '<p>' . $order_info['customer_service_name'] . '</p>';
					$result = false;
					shuffle($email_data);
					foreach ($email_data as $email_info) {
						$result = $this->send_email($order_info['customers_email_address'], $order_info['customers_name'], $email_subject, $email_text, $email_text, $email_info['address'], $email_info['password'], $email_info['smtp'], $email_info['port']);
						if ($result !== true) $result = $this->send_email2($order_info['customers_email_address'], $order_info['customers_name'], $email_subject, $email_text, $email_text, $email_info['address'], $email_info['password'], $email_info['smtp'], $email_info['port']);
						if ($result === true) break;
					}
					if($result !== true) $error[] = '换货邮件发送失败(Error:' . $result . ')!';
				}
			}
			if(count($error) > 0){
				$message = implode('<br>', $error) . '<br>请通知业务员发送邮件给客户！';
				$this->error($message, U('Order/Purchase/enterReceipt'), intval(strlen($message)/10));
			}
			$this->success('录入成功！', U('Order/Purchase/enterReceipt'));
		}
		$order_no = I('order_no','');
		if(!empty($order_no)){
			if (false!==($zencart_no = parseZencartNo($order_no)) || preg_match('~-~', $order_no)) {
				if(preg_match('~-~', $order_no)) {
					$order_info = M('orders_remark')->field('site_id,orders_id')->where(array('order_no'=>$order_no))->find();
				} else {
					$order_info = M('Orders')->alias('o')->join(array('LEFT JOIN __SITE__ s ON s.site_id=o.site_id'))->field('o.site_id,o.orders_id')->where(array('s.order_no_prefix' => $zencart_no['orders_prefix'],'o.orders_id' => $zencart_no['orders_id']))->find();
				}
				if (empty($order_info)){
					$this->error('没有在系统中找到对应的订单记录');
				}else{
					$all_remove = true;
					$products = M('OrdersProducts')->alias('op')->join(array('LEFT JOIN __ORDERS_PRODUCTS_REMARK__ opr ON op.site_id=opr.site_id AND op.orders_products_id=opr.orders_products_id','LEFT JOIN __ORDERS_PRODUCTS_SUPPLIER__ ops ON opr.supplier_id=ops.supplier_id'))->where(array('op.site_id' => $order_info['site_id'],'op.orders_id' => $order_info['orders_id']))->field('op.site_id,op.orders_products_id,op.products_model,op.products_name,op.products_quantity,op.products_image,opr.orders_delivery_id,opr.out_of_stock,opr.remove,ops.supplier_name')->order('op.orders_products_id asc')->select();
					foreach($products as $k=>$v){
						if($all_remove && $v['remove'] != 1) $all_remove = false;
						$v['products_image'] = $this->_getProductImage($v['site_id'], $v['orders_products_id']);
						$v['products_attributes'] = M('orders_products_attributes')->where(array('site_id'=>$v['site_id'], 'orders_products_id'=>$v['orders_products_id']))->select();
						$products[$k] = $v;
					}
					if($all_remove) $this->error('订单中的产品已全部取消，无需再发货！');
					$order_info['products'] = $products;
				}
				$order_info['order_no'] = $order_no;
				$order_info['orders_delivery_history'] = M('orders_delivery')->alias('od')->join(array('LEFT JOIN __ORDERS_PRODUCTS_REMARK__ opr ON od.orders_delivery_id=opr.orders_delivery_id'))->where(array('od.site_id' => $order_info['site_id'], 'od.orders_id' => $order_info['orders_id']))->getField('orders_products_id',true);
				$this->assign('order_info',$order_info);
			} else {
				$this->error('单号有问题');
			}
		}
		$this->display();
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
}