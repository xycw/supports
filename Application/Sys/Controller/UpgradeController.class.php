<?php

namespace Sys\Controller;

use Common\Controller\CommonController;
use Site\Model\SiteModel;

class UpgradeController extends CommonController {

    function interface_versionAction($site_id) {
        layout(false);

        $site_row = D('site')->field('site_interface')->find($site_id);
        $site_version_url = $site_row['site_interface'] . 'version.php';//升级接口

        vendor('Request.Requests');
        \Requests::register_autoloader();
        $response = \Requests::get($site_version_url);
        
        if ($response->success) {
            $version_string = $result = $response->body;
            //var_dump($version_string);exit;
            if($version_array= json_decode($version_string, true)){
                if (preg_match('~^\d+$~', $version_array['version']))
                    $this->ajaxReturn (array('version'=>$version_array['version']));
                else
                    $this->ajaxReturn (array('version'=>'000000000000'));
            }else{
                $this->ajaxReturn (array('version'=>'000000000000'));
            }
        }else{
            $this->ajaxReturn (array('version'=>'000000000000'));
        }
    }

    function upgradeAction($site_id, $patch_file) {
        $site_row = D('site')->field('site_interface')->find($site_id);
        $site_interface = $site_row['site_interface'] . 'upgrade.php'; //升级接口
        $patch_filename = DIR_FS_UPLOADS . 'Patch/'.$patch_file;

//        $encryption_string = $this->_encryption($patch_filename);
        if (file_exists($patch_filename)) {
            $data = array(
                'file' => new \CURLFile(realpath($patch_filename))
            );
            vendor('Request.Requests');
            \Requests::register_autoloader();

            $response = \Requests::post($site_interface, array(), $data, array('timeout'=>60));

            if ($response->success) {
                if($data = json_decode($response->body, true)){
                    $this->ajaxReturn($data);    
                }else{
                    $this->ajaxReturn(array('error'=>'升级状态未知'));
                }
            }else{
                $this->ajaxReturn(array('error'=>'升级包传输失败'));
            }
        }else {
            $this->ajaxReturn(array('error'=>'系统中没有找到指定的升级包'));
        }
    }

    //补丁列表
    function patch_listAction() {
        $site_model = new SiteModel();
        $where = array('status'=>1,'type'=>1);
        $site_list = $site_model->where($where)->order('type asc,site_id asc')->select();

        $patch_dir = DIR_FS_UPLOADS . 'Patch/';

        $patch_list = array();
 
        $d = dir($patch_dir);
        while ($e = $d->read()) {
            if (preg_match('~\d{12}\.zip~', $e)) {
                $patch_list[] = $e;
            }
        }
        $d->close();
        arsort($patch_list);
        
        $this->assign('patch_list', $patch_list);
        $this->assign('site_list', $site_list);
        
        $this->display();
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
                $upload->savePath = 'Patch/'; //设置上传目录
                $upload->exts = array('zip'); //  设置附件上传类型     

                if (!preg_match('~^\d{12}\.zip$~', $_FILES['file']['name'])) {
                    $this->error('更新包名称不正确!');
                }
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
                $this->success('更新包上传成功!');
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

    private function _decryption($filename) {
        $f = fopen($filename, 'rb');
        $content = fread($f, filesize($filename));
        fclose($f);
        return authcode($content, 'DECODE', '1234567');
    }

}
