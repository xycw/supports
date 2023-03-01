<?php
namespace Email\Controller;
use Think\Controller;
class TemplateController extends Controller {
    public function ListAction(){
        $email_template_model = new \Email\Model\EmailTemplateModel();
        $where = array();
        $list = $email_template_model->where($where)->select();
        
        $this->assign('list', $list);
        $this->display();
    }
    
    public function EditAction($id){
        $email_template_model = new \Email\Model\EmailTemplateModel();
        $where = array();
        
        if(IS_POST){
            $data = array(
                'email_template_id'=>$id,
                'email_template_title'=>I('title'),
                'email_template_content'=>I('content'),
                'status'=>I('status'),
            );
            $email_template_model->save($data); 
            
            $this->success('保存成功',U('Email/Template/List'));
            exit;
        }
        
        
        $row = $email_template_model->where($where)->find();
        $this->assign('data',$row);
        $this->assign('action','edit');
        $this->display();
    }
}