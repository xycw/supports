<?php
namespace Order\Controller;
use Common\Controller\CommonController;

class PaymentModuleStatisticsController extends CommonController {
    
    public function indexAction() {
        
        $where = array();
        if(I('date_start', '')==='' || I('date_end', '')===''){
            $date_end   = date('Y-m-d');
            $t = 86400;//默认显示最近1天的统计数据
            $date_start = date('Y-m-d', strtotime($date_end)-$t);
        }else{
            $date_start   = I('date_start');
            $date_end   = I('date_end');
        }
        $where['date'] = array('between', array($date_start, $date_end));
        if(I('site_type', '')!=''){
            $where['site_type'] = I('site_type');
            $this->assign('site_type_selected', I('site_type'));
        }
        if(I('site_id', false)!=false){
            $where['s.site_id'] = array('IN', I('site_id'));
            $this->assign('site_id_selected', I('site_id'));
        }else
            $this->assign('site_id_selected', array());  
        if(I('payment_module', false)!=false){
            $where['payment_module'] = array('IN', I('payment_module'));
            $this->assign('payment_module_selected', I('payment_module'));
        }      
        
        $statistic_type = I('statistic_type', 1);
        $this->assign('statistic_type_selected', $statistic_type);

        if($statistic_type==1)        
            $rows = M('payment_module_statistics')->alias('p')->join(array('__SITE__ s ON s.site_id=p.site_id'))->where($where)->order('date desc')->field('p.*,s.site_name,s.type')->select();
        else
            $rows = M('payment_module_statistics')->alias('p')->join(array('__SITE__ s ON s.site_id=p.site_id'))->where($where)->group('date,site_type,payment_module')->order('date desc')->field('p.date,p.site_type,p.payment_module,sum(num_pending) as num_pending,sum(num_success) as num_success,sum(num_failure) as num_failure')->select();
            
        $list = array();
        $i = strtotime($date_start);
        $j = strtotime($date_end);
        for($i;$i<=$j;$j=$j-86400){
            $d = date('Y-m-d', $j);
            $list[$d] = array();
        }
        foreach($rows as $row){
            $d = date('Y-m-d', strtotime($row['date']));
            $list[$d][] = $row;
        }
        $options_site_name = array();
        $data_site = M('site')->where(array('status'=>1))->order('site_id asc')->select();
        if ($data_site){
            foreach ($data_site as $row){
                $options_site_name[$row['type']][$row['site_id']]='#'.$row['site_id'].'-'.$row['site_name'];
            }
        }

        $this->assign('option_site_name', $options_site_name);
        $this->assign('option_site_type', array('1'=>'独立站','10'=>'平台站'));
        $this->assign('option_payment_module', array('custom'=>'custom','moneytransfers'=>'moneytransfers','pingpong'=>'pingpong','rxhpay_inline'=>'rxhpay_inline','security_pingpong'=>'security_pingpong','zdcheckout2f3d'=>'zdcheckout2f3d','zdcheckout3f'=>'zdcheckout3f'));
        $this->assign('date_end', $date_end);
        $this->assign('date_start', $date_start);
        $this->assign('option_statistic_type', array('1'=>'按日期,网站类型,网站,接口统计','10'=>'按日期,网站类型,接口统计'));
        $this->assign('list', $list);
        $this->display();
    }
    
    public function updateAction($date){
        
        $date_start = date('Y-m-d', strtotime($date)).' 0:0:0';
        $date_end   = date('Y-m-d', strtotime($date)).' 23:59:59';
        $list = M('orders')->alias('o')->join(array('__ORDERS_REMARK__ o_r ON o.orders_id=o_r.orders_id AND o.site_id=o_r.site_id', 'LEFT JOIN __SITE__ s ON s.site_id=o.site_id'))->where(array('date_purchased'=>array('between', array($date_start, $date_end))))->select();
        
        $statusSwigch = array(
            // '待处理' => '',
            // '付款失败or未付款' => '',
            // '付款确认中' => 'pending',
            '已确认付款' => 'success',
            '待订货' => 'success',
            '已订货' => 'success',
            '待发货' => 'success',
            '部分发货' => 'success',
            '已发货' => 'success',
            '订单取消' => 'failure',
            '老订单' => 'pending',
        );
        
        /*
        订单去重
        前提：一天内，相同邮箱，相同sub-total,相同接口
        1、多个订单，只有一个成功的，保留成功订单
        2、多个订单，无成功的,只保留一张失败订单
        3、多个订单，多个成功，全保留
        4、多个订单，没有成功和失败，都是pending，那直接保留那一单pending
        */
        
        //一、订单整理
        $orders = array();
        foreach($list as $row){
            
            if(isset($statusSwigch[$row['order_status_remark']])){
                $status = $statusSwigch[$row['order_status_remark']];
            }else{
                if($row['orders_status_name']=='支付成功' || stripos($row['orders_status_name'], 'success')!==false){
                    $status = 'success';
                }elseif($row['orders_status_name']=='未支付' || stripos($row['orders_status_name'], 'pending')!==false){
                    $status = 'pending';
                }else{
                    $status = 'failure';
                }
            }
            // echo $row['order_status_remark']."=".$status."\n";
            $sub_total = '';
            if($row['type']==1 && !empty($row['order_total_detail'])){
                $order_total_detail = json_decode($row['order_total_detail'], true);
                foreach($order_total_detail as $entry){
                    if(strtolower($entry['title'])=='sub-total:'){
                        $sub_total = $entry['value'];
                    }
                }
            }
            if($sub_total==''){
                $sub_total = $row['order_total']-$row['shipping_cost'];   
            }
            
            $orders[$row['customers_email_address']][$row['site_id']][$row['payment_module_code']][$sub_total][$status][] = $row;
        }

        $statistics_data = array();
        //二、订单统计
        foreach($orders as $email_address=>$orders1){
            foreach($orders1 as $site_id=>$orders2){
                foreach($orders2 as $payment_module_code=>$orders3){
                    foreach($orders3 as $sub_total=>$orders4){
                        if(isset($orders4['success'])){
                            foreach($orders4['success'] as $row){
                                if(!isset($statistics_data[$row['type']][$row['site_id']][$row['payment_module_code']])){
                                    $statistics_data[$row['type']][$row['site_id']][$row['payment_module_code']] = array(
                                        'pending'=>0,
                                        'failure'=>0,
                                        'success'=>0,
                                    );
                                }
                                
                                $statistics_data[$row['type']][$row['site_id']][$row['payment_module_code']]['success'] +=1;
                            }
                        }elseif(isset($orders4['failure'])){
                            foreach($orders4['failure'] as $row){
                                if(!isset($statistics_data[$row['type']][$row['site_id']][$row['payment_module_code']])){
                                    $statistics_data[$row['type']][$row['site_id']][$row['payment_module_code']] = array(
                                        'pending'=>0,
                                        'failure'=>0,
                                        'success'=>0,
                                    );
                                }
                                $statistics_data[$row['type']][$row['site_id']][$row['payment_module_code']]['failure'] =1;
                                break;
                            }
                        }elseif(isset($orders4['pending'])){
                            foreach($orders4['pending'] as $row){
                                if(!isset($statistics_data[$row['type']][$row['site_id']][$row['payment_module_code']])){
                                    $statistics_data[$row['type']][$row['site_id']][$row['payment_module_code']] = array(
                                        'pending'=>0,
                                        'failure'=>0,
                                        'success'=>0,
                                    );
                                }
                                $statistics_data[$row['type']][$row['site_id']][$row['payment_module_code']]['pending'] =1;
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        
        date_default_timezone_set('Asia/Shanghai');//设置时区
        if(sizeof($statistics_data)){
            foreach($statistics_data as $site_type=>$statistics1){
                foreach($statistics1 as $site_id=>$statistics2){
                    foreach($statistics2 as $payment_module=>$num){

                        $data = array(
                            'date'=>$date,
                            'site_type'=>$site_type,
                            'site_id'=>$site_id,
                            'payment_module'=>$payment_module,
                            'num_success'=>$num['success'],
                            'num_failure'=>$num['failure'],
                            'num_pending'=>$num['pending'],
                            'update_time'=>date('Y-m-d H:i:s')
                        );
                        
                        $where = array(
                            'date'=>$date,
                            'site_type'=>$site_type,
                            'site_id'=>$site_id,
                            'payment_module'=>$payment_module,                            
                        );
                        $check_row = M('payment_module_statistics')->where($where)->find();
                        if($check_row){
                            M('payment_module_statistics')->where($where)->save($data);
                        }else{
                            M('payment_module_statistics')->add($data);
                        } 
                    }
                }
            }
        }
        
        $this->ajaxReturn($statistics_data);
    }
}