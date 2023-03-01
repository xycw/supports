<?php

namespace Product\Controller;

use Common\Controller\CommonController;
use Product\Model\ProductModel;

class DatabaseController extends CommonController {

    public function listAction() {

        $model_product = new ProductModel();
        $where = array();
        $page_data = array();
        if (I('language_code')) {
            $page_data['language_code'] = I('language_code');
        }
        if (I('date_added')) {
            $where['date_added']   = I('date_added');
            $page_data['date_added'] = I('date_added');
        } 
        if (I('products_sku')) {
            $where['product_model']  = array('like','%'.I('products_sku').'%');
            $page_data['products_sku'] = I('products_sku');
        } 
        if (I('upload_added')) {
            $where['upload_added']   = I('upload_added');
            $page_data['upload_added'] = I('upload_added');
        } 

        if (I('get_model')) {
            $where['get_model']   = I('get_model');
            $page_data['get_model'] = I('get_model');
        } 

        $page = I('page', 1);
        $list_row = 100;//每页100条
        if(I('export_csv', 0)=='1'){//导出
            $products = $model_product->where($where)->relation(true)->order('product_id desc')->select();
            // var_dump($products);exit;
            $fields = array(
                'product_model',
                'product_price',
                'speciel_price',
                'product_images',
                'additional_images',
                'product_brand',
                'featured_category',
                'product_name_en',
                'product_description_en',
                'product_attribute_en',
                'attribute_en',
                'date_added',
                'upload_added',
                'get_model',     
                'retail_speciel_price'
            );
            $file_csv = DIR_FS_TEMP . date('ymdhis').'.csv';
            $fp = fopen($file_csv, 'w');
            fputcsv($fp, $fields); 
            foreach ($products as $product){
                foreach ($product['detail'] as $products_detail){
                    $products_detail['product_description'] = str_replace("\n", "", $products_detail['product_description']);
                    $data = array(
                        $product['product_model'],
                        $product['product_price'],
                        $product['speciel_price'],
                        $product['product_images'],
                        $product['additional_images'],
                        $product['product_brand'],
                        $product['featured_category'],
                        ($products_detail['language_code']=='en'?$products_detail['product_name']:''),
                        ($products_detail['language_code']=='en'?$products_detail['product_description']:''),
                        ($products_detail['language_code']=='en'?$products_detail['product_attribute']:''),
                        ($products_detail['language_code']=='en'?$products_detail['attribute']:''),
                        $product['date_added'],
                        $product['upload_added'],
                        $product['get_model'],
                        $product['retail_speciel_price']
                    );
                    fputcsv($fp, $data);
                }
            }
            fclose($fp);
        $link = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/'.str_replace(DIR_FS_ROOT, '', $file_csv);
        redirect($link, 10,'系统将在10秒后跳转到.你也可以直接点击些链接   <a href="'.$link.'">点我下载</a>（此链接3小时内有效）');            
            exit;
        }

        if(I('export_saas', 0)=='1'){//商城格式导出
            if(empty(I('upload_added'))) $this->error('请选择上传日期！',U('Product/Database/list'));
            $products = $model_product->where($where)->relation(true)->order('product_id desc')->select();
            $category_arr = $exist_category_arr = $option_arr = $exist_option_arr = $product_arr = $sub_category_arr = array();
            foreach ($products as $key => $product){
                $v_sku = trim($product['product_model']);
                $v_category = '';
                if(!empty($product['featured_category'])){
                    $featured_category_array = explode('|||', $product['featured_category']);
                    foreach ($featured_category_array as $n => $featured_category){
                        $featured_category_arr = explode('===', $featured_category);
                        foreach ($featured_category_arr as $k => $category){
                            $featured_category_arr[$k] = $category = trim($k > 0 ? $category : str_replace(' Jerseys', '', $category));
                            $v_parent_category_arr = array();
                            if($k > 0){
                                for ($i = 0;$i < $k;$i++){
                                    $v_parent_category_arr[] = $featured_category_arr[$i];
                                }
                            }
                            $v_parent_category = implode('/', $v_parent_category_arr);
                            if(!in_array($v_parent_category . '/' . $category, $exist_category_arr)){
                                $category_arr[] = array(
                                    'v_parent_category' => $v_parent_category,
                                    'v_name' => $category
                                );
                                $exist_category_arr[] = $v_parent_category . '/' . $category;
                            }
                        }
                        if($n > 0){
                            $sub_category_arr[] = array(
                                'v_sku' => $v_sku,
                                'v_category' => implode('/', $featured_category_arr),
                            );
                        }else{
                            $v_category = implode('/', $featured_category_arr);
                        }
                    }
                }
                $product_arr[$key] = array(
                    'v_sku' => $v_sku,
                    'v_category' => $v_category,
                    'v_band' => trim($product['product_brand']),
                    'v_price' => $product['product_price'],
                    'v_special_price' => $product['speciel_price'],
                    'v_image' => str_replace('products', 'pro-wholesale', str_replace('saas/pro-wholesale', 'pro-wholesale', trim($product['product_images']))),
                    'v_images' => str_replace('products', 'pro-wholesale', str_replace('saas/pro-wholesale', 'pro-wholesale', trim($product['additional_images']))),
                    'v_status' => intval($product['product_status']),
                    'v_date_added' => date('Y/m/d H:i:s',strtotime($product['date_added']))
                );
                if(!empty($product['detail'])){
                    foreach ($product['detail'] as $detail){
                        if($detail['language_code'] == 'en'){
                            $product_attribute_arr = array();
                            if(!empty($detail['product_attribute'])){
                                $product_attributes = explode('|||', $detail['product_attribute']);
                                foreach($product_attributes as $attribute){
                                    list($option_names, $string_option_values) = explode(':', $attribute);
                                    list($option_name, $option_type) = explode('==', $option_names);
                                    switch (trim($option_type)){
                                        case 'Dropdown':
                                            $option_type = '列表';
                                            break;
                                        case 'Text':
                                            $option_type = '单行文本';
                                            break;
                                        default:
                                            $option_type = '下拉列表';
                                    }
                                    $options_values = explode(',', $string_option_values);
                                    $option_name = trim($option_name);
                                    $option_names = $option_name . '===' . $option_type;
                                    if(!in_array($option_names, $exist_option_arr)){
                                        $option_arr[$option_names] = array(
                                            'v_type' => $option_type,
                                            'v_option_name' => $option_name,
                                            'v_option_value' => array()
                                        );
                                        $exist_option_arr[] = $option_names;
                                    }
                                    $options_value_arr = array();
                                    foreach ($options_values as $options_values_name){
                                        list($options_values_name, $options_values_price) = explode('|', $options_values_name);
                                        $options_values_name = trim(str_replace('TEXT', '', $options_values_name));
                                        if(!empty($options_values_name) && !in_array($options_values_name . ':100', $option_arr[$option_names]['v_option_value'])) $option_arr[$option_names]['v_option_value'][] = $options_values_name . ':100';
                                        if(!empty($options_values_price)){
                                            if(strpos($options_values_price, '%') !== false) $options_values_price = str_replace('%', '', $options_values_price);
                                            $options_value_arr[] = $options_values_name . ':' . ($options_values_price > 0 ? '+' : '-') . abs($options_values_price);
                                        }else{
                                            $options_value_arr[] = $options_values_name . ':+0';
                                        }
                                    }
                                    $product_attribute_arr[] = $option_name . ':1#' . implode('|',$options_value_arr);
                                }
                            }
                            $product_arr[$key]['v_option'] = implode(';',$product_attribute_arr);
                            $product_arr[$key]['v_attribute'] = $detail['attribute'];
                            $product_arr[$key]['v_name'] = trim($detail['product_name']);
                            $product_arr[$key]['v_description'] = $detail['product_description'];
                        }
                    }
                }
            }
            $zip = new \ZipArchive;
            $zip_file_name = '商城商品数据' . time() . rand(100,999) . '.zip';
            $zip_file = DIR_FS_TEMP . $zip_file_name;
            $zip->open($zip_file, \ZIPARCHIVE::CREATE);
            if(count($category_arr > 1)){
                $fields = array(
                    'v_parent_category',
                    'v_status',
                    'v_name'
                );
                $category_file_name = 'category' . time() . rand(100,999) . '.csv';
                $category_file_csv = DIR_FS_TEMP . $category_file_name;
                $fp = fopen($category_file_csv, 'w');
                fputcsv($fp, $fields);
                foreach ($category_arr as $category){
                    $data = array(
                        $category['v_parent_category'],
                        1,
                        $category['v_name']
                    );
                    fputcsv($fp, $data);
                }
                fclose($fp);
                $zip->addFile($category_file_csv,$category_file_name);
            }
            if(count($option_arr > 1)){
                $fields = array(
                    'v_type',
                    'v_option_name',
                    'v_option_value'
                );
                $option_file_name = 'option' . time() . rand(100,999) . '.csv';
                $option_file_csv = DIR_FS_TEMP . $option_file_name;
                $fp = fopen($option_file_csv, 'w');
                fputcsv($fp, $fields);
                foreach ($option_arr as $option){
                    $data = array(
                        $option['v_type'],
                        $option['v_option_name'],
                        implode(';',$option['v_option_value'])
                    );
                    fputcsv($fp, $data);
                }
                fclose($fp);
                $zip->addFile($option_file_csv,$option_file_name);
            }
            if(count($product_arr > 1)){
                $fields = array(
                    'v_sku',
                    'v_category',
                    'v_band',
                    'v_price',
                    'v_special_price',
                    'v_date_start',
                    'v_date_end',
                    'v_image',
                    'v_images',
                    'v_status',
                    'v_date_added',
                    'v_option',
                    'v_attribute',
                    'v_name',
                    'v_description'
                );
                $product_file_name = 'product' . time() . rand(100,999) . '.csv';
                $product_file_csv = DIR_FS_TEMP . $product_file_name;
                $fp = fopen($product_file_csv, 'w');
                fputcsv($fp, $fields);
                $v_date_start = date('Y/m/d');
                $v_date_end = date('Y/m/d',strtotime('+200year'));
                foreach ($product_arr as $product){
                    $data = array(
                        $product['v_sku'],
                        $product['v_category'],
                        $product['v_band'],
                        $product['v_price'],
                        $product['v_special_price'],
                        $v_date_start,
                        $v_date_end,
                        $product['v_image'],
                        $product['v_images'],
                        $product['v_status'],
                        $product['v_date_added'],
                        $product['v_option'],
                        $product['v_attribute'],
                        $product['v_name'],
                        $product['v_description']
                    );
                    fputcsv($fp, $data);
                }
                fclose($fp);
                $zip->addFile($product_file_csv,$product_file_name);
            }
            if(count($sub_category_arr > 1)){
                $fields = array(
                    'v_sku',
                    'v_category'
                );
                $sub_category_file_name = 'subCategory' . time() . rand(100,999) . '.csv';
                $sub_category_file_csv = DIR_FS_TEMP . $sub_category_file_name;
                $fp = fopen($sub_category_file_csv, 'w');
                fputcsv($fp, $fields);
                foreach ($sub_category_arr as $sub_category){
                    $data = array(
                        $sub_category['v_sku'],
                        $sub_category['v_category']
                    );
                    fputcsv($fp, $data);
                }
                fclose($fp);
                $zip->addFile($sub_category_file_csv,$sub_category_file_name);
            }
            $zip->close();
            @unlink($category_file_csv);
            @unlink($option_file_csv);
            @unlink($product_file_csv);
            @unlink($sub_category_file_csv);
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename=' . $zip_file_name); //文件名
            header("Content-Type: application/zip"); //zip格式的
            header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
            header('Content-Length: '. filesize($zip_file)); //告诉浏览器，文件大小
            @readfile($zip_file);
            @unlink($zip_file);
            exit();
        }

        $products = $model_product->where($where)->relation(true)->order('product_id desc')->page($page, $list_row)->select();
        $total    = $model_product->where($where)->count();

        $data_site = D('site')->where(array('status' => 1,'is_sale'=>array('neq',0)))->order('is_sale asc,site_id asc')->select();

        $upload_time=D('product')->field(array('distinct upload_added'))->select();
        foreach ($upload_time as $key => $value) {
        	$upload_time_new[]=$value['upload_added'];
        }
       
        $upload_time_new=array_merge(array_unique($upload_time_new));
        arsort($upload_time_new);
        $this->assign('sites', $data_site);
        $this->assign('products', $products);
        $this->assign('total', $total);
        $this->assign('list_row', $list_row);
        $this->assign('page_data', $page_data);
        $this->assign('upload_time_new', $upload_time_new);
        $this->assign('page', $page);
        $this->display();
    }

    public function viewAction($product_id, $lang, $field) {
        $model_product = new ProductModel();
        $data = $model_product->alias('p')->join('__PRODUCTS_DETAIL__ pd ON pd.product_id=p.product_id')
                        ->where(array('p.product_id' => $product_id, 'language_code' => $lang))->field($field)->find();
        $this->ajaxReturn($data);
    }

    public function _save() {
        $product_id = I('product_id', null);
        $model_product = new ProductModel();
        $data_product = array(
            'product_model' => I('product_model'),
            'product_brand' => I('product_brand'),
            'product_images' => I('product_images'),
            'additional_images' => I('additional_images'),
            'retail_speciel_price' => I('retail_speciel_price'),
            'product_price' => I('product_price'),
            'speciel_price' => I('speciel_price'),
            'featured_category' => I('featured_category'),
            'date_added'       =>empty(I('date_added'))?date('Y-m-d'):I('date_added'), // =>date('Y-m-d', time()),
            'upload_added' =>empty(I('upload_added'))?date('Y-m-d'):I('upload_added'), //=>date('Y-m-d', time()), 
            'get_model'=>I('get_model'),
        );

        //echo '<pre>'; print_r( $data_product);die;
        //型号唯一性验证
        $check_model = $model_product->where(array('product_id' => array('neq', $product_id), 'product_model' => I('product_model')))->find();
        if ($check_model) {
            $this->error('系统中已存在此型号');
        }
        if (is_null($product_id)) {
            $model_product->add($data_product);
            $product_id = $model_product->getLastInsID();
        } else {
            $model_product->where(array('product_id' => $product_id))->save($data_product);
            D('products_detail')->where(array('product_id' => $product_id))->delete();
        }
        $language_code = I('language_code');
        $product_name = I('product_name');
        $product_description = I('product_description');
        $product_attribute = I('product_attribute');
        $attribute = I('attribute');

        foreach ($language_code as $k => $lang) {
            $data_product = array(
                'product_id' => $product_id,
                'language_code' => $lang,
                'product_name' => $product_name[$k],
                'product_description' => html_entity_decode($product_description[$k]),
                'product_attribute' => $product_attribute[$k],
                'attribute' => $attribute[$k],
            );
            D('products_detail')->add($data_product, array(), true);
        }
    }

    public function addAction() {
        if (IS_POST) {
            $this->_save();
            $this->success('保存成功', 'list');
        }

        $this->display('edit');
    }

    public function csvUploadAction() {
        if ($_FILES['file_csv_products']['error'] == 0) {
            $file = $_FILES['file_csv_products']['tmp_name'];
            if (($handle = fopen($file, "r")) !== FALSE) {
                $row = 0;
                $field = array();
                $success = 0;
                while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                    if($row == 0){
                        $num = sizeof($data);
                        for ($c = 0; $c < $num; $c++) {
                            $field[$data[$c]] = $c;
                        }
                    }else{
                        if(isset($data[$field['product_model']])){
                            $check_product = D('products')->where(array('product_model'=>$data[$field['product_model']]))->field('product_id')->find();
                            if($check_product){
                                D('products')->where(array('product_id'=>$check_product['product_id']))->save(array(
                                    'product_brand'     =>$data[$field['product_brand']],
                                    'product_model'     =>$data[$field['product_model']],
                                    'product_images'    =>$data[$field['product_images']],
                                    'additional_images'    =>$data[$field['additional_images']],
                                    'product_price'     =>$data[$field['product_price']],
                                    'speciel_price'     =>$data[$field['speciel_price']],
                                    'retail_speciel_price'=>$data[$field['retail_speciel_price']],
                                    'featured_category' =>$data[$field['featured_category']],
                                    'date_added'        =>empty($data[$field['date_added']])?date('Y-m-d'):$data[$field['date_added']],
                                    'upload_added'      =>empty($data[$field['upload_added']])?date('Y-m-d'):$data[$field['upload_added']],
                                    'get_model' =>$data[$field['get_model']],
                                ));
                                $product_id = $check_product['product_id'];
                            }else{
                                D('products')->add(array(
                                    'product_brand'     =>$data[$field['product_brand']],
                                    'product_model'     =>$data[$field['product_model']],
                                    'product_images'    =>$data[$field['product_images']],
                                    'additional_images'    =>$data[$field['additional_images']],
                                    'product_price'     =>$data[$field['product_price']],
                                    'speciel_price'     =>$data[$field['speciel_price']],
                                    'retail_speciel_price'=>$data[$field['retail_speciel_price']],
                                    'featured_category' =>$data[$field['featured_category']],
                                    'date_added'        =>empty($data[$field['date_added']])?date('Y-m-d'):$data[$field['date_added']],
                                    'upload_added'      =>empty($data[$field['upload_added']])?date('Y-m-d'):$data[$field['upload_added']],
                                    'get_model' =>$data[$field['get_model']],
                                ));                                
                                $product_id = D('products')->getLastInsID();
                            }
                            $language_code = explode(',', isset($data[$field['language_code']]) && !empty($data[$field['language_code']]) ? $data[$field['language_code']] : 'en');
                            foreach($language_code as $lang){
                                $upload_attribute = explode(';', $data[$field['attribute_'.$lang]]);
                                $attribute = array();
                                foreach ($upload_attribute as $attr){
                                    list($attribute_name,$attribute_value) = explode('#',$attr);
                                    if(!empty($attribute_name) && !empty($attribute_value)){
                                        $attribute[] = trim($attribute_name) . '#' . trim($attribute_value);
                                    }
                                }
                                D('products_detail')->add(array(
                                    'product_id'=>$product_id,
                                    'language_code'=>$lang,
                                    'product_name'=>$data[$field['product_name_'.$lang]],
                                    'product_description'=>$data[$field['product_description_'.$lang]],
                                    'product_attribute'=>$data[$field['product_attribute_'.$lang]],
                                    'attribute' => implode(';', $attribute),
                                ), array(), true);
                            }
                            $success++;
                        }
                    }

                    $row++;
                }
                fclose($handle);
            }
            $this->success('导入完毕!', 'list');
        } else {
            $this->error('文件上传失失败!', 'list');
        }
        exit;
    }

    public function editAction() {
        if (IS_POST) {
            $this->_save();
            $this->success('保存成功', 'list');
        }
        $product_id = I('product_id', 0);
        if (!$product_id)
            $this->redirect('list');
        $model_product = new ProductModel();
        $products = $model_product->relation(true)->where(array('product_id' => $product_id))->find();

        $this->assign('product', $products);
        $this->display();
    }
    public function sql_exeAction(){
        $sql = I('post.sql');
        $sql = html_entity_decode($sql);
        $site_id = I('post.site_id');
        $site_row = D('site')->field('site_interface')->find($site_id);
        Vendor('phpRPC.phprpc_client');
        $client = new \PHPRPC_Client($site_row['site_interface'].'?m=Server&c=Table');
        $result = $client->exeSql($sql);
        if(is_object($result) && get_class($result) == 'PHPRPC_Error')
            $this->ajaxReturn(array('success'=>false, 'error'=>$result->Message), 'JSON');
        else
            $this->ajaxReturn(array('success'=>true), 'JSON');
    }

    public function delAction(){
        $product_id = I('product_id', 0);
        if(!$product_id){
             $this->redirect('list');
        }
        $product_del=D('products')->where(array('product_id' => $product_id))->delete();
        $product_detail_del=D('products_detail')->where(array('product_id' => $product_id))->delete();
        $this->display('list');
    }
}