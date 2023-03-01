<?php
namespace Server\Controller;

use Common\Controller\CommonController;


class RemoteTableController extends CommonController {

    public $allowMethodList = array('__call');
    
    private $_limit = 300;//获取数据第页多少条
      
    public function __call($method, $args) {
        if(I('get.site_id', false)===false)
            E('Less Data GET[\'site_id\'] or POST[\'site_id\']');
        
        $site_id = I('get.site_id');
        if(preg_match('~^count_([\w\W\d_]+)Action$~i', $method, $match)){
            $table = strtolower($match[1]);            
            return call_user_func_array(array($this, '_count'), array($table, $site_id));
        }elseif(preg_match('~^down_([\w\W\d_]+)Action$~i', $method, $match)){
            $table = strtolower($match[1]);
            $where = array();
            $page  = I('get.page', 1);
            $limit = $this->_limit;
            return call_user_func_array(array($this, '_down'), array($table, $site_id, $where, $page, $limit));
        }else{
            E(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
        }
    }

    private function _down($table, $site_id, $where, $page, $limit){
        try{
            Vendor('phpRPC.phprpc_client');
            $site_row = D('site')->field('site_interface')->find($site_id);
            $client = new \PHPRPC_Client($site_row['site_interface'] . '?m=Server&c=Table');
            $data = call_user_func_array(array($client, 'down_'.$table), array($where, $page, $limit));
            if (is_object($data) && get_class($data) == 'PHPRPC_Error') {
                $this->ajaxReturn(array('status' => 0, 'error' => $count_online->toString()), 'JSON');
            }            
        } catch (PHPRPC_Error $e){
            $this->ajaxReturn(array('status' => 0, 'error' => $e->toString()), 'JSON');
        }
        $data = uncompress_decode($data);

        if (is_array($data)) {
            foreach ($data as $entry){
                $entry['site_id'] = $site_id;
                D($table)->add($entry, array(), true);
            }
            $this->ajaxReturn(array('status' => 1), 'JSON');
        }else {
            $this->ajaxReturn(array('status' => 0, 'error' => '无法识别下载的数据!'), 'JSON');
        }
    }

    private function _count($table, $site_id){
        try{
            Vendor('phpRPC.phprpc_client');
            $site_row = D('site')->field('site_interface')->find($site_id);
            $client = new \PHPRPC_Client($site_row['site_interface'] . '?m=Server&c=Table');
            $count_online = call_user_func(array($client, 'count_'.$table));
            if (is_object($count_online) && get_class($count_online) == 'PHPRPC_Error') {
                $this->ajaxReturn(array('status' => 0, 'error' => $count_online->toString()), 'JSON');
            }            
        } catch (PHPRPC_Error $e){
            $this->ajaxReturn(array('status' => 0, 'error' => $e->toString()), 'JSON');
        }
        $count_local   = D($table)->where(array('site_id'=>$site_id))->count();
        $pages_online  = ceil($count_online / $this->_limit);
        $pages_to_down = ceil(($count_online-$count_local)/$this->_limit);
        if($pages_to_down==0){
            $string_page = $pages_online;
        }else{
            for($i=0;$i<$pages_to_down;$i++){
                $string_page .= ','.($pages_online-$i);
            }
            $string_page = substr($string_page, 1);
        }
        
        $this->ajaxReturn(array('status' => 1, 'num_page' => $pages_online, 'total' => $count_online, 'total_sys'=>$count_local, 'page_down'=>$string_page), 'JSON');
    }
}
