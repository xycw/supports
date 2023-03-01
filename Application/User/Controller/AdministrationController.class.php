<?php
namespace User\Controller;

use User\Controller\UserController;
use Common\Controller\CommonController;
use User\Model\UserModel;

class AdministrationController extends UserController {

    public function userListAction() {
        $list = D('user')->alias('u')->join('LEFT JOIN __USERS_PROFILE__ up ON up.profile_id=u.user_profile')->order('user_id')->select();
        //var_dump($list);
        $this->assign('list', $list);
        $this->display();
    }

    public function editAction(){
        $user_id = I('user_id');
        
        if(IS_POST){
            if($user_id==0){
                if(I('password','')=='') $this->error('请输入登录密码!');
                if(I('username','')=='') $this->error('请输入登录名!');
                $row = D('user')->where(array('username'=>I('username')))->find();
                if(empty($row)==false){
                    $this->error('系统已存此登录名!');
                }
            }
            $status = I('status');
            if($status == 0){
                $count = M('Site')->where(array('system_tuiguangy' => $user_id, 'status' => 1))->count();
                if($count > 0) $this->error('还有归属此用户的网站，请转移网站后再设置为禁止状态！');
            }

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
                'user_profile'=>I('profile'),
                'status'=>$status,
                'mail_template_params'=>json_encode($params),
            );
            
            if(I('password','')!=''){
                $data['password'] = md5(I('password',''));
            }

            $this->_save($data);
            $this->success('编辑成功', 'userList');
        }

        $user_info = D('user')->alias('u')->where(array('user_id'=>$user_id))->join('JOIN __USERS_PROFILE__ up ON up.profile_id=u.user_profile')->find();
        
        $user_profile = D('users_profile')->select();
        $option_profile = array();
        foreach ($user_profile as $entry){
            $option_profile[$entry['profile_id']] = $entry['profile_name'];
        }

        $this->assign('option_profile_selected', $user_info['user_profile']);
        $this->assign('option_profile', $option_profile);
        
        $this->assign('option_status',array('0'=>'禁止','1'=>'允许'));
        $this->assign('option_status_selected', $user_info['status']);
        $this->assign('user_info', $user_info);
        $this->display();
    }
    
    public function addAction(){
        
        $user_profile = D('users_profile')->select();
        $option_profile = array();
        foreach ($user_profile as $entry){
            $option_profile[$entry['profile_id']] = $entry['profile_name'];
        }
        $this->assign('option_profile_selected', 1);
        $this->assign('option_profile', $option_profile);
        $this->assign('option_status',array('0'=>'禁止','1'=>'允许'));      
        $this->assign('option_status_selected', 1);
        $this->display('edit');
    }
    
    public function permissionAction(){
        $user_id = I('user_id');
        $user_model = new UserModel();
        $user_info  = $user_model->relation(true)->where(array('user_id'=>$user_id))->find();
//        var_dump($user_info);exit;
        if($user_info['user_profile']==1)
            $this->success('你的角色是超级管理员,拥有系统的所有权限,无需要分配!', U('User/Administration/userList'));
        
        if(IS_POST){
            D('users_to_site')->where(array('user_id'=>$user_id))->delete();
            $site = I('site_id', array());
            $site = array_unique($site);
            foreach ($site as $site_id){
               D('users_to_site')->add(array('site_id'=>$site_id, 'user_id'=>$user_id));
            }
            $this->success('保存成功', 'userList');
        }
        
        $where = array('status'=>array('eq',1));
        $site_list  = D('site')->alias('s')->join(array('LEFT JOIN __PROMOTION_DEPARTMENT__ p ON s.system_depart=p.department_id'))->field('s.site_id,s.site_name,p.department_name')->where($where)
                         ->order('s.type asc,s.site_id asc')
                         ->select();
        foreach ($site_list as $k=>$entry){
            $user = D('users_to_site')->alias('u2s')->join(array('__USERS__ u ON u.user_id=u2s.user_id'))->where(array('site_id'=>$entry['site_id']))->select();
            $site_list[$k]['user'] = $user;
        }
        $this->assign('data_site', $site_list);
        $this->assign('user_info', $user_info);
        $this->display();
    }

    public function send_mailAction(){
        $result = send_mail(I('email'), I('name'), '测试', '测试');
        echo json_encode($result);
        exit();
    }

    public function send_notice_mailAction(){
        $body = '后台地址：https://2022121501.umieshop.com<br>商户号：2022121501<br>账号：' . I('saas_username') . '<br>密码：' . I('saas_password');
        $result = send_mail(I('email'), I('name'), '新商城账号信息', $body);
        echo json_encode($result);
        exit();
    }
}
