<?php
namespace Sys\Controller;

use Common\Controller\CommonController;


class FileUploadController extends CommonController {

    function file_listAction() {
        $where = array('status'=>1, 'type'=>1);
        $site_list = D('site')->where($where)->order('type asc,site_id asc')->select();        
        $this->assign('site_list', $site_list);
        
        $file_list = array(); 
        $d = dir(DIR_FS_UPLOADS . 'zencart_file/');
        $now_time = time();
        while ($e = $d->read()) {
            if(preg_match('~\.zip$~', $e)){
                $filemtime = filemtime(DIR_FS_UPLOADS . 'zencart_file/'.$e);
                $s = 14400-($now_time-$filemtime);
                if($s>0)
                    $file_list[] = array('f'=>$e, 'e'=>$s);
                else 
                    unlink (DIR_FS_UPLOADS . 'zencart_file/'.$e);
            }
        }
        $d->close();
        arsort($file_list);
        
        $this->assign('file_list', $file_list);
        
        $this->display();
    }
    
    function _compare($file_compare, $site_id){
        $same = 1;
        $site_row = D('site')->field('site_interface')->find($site_id);
        Vendor('phpRPC.phprpc_client');
        $client = new \PHPRPC_Client($site_row['site_interface'].'?m=Server&c=File');
        $file_compare = $client->compare($file_compare);
        if(is_object($file_compare) && get_class($file_compare)=='PHPRPC_Error')
            return 0;
        if(is_array($file_compare)){        
            foreach ($file_compare as $file){
                if(!$file['same']){
                    $same = 0;
                    break;
                }
            }
        }
        return $same;
    }
    
    /*
     * 压缩包上传到网站
     */
    
    function transferAction($zip, $site_id){
        $site_row = D('site')->field('site_interface')->find($site_id);
        $site_interface = $site_row['site_interface'] . 'upload.php'; //升级接口
        $zip_filename = DIR_FS_UPLOADS . 'zencart_file/'.$zip;
        if (file_exists($zip_filename)) {
            $data = array(
                'file' => new \CURLFile(realpath($zip_filename))
            );
            vendor('Request.Requests');
            \Requests::register_autoloader();
            $response = \Requests::post($site_interface, array(), $data, array('timeout'=>60));
            if ($response->success) {
                if($data = json_decode($response->body, true)){
                    $this->ajaxReturn($data);    
                }else{
                    $this->ajaxReturn(array('error'=>'上传状态未知'));
                }
            }else{
                $this->ajaxReturn(array('error'=>'上传压缩包传输失败'));
            }
        }else {
            $this->ajaxReturn(array('error'=>'系统中没有找到指定的压缩包'));
        }
    }
    /*
     * 压缩包中的文件与网站中文件对比
     */
    function compareAction($zip, $site_id){
        $zip_file = DIR_FS_UPLOADS . 'zencart_file/'.$zip;
        if(file_exists($zip_file)){
            if(file_exists($zip_file.'.md5')){//判断文件md5缓存是否存在
                $cache_last_time = filemtime($zip_file.'.md5');
                $zip_last_time   = filemtime($zip_file);
                if($cache_last_time>$zip_last_time){
                    $cache_content = file_get_contents($zip_file.'.md5');
                    $file_compare = json_decode($cache_content, true);
                    $same = $this->_compare($file_compare, $site_id);
                    $this->ajaxReturn(array('same'=>$same), 'json');
                }
            }
            $content = file_get_contents($zip_file);
            $content = authcode($content, 'DECODE', '1234567');
            $f = fopen($zip_file.'.temp', 'wb');
            fwrite($f, $content);
            fclose($f);
            $zip = new \ZipArchive;
            if (true===$zip->open($zip_file.'.temp')) {
                $file_compare = array();
                for ($i=0; $i<$zip->numFiles;$i++) {
                    $stat  = $zip->statIndex($i);
                    $is_dir = (substr($stat['name'], -1, 1) == '/');
                    if(!$is_dir){
                        $content = $zip->getFromIndex($i);
                        $md5 = md5($content);
                        $file_compare[] = array('file'=>$stat['name'], 'md5'=>$md5);
                    }
                }
                $zip->close();
                unlink($zip_file.'.temp');
                $md5_cache = $zip_file.'.md5';
                $f = fopen($md5_cache, 'wb');
                fwrite($f, json_encode($file_compare));
                fclose($f);
                
                $same = $this->_compare($file_compare, $site_id);
                $this->ajaxReturn(array('same'=>$same), 'json');
            } else {
                unlink($zip_file.'.temp');
                $this->ajaxReturn(array('same'=>0, 'msg'=>'无效的压缩包'), 'json');
            }
        }else{
            $this->ajaxReturn(array('same'=>0, 'msg'=>'压缩包不存在'), 'json');
        }
    }
    /*
     * 上补补丁到服务端
     */
    function uploadAction() {
        if (isset($_FILES['file'])) {
            if ($_FILES['file']['error'] == UPLOAD_ERR_OK) {
                $upload = new \Think\Upload();
                $upload->maxSize = 10485760; //  设置附件上传大小
                $upload->hash = false;
                $upload->replace = true;
                $upload->autoSub = false;
                $upload->rootPath = DIR_FS_UPLOADS;
                $upload->savePath = 'zencart_file/'; //设置上传目录
                $upload->exts = array('zip'); //  设置附件上传类型     

                $upload->saveName = basename($_FILES['file']['name'], '.zip');

                $file = array(
                    'name' => $_FILES['file']['name'],
                    'type' => $_FILES['file']['type'],
                    'tmp_name' => $_FILES['file']['tmp_name'],
                    'error' => $_FILES['file']['error'],
                    'size' => $_FILES['file']['size'],
                );
                $upload_info = $upload->upload(array('file' => $file));
                if (!$upload_info) {//  上传错误提示错误信息
                    $this->error($upload->getError());
                }else{
                    $zip_file = $upload->rootPath . $upload->savePath . $upload->saveName .'.zip';
                    $s = $this->_encryption($zip_file);
                    $f = fopen($zip_file, 'wb');
                    fwrite($f, $s);
                    fclose($f);                    
                }
                $this->success('上传成功!');
            }
        } else {
            $this->error('上传失败!');
        }
    }   
    
    private function _encryption($filename) {
        $f = fopen($filename, 'rb');
        $content = fread($f, filesize($filename));
        fclose($f);
        return authcode($content, 'ENCODE', '1234567');
    }    
}