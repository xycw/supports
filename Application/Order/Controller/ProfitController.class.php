<?php
namespace Order\Controller;
use Common\Controller\CommonController;
use Order\Model\OrderModel;

class ProfitController extends CommonController {
	
	public function listAction(){
		$order = new OrderModel();
		
		$join  = array(
			'LEFT JOIN __SITE__ s ON s.site_id=o.site_id',
			'JOIN __ORDERS_REMARK__ o_r ON o_r.site_id=o.site_id AND o_r.orders_id=o.orders_id'
		);
		$where = array(
			'date_purchased'=>array('between', array(I('date_start'), I('date_end'))),
			'_complex'=>array(
				'_logic'=>'OR',	
				'order_status_remark'=>'已发货',
				'_complex'=>array(
                                    'order_status_remark'=>'取消',
                                    'customer_feedback'=>'全额退款'	
				)					
			)
		);
		if(I('payment_method','')=='not-credit-card'){
		    $where['payment_method'] = array('neq','Credit Card Payment');
		}elseif(I('payment_method','')=='credit-card'){
		    $where['payment_method'] = 'Credit Card Payment';
		}
		$profit_list = $order->alias('o')->relation(array('order_remark','site','product'))->join($join)->where($where)->select();
// echo $profit_list;exit;
		
		$where_yidinghuo = array(
			'date_purchased'=>array('between', array(I('date_start'), I('date_end'))),
			'order_status_remark'=>'已订货'
		);
		$yidinghuo_num = $order->alias('o')->relation(false)->join($join)->where($where_yidinghuo)->count();
		
		vendor('zencartManagement.zencartManagementAutoload');
		$payment = new \payment();
		
		$this->assign('profit_list', $profit_list);
		$this->assign('payment', $payment);
		$this->assign('yidinghuo_num', $yidinghuo_num);
		$this->display();
	}
	
}