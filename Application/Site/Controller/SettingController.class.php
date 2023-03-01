<?php
namespace Site\Controller;

use Think\Controller;
use Site\Model\SiteModel;

class SettingController extends Controller {
    public function downAction($site_id){
        $site_row = D('site')->field('site_interface')->find($site_id);
        Vendor('phpRPC.phprpc_client');
        $client = new \PHPRPC_Client($site_row['site_interface'].'?m=Server&c=Table');
        $where = array(
                'configuration_key|configuration_key|configuration_key|configuration_key|configuration_key|configuration_key|configuration_key|configuration_key' => array(
                    array('like', '%MODULE_PAYMENT_SECURITY_PINGPONG%'),
                    array('like', '%MODULE_PAYMENT_ZDCHECKOUT%'),
                    array('like', '%MODULE_PAYMENT_RXHPAY%'),
                    array('like', '%MODULE_PAYMENT_MONEYTRANSFERS%'),
                    array('like', '%MODULE_PAYMENT_WESTERNUNION%'),
                    array('like', '%MODULE_PAYMENT_WIRE%'),
                    array('like', '%MODULE_PAYMENT_MONEYGRAM%'),
                    array('like', '%MODULE_PAYMENT_TPO%'),
                    '_multi' => true,
                )                
        );
        $result = $client->_down('configuration', $where, 1, 99999);
        // echo $result;exit;
        if (is_object($result) && get_class($result) == 'PHPRPC_Error') {
            $this->ajaxReturn(array('status' => 0, 'error'=>$result->toString()), 'JSON');
        }
        $data = uncompress_decode($result);
        D('configuration')->delete(array('where' => array('site_id' => $site_id)));
        foreach($data as $row){
            $row['site_id'] = $site_id;
            D('configuration')->add($row, array(), true);
        }
        $this->ajaxReturn(array('status' => 1), 'JSON');
    }
}