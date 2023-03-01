<?php
namespace Product\Controller;
use Common\Controller\CommonController;
use Order\Model\OrderModel;

class DataController extends CommonController {

    public function getCategoryAction($site_id) {
        Vendor('phpRPC.phprpc_client');
        $interface_url = $this->getInterfaceUrl($site_id);
        
        try{
            $client = new \PHPRPC_Client($interface_url . '?m=Server&c=Product');
            
            $category = $client->getCategory();//array(分类id=>分类名称)
             
            if (is_object($category) && get_class($category) == 'PHPRPC_Error') {
                $this->ajaxReturn(array('status' => 0, 'error' => $category->toString()), 'JSON');
            }
        } catch (PHPRPC_Error $e){
            $this->ajaxReturn(array('status' => 0, 'error' => $e->toString()), 'JSON');
        }
        $this->ajaxReturn(array('status' => 1, 'category'=>$category), 'JSON');
    }
    
    
    private function _upload($data_upload, $site_id, $category, $status){
        $data_string = encode_compress($data_upload);
        $upload_file = DIR_FS_TEMP . time() . '_update_product.update_product';
        $f = fopen($upload_file, 'w');
        fwrite($f, $data_string);
        fclose($f);

        if(version_compare(PHP_VERSION, '5.5.0') >= 0){
            $data = array(
                'file' => new \CURLFile($upload_file),
                'category'=>$category,
                'status'=>(int)$status,
            );
        }else{
            $data = array(
                'file' => '@'.$upload_file,
                'category'=>$category,
                'status'=>(int)$status,
            );
        }
        
        vendor('Request.Requests');
        
        \Requests::register_autoloader();
        $interface_url = $this->getInterfaceUrl($site_id);
        $response = \Requests::post($interface_url . '?m=Server&c=ProductUpdate', array(), $data, array('timeout'=>60));

        //if(file_exists($upload_file)) unlink ($upload_file);
        $result = json_decode($response->body, true);

        return is_array($result)?$result:false;

    }
    
    public function uploadAction(){
        $product = I('product');
        $data_upload = array();
        foreach($product as $entry){
            list($product_id, $language_code) = explode('_', $entry);
            $row = D('products')->alias('p')->join('__PRODUCTS_DETAIL__ pd ON p.product_id=pd.product_id')
                    ->where(array('pd.product_id'=>$product_id, 'language_code'=>$language_code))->find();
            $data_upload[] = $row;
        }
        $result = $this->_upload($data_upload, I('site_id'), I('category'), I('action_product_upload'));
        if($result===false)
            $result = array('status'=>0, 'error'=>'上传超时');
        $this->ajaxReturn($result, 'JSON');
    }
    
    public function upload2Action(){
        $language_code = I('language_code', '');
        $date_added    = I('date_added', '');
        $upload_added  = I('upload_added','');
        $products_sku  = I('products_sku','');
        $where = array();
        if($language_code!=''){
            $where['pd.language_code'] = array('IN', explode(',', $language_code));
        }
        if($date_added!=''){
            $where['p.date_added'] = $date_added;
        }
        if($upload_added!=''){
            $where['p.upload_added'] = $upload_added;
        }        
        if($products_sku!=''){
            $where['p.product_model'] = $products_sku;
        }         
        $limit = 100;
        if(I('action')=='count'){
            $count = D('products')->alias('p')->join('__PRODUCTS_DETAIL__ pd ON pd.product_id=p.product_id')->where($where)->count();
            $num_page = ceil($count/$limit);
            $url      = ($num_page>0?U('Product/Data/upload2', array('language_code'=>$language_code, 'date_added'=>$date_added,'upload_added'=>$upload_added, 'page'=>1)):'');
            if($num_page==0)
                $tip = '当前筛选条件没有可上传的产品!';
            else{
                $tip = '当前筛选条件共找到'.$count.'个产品上传!系统将分'.$num_page.'批次上传,每批上传'.$limit.'个';
            }
            $this->ajaxReturn(array('tip'=>$tip, 'num_page'=>$num_page), 'JSON');
        }elseif(I('page', false)){
            $page = I('page');
            $count = D('products')->alias('p')->join('__PRODUCTS_DETAIL__ pd ON pd.product_id=p.product_id')->where($where)->count();
            $num_page = ceil($count/$limit);
            if($page<=$num_page){
                $data_upload = D('products')->alias('p')->join('__PRODUCTS_DETAIL__ pd ON pd.product_id=p.product_id')->where($where)->page($page, $limit)->select();
                $result = $this->_upload($data_upload, I('site_id'), I('category'), I('action_product_upload'));
                if($result===false)
                    $result = array('success'=>false, 'tip'=>'上传超时');
                else{
                    if($result['status']){
                        $success = true;
                        $tip = '第'.$page.'批产品上传成功!';
                        foreach($result['result'] as $entry){
                            if(isset($entry['error'])){
                                $success = false;
                                $tip = '第'.$page.'批产品上传失败(可能有部分产品上传不成功)!';
                                break;
                            }
                        }
                    } else {
                        $success = false;
                        $tip = '第'.$page.'批产品上传失败!';
                    }
                }
                $this->ajaxReturn(array('tip'=>$tip, 'cur_page'=>$page, 'success'=>$success), 'JSON');
            }else{
                $this->ajaxReturn(array('tip'=>'页码错误', 'cur_page'=>''), 'JSON');
            }            
        }
    }


    private function getInterfaceUrl($site_id) {
        $site_row = D('site')->field('site_interface')->find($site_id);
        $site_index = rtrim($site_row['site_index'], '/zc_api/');
        return $site_row['site_interface'];
    } 
}