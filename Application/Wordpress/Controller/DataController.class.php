<?php

namespace Wordpress\Controller;

use Think\Controller;

class DataController extends Controller {

    private $num_package = 100; //每个包的订单数

    public function StatisticsAction($site_id) {
        $where = array();
        $page = I('page', 1);
        Vendor('phpRPC.phprpc_client');
        $interface_url = $this->getInterfaceUrl($site_id);
        $client = new \PHPRPC_Client($interface_url . '?m=wordpress&c=statistics');
        
        $result = $client->down($where, $page, $this->num_package);
        if (is_object($result) && get_class($result) == 'PHPRPC_Error') {
            $this->ajaxReturn(array('status' => 0, 'error'=>$result->toString()), 'JSON');
        }
        $data = uncompress_decode($result);
        if (is_array($data)) {
            $model_jump_statistics = D('jump_statistics');
            foreach ($data as $_data){
                $_data['site_id'] = $site_id;
                $model_jump_statistics->add($_data, array(), true);
            }            
            $this->ajaxReturn(array('status' => 1), 'JSON');
        } else {
            $this->ajaxReturn(array('status' => 0, 'error'=>'无法识别下载的数据!'), 'JSON');
        }
    }
    
    public function CountStatisticsAction($site_id) {
        Vendor('phpRPC.phprpc_client');
        $interface_url = $this->getInterfaceUrl($site_id);
        $client = new \PHPRPC_Client($interface_url . '?m=wordpress&c=statistics');
        $total = $client->count($site_id);
        if (is_object($total) && get_class($total) == 'PHPRPC_Error') {
            $this->ajaxReturn(array('status' => 0, 'error' => $total->toString()), 'JSON');
        }
        $num_page = ceil($total / $this->num_package);

        $this->ajaxReturn(array('status' => 1, 'num_page' => $num_page, 'total' => $total), 'JSON');
    }
    
    private function getInterfaceUrl($site_id) {
        $site_row = D('site')->field('site_interface')->find($site_id);

        return $site_row['site_interface'];
    }


}
