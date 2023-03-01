<?php
namespace User\Controller;

use Common\Controller\CommonController;
use User\Model\UserModel;

class UserController extends CommonController {

    public function userListAction() {
        if(session(C('USER_INFO').'.profile_id')==1)
            $this->redirect('User/Administration/userList');
        else
            $this->redirect('User/User/edit');
    }
    
    protected function _save($data){
        if($data['username']=='') $this->error('请输入登录名!');
        if($data['chinese_name']=='') $this->error('请输入中文名!');
        if($data['english_name']=='') $this->error('请输入英文名!');
        
        if(isset($data['user_id']) && $data['user_id']>0){
            D('user')->where(array('user_id'=>$data['user_id']))->save($data);
        }else{
             D('user')->add($data);
        }
    }


    public function editAction(){
        $user_id = session(C('USER_INFO').'.user_id');
        
        if(IS_POST){
            $row = D('user')->where(array('username'=>I('username'), 'user_id'=>array('neq', $user_id)))->find();
            if(empty($row)==false)
                $this->error('系统已存此登录名!');
            
            $post_mail_template_params = I('mail_template_params');
            $params = array();
            foreach($post_mail_template_params['key'] as $k=>$v){
                $param_name  = $v;
                $param_value = $post_mail_template_params['value'][$k];
                $param_remark = $post_mail_template_params['remark'][$k];
                if(!empty($param_name) && !empty($param_value)){
                    $params[] = array(
                        'key'=>$param_name,
                        'value'=>$param_value,
                        'remark'=>$param_remark,
                    );
                }
                
            }
            $data = array(
                'user_id'=>$user_id,
                'username'=>I('username'),
                'chinese_name'=>I('chinese_name'),
                'english_name'=>I('english_name'),
                'email'=>trim(I('email')),
                'mail_template_params'=>json_encode($params),
            );
            if(I('password','')!=''){
                $data['password'] = md5(I('password',''));
            }
            
            $this->_save($data);
            $this->success('编辑成功', 'userList');
        }

        $user_info = D('user')->alias('u')->where(array('user_id'=>$user_id))->join('JOIN __USERS_PROFILE__ up ON up.profile_id=u.user_profile')->find();
        
        $this->assign('user_info', $user_info);
        $this->display();
    }
}
