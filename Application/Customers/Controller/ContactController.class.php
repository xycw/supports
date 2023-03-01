<?php
namespace Customers\Controller;
use Common\Controller\CommonController;
use Site\Model\SiteModel;


class ContactController extends CommonController {
    public function listAction(){
        $page = I('page', 1);//当前页码
        $num  = 200;//每页显示记录数
        $site_model = new SiteModel();
        $where = array('status'=>array('eq',1));      
        if(!isset($_GET['is_sale']) || $_GET['is_sale']==""){
            $_GET['is_sale']=-1;
        }
        if($_GET['is_sale']!=-1){
            $where['is_sale']=$_GET['is_sale'];
            
        }
        $site_list  = $site_model->where($where)->order('type asc,site_id asc')->select();
        $site_id_str="";
        foreach ($site_list as $key => $value) {
             $site_id_str .= $value['site_id'].',';
        }
        $site_id_str=trim($site_id_str,',');
        $join = array('__SITE__ s ON s.site_id=c.site_id', 'LEFT JOIN __CONTACT_US_RECORDS_REMARK__ r ON r.site_id=c.site_id AND r.contact_us_records_id=c.contact_us_records_id WHERE s.site_id IN('.$site_id_str.')');
        $list = D('contact_us_records')->alias('c')->join($join)->field('c.*,s.site_name,r.state')->page($page, $num)->order('r_send_time desc')->select();
        

        $wheres['s.site_id'] = array('in',$site_id_str);
        $count = D('contact_us_records')->alias('c')->join('__SITE__ s ON s.site_id=c.site_id')->where($wheres)->count();
        
        $this->assign('page', $page);
        $this->assign('num', $num);
        $this->assign('count', $count);
        $this->assign('list',$list);
        $this->display();
    }

    public function viewAction($site_id, $contact_id){
        if(IS_AJAX) layout (false);
        $join = array('__SITE__ s ON s.site_id=c.site_id', 'LEFT JOIN __CONTACT_US_RECORDS_REMARK__ r ON r.site_id=c.site_id AND r.contact_us_records_id=c.contact_us_records_id');
        $contact_info = D('contact_us_records')->alias('c')
                ->join($join)
                ->where(array('c.site_id'=>$site_id, 'c.contact_us_records_id'=>$contact_id))
                ->field('c.*,s.site_name,r.state')
                ->find();

        $this->assign('contact', $contact_info);
        $this->display();
    }
    
    public function markAction($site_id, $contact_id, $state){
        D('contact_us_records_remark')->add(array('site_id'=>$site_id, 'contact_us_records_id'=>$contact_id, 'state'=>$state), array(), true);
        
        $this->ajaxReturn(array('state'=>1), 'JSON');
    }
}