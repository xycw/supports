<?php
namespace Server\Controller;
use Think\Controller\RpcController;
use Server\Model\Customers;

class CustomersController extends RpcController{
    
    public function down($site_id, $where=array()){
        $order = new OrderModel();
        $data  = $order->relation(true)->where($where)->select();
        foreach ($data as $i=>$o_entry){
            $o_entry['site_id'] = $site_id;
            if (isset($o_entry['product'])) {
                foreach ($o_entry['product'] as $j=>$p_entry){
                    $p_entry['site_id'] = $site_id;
                    if (isset($p_entry['attribute'])) {
                        foreach ($p_entry['attribute'] as $k=>$a_entry){
                            $a_entry['site_id'] = $site_id;
                            $p_entry['attribute'][$k] = $a_entry;
                        }
                    }
                    $o_entry['product'][$j] = $p_entry;
                }
            }
            	
            if (isset($o_entry['history'])) {
                foreach ($o_entry['history'] as $j=>$h_entry){
                    $h_entry['site_id'] = $site_id;
                    $o_entry['history'][$j] = $h_entry;
                }
            }
            	
            $data[$i] 				= $o_entry;
        }
        return $data;
    }
    
}