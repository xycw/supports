<?php
namespace Wordpress\Controller;

use Think\Controller;
use Site\Model\SiteModel;

class StatisticsController extends Controller {
    public function ListAction(){
    	
        
        $where = array('status'=>1,'type'=>10);
        if(''!=I('site_id', '')){
            $where['site_id'] = I('site_id');
            $this->assign('site_selected', I('site_id'));
        }
        
        $site_model = new SiteModel();
    	$site_list  = $site_model->where($where)->order('site_id asc')
                                 ->select();
        $site = array();
        foreach ($site_list as $entry){
            $site[$entry['site_id']] = $entry['site_name'];
        }
        
        $where = array();
        $date_start = I('date_start', date("Y-m-d",strtotime("-1 day")));
        $date_end   = I('date_end', date('Y-m-d'));
        $where['date'] = array('between', array($date_start, $date_end));
               
        $data_statistics = D('jump_statistics')->alias('js')
                ->join(array('__SITE__ s ON s.site_id=js.site_id'))
                ->where($where)
                ->field('js.*,s.site_name,s.remark')
                ->order('date desc,site_id asc')
                ->select();
        
        $this->assign('date_start', $date_start);
        $this->assign('date_end', $date_end);
    	$this->assign('data_statistics', $data_statistics);
        $this->assign('site', $site);
    	$this->display();
    }
    
   
}