<?php
namespace Sys\Controller;
use Common\Controller\CommonController;

class SqlExeController extends CommonController {
    
    function indexAction(){
        $where = array('status'=>1,'type'=>1);
        $site_list = D('site')->where($where)->order('type asc,site_id asc')->select();        
        $this->assign('site_list', $site_list);
        
        $this->display();
    }
    
    function runAction(){
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
}


